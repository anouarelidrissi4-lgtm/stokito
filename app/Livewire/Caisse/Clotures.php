<?php

namespace App\Livewire\Caisse;

use App\Models\CaisseCloture;
use App\Models\CaisseJournal;
use App\Models\CaisseTicket;
use App\Support\Caisse;
use App\Support\CaisseException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/** Z closures (2026-11-25): close the day, list day/month/year closures, archives, integrity, compliance info. */
class Clotures extends Component
{
    use WithFileUploads, WithPagination;

    public string $type = 'jour';

    public ?array $integrity = null;

    public $archiveFile = null;

    public ?array $archiveCheck = null;

    public function mount(): void
    {
        Pos::guard('caisse_cloturer');
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function cloturer(): void
    {
        Pos::guard('caisse_cloturer');
        try {
            $c = Caisse::cloturerJour();
        } catch (CaisseException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }
        session()->flash('success', "Clôture Z n° {$c->sequence} enregistrée.");
    }

    public function verify(): void
    {
        Pos::guard('caisse_cloturer');
        $this->integrity = Caisse::verifier();
        Caisse::journal('CONTROLE_INTEGRITE', $this->integrity['ok'] ? 'Contrôle d’intégrité : conforme' : 'Contrôle d’intégrité : anomalies détectées',
            ['ok' => $this->integrity['ok'], 'erreurs' => count($this->integrity['errors'])]);
    }

    /** Checks an archive ZIP given back by the user (signature + file fingerprints). */
    public function updatedArchiveFile(): void
    {
        Pos::guard('caisse_cloturer');
        $this->validate(['archiveFile' => 'required|file|mimes:zip|max:51200'], ['archiveFile.mimes' => 'Choisissez le fichier .zip téléchargé depuis Stokito.']);
        $this->archiveCheck = Caisse::verifierArchive($this->archiveFile->getRealPath());
        Caisse::journal('CONTROLE_ARCHIVE', 'Contrôle d’une archive : '.($this->archiveCheck['ok'] ? 'conforme' : 'non conforme'),
            ['ok' => $this->archiveCheck['ok'], 'periode' => $this->archiveCheck['manifest']['cloture']['periode'] ?? null]);
        $this->archiveFile = null;
    }

    public function render()
    {
        return view('livewire.caisse.clotures', [
            'clotures' => CaisseCloture::where('type', $this->type)->orderByDesc('sequence')->paginate(30),
            'open' => Caisse::nonClotures(),
            'currency' => \App\Models\Societe::current()->currency ?: 'EUR',
            'activation' => CaisseJournal::where('code', 'ACTIVATION')->orderBy('sequence')->value('created_at'),
            'firstTicket' => CaisseTicket::orderBy('sequence')->value('created_at'),
            'france' => (\App\Models\Societe::current()->country_code ?: 'MA') === 'FR',
        ])->layout('layouts.app');
    }
}
