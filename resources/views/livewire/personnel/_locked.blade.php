{{-- Module Personnel on the Free plan: what it does and where to upgrade (2026-11-28) --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 max-w-2xl">
    <div class="flex items-center gap-3">
        <span class="grid place-items-center w-11 h-11 rounded-xl bg-[#ffcc00] text-zinc-900"><x-sp-icon name="user" class="w-6 h-6" /></span>
        <div>
            <p class="text-lg font-bold text-slate-900">Le module Personnel est inclus dans les plans Pro et Premium</p>
            <p class="text-sm text-slate-500">Vos données restent là : passez au plan Pro pour l’ouvrir.</p>
        </div>
    </div>
    <ul class="mt-5 space-y-2 text-sm text-slate-700">
        <li class="flex gap-2"><span class="text-[#16a34a] font-bold">✓</span> Vos employés payés à la journée, à la semaine ou au mois</li>
        <li class="flex gap-2"><span class="text-[#16a34a] font-bold">✓</span> Le pointage des jours travaillés, en un clic chaque matin</li>
        <li class="flex gap-2"><span class="text-[#16a34a] font-bold">✓</span> Les avances et les crédits, retirés automatiquement de la paie</li>
        <li class="flex gap-2"><span class="text-[#16a34a] font-bold">✓</span> La paie calculée, enregistrée dans vos dépenses, avec un reçu PDF à faire signer</li>
    </ul>
    <a href="{{ route('abonnement') }}" wire:navigate class="mt-6 inline-flex items-center gap-2 h-11 px-5 rounded-lg bg-[#16a34a] text-white text-sm font-semibold hover:bg-[#15803d]">Voir les plans Pro et Premium</a>
</div>
