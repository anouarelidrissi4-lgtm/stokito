<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Documents Externes</h1>
        <button wire:click="create" class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">+ Televerser</button>
    </div>

    @if (session('error'))
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm print:hidden">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ajouté par</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($documents as $doc)
                    <tr wire:key="doc-{{ $doc->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $doc->title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $doc->uploader->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $doc->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($doc->file_path) }}" target="_blank" class="text-indigo-600 hover:underline">Télécharger</a>
                            <button wire:click="delete({{ $doc->id }})" wire:confirm="Supprimer ce document ?" class="text-red-600 hover:underline">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-400">Aucun document.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $documents->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4 text-gray-900">Televerser un document</h2>

                <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                <input wire:model="title" type="text" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @error('title') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Fichier</label>
                <input wire:model="file" type="file" class="w-full rounded-md border-gray-300 text-sm mb-1">
                @error('file') <p class="text-red-600 text-xs mb-2">{{ $message }}</p> @enderror
                <div wire:loading wire:target="file" class="text-xs text-gray-400">Televersement en cours...</div>

                <label class="block text-sm font-medium text-gray-700 mb-1 mt-2">Notes</label>
                <textarea wire:model="notes" rows="2" class="w-full rounded-md border-gray-300 text-sm"></textarea>

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                    <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif
</div>
