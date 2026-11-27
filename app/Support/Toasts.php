<?php

namespace App\Support;

use function Livewire\on;
use function Livewire\store;

/**
 * After each Livewire action the browser gets a "stk-toast" event (2026-11-27):
 *  - the flash message of the action (session success / error), or
 *  - the first validation error, or
 *  - a generic "Enregistré / Supprimé avec succès" for save / delete actions that say nothing.
 * When the action redirects, the next page shows the flash message itself (layouts/app).
 */
class Toasts
{
    public const SAVE = '/^(save|store|enregistrer|addPayment|marquer|valider|toutCharger|setPlan|duplicate|convert|import)/i';

    public const DELETE = '/^(delete|destroy|supprimer|removePayment|deleteSelected|removeLogo)/i';

    public static function register(): void
    {
        on('call', function ($component, $method) {
            if (is_string($method) && ! str_starts_with($method, '$')) {
                store($component)->push('stkCalls', $method);
            }
        });

        on('dehydrate', function ($component, $context) {
            if ($context->isMounting()) {
                return;
            }
            $calls = store($component)->get('stkCalls', []);
            if ($calls === [] || store($component)->get('redirect')) {
                return;
            }
            // added straight to the response: Livewire has already collected the component's events at this point
            $toast = fn (string $type, string $message) => $context->pushEffect('dispatches', ['name' => 'stk-toast', 'params' => ['type' => $type, 'message' => $message]]);
            $new = (array) session()->get('_flash.new', []);
            $msg = fn ($k) => in_array($k, $new, true) && is_string(session($k)) && session($k) !== '' ? session($k) : null;
            $isSave = (bool) array_filter($calls, fn ($c) => preg_match(self::SAVE, $c));
            $isDelete = (bool) array_filter($calls, fn ($c) => preg_match(self::DELETE, $c));

            if ($m = $msg('error')) {
                $toast('error', $m);
            } elseif (($isSave || $isDelete) && $component->getErrorBag()->isNotEmpty()) {
                $toast('error', 'Vérifiez le formulaire : '.$component->getErrorBag()->first());
            } elseif ($m = $msg('success')) {
                $toast('success', $m);
            } elseif ($isDelete) {
                $toast('success', 'Supprimé avec succès.');
            } elseif ($isSave) {
                $toast('success', 'Enregistré avec succès.');
            }
        });
    }
}
