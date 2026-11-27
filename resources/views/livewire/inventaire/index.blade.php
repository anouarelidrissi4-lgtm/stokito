<div class="p-4 sm:p-6 lg:p-8 max-w-6xl">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Inventaires</h1>
            <p class="text-sm text-slate-500">Comptez votre stock réel et corrigez les écarts en un clic.</p>
        </div>
        <a href="{{ route('inventaire.create') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg bg-[#16a34a] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#15803d]">
            <x-sp-icon name="plus" class="w-4 h-4" /> Ajouter un inventaire
        </a>
    </div>


    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Numéro</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Date</th>
                        <th class="px-5 py-3 text-start text-xs font-semibold text-slate-500 uppercase">Par</th>
                        <th class="px-5 py-3 text-end text-xs font-semibold text-slate-500 uppercase">Produits comptés</th>
                        <th class="px-5 py-3 text-end text-xs font-semibold text-slate-500 uppercase">Écarts corrigés</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($inventaires as $inv)
                        <tr wire:key="inv-{{ $inv->id }}" class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap">{{ $inv->numero }}</td>
                            <td class="px-5 py-3 text-sm text-slate-600 whitespace-nowrap">{{ $inv->date->format('d/m/Y') }}</td>
                            <td class="px-5 py-3 text-sm text-slate-600">{{ $inv->user->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-sm text-end text-slate-900">{{ $inv->nb_produits }}</td>
                            <td class="px-5 py-3 text-sm text-end {{ $inv->nb_ecarts ? 'text-amber-600 font-semibold' : 'text-slate-500' }}">{{ $inv->nb_ecarts }}</td>
                            <td class="px-5 py-3 text-end"><a href="{{ route('inventaire.show', $inv->id) }}" wire:navigate class="text-sm font-semibold text-indigo-600 hover:underline">Voir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center">
                            <div class="text-sm text-slate-500">Aucun inventaire pour le moment.</div>
                            <a href="{{ route('inventaire.create') }}" wire:navigate class="mt-3 inline-block text-sm font-semibold text-indigo-600 hover:underline">Faire le premier inventaire</a>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $inventaires->links() }}</div>
    </div>
</div>
