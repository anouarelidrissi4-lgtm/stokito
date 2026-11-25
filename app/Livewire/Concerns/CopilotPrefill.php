<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * Opens a document form already filled by the Copilot (2026-11-25): ?copilot=<key> points to a payload the
 * Copilot stored for this user. Nothing is saved: the user checks the form and clicks "Enregistrer".
 */
trait CopilotPrefill
{
    protected function applyCopilotPrefill(): void
    {
        $key = (string) request()->query('copilot', '');
        if ($key === '' || ! preg_match('/^[A-Za-z0-9]{8,40}$/', $key)) {
            return;
        }
        $p = Cache::pull('copilot:'.auth()->id().':'.$key);
        if (! is_array($p) || ($p['form'] ?? null) !== static::class) {
            return;
        }
        if (! empty($p['party_field']) && property_exists($this, $p['party_field'])) {
            $this->{$p['party_field']} = $p['party_id'];
        }
        if (! empty($p['notes']) && property_exists($this, 'notes')) {
            $this->notes = $p['notes'];
        }
        if (! empty($p['items'])) {
            $this->items = $p['items'];
        }
        // extra fields (an invoice read in the Copilot chat: n° facture fournisseur, date, VAT, supplier to create…)
        foreach ((array) ($p['props'] ?? []) as $prop => $value) {
            if (property_exists($this, $prop)) {
                $this->{$prop} = $value;
            }
        }
        session()->flash('success', 'Préparé par le Copilot : vérifiez puis enregistrez.');
    }
}
