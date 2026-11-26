{{-- Cookie banner (2026-11-26): Accepter / Refuser with the same weight. The choice is kept in localStorage (stk_consent).
     "Gérer les cookies" in the footer calls stkConsent.open(). Needs partials.analytics in <head>. --}}
@if (app()->environment('production'))
@php
    $loc = app()->getLocale();
    $t = [
        'fr' => ['Nous aimerions mesurer l’audience du site avec Google Analytics, pour savoir quelles pages vous aident. Rien n’est déposé sans votre accord.', 'En savoir plus', 'Refuser', 'Accepter'],
        'ar' => ['نود قياس عدد زوار الموقع عبر Google Analytics لمعرفة الصفحات المفيدة لكم. لا شيء يُحفظ دون موافقتكم.', 'اعرف المزيد', 'رفض', 'قبول'],
        'en' => ['We would like to measure the site audience with Google Analytics, to know which pages help you. Nothing is stored without your consent.', 'Learn more', 'Refuse', 'Accept'],
    ][$loc] ?? null;
    $t ??= ['Nous aimerions mesurer l’audience du site avec Google Analytics, pour savoir quelles pages vous aident. Rien n’est déposé sans votre accord.', 'En savoir plus', 'Refuser', 'Accepter'];
@endphp
<div id="stk-cookie" hidden role="dialog" aria-live="polite" aria-label="Cookies" class="fixed z-[60] bottom-3 inset-x-3 sm:inset-x-auto sm:end-4 sm:bottom-4 sm:max-w-sm rounded-xl border border-zinc-200 bg-white p-4 text-zinc-800 shadow-2xl">
    <p class="text-sm leading-relaxed">{{ $t[0] }} <a href="{{ route('legal.privacy') }}#cookies" class="font-semibold text-zinc-900 underline decoration-[#ffcc00] decoration-2 underline-offset-2">{{ $t[1] }}</a></p>
    <div class="mt-3 grid grid-cols-2 gap-2">
        <button type="button" onclick="stkConsent.set('denied')" class="h-10 rounded-lg border border-zinc-300 bg-white text-sm font-semibold text-zinc-900 hover:border-zinc-900">{{ $t[2] }}</button>
        <button type="button" onclick="stkConsent.set('granted')" class="h-10 rounded-lg border border-zinc-900 bg-zinc-900 text-sm font-semibold text-white hover:bg-zinc-700">{{ $t[3] }}</button>
    </div>
</div>
<script>
    window.stkConsent = {
        get: function () { try { return localStorage.getItem('stk_consent'); } catch (e) { return null; } },
        set: function (v) {
            try { localStorage.setItem('stk_consent', v); } catch (e) {}
            document.getElementById('stk-cookie').hidden = true;
            if (v === 'granted') { window.stkLoadGtm && window.stkLoadGtm(); return; }
            if (window.gtag) gtag('consent', 'update', { analytics_storage: 'denied' });
            document.cookie.split(';').forEach(function (c) {            // remove Google Analytics cookies already set
                var n = c.split('=')[0].trim();
                if (/^_ga/.test(n)) ['', '.' + location.hostname.replace(/^www\./, '')].forEach(function (d) {
                    document.cookie = n + '=; Max-Age=0; path=/' + (d ? '; domain=' + d : '');
                });
            });
        },
        open: function () { document.getElementById('stk-cookie').hidden = false; }
    };
    if (!window.stkConsent.get()) document.getElementById('stk-cookie').hidden = false;
</script>
@endif
