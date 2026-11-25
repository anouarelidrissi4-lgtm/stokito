<?php

namespace App\Livewire\Copilot;

use App\Models\AiScan;
use App\Support\Copilot;
use App\Support\CopilotException;
use Livewire\Component;

/** Copilot IA chat (2026-11-25). The conversation lives in the session; every answer is logged in ai_scans. */
class Chat extends Component
{
    public array $thread = [];

    public string $input = '';

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
