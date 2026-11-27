<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Projets</h1>
        <button wire:click="create" class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">+ Ajouter un projet</button>
    </div>


    @php
        $planBtns = [['pro', 3, 'Pro 3 mois'], ['pro', 12, 'Pro 1 an'], ['premium', 3, 'Premium 3 mois'], ['premium', 12, 'Premium 1 an'], ['free', 0, 'Free']];
        $planBadge = fn ($eff) => $eff === 'premium' ? 'bg-zinc-900 text-amber-300' : ($eff === 'pro' ? 'bg-[#ffcc00] text-zinc-900' : 'bg-gray-100 text-gray-600');
    @endphp

    {{-- téléphone / tablette : une carte par projet --}}
    <div class="lg:hidden space-y-3">
        @forelse ($projects as $project)
            @php $eff = \App\Support\Plans::effective($project); @endphp
            <div wire:key="proj-card-{{ $project->id }}" class="bg-white shadow rounded-lg p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 break-words">{{ $project->name }}</p>
                        <p class="mt-0.5 text-xs text-gray-500">{{ $project->users_count }} utilisateur{{ $project->users_count > 1 ? 's' : '' }}</p>
                    </div>
                    <span class="shrink-0 px-2 py-1 rounded-full text-xs font-semibold {{ $project->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $project->is_active ? 'Actif' : 'Inactif' }}</span>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $planBadge($eff) }}">{{ \App\Support\Plans::LABELS[$eff] }}{{ $project->plan_trial && $eff !== 'free' ? ' · essai' : '' }}</span>
                    @if ($eff !== 'free' && $project->plan_ends_at)<span class="text-xs text-gray-500">jusqu’au {{ $project->plan_ends_at->format('d/m/Y') }}</span>@endif
                </div>
                <p class="mt-3 text-xs font-medium text-gray-500">Changer le plan</p>
                <div class="mt-1.5 grid grid-cols-2 gap-2">
                    @foreach ($planBtns as [$pl, $m, $lab])
                        <button type="button" wire:click="setPlan({{ $project->id }}, '{{ $pl }}', {{ $m }})" wire:confirm="{{ $project->name }} : {{ $lab }} ?" class="h-10 px-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 whitespace-nowrap hover:border-zinc-900 {{ $loop->last ? 'col-span-2' : '' }}">{{ $lab }}</button>
                    @endforeach
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 flex justify-end">
                    <button wire:click="toggleActive({{ $project->id }})" class="h-9 px-3 rounded-lg text-sm font-semibold {{ $project->is_active ? 'text-red-600 hover:bg-red-50' : 'text-green-700 hover:bg-green-50' }}">{{ $project->is_active ? 'Désactiver le projet' : 'Activer le projet' }}</button>
                </div>
            </div>
        @empty
            <div class="bg-white shadow rounded-lg px-4 py-8 text-center text-sm text-gray-400">Aucun projet.</div>
        @endforelse
        <div>{{ $projects->links() }}</div>
    </div>

    {{-- ordinateur : tableau --}}
    <div class="hidden lg:block bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Utilisateurs</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($projects as $project)
                    @php $eff = \App\Support\Plans::effective($project); @endphp
                    <tr wire:key="proj-{{ $project->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $project->name }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-500">{{ $project->users_count }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $project->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $project->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm min-w-[280px]">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $planBadge($eff) }}">{{ \App\Support\Plans::LABELS[$eff] }}{{ $project->plan_trial && $eff !== 'free' ? ' · essai' : '' }}</span>
                            @if ($eff !== 'free' && $project->plan_ends_at)<span class="block mt-1 text-xs text-gray-500">jusqu’au {{ $project->plan_ends_at->format('d/m/Y') }}</span>@endif
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach ($planBtns as [$pl, $m, $lab])
                                    <button type="button" wire:click="setPlan({{ $project->id }}, '{{ $pl }}', {{ $m }})" wire:confirm="{{ $project->name }} : {{ $lab }} ?" class="px-2 py-0.5 rounded border border-gray-300 text-[11px] font-semibold text-gray-700 whitespace-nowrap hover:border-zinc-900">{{ $lab }}</button>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                            <button wire:click="toggleActive({{ $project->id }})" class="text-indigo-600 hover:underline">
                                {{ $project->is_active ? 'Desactiver' : 'Activer' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">Aucun projet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4">{{ $projects->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">Nouveau projet</h2>

                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du projet / société</label>
                <input wire:model="project_name" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @error('project_name') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Logo (optionnel)</label>
                <div class="flex items-center gap-3">
                    <div class="h-14 w-14 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" class="h-full w-full object-contain">
                        @else
                            <span class="text-xl">🏢</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <input wire:model="logo" type="file" accept="image/*" class="w-full text-sm">
                        <div wire:loading wire:target="logo" class="text-xs text-gray-400 mt-1">Televersement...</div>
                        @error('logo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Apparaitra sur les PDF (devis, BL, factures) de ce projet. Modifiable plus tard depuis Ma Societe.</p>

                <div class="border-t my-3 pt-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Premier administrateur du projet</p>

                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input wire:model="admin_name" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    @error('admin_name') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                    <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Email</label>
                    <input wire:model="admin_email" type="email" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    @error('admin_email') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                    <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Mot de passe</label>
                    <input wire:model="admin_password" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">
                    @error('admin_password') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Créer le projet</button>
                </div>
            </div>
        </div>
    @endif
</div>
