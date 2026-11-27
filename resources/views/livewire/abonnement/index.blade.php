{{-- Administration › Abonnement (2026-11-27): current plan, usage this month, the 3 plans and how to pay (WhatsApp / transfer now, PayPal later) --}}
@php
    $L = \App\Support\Plans::LABELS;
    $ends = $project?->plan_ends_at;
    $daysLeft = $ends && $plan !== 'free' ? max(0, (int) now()->startOfDay()->diffInDays($ends->copy()->startOfDay(), false)) : null;
    $fmtLimit = fn ($used, $limit, $none = 'Non inclus') => $limit === null ? $used.' · illimité' : ($limit === 0 ? $none : $used.' / '.$limit);
    $prices = [];
    foreach (['pro', 'premium'] as $p) {
        foreach (\App\Support\Plans::PERIODS as $k => $per) {
            $prices[$p][$k] = \App\Support\Plans::price($p, $k, $currency);
        }
    }
    $wa = function ($plan, $period) use ($societe, $currency) {
        return 'https://wa.me/212676035777?text='.rawurlencode('Bonjour, je souhaite passer au plan '.\App\Support\Plans::LABELS[$plan].' ('.\App\Support\Plans::PERIODS[$period]['label'].', '.\App\Support\Plans::price($plan, $period, $currency).') pour la société '.($societe?->name ?? '').' (compte : '.auth()->user()->email.').');
    };
    // PayPal.me (2026-11-27): PayPal has no MAD, so PayPal amounts are always in euros
    $paypal = fn ($plan, $period) => 'https://paypal.me/'.\App\Support\Plans::PAYPAL_ME.'/'.\App\Support\Plans::PRICES[$plan][$period]['EUR'].'EUR';
    $features = [
        'free' => [['Devis, bons de livraison, stock et caisse', true], [\App\Support\Plans::FREE_INVOICES_PER_MONTH.' factures par mois', true], ['Copilot IA', false], ['Scanner IA des factures', false]],
        'pro' => [['Devis, bons de livraison, stock et caisse', true], ['Factures illimitées', true], [config('services.anthropic.copilot_per_month', 150).' messages Copilot par mois', true], [config('services.anthropic.scans_per_month', 30).' scans de factures par mois', true]],
        'premium' => [['Devis, bons de livraison, stock et caisse', true], ['Factures illimitées', true], ['Copilot IA illimité', true], ['Scanner IA illimité', true]],
    ];
@endphp
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-6xl" x-data="{ period: 'annee' }">
    <h1 class="text-2xl font-bold text-zinc-900">Abonnement</h1>

    {{-- current plan + usage --}}
    <div class="mt-5 grid lg:grid-cols-[1.1fr_2fr] gap-4">
        <div class="rounded-2xl bg-zinc-900 text-white p-6">
            <p class="text-sm text-zinc-400">Votre plan</p>
            <p class="mt-1 flex items-center gap-3 text-3xl font-extrabold">
                {{ $L[$plan] }}
                @if ($project?->plan_trial && $plan !== 'free')<span class="rounded-full bg-[#ffcc00] px-3 py-1 text-xs font-bold text-zinc-900">Essai gratuit</span>@endif
            </p>
            @if ($plan !== 'free' && $ends)
                <p class="mt-3 text-sm text-zinc-300">Jusqu’au <strong class="text-white">{{ $ends->format('d/m/Y') }}</strong>{{ $daysLeft !== null ? ' · '.$daysLeft.' jour'.($daysLeft > 1 ? 's' : '').' restant'.($daysLeft > 1 ? 's' : '') : '' }}</p>
                <p class="mt-1 text-xs text-zinc-400">Ensuite, votre compte passe au plan Free : vos données restent là.</p>
            @elseif ($plan === 'free')
                <p class="mt-3 text-sm text-zinc-300">Gratuit, sans carte bancaire.</p>
            @endif
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
            <p class="text-sm font-semibold text-zinc-900">Ce mois-ci</p>
            <div class="mt-3 grid sm:grid-cols-3 gap-3">
                @foreach ([['Factures', $usage['factures'], $limits['factures'], 'illimitées'], ['Messages Copilot', $usage['copilot'], $limits['copilot'], null], ['Scans de factures', $usage['scans'], $limits['scans'], null]] as [$label, $used, $limit])
                    @php $pct = $limit ? min(100, round($used / $limit * 100)) : 0; @endphp
                    <div class="rounded-xl bg-zinc-50 p-4">
                        <p class="text-xs font-medium text-zinc-500">{{ $label }}</p>
                        <p class="mt-1 text-lg font-bold {{ $limit === 0 ? 'text-zinc-400' : 'text-zinc-900' }}">{{ $fmtLimit($used, $limit) }}</p>
                        @if ($limit)
                            <div class="mt-2 h-2 rounded-full bg-zinc-200"><div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-red-500' : 'bg-[#ffcc00]' }}" style="width: {{ $pct }}%"></div></div>
                        @endif
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-zinc-400">Les compteurs repartent à zéro le 1er de chaque mois.</p>
        </div>
    </div>

    {{-- period switch --}}
    <div class="mt-10 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-bold text-zinc-900">Choisir un plan</h2>
        <div class="inline-flex rounded-xl bg-zinc-100 p-1" role="radiogroup" aria-label="Durée">
            <button type="button" @click="period = 'trimestre'" :class="period === 'trimestre' ? 'bg-white shadow text-zinc-900' : 'text-zinc-500'" class="h-9 px-4 rounded-lg text-sm font-semibold">3 mois</button>
            <button type="button" @click="period = 'annee'" :class="period === 'annee' ? 'bg-white shadow text-zinc-900' : 'text-zinc-500'" class="h-9 px-4 rounded-lg text-sm font-semibold">1 an <span class="ms-1 rounded-full bg-[#ffcc00] px-2 py-0.5 text-[11px] font-bold text-zinc-900">économique</span></button>
        </div>
    </div>

    <div class="mt-5 grid md:grid-cols-3 gap-4">
        @foreach (['free', 'pro', 'premium'] as $p)
            @php $isCurrent = $p === $plan; $top = $p === 'premium'; @endphp
            <div class="relative flex flex-col rounded-2xl border bg-white p-6 {{ $top ? 'border-zinc-900 ring-2 ring-[#ffcc00]' : 'border-zinc-200' }}">
                @if ($top)<span class="absolute -top-3 start-6 rounded-full bg-[#ffcc00] px-3 py-1 text-xs font-bold text-zinc-900">Le plus complet</span>@endif
                <p class="text-lg font-extrabold text-zinc-900">{{ $L[$p] }}</p>
                <p class="mt-3 min-h-[3.5rem]">
                    @if ($p === 'free')
                        <span class="text-3xl font-extrabold text-zinc-900">0</span> <span class="text-sm text-zinc-500">pour toujours</span>
                    @else
                        <span class="text-3xl font-extrabold text-zinc-900" x-text="period === 'annee' ? @js($prices[$p]['annee']) : @js($prices[$p]['trimestre'])"></span>
                        <span class="text-sm text-zinc-500" x-text="period === 'annee' ? '/ an' : '/ 3 mois'"></span>
                    @endif
                </p>
                <ul class="mt-4 space-y-2 text-sm flex-1">
                    @foreach ($features[$p] as [$f, $ok])
                        <li class="flex items-start gap-2 {{ $ok ? 'text-zinc-800' : 'text-zinc-400 line-through' }}">
                            <span class="mt-0.5 grid place-items-center w-5 h-5 shrink-0 rounded-full {{ $ok ? 'bg-[#ffcc00] text-zinc-900' : 'bg-zinc-100 text-zinc-400' }}"><x-sp-icon :name="$ok ? 'check' : 'plus'" class="w-3 h-3 {{ $ok ? '' : 'rotate-45' }}" /></span>{{ $f }}
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6">
                    @php $renew = $isCurrent && ! ($project?->plan_trial); @endphp
                    @if ($p === 'free')
                        <span class="flex h-11 items-center justify-center rounded-lg border border-zinc-200 text-sm {{ $isCurrent ? 'font-semibold text-zinc-600' : 'text-zinc-400' }}">{{ $isCurrent ? 'Votre plan actuel' : 'Toujours disponible' }}</span>
                    @else
                        @if ($renew)<p class="mb-2 text-center text-xs font-semibold text-zinc-500">Votre plan actuel</p>@endif
                        <a :href="period === 'annee' ? @js($wa($p, 'annee')) : @js($wa($p, 'trimestre'))" target="_blank" rel="noopener"
                           class="flex h-11 items-center justify-center gap-2 rounded-lg text-sm font-semibold {{ $top ? 'bg-zinc-900 text-white hover:bg-zinc-700' : 'bg-[#ffcc00] text-zinc-900 hover:bg-[#ffd633]' }}">
                            <x-sp-icon name="whatsapp" class="w-4 h-4" /> {{ $renew ? 'Renouveler' : 'Choisir' }} {{ $L[$p] }}
                        </a>
                        <a :href="period === 'annee' ? @js($paypal($p, 'annee')) : @js($paypal($p, 'trimestre'))" target="_blank" rel="noopener"
                           class="mt-2 flex h-11 items-center justify-center gap-1.5 rounded-lg border border-zinc-300 bg-white text-sm font-semibold text-zinc-800 hover:border-zinc-900">
                            Payer avec PayPal · <span x-text="period === 'annee' ? @js(\App\Support\Plans::PRICES[$p]['annee']['EUR'].' €') : @js(\App\Support\Plans::PRICES[$p]['trimestre']['EUR'].' €')"></span>
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 rounded-2xl border border-zinc-200 bg-zinc-50 p-5 text-sm text-zinc-600 leading-relaxed">
        <p><strong class="text-zinc-900">Comment payer ?</strong> Cliquez sur « Choisir » : votre demande part sur WhatsApp. Nous vous envoyons les coordonnées pour payer par virement ou versement bancaire, et votre plan est activé dès réception du paiement.</p>
        <p class="mt-2"><strong class="text-zinc-900">PayPal ?</strong> Cliquez sur « Payer avec PayPal » (montant en euros) et choisissez « Biens et services ». Envoyez ensuite la confirmation sur WhatsApp : votre plan est activé dans la journée.</p>
    </div>
</div>
