<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-4xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Ma Société</h1>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden divide-y divide-gray-100">

        <!-- Logo -->
        <div class="p-6">
            <h2 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-4">Logo de la société</h2>
            <div class="flex items-center gap-6">
                <div class="h-24 w-24 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" class="h-full w-full object-contain">
                    @elseif ($societe->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($societe->logo_path) }}" class="h-full w-full object-contain">
                    @else
                        <span class="text-gray-300 text-sm">Logo</span>
                    @endif
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Changer le logo</label>
                    <input wire:model="logo" type="file" accept="image/*" class="w-full text-sm">
                    <div wire:loading wire:target="logo" class="text-xs text-gray-400 mt-1">Televersement...</div>
                    @error('logo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-400 mt-1">Formats autorises : JPG, PNG — Max 2 Mo. Apparait sur les PDF (devis, BL, factures).</p>
                    @if ($societe->logo_path && ! $logo)
                        <button type="button" wire:click="removeLogo" wire:confirm="Supprimer le logo ?" class="text-xs text-red-600 hover:underline mt-1">Supprimer le logo</button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Couleur des documents (2026-11-26) -->
        <div class="p-6" x-data="{ c: @entangle('pdf_color') }">
            <h2 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-1">Couleur des documents</h2>
            <p class="text-sm text-gray-500 mb-4">La couleur de vos devis, factures, bons de livraison et bons de commande en PDF.</p>
            <div class="flex flex-col lg:flex-row gap-6">
                <div class="flex-1">
                    <div class="flex flex-wrap gap-2">
                        @foreach (\App\Models\Societe::PDF_COLORS as $label => $hex)
                            <button type="button" @click="c = '{{ $hex }}'" title="{{ $label }}" aria-label="{{ $label }}"
                                class="w-10 h-10 rounded-full ring-offset-2 transition" style="background: {{ $hex }}"
                                :class="(c || '').toLowerCase() === '{{ $hex }}' ? 'ring-2 ring-zinc-900 scale-110' : 'ring-1 ring-black/10 hover:scale-105'"></button>
                        @endforeach
                    </div>
                    <label class="mt-4 inline-flex items-center gap-3 text-sm text-gray-700">
                        <input type="color" x-model="c" class="h-10 w-14 cursor-pointer rounded border border-gray-300 bg-white p-1">
                        Autre couleur : <span class="font-mono" x-text="(c || '').toUpperCase()"></span>
                    </label>
                    @error('pdf_color') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-400 mt-2">Le texte passe en blanc sur les couleurs foncées, pour rester lisible. Cliquez sur Enregistrer en bas de la page.</p>
                </div>
                {{-- mini preview of a document --}}
                <div class="w-full lg:w-80 shrink-0 rounded-lg border border-gray-200 bg-white p-4 shadow-sm text-[10px] text-zinc-800" aria-hidden="true">
                    <div class="h-1 rounded" :style="'background:' + c"></div>
                    <div class="mt-3 flex justify-between"><span class="font-bold uppercase">{{ $name ?: 'Votre société' }}</span><span class="text-zinc-500">FAC-00001</span></div>
                    <div class="mt-3 text-center"><span class="font-bold tracking-[3px] text-xs border-b-[3px] pb-0.5" :style="'border-color:' + c">FACTURE</span></div>
                    <div class="mt-3 grid grid-cols-[1fr_auto_auto] text-[9px] font-bold border border-zinc-900 rounded-lg" :style="'background:' + c + ';color:' + (luma(c) > 0.4 ? '#111' : '#fff')">
                        <span class="px-1.5 py-1">Désignation</span><span class="px-1.5 py-1">Qté</span><span class="px-1.5 py-1">Total HT</span>
                    </div>
                    <div class="grid grid-cols-[1fr_auto_auto] border-x border-b border-zinc-900"><span class="px-1.5 py-1">Produit</span><span class="px-1.5 py-1">2</span><span class="px-1.5 py-1">100,00</span></div>
                    <div class="mt-1 ms-auto w-40 grid grid-cols-2 border border-zinc-900 font-bold text-[9px] rounded-lg" :style="'background:' + c + ';color:' + (luma(c) > 0.4 ? '#111' : '#fff')">
                        <span class="px-1.5 py-1">TOTAL TTC</span><span class="px-1.5 py-1 text-right">120,00</span>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.luma = window.luma || function (hex) {
                var m = /^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(hex || ''); if (!m) return 1;
                var l = [m[1], m[2], m[3]].map(function (x) { x = parseInt(x, 16) / 255; return x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4); });
                return 0.2126 * l[0] + 0.7152 * l[1] + 0.0722 * l[2];
            };
        </script>

        <!-- Informations generales -->
        <div class="p-6">
            <h2 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-4">Informations generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Raison sociale *</label>
                    <input wire:model="name" type="text" class="w-full rounded-md border-gray-300 text-sm">
                    @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Activité <span class="text-gray-400 font-normal">(sous le nom, sur les factures)</span></label>
                    <input wire:model="activite" type="text" maxlength="255" placeholder="Ex. Négoce de matériaux et travaux divers" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                    <select wire:model.live="country_code" class="w-full rounded-md border-gray-300 text-sm">
                        @foreach (\App\Support\Countries::options() as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                    <input wire:model="city" type="text" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input wire:model="address" type="text" class="w-full rounded-md border-gray-300 text-sm">
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="p-6">
            <h2 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-4">Contact</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone 1</label>
                    <input wire:model="phone" type="text" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone 2</label>
                    <input wire:model="phone2" type="text" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fax</label>
                    <input wire:model="fax" type="text" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input wire:model="email" type="email" class="w-full sm:w-1/3 rounded-md border-gray-300 text-sm">
                    @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Parametres -->
        <div class="p-6">
            <h2 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-4">Paramètres</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Décimales quantites</label>
                    <input wire:model="qty_decimals" type="number" min="0" max="6" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Décimales prix</label>
                    <input wire:model="price_decimals" type="number" min="0" max="6" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Seuil minimal stock</label>
                    <input wire:model="stock_threshold_default" type="number" min="0" class="w-full rounded-md border-gray-300 text-sm">
                    <p class="text-xs text-gray-400 mt-1">Utilise si un produit n'a pas son propre seuil.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type facture</label>
                    <select wire:model="facture_type" class="w-full rounded-md border-gray-300 text-sm">
                        <option value="sans_entete">Sans En-tête</option>
                        <option value="avec_entete">Avec En-tête</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vérification du stock</label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <span class="relative inline-block w-10 h-5">
                            <input wire:model="stock_verification" type="checkbox" class="peer sr-only">
                            <span class="absolute inset-0 rounded-full bg-zinc-300 peer-checked:bg-[#ffcc00] transition-colors"></span>
                            <span class="absolute left-0.5 top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                        </span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Facture</label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <span class="relative inline-block w-10 h-5">
                            <input wire:model="show_ref_facture" type="checkbox" class="peer sr-only">
                            <span class="absolute inset-0 rounded-full bg-zinc-300 peer-checked:bg-[#ffcc00] transition-colors"></span>
                            <span class="absolute left-0.5 top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                        </span>
                        <span class="text-sm text-gray-600">Afficher désignation et référence</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bon de livraison</label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <span class="relative inline-block w-10 h-5">
                            <input wire:model="show_ref_bl" type="checkbox" class="peer sr-only">
                            <span class="absolute inset-0 rounded-full bg-zinc-300 peer-checked:bg-[#ffcc00] transition-colors"></span>
                            <span class="absolute left-0.5 top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                        </span>
                        <span class="text-sm text-gray-600">Afficher désignation et référence</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Informations fiscales -->
        <div class="p-6">
            <h2 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-4">Informations fiscales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                @php $lbl = \App\Support\Countries::get($country_code)['labels']['ice'] ?? null; @endphp
                @if ($lbl)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $lbl }}</label>
                    <input wire:model="ice" type="text" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                @endif
                @php $lbl = \App\Support\Countries::get($country_code)['labels']['identifiant_fiscal'] ?? null; @endphp
                @if ($lbl)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $lbl }}</label>
                    <input wire:model="identifiant_fiscal" type="text" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                @endif
                @php $lbl = \App\Support\Countries::get($country_code)['labels']['registre_commerce'] ?? null; @endphp
                @if ($lbl)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $lbl }}</label>
                    <input wire:model="registre_commerce" type="text" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                @endif
                @php $lbl = \App\Support\Countries::get($country_code)['labels']['patente'] ?? null; @endphp
                @if ($lbl)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $lbl }}</label>
                    <input wire:model="patente" type="text" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                @endif
                @php $lbl = \App\Support\Countries::get($country_code)['labels']['cnss'] ?? null; @endphp
                @if ($lbl)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $lbl }}</label>
                    <input wire:model="cnss" type="text" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                @endif
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Conditions de paiement (imprimées sous les factures)</label>
                <textarea wire:model="conditions_paiement" rows="2" class="w-full rounded-md border-gray-300 text-sm" placeholder="Ex. Paiement à 30 jours par virement ou chèque."></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Banque et RIB <span class="text-gray-400 font-normal">(imprimés en bas des factures)</span></label>
                <input wire:model="rib" type="text" maxlength="255" placeholder="Ex. RIB : 000 000 0000000000000000 00 · Banque, agence" class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Devise</label>
                    <input wire:model="currency" type="text" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Taux TVA par defaut (%)</label>
                    <input wire:model="tva_rate_default" type="number" step="0.01" class="w-full rounded-md border-gray-300 text-sm">
                    @error('tva_rate_default') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Validité des devis (jours)</label>
                    <input wire:model="devis_validite_jours" type="number" min="1" max="365" class="w-full rounded-md border-gray-300 text-sm">
                    @error('devis_validite_jours') <p class="text-red-600 text-xs mt-1">Entre 1 et 365 jours.</p> @enderror
                </div>
            </div>
            <div class="mt-4 border border-zinc-200 bg-zinc-50 p-4 rounded-xl">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input wire:model="relances_auto" type="checkbox" class="mt-1 rounded border-gray-300">
                    <span class="text-sm text-gray-700"><strong>Relances automatiques par email</strong> : un rappel poli 7 jours après l’échéance, puis une deuxième relance à 30 jours, avec la facture en PDF. Seulement pour les factures non réglées dont le client a un email ; une seule fois par niveau. Les réponses arrivent sur l’email de Ma Société.</span>
                </label>
            </div>
        </div>

        @if ($country_code === 'FR')
        <!-- Mentions sur les factures (France) -->
        <div class="p-6">
            <h2 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-1">Mentions sur les factures (France)</h2>
            <p class="text-xs text-gray-500 mb-4">Ajoutées automatiquement en bas de vos factures PDF. Vérifiez-les avec votre expert-comptable.</p>
            <div class="space-y-3">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input wire:model="tva_franchise" type="checkbox" class="mt-1 rounded border-gray-300">
                    <span class="text-sm text-gray-700"><strong>Franchise en base de TVA</strong> (micro-entreprise) : les nouveaux documents sont à 0 % et la facture porte « TVA non applicable, art. 293 B du CGI ».</span>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input wire:model="indemnite_40" type="checkbox" class="mt-1 rounded border-gray-300">
                    <span class="text-sm text-gray-700">Indemnité forfaitaire de <strong>40 €</strong> pour frais de recouvrement en cas de retard de paiement (clients professionnels).</span>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input wire:model="escompte_neant" type="checkbox" class="mt-1 rounded border-gray-300">
                    <span class="text-sm text-gray-700">« Escompte pour paiement anticipé : néant ».</span>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input wire:model="tva_debits" type="checkbox" class="mt-1 rounded border-gray-300">
                    <span class="text-sm text-gray-700">Option pour le paiement de la TVA d’après les débits.</span>
                </label>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Taux des pénalités de retard</label>
                    <input wire:model="penalites_taux" type="text" maxlength="255" class="w-full rounded-md border-gray-300 text-sm" placeholder="Ex. 3 fois le taux d’intérêt légal">
                    @error('penalites_taux') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nature des opérations (par défaut)</label>
                    <select wire:model="nature_operation_default" class="w-full rounded-md border-gray-300 text-sm">
                        <option value="">—</option>
                        <option value="biens">Livraison de biens</option>
                        <option value="services">Prestation de services</option>
                        <option value="mixte">Biens et services</option>
                    </select>
                </div>
            </div>
        </div>
        @endif

        <!-- Modules actifs -->
        <div class="p-6">
            <h2 class="text-sm font-semibold text-indigo-600 uppercase tracking-wide mb-4">Modules actifs</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Module chargement</label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <span class="relative inline-block w-10 h-5">
                        <input wire:model="bons_chargement_enabled" type="checkbox" class="peer sr-only">
                        <span class="absolute inset-0 rounded-full bg-zinc-300 peer-checked:bg-[#ffcc00] transition-colors"></span>
                        <span class="absolute left-0.5 top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm text-gray-600">Activer les bons de chargement</span>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Caisse (vente au comptoir)</label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <span class="relative inline-block w-10 h-5">
                        <input wire:model="caisse_enabled" type="checkbox" class="peer sr-only">
                        <span class="absolute inset-0 rounded-full bg-zinc-300 peer-checked:bg-[#ffcc00] transition-colors"></span>
                        <span class="absolute left-0.5 top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm text-gray-600">Activer la caisse : tickets, paiements et clôtures Z</span>
                </label>
            </div>
        </div>

        <div class="p-6 flex justify-end">
            <button wire:click="save" class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
        </div>
    </div>

    <livewire:societe.menu-editor />
</div>
