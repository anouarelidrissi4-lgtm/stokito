<?php

namespace App\Support;

use App\Models\Facture;
use App\Models\Societe;

/** WhatsApp reminder link for an unpaid invoice (opens WhatsApp with a ready message). */
class Relance
{
    /**
     * Phone -> international digits for wa.me, using the company's country (2026-11-24; was Morocco only):
     * 0612345678 -> 212612345678 in Morocco, 33612345678 in France. Null if unusable.
     */
    public static function phone(?string $raw, ?string $countryCode = null): ?string
    {
        $raw = trim((string) $raw);
        $d = preg_replace('/\D/', '', $raw);
        if (str_starts_with($raw, '+') || str_starts_with($d, '00')) {
            $d = ltrim(str_starts_with($d, '00') ? substr($d, 2) : $d, '0');

            return strlen($d) >= 10 ? $d : null;
        }
        $cc = $countryCode ?? (Societe::current()->country_code ?: 'MA');
        $dial = Countries::get($cc)['dial'] ?? null;
        if (! $dial) {
            return strlen($d) >= 11 ? $d : null;
        }
        if (str_starts_with($d, $dial) && strlen($d) >= strlen($dial) + 8) {
            return $d; // already international, without the +
        }
        // countries whose national numbers carry a trunk 0 that is dropped abroad
        if (in_array($cc, ['MA', 'FR', 'BE', 'CH', 'DZ'], true) && str_starts_with($d, '0')) {
            $d = substr($d, 1);
        }

        return strlen($d) >= 8 ? $dial.$d : null;
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
            .".\n\nLa facture : ".WhatsAppShare::pdfLink($f)."\n\nMerci de nous indiquer la date de règlement prévue.\n\n"
            .'Cordialement,'."\n".$s->name;

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($msg);
    }
}
