<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Emails every reported exception to the platform mailbox (2026-11-24). Production only.
 * The same error (class + file + line) is sent at most once an hour, and 20 alerts a day at most.
 * Throttling uses the file cache so it still works when the database is down.
 */
class ErrorAlert
{
    protected static bool $busy = false;

    public static function send(Throwable $e): void
    {
        if (self::$busy || ! app()->isProduction()) {
            return;
        }
        self::$busy = true;
        try {
            $c = Cache::store('file');
            $sig = sha1(get_class($e).'|'.$e->getFile().'|'.$e->getLine());
            if (! $c->add('err-alert:'.$sig, 1, 3600)) {
                return;
            }
            $day = 'err-alert-day:'.date('Ymd');
            $c->add($day, 0, 90000);
            if ($c->increment($day) > 20) {
                return;
            }

            $base = base_path().'/';
            $rel = fn ($f) => str_replace($base, '', (string) $f);
            $u = auth()->user();
            $lines = [
                'Date : '.now()->format('d/m/Y H:i:s'),
                'Erreur : '.get_class($e),
                'Message : '.Str::limit($e->getMessage(), 1500),
                'Fichier : '.$rel($e->getFile()).':'.$e->getLine(),
            ];
            if (app()->runningInConsole()) {
                $lines[] = 'Contexte : console ('.implode(' ', array_slice($_SERVER['argv'] ?? [], 1)).')';
            } else {
                $r = request();
                $lines[] = 'Requête : '.$r->method().' '.$r->fullUrl();
                if ($r->headers->get('referer')) {
                    $lines[] = 'Page : '.$r->headers->get('referer');
                }
            }
            $lines[] = 'Utilisateur : '.($u ? '#'.$u->id.' projet #'.($u->project_id ?? '-').($u->is_demo ? ' (démo)' : '') : 'invité');
            $lines[] = '';
            $lines[] = 'Trace :';
            foreach (array_slice($e->getTrace(), 0, 12) as $i => $t) {
                $lines[] = '#'.$i.' '.$rel($t['file'] ?? '?').':'.($t['line'] ?? '?').' '.($t['class'] ?? '').($t['type'] ?? '').($t['function'] ?? '');
            }
            $lines[] = '';
            $lines[] = 'Même erreur : au plus un email par heure. Journal complet : storage/logs/laravel.log';

            Mail::raw(implode("\n", $lines), fn ($m) => $m->to(config('mail.from.address'))
                ->subject('[Stokito] Erreur : '.class_basename($e).' – '.Str::limit($e->getMessage(), 70)));
        } catch (Throwable) {
            // never let the alert itself break the request
        } finally {
            self::$busy = false;
        }
    }
}
