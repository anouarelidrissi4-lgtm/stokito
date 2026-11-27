{{-- Pointage (2026-11-28): "today" panel (phone friendly) + month grid (click a day to cycle ✓ ½ ✗ C F) --}}
@php
    $S = \App\Models\EmployePresence::STATUTS;
    $cell = ['P' => 'bg-green-100 text-green-800', 'D' => 'bg-lime-100 text-lime-800', 'A' => 'bg-red-100 text-red-700', 'C' => 'bg-sky-100 text-sky-800', 'F' => 'bg-violet-100 text-violet-800'];
    $chipOn = ['P' => 'bg-green-600 text-white border-green-600', 'D' => 'bg-lime-600 text-white border-lime-600', 'A' => 'bg-red-600 text-white border-red-600', 'C' => 'bg-sky-600 text-white border-sky-600', 'F' => 'bg-violet-600 text-white border-violet-600'];
    $moisLabel = ucfirst($debut->locale('fr')->translatedFormat('F Y'));
    $today = now()->toDateString();
@endphp
<div class="p-4 sm:p-6 lg:p-8 max-w-7xl">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pointage</h1>
            <p class="text-sm text-slate-500">Les jours travaillés de vos employés : ils servent à calculer la paie.</p>
        </div>
        <a href="{{ route('personnel.index') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"><x-sp-icon name="user" class="w-4 h-4" /> Employés</a>
    </div>

    @if ($locked)
        @include('livewire.personnel._locked')
    @elseif ($employes->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-500">
            Ajoutez d’abord vos employés dans <a href="{{ route('personnel.index') }}" wire:navigate class="font-semibold text-slate-900 underline">Personnel</a>.
        </div>
    @else
        {{-- one day --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-6">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Jour</label>
                    <input type="date" wire:model.live="jour" class="rounded-md border-gray-300 text-sm">
                </div>
                <button type="button" wire:click="toutPresent" class="inline-flex items-center gap-1.5 h-10 px-4 rounded-lg bg-[#16a34a] text-sm font-semibold text-white hover:bg-[#15803d]">✓ Tout le monde présent</button>
                <p class="text-xs text-slate-500">Marque « présent » les employés pas encore pointés ce jour-là.</p>
            </div>
            <ul class="mt-4 divide-y divide-slate-100">
                @foreach ($employes as $e)
                    @php $cur = $jourStatuts[$e->id] ?? null; @endphp
                    <li wire:key="j-{{ $e->id }}" class="py-2.5 flex flex-wrap items-center gap-2">
                        <span class="flex-1 min-w-[9rem] font-medium text-slate-900 truncate">{{ $e->nom }}</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($S as $code => [$lab, $short])
                                <button type="button" wire:click="marquer({{ $e->id }}, '{{ $cur === $code ? '' : $code }}')" title="{{ $lab }}" aria-pressed="{{ $cur === $code ? 'true' : 'false' }}"
                                    class="h-9 min-w-[2.75rem] px-2 rounded-lg border text-sm font-semibold {{ $cur === $code ? $chipOn[$code] : 'bg-white border-slate-300 text-slate-600 hover:border-slate-500' }}">{{ $short }}<span class="hidden sm:inline"> {{ $code === 'P' ? 'Présent' : ($code === 'D' ? 'Demi' : ($code === 'A' ? 'Absent' : ($code === 'C' ? 'Congé' : 'Férié'))) }}</span></button>
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- month grid --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center gap-2">
                <button type="button" wire:click="moisPrecedent" class="h-9 w-9 grid place-items-center rounded-lg border border-slate-300 hover:bg-slate-50" aria-label="Mois précédent"><x-sp-icon name="chevron" class="w-4 h-4 rotate-180" /></button>
                <span class="min-w-[9rem] text-center font-semibold text-slate-900">{{ $moisLabel }}</span>
                <button type="button" wire:click="moisSuivant" class="h-9 w-9 grid place-items-center rounded-lg border border-slate-300 hover:bg-slate-50" aria-label="Mois suivant"><x-sp-icon name="chevron" class="w-4 h-4" /></button>
                <div class="ms-auto flex flex-wrap gap-2 text-xs">
                    @foreach ($S as $code => [$lab, $short])<span class="inline-flex items-center gap-1"><span class="grid place-items-center w-5 h-5 rounded {{ $cell[$code] }} font-bold">{{ $short }}</span>{{ $lab }}</span>@endforeach
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="text-sm border-separate border-spacing-0">
                    <thead>
                        <tr class="text-xs text-slate-500">
                            <th class="sticky left-0 z-10 bg-white px-3 py-2 text-start font-semibold min-w-[10rem] border-b border-slate-100">Employé</th>
                            @for ($d = 1; $d <= $nbJours; $d++)
                                @php $dt = $debut->copy()->day($d); @endphp
                                <th class="px-0.5 py-2 font-semibold text-center border-b border-slate-100 {{ $dt->isSunday() ? 'text-slate-300' : '' }} {{ $dt->toDateString() === $today ? 'text-[#16a34a]' : '' }}">{{ $d }}<span class="block font-normal">{{ mb_substr($dt->locale('fr')->translatedFormat('D'), 0, 1) }}</span></th>
                            @endfor
                            <th class="px-3 py-2 text-end font-semibold border-b border-slate-100">Jours</th>
                            <th class="px-3 py-2 text-end font-semibold border-b border-slate-100">Abs.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employes as $e)
                            <tr wire:key="g-{{ $e->id }}">
                                <td class="sticky left-0 z-10 bg-white px-3 py-1.5 font-medium text-slate-900 border-b border-slate-50 whitespace-nowrap"><a href="{{ route('personnel.show', $e->id) }}" wire:navigate class="hover:underline">{{ $e->nom }}</a></td>
                                @for ($d = 1; $d <= $nbJours; $d++)
                                    @php $s = $grid[$e->id][$d] ?? null; $date = $debut->copy()->day($d)->toDateString(); @endphp
                                    <td class="p-0.5 border-b border-slate-50">
                                        <button type="button" wire:click="cycle({{ $e->id }}, '{{ $date }}')" title="{{ $s ? $S[$s][0] : 'Non pointé' }} — {{ \Illuminate\Support\Carbon::parse($date)->format('d/m') }}"
                                            class="grid place-items-center w-7 h-7 rounded text-xs font-bold {{ $s ? $cell[$s] : 'bg-slate-50 text-slate-300 hover:bg-slate-100' }}">{{ $s ? $S[$s][1] : '·' }}</button>
                                    </td>
                                @endfor
                                <td class="px-3 py-1.5 text-end font-semibold tabular-nums border-b border-slate-50">{{ rtrim(rtrim(number_format($totaux[$e->id]['jours'], 1, ',', ' '), '0'), ',') }}</td>
                                <td class="px-3 py-1.5 text-end tabular-nums border-b border-slate-50 {{ $totaux[$e->id]['absences'] > 0 ? 'text-red-700 font-semibold' : 'text-slate-400' }}">{{ rtrim(rtrim(number_format($totaux[$e->id]['absences'], 1, ',', ' '), '0'), ',') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="px-4 py-3 text-xs text-slate-500 border-t border-slate-100">Cliquez sur un jour pour passer de ✓ (présent) à ½ (demi-journée), ✗ (absent), C (congé), F (férié), puis vide.</p>
        </div>
    @endif
</div>
