<?php

namespace App\Support;

use App\Models\BonLivraison;
use App\Models\Facture;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Sales take products out of stock (added 2026-11-22, owner decision):
 *  - a bon de livraison when it is "Livré" (delivered);
 *  - a facture created WITHOUT a BL (a facture made from a BL never touches stock,
 *    the BL already did).
 * sync() first gives back what this document took before, then takes the current
 * lines again, so editing / un-delivering / deleting a document always leaves the
 * stock right. Documents created before the go-live date are never touched, so the
 * stock of existing clients is not reduced a second time for old sales.
 */
class StockSync
{
    public const SINCE = '2026-11-22 00:00:00';

    public static function bonLivraison(BonLivraison $bl, bool $deleting = false): void
    {
        self::sync('bon_livraison', $bl, ! $deleting && $bl->status === 'delivered', $bl->items()->get());
    }

    public static function facture(Facture $f, bool $deleting = false): void
    {
        self::sync('facture', $f, ! $deleting && ! $f->bon_livraison_id, $f->items()->get());
    }

    protected static function sync(string $type, $doc, bool $active, $items): void
    {
        if (! $doc->exists || ($doc->created_at && $doc->created_at->lt(self::SINCE))) {
            return;
        }

        DB::transaction(function () use ($type, $doc, $active, $items) {
            foreach (StockMovement::where('reference_type', $type)->where('reference_id', $doc->id)->get() as $m) {
                $q = Product::whereKey($m->product_id);
                $m->type === 'exit' ? $q->increment('stock_qty', $m->quantity) : $q->decrement('stock_qty', $m->quantity);
                $m->delete();
            }

            if (! $active) {
                return;
            }

            foreach ($items as $item) {
                if (! $item->product_id || (float) $item->quantity <= 0) {
                    continue;
                }
                Product::whereKey($item->product_id)->decrement('stock_qty', $item->quantity);
                StockMovement::create([
                    'product_id' => $item->product_id, 'type' => 'exit', 'quantity' => $item->quantity,
                    'reference_type' => $type, 'reference_id' => $doc->id,
                    'date' => optional($doc->date)->toDateString() ?? now()->toDateString(),
                    'notes' => ($type === 'facture' ? 'Facture ' : 'BL ').$doc->numero,
                ]);
            }
        });
    }
}
