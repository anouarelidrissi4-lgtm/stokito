@php($money = fn ($v) => \App\Support\Fmt::money($v))
<div class="p-3 sm:p-5 lg:p-6 max-w-6xl">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="ind-h text-3xl sm:text-4xl uppercase leading-none">Clôtures de caisse</h1>
        <div class="flex gap-2 text-sm font-semibold">
            <a href="{{ route('caisse.pos') }}" wire:navigate class="border-2 border-[#111] bg-[#ffcc00] px-3 py-1.5">← Caisse</a>
            <a href="{{ route('caisse.tickets') }}" wire:navigate class="border-2 border-[#111] bg-white px-3 py-1.5 hover:bg-[#ffcc00]">Tickets</a>
        </div>
    </div>
    @if (session('success'))<div class="mb-4 border-2 border-[#111] bg-[#dff3e6] px-4 py-2 text-sm font-semibold">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="mb-4 border-2 border-[#111] bg-[#fde3e3] px-4 py-2 text-sm font-semibold">{{ session('error') }}</div>@endif

    <div class="grid md:grid-cols-2 gap-4 mb-5">
        <div class="border-2 border-[#111] bg-white p-4">
            <div class="ind-mono text-[11px] text-[#666]">NON CLÔTURÉ</div>
            <div class="mt-1 ind-h text-3xl">{{ $money($open->sum(fn ($t) => (float) $t->total_ttc)) }} <span class="text-base text-[#666]">{{ $currency }}</span></div>
            <div class="text-sm text-[#555]">{{ $open->where('type', 'vente')->count() }} vente(s), {{ $open->where('type', 'annulation')->count() }} annulation(s) depuis la dernière clôture.</div>
            <button type="button" wire:click="cloturer" wire:confirm="Clôturer la journée ? Les tickets non clôturés seront figés dans un Z." @disabled($open->isEmpty())
                class="mt-3 ind-h uppercase tracking-wider border-2 border-[#111] bg-[#ffcc00] px-4 py-2 shadow-[3px_3px_0_#111] disabled:opacity-40">Clôturer la journée (Z)</button>
            <p class="mt-2 text-xs text-[#666]">Sans clôture manuelle, Stokito clôture automatiquement chaque jour à minuit, puis le mois et l’année.</p>
        </div>
        <div class="border-2 border-[#111] bg-white p-4">
            <div class="ind-mono text-[11px] text-[#666]">INTÉGRITÉ DES DONNÉES</div>
            @if ($integrity)
                <div class="mt-1 ind-h text-2xl {{ $integrity['ok'] ? 'text-[#0a6b37]' : 'text-[#b42318]' }}">{{ $integrity['ok'] ? '✓ Conforme' : '✗ Anomalies' }}</div>
                <div class="text-sm text-[#555]">{{ $integrity['tickets'] }} ticket(s), {{ $integrity['clotures'] }} clôture(s), {{ $integrity['journal'] }} événement(s) vérifiés.</div>
                @foreach (array_slice($integrity['errors'], 0, 8) as $e)<div class="text-xs text-[#b42318]">{{ $e }}</div>@endforeach
            @else
                <p class="mt-1 text-sm text-[#555]">Chaque ticket, clôture et événement est signé et chaîné au précédent. Le contrôle recalcule toutes les signatures.</p>
            @endif
            <button type="button" wire:click="verify" class="mt-3 border-2 border-[#111] bg-white px-4 py-2 text-sm font-semibold hover:bg-[#ffcc00]">Contrôler l’intégrité</button>
        </div>
    </div>

    <div class="flex mb-3" role="tablist">
        @foreach (['jour' => 'Jours', 'mois' => 'Mois', 'annee' => 'Années'] as $k => $l)
            <button type="button" wire:click="$set('type', '{{ $k }}')" class="ind-h uppercase tracking-wider px-4 py-1.5 border-2 border-[#111] -me-0.5 {{ $type === $k ? 'bg-[#111] text-[#ffcc00]' : 'bg-white' }}">{{ $l }}</button>
        @endforeach
    </div>
    <div class="border-2 border-[#111] bg-white overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-[#111] text-[#ffcc00] ind-mono text-[11px]">
                <tr><th class="px-3 py-2 text-start">Z N°</th><th class="px-3 py-2 text-start">Période</th><th class="px-3 py-2 text-start">Tickets</th><th class="px-3 py-2 text-end">Total TTC</th><th class="px-3 py-2 text-end">Grand total perpétuel</th><th class="px-3 py-2"></th></tr>
            </thead>
            <tbody class="divide-y divide-[#e3e0d8]">
                @forelse ($clotures as $c)
                    <tr wire:key="c{{ $c->id }}">
                        <td class="px-3 py-2.5 ind-mono">{{ $c->sequence }}</td>
                        <td class="px-3 py-2.5">{{ $c->periode }}<span class="block text-[10px] text-[#888]">{{ $c->created_at->format('d/m/Y H:i') }} · {{ $c->user_name ?: 'automatique' }}</span></td>
                        <td class="px-3 py-2.5 ind-mono text-xs">{{ $c->nb_tickets }} vente(s) · {{ $c->nb_annulations }} annul.<span class="block text-[#888]">T-{{ str_pad($c->first_seq, 6, '0', STR_PAD_LEFT) }} → T-{{ str_pad($c->last_seq, 6, '0', STR_PAD_LEFT) }}</span></td>
                        <td class="px-3 py-2.5 text-end ind-mono font-semibold">{{ $money($c->total_ttc) }}</td>
                        <td class="px-3 py-2.5 text-end ind-mono">{{ $money($c->cumul_ttc) }}</td>
                        <td class="px-3 py-2.5 text-end"><a href="{{ route('caisse.clotures.print', $c->id) }}" target="_blank" class="border-2 border-[#111] bg-white px-2 py-1 text-xs font-semibold hover:bg-[#ffcc00]">Imprimer</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-8 text-center text-[#888]">Aucune clôture.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $clotures->links() }}</div>
</div>
