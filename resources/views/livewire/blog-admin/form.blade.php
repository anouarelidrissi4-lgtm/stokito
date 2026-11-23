<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-4xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $post ? 'Modifier' : 'Nouvel' }} article</h1>
        <a href="{{ route('blog-admin.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">&larr; Retour</a>
    </div>

    <div class="bg-white shadow rounded-lg p-6 space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
            <input wire:model="title" type="text" class="w-full rounded-md border-gray-300 text-sm">
            @error('title') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL, laisser vide pour auto)</label>
                <input wire:model="slug" type="text" placeholder="genere automatiquement" class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                <input wire:model="category" list="categories-list" type="text" class="w-full rounded-md border-gray-300 text-sm">
                <datalist id="categories-list">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}"></option>
                    @endforeach
                </datalist>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description (meta / resume, 160 caracteres max conseille)</label>
            <textarea wire:model="description" rows="2" maxlength="320" class="w-full rounded-md border-gray-300 text-sm"></textarea>
            @error('description') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contenu (Markdown)</label>
            <textarea wire:model="content" rows="20" class="w-full rounded-md border-gray-300 text-sm font-mono text-xs"></textarea>
            @error('content') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image de couverture (URL)</label>
                <input wire:model="cover_image" type="text" placeholder="https://..." class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Temps de lecture (min)</label>
                <input wire:model="reading_time" type="number" min="1" class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Auteur</label>
                <input wire:model="author" type="text" class="w-full rounded-md border-gray-300 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select wire:model="status" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="draft">Brouillon</option>
                    <option value="published">Publie</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date de publication</label>
                <input wire:model="published_at" type="date" class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input wire:model="featured" type="checkbox" class="rounded border-gray-300">
                    Mettre a la une
                </label>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t">
            <a href="{{ route('blog-admin.index') }}" wire:navigate class="px-4 py-2 text-sm text-gray-600">Annuler</a>
            <button wire:click="save" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700">Enregistrer</button>
        </div>
    </div>
</div>
