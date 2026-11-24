<?php

namespace App\Livewire\Concerns;

use App\Support\DocumentSender;

/** "Envoyer par email" modal for the devis / factures lists (2026-11-24). The component defines sendModelClass(). */
trait SendsDocument
{
    public bool $showSend = false;

    public ?int $sendId = null;

    public string $sendTo = '';

    public string $sendSujet = '';

    public string $sendMessage = '';

    public function openSend(int $id): void
    {
        $doc = ($this->sendModelClass())::with('client')->findOrFail($id);
        $d = DocumentSender::defaults($doc);
        $this->sendId = $doc->id;
        $this->sendTo = $d['to'];
        $this->sendSujet = $d['sujet'];
        $this->sendMessage = $d['message'];
        $this->resetValidation();
        $this->showSend = true;
    }

    public function sendDocument(): void
    {
        $this->validate([
            'sendTo' => 'required|email:rfc|max:255',
            'sendSujet' => 'required|string|max:200',
            'sendMessage' => 'required|string|max:5000',
        ], [], ['sendTo' => 'adresse email', 'sendSujet' => 'objet', 'sendMessage' => 'message']);

        $doc = ($this->sendModelClass())::with('client')->findOrFail($this->sendId);
        $err = DocumentSender::send($doc, trim($this->sendTo), $this->sendSujet, $this->sendMessage);
        if ($err) {
            $this->addError('sendTo', $err);

            return;
        }

        // remember the address on the customer when it had none
        if ($doc->client && ! $doc->client->email) {
            $doc->client->update(['email' => trim($this->sendTo)]);
        }
        $this->showSend = false;
        session()->flash('success', $doc->numero.' envoyé à '.trim($this->sendTo).'.');
    }
}
