<div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $fournisseur->name }}</h1>
            <p class="text-sm text-gray-500">{{ $fournisseur->phone }} {{ $fournisseur->email ? '· '.$fournisseur->email : '' }}</p>
        </div>
        <a href="{{ route('soldes-fournisseurs.index') }}" wire:navigate class="text-sm text-gray-500 hover:underline">&larr; Retour</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-sm text-gray-500 mb-1">Solde du (par nous)</div>
            <div class="text-2xl font-bold {{ $fournisseur->solde() > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ \App\Support\Fmt::money($fournisseur->solde()) }}</div>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <div class="text-sm text-gray-500 mb-1">{{ \App\Support\Countries::for(\App\Models\Societe::current())['client'] }}</div>
            <div class="text-lg font-semibold text-gray-900">{{ $fournisseur->ice ?: '-' }}</div>
        </div>
    </div>

    @foreach ([
        ['title' => 'Commandes', 'rows' => $commandes, 'cols' => ['numero', 'date', 'total', 'status']],
        ['title' => 'Reglements', 'rows' => $reglements, 'cols' => ['date', 'montant', 'mode']],
        ['title' => 'Avoirs', 'rows' => $avoirs, 'cols' => ['numero', 'date', 'montant', 'motif']],
        ['title' => 'Cheques / Effets', 'rows' => $cheques, 'cols' => ['numero_cheque', 'date_echeance', 'montant', 'status']],
    ] as $section)
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-5 py-3 border-b bg-gray-50">
                <h3 class="font-semibold text-gray-800">{{ $section['title'] }}</h3>
            </div>
            @if ($section['rows']->isEmpty())
                <div class="px-5 py-6 text-center text-sm text-gray-400">Aucun element.</div>
            @else
                <div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-100 text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($section['rows'] as $row)
                            <tr>
                                @foreach ($section['cols'] as $col)
                                    <td class="px-5 py-2 {{ in_array($col, ['total', 'montant']) ? 'text-right' : '' }}">
                                        @if (in_array($col, ['total', 'montant']))
                                            {{ \App\Support\Fmt::money($row->{$col}) }}
                                        @elseif (in_array($col, ['date', 'date_echeance']))
                                            {{ $row->{$col}->format('d/m/Y') }}
                                        @else
                                            {{ $row->{$col} }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
</div>
            @endif
        </div>
    @endforeach
</div>
