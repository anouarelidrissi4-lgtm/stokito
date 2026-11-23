<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Soldes Fournisseurs</h1>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher un fournisseur..." class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Solde du</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dossier</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($fournisseurs as $fournisseur)
                    <tr wire:key="fr-{{ $fournisseur->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $fournisseur->name }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold {{ $fournisseur->solde_calcule > 0 ? 'text-red-600' : 'text-gray-900' }} whitespace-nowrap">{{ \App\Support\Fmt::money($fournisseur->solde_calcule) }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('dossier-fournisseurs.show', $fournisseur->id) }}" wire:navigate class="text-indigo-600 hover:underline">Voir dossier</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-8 text-center text-sm text-gray-400">Aucun fournisseur.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $fournisseurs->links() }}</div>
    </div>
</div>
