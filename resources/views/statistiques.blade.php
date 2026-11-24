<x-app-layout>
    <x-slot name="header">
        <h1 class="ind-h text-3xl sm:text-4xl uppercase leading-none text-[#111]">{{ __('Statistiques') }}</h1>
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow p-6">
                <div class="text-sm text-gray-500 mb-1">Chiffre d'affaires encaisse</div>
                <div class="text-2xl font-bold text-green-700">{{ \App\Support\Fmt::money($caEncaisse) }}</div>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <div class="text-sm text-gray-500 mb-1">Creances en cours</div>
                <div class="text-2xl font-bold text-amber-600">{{ \App\Support\Fmt::money($creancesEnCours) }}</div>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <div class="text-sm text-gray-500 mb-1">Total Achats</div>
                <div class="text-2xl font-bold text-gray-900">{{ \App\Support\Fmt::money($totalAchats) }}</div>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <div class="text-sm text-gray-500 mb-1">Solde Trésorerie</div>
                <div class="text-2xl font-bold {{ $soldeTresorerie < 0 ? 'text-red-600' : 'text-gray-900' }}">{{ \App\Support\Fmt::money($soldeTresorerie) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-5 py-3 border-b bg-gray-50"><h3 class="font-semibold text-gray-800">Top 5 Clients (par solde du)</h3></div>
                <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-100 text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($topClients as $client)
                            <tr>
                                <td class="px-5 py-2">{{ $client->name }}</td>
                                <td class="px-5 py-2 text-right font-semibold {{ $client->solde_calcule > 0 ? 'text-red-600' : 'text-gray-500' }}">{{ \App\Support\Fmt::money($client->solde_calcule) }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-5 py-6 text-center text-gray-400">Aucune donnee.</td></tr>
                        @endforelse
                    </tbody>
                </table>
</div>
            </div>

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-5 py-3 border-b bg-gray-50"><h3 class="font-semibold text-gray-800">Produits les plus vendus</h3></div>
                <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-100 text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($topProduits as $produit)
                            <tr>
                                <td class="px-5 py-2">{{ $produit->name }}</td>
                                <td class="px-5 py-2 text-right text-gray-500">{{ (int) $produit->qte_vendue }} unites</td>
                            </tr>
                        @empty
                            <tr><td class="px-5 py-6 text-center text-gray-400">Aucune vente enregistrée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-5 py-3 border-b-2 border-[#111]"><h3 class="font-semibold text-red-700">Chèques en retard</h3></div>
            <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-100 text-sm">
                <tbody class="divide-y divide-gray-100">
                    @forelse ($chequesEnRetard as $cheque)
                        <tr>
                            <td class="px-5 py-2">{{ $cheque->numero_cheque }} - {{ $cheque->fournisseur->name ?? '-' }}</td>
                            <td class="px-5 py-2 text-gray-500">Echeance: {{ $cheque->date_echeance->format('d/m/Y') }}</td>
                            <td class="px-5 py-2 text-right font-semibold text-red-600">{{ \App\Support\Fmt::money($cheque->montant) }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-6 text-center text-gray-400">Aucun chèque en retard.</td></tr>
                    @endforelse
                </tbody>
            </table>
</div>
        </div>
    </div>
</x-app-layout>
