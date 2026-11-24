<?php

namespace App\Support;

use App\Mail\DocumentMail;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\Societe;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/** Default texts + sending of a quote / invoice by email (2026-11-24). */
class DocumentSender
{
    public static function defaults(Devis|Facture $doc): array
    {
        $s = Societe::current();
        $cur = $s->currency ?: 'EUR';
        $ttc = Fmt::money($doc->total_ttc).' '.$cur;
        if ($doc instanceof Devis) {
            $sujet = 'Devis '.$doc->numero.' – '.$s->name;
            $corps = "Bonjour,\n\nVeuillez trouver ci-joint notre devis {$doc->numero} d’un montant de {$ttc} TTC"
                .($doc->valid_until ? ', valable jusqu’au '.$doc->valid_until->format('d/m/Y') : '').".\n\n"
                ."Pour l’accepter, il suffit de nous le renvoyer signé avec la mention « Bon pour accord ».\n\nCordialement,\n{$s->name}";
        } elseif (($r = self::relance($doc, 1)) !== null) {
            return ['to' => (string) ($doc->client->email ?? '')] + $r; // overdue: the default text is a reminder
        } else {
            $sujet = 'Facture '.$doc->numero.' – '.$s->name;
            $corps = "Bonjour,\n\nVeuillez trouver ci-joint notre facture {$doc->numero} d’un montant de {$ttc} TTC"
                .($doc->due_date ? ', à régler avant le '.$doc->due_date->format('d/m/Y') : '').".\n\nCordialement,\n{$s->name}";
        }

        return ['to' => (string) ($doc->client->email ?? ''), 'sujet' => $sujet, 'message' => $corps];
    }

    /** Reminder subject + text for an overdue invoice (level 1 = J+7, level 2 = J+30); null when nothing is due. */
    public static function relance(Facture $f, int $niveau): ?array
    {
        $reste = round((float) $f->total_ttc - (float) $f->montant_paye, 2);
        if ($reste <= 0.009 || ! $f->due_date || ! $f->due_date->isPast()) {
            return null;
        }
        $s = Societe::current();
        $cur = $s->currency ?: 'EUR';
        $base = "la facture {$f->numero} du ".$f->date->format('d/m/Y').' ('.Fmt::money($f->total_ttc).' '.$cur.' TTC), échue le '.$f->due_date->format('d/m/Y')
            .', présente un reste à payer de '.Fmt::money($reste).' '.$cur;
        if ($niveau <= 1) {
            $sujet = 'Rappel : facture '.$f->numero.' – '.$s->name;
            $corps = "Bonjour,\n\nSauf erreur de notre part, {$base}.\n\n"
                ."Si le règlement est déjà parti, merci de ne pas tenir compte de ce message. Sinon, pourriez-vous nous indiquer la date de paiement prévue ? La facture est jointe à cet email.\n\nCordialement,\n{$s->name}";
        } else {
            $sujet = 'Deuxième relance : facture '.$f->numero.' – '.$s->name;
            $fr = ($s->country_code ?? '') === 'FR';
            $corps = "Bonjour,\n\nMalgré notre précédent rappel, {$base}.\n\n"
                .'Nous vous remercions de procéder au règlement dans les meilleurs délais.'
                .($fr && $s->penalites_taux ? ' Conformément à nos conditions, des pénalités de retard ('.rtrim($s->penalites_taux, '. ').') peuvent s’appliquer' : '')
                .($fr && $s->indemnite_40 ? ($fr && $s->penalites_taux ? ', ainsi que' : ' Conformément à nos conditions, peut s’appliquer').' l’indemnité forfaitaire de 40 € pour frais de recouvrement (clients professionnels)' : '')
                .($fr && ($s->penalites_taux || $s->indemnite_40) ? '.' : '')
                ."\n\nSi le règlement est déjà parti, merci de ne pas tenir compte de ce message.\n\nCordialement,\n{$s->name}";
        }

        return ['sujet' => $sujet, 'message' => $corps];
    }

    /** @return string|null error message, null when sent */
    public static function send(Devis|Facture $doc, string $to, string $sujet, string $message, bool $withPdf = true): ?string
    {
        $user = auth()->user();
        if ($user->is_demo) {
            return 'L’envoi d’emails est désactivé dans la démo. Créez un compte gratuit pour envoyer vos documents.';
        }
        $kH = 'doc-mail-h:'.$user->id;
        $kD = 'doc-mail-d:'.$user->id;
        if (RateLimiter::tooManyAttempts($kH, 20) || RateLimiter::tooManyAttempts($kD, 100)) {
            return 'Limite d’envoi atteinte (20 emails par heure, 100 par jour). Réessayez plus tard.';
        }

        $s = Societe::current();
        $pdf = null;
        if ($withPdf) {
            $pdf = ($doc instanceof Devis ? DocumentPdf::devis($doc) : DocumentPdf::facture($doc))->output();
        }
        try {
            Mail::to($to)->send(new DocumentMail($sujet, $message, $s->name ?: 'Stokito', $s->email ?: $user->email, $pdf, $withPdf ? $doc->numero.'.pdf' : null));
        } catch (\Throwable $e) {
            report($e);

            return 'L’email n’a pas pu partir. Vérifiez l’adresse et réessayez dans quelques minutes.';
        }
        RateLimiter::hit($kH, 3600);
        RateLimiter::hit($kD, 86400);

        if ($doc instanceof Devis) {
            $doc->update(['sent_at' => now(), 'status' => $doc->status === 'draft' ? 'sent' : $doc->status]);
        } else {
            $doc->update(['sent_at' => now(), 'status' => $doc->status === 'brouillon' ? 'envoyee' : $doc->status]);
        }

        return null;
    }
}
