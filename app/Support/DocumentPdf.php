<?php

namespace App\Support;

use App\Models\Devis;
use App\Models\Facture;

/** Builds the PDF of a quote / an invoice (used by the download routes and by the email). 2026-11-24 */
class DocumentPdf
{
    public static function devis(Devis $devis): \Barryvdh\DomPDF\PDF
    {
        $devis->loadMissing(['client', 'items.product']);
            $societe = \App\Models\Societe::current();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.document', [
                'societe' => $societe,
                'title' => 'Devis',
                'numero' => $devis->numero,
                'date' => $devis->date->format('d/m/Y'),
                'dueDate' => null,
                'validUntil' => optional($devis->valid_until)->format('d/m/Y'),
                'status' => $devis->status,
                'partyLabel' => 'Client',
                'party' => $devis->client,
                'items' => $devis->items->map(fn ($i) => [
                    'name' => $i->product->name ?? '-',
                    'unit' => $i->product->unit ?? '',
                    'quantity' => $i->quantity,
                    'unit_price' => $i->unit_price,
                    'tva_rate' => $i->tva_rate ?? $devis->tva_rate,
                    'total' => $i->total,
                ]),
                'showPrices' => true,
                'totals' => [
                    'brut' => $devis->montantBrutHt(),
                    'remise' => $devis->montantRemise(),
                    'ht' => $devis->total_ht,
                    'tva_rate' => $devis->tva_rate,
                    'groups' => $devis->vatTotals()['groups'],
                    'ttc' => $devis->total_ttc,
                ],
                'notes' => $devis->notes,
            ]);

        return $pdf;
    }

    public static function facture(Facture $facture): \Barryvdh\DomPDF\PDF
    {
        $facture->loadMissing(['client', 'items.product']);
            $societe = \App\Models\Societe::current();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.document', [
                'societe' => $societe,
                'title' => 'Facture',
                'numero' => $facture->numero,
                'date' => $facture->date->format('d/m/Y'),
                'dueDate' => optional($facture->due_date)->format('d/m/Y'),
                'status' => str_replace('_', ' ', $facture->status),
                'showHeader' => $societe->facture_type !== 'sans_entete',
                'partyLabel' => 'Client',
                'party' => $facture->client,
                'items' => $facture->items->map(fn ($i) => [
                    'name' => ($i->product->name ?? '-').($societe->show_ref_facture && ($i->product->reference ?? null) ? ' ('.$i->product->reference.')' : ''),
                    'unit' => $i->product->unit ?? '',
                    'quantity' => $i->quantity,
                    'unit_price' => $i->unit_price,
                    'tva_rate' => $i->tva_rate ?? $facture->tva_rate,
                    'total' => $i->total,
                ]),
                'showPrices' => true,
                'totals' => [
                    'brut' => $facture->montantBrutHt(),
                    'remise' => $facture->montantRemise(),
                    'ht' => $facture->total_ht,
                    'tva_rate' => $facture->tva_rate,
                    'groups' => $facture->vatTotals()['groups'],
                    'ttc' => $facture->total_ttc,
                ],
                'notes' => $facture->notes,
                'facture' => $facture,
                'devisRef' => $facture->devis?->numero,
            ]);

        return $pdf;
    }
}
