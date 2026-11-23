<?php

namespace App\Support;

use App\Models\Facture;
use App\Models\Societe;

/** WhatsApp reminder link for an unpaid invoice (opens WhatsApp with a ready message). */
class Relance
{
    /** Moroccan phone -> international digits for wa.me (0612.. -> 212612..), null if unusable. */
    public static function phone(?string $raw): ?string
    {
        $d = preg_replace('/\D/', '', (string) $raw);
        if (str_starts_with($d, '00')) {
            $d = substr($d, 2);
        }
        if (str_starts_with($d, '0') && strlen($d) === 10) {
            $d = '212'.substr($d, 1);
        }
        if (strlen($d) === 9 && in_array($d[0], ['5', '6', '7'], true)) {
            $d = '212'.$d;
        }

        return strlen($d) >= 11 ? $d : null;
    }

    public static function whatsappUrl(Facture $f): ?string
    {
        $reste = (float) $f->total_ttc - (float) $f->montant_paye;
        $phone = self::phone($f->client->phone ?? null);
        if ($reste <= 0.009 || ! $phone) {
            return null;
        }
        $s = Societe::current();
        $cur = $s->currency ?: 'MAD';
        $late = $f->due_date && $f->due_date->isPast();
        $msg = 'Bonjour '.($f->client->name ?? '').",\n\n"
            .'Nous vous rappelons que la facture '.$f->numero.' du '.$f->date->format('d/m/Y')
            .' ('.Fmt::money($f->total_ttc).' '.$cur.' TTC) présente un reste à payer de '.Fmt::money($reste).' '.$cur
            .($f->due_date ? ($late ? ', échue depuis le '.$f->due_date->format('d/m/Y') : ', échéance le '.$f->due_date->format('d/m/Y')) : '')
            .".\n\nMerci de nous indiquer la date de règlement prévue. Nous pouvons vous renvoyer la facture si besoin.\n\n"
            .'Cordialement,'."\n".$s->name;

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($msg);
    }
}
