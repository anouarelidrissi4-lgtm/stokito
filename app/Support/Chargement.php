<?php

namespace App\Support;

use App\Models\BonDeChargement;
use App\Models\BonLivraison;

/** Every delivery note has its own loading slip, kept in line with the note's products (2026-11-27). */
class Chargement
{
    public static function enabled(): bool
    {
        return (bool) \App\Models\Societe::current()?->bons_chargement_enabled;
    }

    public static function syncFromBl(BonLivraison $bl): ?BonDeChargement
    {
        $bl->load('items.product');
        $bc = BonDeChargement::where('bon_livraison_id', $bl->id)->first();
        if (! $bc && $bl->chargement_supprime) {
            return null;   // the user deleted this note's loading slip: not created again (2026-11-28)
        }
        if (! $bc) {
            $bc = BonDeChargement::create(['numero' => Tenant::nextNumero(BonDeChargement::class, 'BC-'), 'bon_livraison_id' => $bl->id,
                'date' => $bl->date, 'status' => 'preparation', 'commercial' => auth()->user()?->name]);
            $bc->bonLivraisons()->syncWithoutDetaching([$bl->id]);
        } elseif (! $bc->date || ! $bc->date->equalTo($bl->date)) {
            $bc->update(['date' => $bl->date]);
        }
        // keep what was already loaded, product by product
        $loaded = [];
        foreach ($bc->lignes()->get() as $l) {
            $loaded[$l->product_id][] = $l->quantite_chargee;
        }
        $bc->lignes()->delete();
        foreach ($bl->items->values() as $i => $item) {
            $bc->lignes()->create([
                'product_id' => $item->product_id,
                'designation' => $item->product->name ?? '-',
                'unite' => $item->product->unit ?? null,
                'quantite_commandee' => (float) $item->quantity,
                'quantite_chargee' => ! empty($loaded[$item->product_id]) ? array_shift($loaded[$item->product_id]) : null,
                'position' => $i,
            ]);
        }
        $bc->refresh()->refreshStatus();

        return $bc;
    }

    /** delivery notes that have no slip yet (older ones, demo data) */
    public static function ensureForAll(): void
    {
        BonLivraison::whereDoesntHave('chargementAuto')->where('chargement_supprime', false)->get()->each(fn ($bl) => self::syncFromBl($bl));
    }
}
