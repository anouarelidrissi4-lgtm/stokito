<div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl">
    @php
        $card = 'bg-white border-2 border-[#111] p-4 sm:p-5';
        $pct = fn ($a, $b) => $b > 0 ? round($a / $b * 100) : 0;
        $flags = \App\Support\Countries::ALL;
    @endphp
    <div>
        <p class="ind-mono text-xs text-[#666] uppercase">Superadmin</p>
        <h1 class="ind-h text-4xl uppercase leading-none mt-1">Inscriptions</h1>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="{{ $card }}">
            <div class="ind-mono text-[11px] uppercase text-[#666]">Sociétés inscrites</div>
            <div class="mt-2 ind-h text-4xl leading-none">{{ (int) $stats->total }}</div>
            <div class="mt-1 text-xs text-slate-500">{{ (int) $stats->self }} en libre-service</div>
        </div>
        <div class="{{ $card }}">
            <div class="ind-mono text-[11px] uppercase text-[#666]">Nouvelles</div>
            <div class="mt-2 ind-h text-4xl leading-none">{{ (int) $stats->d7 }} <span class="text-sm font-medium text-slate-400">7 j</span></div>
            <div class="mt-1 text-xs text-slate-500">{{ (int) $stats->d30 }} sur 30 jours</div>
        </div>
        <div class="{{ $card }}">
            <div class="ind-mono text-[11px] uppercase text-[#666]">Actives</div>
            <div class="mt-2 ind-h text-4xl leading-none text-emerald-700">{{ $pct((int) $stats->actifs, (int) $stats->total) }} %</div>
            <div class="mt-1 text-xs text-slate-500">{{ (int) $stats->actifs }} ont fait un devis ou une facture</div>
        </div>
        <div class="{{ $card }}">
            <div class="ind-mono text-[11px] uppercase text-[#666]">Pays</div>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach ($pays as $cc => $n)
                    <span class="border-2 border-[#111] px-2 py-0.5 text-sm font-semibold">{{ $flags[$cc]['name'] ?? $cc }} · {{ $n }}</span>
                @endforeach
            </div>
            <div class="mt-1 text-xs text-slate-500">{{ $pct((int) $stats->verifies, (int) $stats->total) }} % ont confirmé leur email</div>
        </div>
    </div>

    <div class="bg-white border-2 border-[#111] p-4 flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.400ms="search" type="search" placeholder="Société, nom ou email…" class="flex-1 min-w-[220px] rounded-md border-gray-300 text-sm">
        <select wire:model.live="periode" class="rounded-md border-gray-300 text-sm">
            <option value="all">Toutes les dates</option>
            <option value="7">7 derniers jours</option>
            <option value="30">30 derniers jours</option>
            <option value="90">90 derniers jours</option>
        </select>
        <label class="inline-flex items-center gap-2 text-sm"><input wire:model.live="actifs" type="checkbox" class="rounded border-gray-300"> Actives seulement</label>
    </div>

    <div class="bg-white border-2 border-[#111] overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-[#111] text-[#ffcc00] text-xs uppercase">
                <tr>
                    <th class="text-start px-4 py-3">Société</th>
                    <th class="text-start px-4 py-3">Contact</th>
                    <th class="text-start px-4 py-3">Inscription</th>
                    <th class="text-end px-4 py-3">Produits</th>
                    <th class="text-end px-4 py-3">Clients</th>
                    <th class="text-end px-4 py-3">Devis</th>
                    <th class="text-end px-4 py-3">Factures</th>
                    <th class="text-start px-4 py-3">Dernière activité</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($rows as $r)
                    @php
                        $last = collect([$r->last_facture, $r->last_devis])->filter()->max();
                        $wa = $r->contact_phone ? \App\Support\Relance::phone($r->contact_phone, $r->country_code ?: 'MA') : null;
                        $actif = ($r->nb_devis + $r->nb_factures) > 0;
                    @endphp
                    <tr class="{{ $r->is_active ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-900">{{ $r->societe ?: $r->name }}</div>
                            <div class="text-xs text-slate-500">{{ $flags[$r->country_code ?: 'MA']['name'] ?? '—' }} · {{ $r->signup_source === 'self' ? 'libre-service' : 'créé par admin' }}@if (! $r->is_active) · désactivé @endif</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-slate-800">{{ $r->admin_name ?: '—' }}</div>
                            @if ($r->admin_email)
                                <a href="mailto:{{ $r->admin_email }}" class="text-xs underline decoration-[#ffcc00] decoration-2">{{ $r->admin_email }}</a>
                                @if (! $r->verified_at)<span class="ms-1 text-[10px] font-semibold text-amber-700">non confirmé</span>@endif
                            @endif
                            @if ($wa)<div><a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="text-xs text-emerald-700 underline">WhatsApp {{ $r->contact_phone }}</a></div>@endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ \Illuminate\Support\Carbon::parse($r->created_at)->format('d/m/Y') }}<div class="text-xs text-slate-400">{{ \Illuminate\Support\Carbon::parse($r->created_at)->locale('fr')->diffForHumans() }}</div></td>
                        <td class="px-4 py-3 text-end">{{ $r->nb_products }}</td>
                        <td class="px-4 py-3 text-end">{{ $r->nb_clients }}</td>
                        <td class="px-4 py-3 text-end">{{ $r->nb_devis }}</td>
                        <td class="px-4 py-3 text-end font-semibold {{ $r->nb_factures ? 'text-emerald-700' : '' }}">{{ $r->nb_factures }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if ($last)
                                {{ \Illuminate\Support\Carbon::parse($last)->format('d/m/Y') }}
                            @else
                                <span class="text-xs font-semibold {{ $actif ? '' : 'text-rose-600' }}">Aucun document</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">Aucune inscription.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $rows->links() }}</div>
</div>
