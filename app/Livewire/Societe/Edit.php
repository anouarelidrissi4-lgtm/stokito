<?php
namespace App\Livewire\Societe;

use App\Models\Societe;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public Societe $societe;

    #[Validate('required|string|max:255')]
    public $name;

    public $country = 'Maroc';

    public $country_code = 'MA';

    public $city = '';

    public $address = '';

    public $phone = '';

    public $phone2 = '';

    public $fax = '';

    public $email = '';

    public $ice = '';

    public $identifiant_fiscal = '';

    public $registre_commerce = '';

    public $patente = '';

    public $cnss = '';

    public $conditions_paiement = '';

    public $currency = 'MAD';

    public $tva_rate_default = 20;

    public $qty_decimals = 2;

    public $price_decimals = 2;

    public $stock_threshold_default = 0;

    public $facture_type = 'sans_entete';

    public $stock_verification = false;

    public $show_ref_facture = true;

    public $show_ref_bl = true;

    public $bons_chargement_enabled = true;

    // French invoice mentions (2026-11-24)
    public $tva_franchise = false;

    public $penalites_taux = '';

    public $indemnite_40 = false;

    public $escompte_neant = false;

    public $tva_debits = false;

    public $nature_operation_default = '';

    public $devis_validite_jours = 30;

    #[Validate('nullable|image|max:2048')]
    public $logo;

    public function mount()
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->hasPermission('gestion_societe'), 403);

        $this->societe = Societe::current();
        $this->name = $this->societe->name;
        $this->country = $this->societe->country ?? 'Maroc';
        $this->country_code = $this->societe->country_code ?: 'MA';
        $this->city = $this->societe->city;
        $this->address = $this->societe->address;
        $this->phone = $this->societe->phone;
        $this->phone2 = $this->societe->phone2;
        $this->fax = $this->societe->fax;
        $this->email = $this->societe->email;
        $this->ice = $this->societe->ice;
        $this->identifiant_fiscal = $this->societe->identifiant_fiscal;
        $this->registre_commerce = $this->societe->registre_commerce;
        $this->patente = $this->societe->patente;
        $this->cnss = $this->societe->cnss;
        $this->conditions_paiement = $this->societe->conditions_paiement;
        $this->currency = $this->societe->currency;
        $this->tva_rate_default = $this->societe->tva_rate_default;
        $this->qty_decimals = $this->societe->qty_decimals ?? 2;
        $this->price_decimals = $this->societe->price_decimals ?? 2;
        $this->stock_threshold_default = $this->societe->stock_threshold_default ?? 0;
        $this->facture_type = $this->societe->facture_type ?? 'sans_entete';
        $this->stock_verification = (bool) $this->societe->stock_verification;
        $this->show_ref_facture = $this->societe->show_ref_facture === null ? true : (bool) $this->societe->show_ref_facture;
        $this->show_ref_bl = $this->societe->show_ref_bl === null ? true : (bool) $this->societe->show_ref_bl;
        $this->bons_chargement_enabled = $this->societe->bons_chargement_enabled === null ? true : (bool) $this->societe->bons_chargement_enabled;
        $this->tva_franchise = (bool) $this->societe->tva_franchise;
        $this->penalites_taux = $this->societe->penalites_taux ?? '';
        $this->indemnite_40 = (bool) $this->societe->indemnite_40;
        $this->escompte_neant = (bool) $this->societe->escompte_neant;
        $this->tva_debits = (bool) $this->societe->tva_debits;
        $this->nature_operation_default = $this->societe->nature_operation_default ?? '';
        $this->devis_validite_jours = $this->societe->devis_validite_jours ?? 30;
    }

    public function updatedCountryCode($code): void
    {
        $c = \App\Support\Countries::get($code);
        $this->currency = $c['currency'];
        $this->tva_rate_default = $c['vat'];
        $this->price_decimals = $c['decimals'];
    }

    public function removeLogo()
    {
        if ($this->societe->logo_path) {
            Storage::disk('public')->delete($this->societe->logo_path);
            $this->societe->update(['logo_path' => null]);
        }

        session()->flash('success', 'Logo supprimé.');
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'country_code' => 'required|in:'.implode(',', array_keys(\App\Support\Countries::ALL)),
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'phone2' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tva_rate_default' => 'required|numeric|min:0|max:100',
            'qty_decimals' => 'required|integer|min:0|max:6',
            'price_decimals' => 'required|integer|min:0|max:6',
            'stock_threshold_default' => 'nullable|integer|min:0',
            'facture_type' => 'required|in:sans_entete,avec_entete',
            'penalites_taux' => 'nullable|string|max:255',
            'nature_operation_default' => 'nullable|in:biens,services,mixte',
            'devis_validite_jours' => 'required|integer|min:1|max:365',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name' => $this->name,
            'country' => \App\Support\Countries::get($this->country_code)['name'],
            'country_code' => $this->country_code,
            'city' => $this->city,
            'address' => $this->address,
            'phone' => $this->phone,
            'phone2' => $this->phone2,
            'fax' => $this->fax,
            'email' => $this->email,
            'ice' => $this->ice,
            'identifiant_fiscal' => $this->identifiant_fiscal,
            'registre_commerce' => $this->registre_commerce,
            'patente' => $this->patente ?: null,
            'cnss' => $this->cnss ?: null,
            'conditions_paiement' => $this->conditions_paiement ?: null,
            'currency' => $this->currency,
            'tva_rate_default' => $this->tva_rate_default,
            'qty_decimals' => $this->qty_decimals,
            'price_decimals' => $this->price_decimals,
            'stock_threshold_default' => $this->stock_threshold_default ?: 0,
            'facture_type' => $this->facture_type,
            'stock_verification' => $this->stock_verification,
            'show_ref_facture' => $this->show_ref_facture,
            'show_ref_bl' => $this->show_ref_bl,
            'bons_chargement_enabled' => $this->bons_chargement_enabled,
            'tva_franchise' => (bool) $this->tva_franchise,
            'penalites_taux' => trim((string) $this->penalites_taux) ?: null,
            'indemnite_40' => (bool) $this->indemnite_40,
            'escompte_neant' => (bool) $this->escompte_neant,
            'tva_debits' => (bool) $this->tva_debits,
            'nature_operation_default' => $this->nature_operation_default ?: null,
            'devis_validite_jours' => (int) $this->devis_validite_jours,
        ];

        if ($this->logo) {
            if ($this->societe->logo_path) {
                Storage::disk('public')->delete($this->societe->logo_path);
            }

            $data['logo_path'] = $this->logo->store('logos', 'public');
        }

        $this->societe->update($data);
        $this->logo = null;

        session()->flash('success', 'Informations société mises à jour.');
    }

    public function render()
    {
        return view('livewire.societe.edit')->layout('layouts.app');
    }
}
