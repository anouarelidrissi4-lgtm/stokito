<?php

namespace App\Livewire\Copilot;

use App\Models\AiScan;
use App\Support\Copilot;
use App\Support\CopilotException;
use App\Support\InvoiceScanException;
use App\Support\InvoiceScanner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Copilot IA chat (2026-11-25). The conversation lives in the session; every answer is logged in ai_scans. */
class Chat extends Component
{
    use WithFileUploads;

    public array $thread = [];

    public string $input = '';

    /** Supplier invoice dropped in the chat (photo or PDF): read by the scanner, turned into a pre-filled purchase order. */
    public $attachment = null;

    public function updatedAttachment(): void
    {
        $u = auth()->user();
        abort_unless($u->hasPermission('copilot'), 403);
        $this->resetErrorBag('attachment');
        $this->validate(['attachment' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240'], [
            'attachment.mimes' => 'Envoyez une photo (JPG, PNG) ou un PDF.',
            'attachment.max' => 'Fichier trop lourd (10 Mo maximum).',
        ]);
        $name = mb_substr((string) $this->attachment->getClientOriginalName(), 0, 80);
        $this->thread[] = ['role' => 'user', 'text' => '📎 Facture jointe : '.$name];
        $scanner = app(InvoiceScanner::class);
        $limit = (int) config('services.anthropic.scans_per_month', 30);
        $block = match (true) {
            ! $u->hasPermission('gestion_commandes_fournisseurs') => 'Vous n’avez pas le droit de créer des bons de commande : demandez-le à votre administrateur.',
            $u->isDemo() => 'La lecture de factures est désactivée dans la démo. Créez votre compte gratuit pour l’essayer sur vos factures.',
            ! $scanner->isConfigured() => 'La lecture de factures n’est pas encore activée sur ce serveur.',
            AiScan::usedThisMonth() >= $limit => "Vous avez utilisé vos {$limit} lectures de factures de ce mois.",
            default => null,
        };
        if ($block) {
            $this->dropAttachment();
            $this->thread[] = ['role' => 'assistant', 'text' => $block, 'actions' => [], 'error' => true];
            $this->persist();

            return;
        }

        @set_time_limit(180);
        $log = ['user_id' => $u->id, 'kind' => 'facture_fournisseur'];
        try {
            $res = $scanner->scan($this->attachment->getRealPath(), (string) $this->attachment->getMimeType());
        } catch (InvoiceScanException $e) {
            AiScan::create($log + ['status' => 'failed', 'input_tokens' => $e->inputTokens, 'output_tokens' => $e->outputTokens,
                'cost_usd' => InvoiceScanner::cost($e->inputTokens, $e->outputTokens), 'error' => mb_substr($e->getPrevious()?->getMessage() ?? $e->getMessage(), 0, 2000)]);
            $this->thread[] = ['role' => 'assistant', 'text' => $e->getMessage(), 'actions' => [], 'error' => true];
            $this->persist();

            return;
        } finally {
            $this->dropAttachment();
        }
        AiScan::create($log + ['status' => 'ok', 'input_tokens' => $res['input_tokens'], 'output_tokens' => $res['output_tokens'],
            'cost_usd' => InvoiceScanner::cost($res['input_tokens'], $res['output_tokens'])]);

        $d = $res['data'];
        if (empty($d['est_une_facture']) || empty($d['lignes'])) {
            $this->thread[] = ['role' => 'assistant', 'text' => 'Je ne trouve aucune ligne d’article : ce document ne ressemble pas à une facture fournisseur.', 'actions' => [], 'error' => true];
            $this->persist();

            return;
        }

        $f = $d['fournisseur'] ?? [];
        $known = InvoiceScanner::matchFournisseur($f);
        $lines = InvoiceScanner::matchLines($d['lignes']);
        $rates = collect($lines)->pluck('scan.tva')->filter(fn ($r) => $r !== null)->map(fn ($r) => (string) (float) $r)->countBy()->sortDesc();
        $props = ['scanRun' => 1];
        if (! $known && trim((string) ($f['nom'] ?? '')) !== '') {
            $props['scanSupplier'] = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $f);
        }
        if (! empty($d['date']) && ($dt = \DateTime::createFromFormat('!Y-m-d', $d['date']))) {
            $props['date'] = $dt->format('Y-m-d');
        }
        if (! empty($d['numero_facture'])) {
            $props['reference_fournisseur'] = mb_substr(trim($d['numero_facture']), 0, 255);
        }
        if ($rates->isNotEmpty()) {
            $props['tva_rate'] = (float) $rates->keys()->first();
        }
        if (! empty($d['remise_globale_ht']) && (float) $d['remise_globale_ht'] > 0) {
            $props += ['remise_type' => 'fixed', 'remise_value' => round((float) $d['remise_globale_ht'], 2)];
        }
        $c = collect($lines)->countBy(fn ($l) => $l['scan']['status']);
        $props['scanMessage'] = count($lines).' ligne(s) lue(s) : '.($c['ok'] ?? 0).' produit(s) reconnu(s)'
            .(($c['check'] ?? 0) ? ', '.$c['check'].' à vérifier' : '').(($c['new'] ?? 0) ? ', '.$c['new'].' à créer' : '')
            .'. Vérifiez les quantités et les prix, puis enregistrez.';

        $key = Str::random(24);
        Cache::put('copilot:'.$u->id.':'.$key, ['form' => \App\Livewire\CommandeFournisseurs\Form::class, 'party_field' => 'fournisseur_id',
            'party_id' => $known?->id, 'items' => $lines, 'props' => $props], now()->addHours(6));

        $cur = $d['devise'] ?: (\App\Models\Societe::current()->currency ?: 'EUR');
        $supplier = $known?->name ?? ($f['nom'] ?? 'fournisseur non lu');
        $text = "**Facture lue** : {$supplier}"
            .(! empty($d['numero_facture']) ? ' · n° '.$d['numero_facture'] : '')
            .(! empty($props['date']) ? ' · '.\Illuminate\Support\Carbon::parse($props['date'])->format('d/m/Y') : '')."\n\n"
            .'- '.count($lines).' ligne(s) : '.($c['ok'] ?? 0).' produit(s) reconnu(s)'
            .(($c['check'] ?? 0) ? ', '.$c['check'].' à vérifier' : '').(($c['new'] ?? 0) ? ', '.$c['new'].' à créer' : '')."\n"
            .(isset($d['total_ht']) && $d['total_ht'] !== null ? '- Total HT : '.\App\Support\Fmt::money($d['total_ht']).' '.$cur."\n" : '')
            .(! $known && ! empty($props['scanSupplier']) ? "- Fournisseur pas encore dans votre liste : vous pourrez le créer en un clic.\n" : '')
            ."\nOuvrez le bon de commande pré-rempli, vérifiez puis enregistrez.";
        $this->thread[] = ['role' => 'assistant', 'text' => $text, 'tools' => ['scanner_facture'],
            'actions' => [['label' => 'Ouvrir le bon de commande pré-rempli', 'url' => route('commande-fournisseurs.create', ['copilot' => $key])]]];
        $this->persist();
        $this->dispatch('copilot-scroll');
    }

    protected function dropAttachment(): void
    {
        try {
            $this->attachment?->delete();
        } catch (\Throwable) {
        }
        $this->attachment = null;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermission('copilot'), 403);
        $this->thread = session('copilot.thread', []);
    }

    public function ask(string $text): void
    {
        $this->input = $text;
        $this->send();
    }

    public function send(): void
    {
        abort_unless(auth()->user()->hasPermission('copilot'), 403);
        $text = trim(mb_substr($this->input, 0, 1500));
        if ($text === '') {
            return;
        }
        $copilot = app(Copilot::class);
        $limit = (int) config('services.anthropic.copilot_per_month', 150);
        $block = match (true) {
            auth()->user()->isDemo() => 'Le Copilot est désactivé dans la démo. Créez votre compte gratuit pour l’essayer avec vos propres produits et clients.',
            ! $copilot->isConfigured() => 'Le Copilot n’est pas encore activé sur ce serveur.',
            AiScan::usedThisMonth('copilot') >= $limit => "Vous avez utilisé les {$limit} messages Copilot de ce mois. Le compteur repart le 1er du mois prochain.",
            default => null,
        };
        $history = $this->thread;
        $this->thread[] = ['role' => 'user', 'text' => $text];
        $this->input = '';
        if ($block) {
            $this->thread[] = ['role' => 'assistant', 'text' => $block, 'actions' => [], 'error' => true];
            $this->persist();

            return;
        }

        @set_time_limit(150);
        $log = ['user_id' => auth()->id(), 'kind' => 'copilot'];
        try {
            $r = $copilot->reply(array_values(array_filter($history, fn ($m) => empty($m['error']))), $text);
            AiScan::create($log + ['status' => 'ok', 'input_tokens' => $r['input_tokens'], 'output_tokens' => $r['output_tokens'],
                'cost_usd' => Copilot::cost($r['input_tokens'], $r['output_tokens'])]);
            $this->thread[] = ['role' => 'assistant', 'text' => $r['text'], 'actions' => $r['actions'], 'tools' => $r['tools'] ?? []];
        } catch (CopilotException $e) {
            AiScan::create($log + ['status' => 'failed', 'input_tokens' => $e->inputTokens, 'output_tokens' => $e->outputTokens,
                'cost_usd' => Copilot::cost($e->inputTokens, $e->outputTokens), 'error' => mb_substr($e->getPrevious()?->getMessage() ?? $e->getMessage(), 0, 2000)]);
            $this->thread[] = ['role' => 'assistant', 'text' => $e->getMessage(), 'actions' => [], 'error' => true];
        }
        $this->persist();
    }

    public function reset_(): void
    {
        $this->thread = [];
        $this->persist();
    }

    protected function persist(): void
    {
        $this->thread = array_slice($this->thread, -40);
        session(['copilot.thread' => $this->thread]);
    }

    public function render()
    {
        return view('livewire.copilot.chat', [
            'left' => max(0, (int) config('services.anthropic.copilot_per_month', 150) - AiScan::usedThisMonth('copilot')),
        ])->layout('layouts.app');
    }
}
