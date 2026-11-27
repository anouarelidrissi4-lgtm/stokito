<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Blog - Articles</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('blog.index') }}" target="_blank" class="text-sm text-gray-500 hover:underline">Voir le blog public &rarr;</a>
            <a href="{{ route('blog-admin.create') }}" wire:navigate class="bg-[#16a34a] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#15803d]">+ Ajouter un article</a>
        </div>
    </div>


    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rechercher par titre..." class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mise à jour</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($posts as $post)
                    <tr wire:key="post-{{ $post->id }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $post->title }}
                            @if ($post->featured)
                                <span class="ml-1 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">A la une</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $post->category ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $post->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $post->status === 'published' ? 'Publie' : 'Brouillon' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $post->updated_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-2 whitespace-nowrap">
                            <a href="{{ route('blog-admin.edit', $post->id) }}" wire:navigate class="text-indigo-600 hover:underline">Modifier</a>
                            <button wire:click="delete({{ $post->id }})" wire:confirm="Supprimer cet article ?" class="text-red-600 hover:underline">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">Aucun article.</td></tr>
                @endforelse
            </tbody>
        </table>
</div>
        <div class="p-4">{{ $posts->links() }}</div>
    </div>
</div>
