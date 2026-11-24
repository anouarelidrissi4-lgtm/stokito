<?php

namespace App\Support;

use App\Models\Devis;
use App\Models\Facture;
use App\Models\Societe;
use Illuminate\Support\Facades\URL;

/**
 * "Envoyer sur WhatsApp" for quotes and invoices (2026-11-24): opens WhatsApp with a ready message and
 * a signed link to the PDF (route documents.public, valid 90 days). The customer needs no account.
 */
class WhatsAppShare
{
    public static function pdfLink(Devis|Facture $doc): string
    {
        return URL::temporarySignedRoute('documents.public', now()->addDays(90), [
            'type' => $doc instanceof Devis ? 'devis' : 'facture',
            'id' => $doc->id,
        ]);
    }

    public static function isLate(Facture $f): bool
    {
        return ((float) $f->total_ttc - (float) $f->montant_paye) > 0.009 && $f->due_date && $f->due_date->isPast();
    }

    public static function message(Devis|Facture $doc): string
    {
        $s = Societe::current();
        $cur = $s->currency ?: 'EUR';
        $ttc = Fmt::money($doc->total_ttc).' '.$cur.' TTC';
        $hello = 'Bonjour'.($doc->client->name ?? '' ? ' '.$doc->client->name : '').",\n\n";
        $link = self::pdfLink($doc);
        if ($doc instanceof Devis) {
            $body = "Voici notre devis {$doc->numero} d’un montant de {$ttc}"
                .($doc->valid_until ? ', valable jusqu’au '.$doc->valid_until->format('d/m/Y') : '').".\n"
                ."Vous pouvez le consulter et le télécharger ici :\n{$link}";
        } else {
            $body = "Voici notre facture {$doc->numero} d’un montant de {$ttc}"
                .(((float) $doc->total_ttc - (float) $doc->montant_paye) <= 0.009
                    ? ' (réglée, merci)'
                    : ($doc->due_date ? ', à régler avant le '.$doc->due_date->format('d/m/Y') : '')).".\n"
                ."Vous pouvez la consulter et la télécharger ici :\n{$link}";
        }

        return $hello.$body."\n\nCordialement,\n".$s->name;
    }

    /** wa.me link; without a usable phone number WhatsApp lets the user pick the contact. */
    public static function url(Devis|Facture $doc): string
    {
        $phone = Relance::phone($doc->client->phone ?? null);

        return 'https://wa.me/'.($phone ?? '').'?text='.rawurlencode(self::message($doc));
    }
}
