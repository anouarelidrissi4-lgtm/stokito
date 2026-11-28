{{-- Google Tag Manager (GA4 + Meta Pixel, 2026-11-28) with consent (2026-11-26). GTM is loaded ONLY after the visitor accepts in the cookie banner
     (CNIL: no audience cookies before consent). Production only. Events pushed with session()->flash('gtm_event', [...]). --}}
@if (app()->environment('production'))
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){ dataLayer.push(arguments); }
    gtag('consent', 'default', { ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'denied' });
    @if (is_array(session('gtm_event')))
    window.stkPending = @json(session('gtm_event'));   // pushed after gtm.js so GA4 is ready
    @endif
    window.stkLoadGtm = function () {
        if (window.stkGtmLoaded) return;
        window.stkGtmLoaded = true;
        gtag('consent', 'update', { analytics_storage: 'granted', ad_storage: 'granted', ad_user_data: 'granted', ad_personalization: 'granted' });   // the banner covers audience + ads (Meta) since 2026-11-28
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-53K7JBJH');
        if (window.stkPending) { dataLayer.push(window.stkPending); window.stkPending = null; }
    };
    try { if (localStorage.getItem('stk_consent_v2') === 'granted') window.stkLoadGtm(); } catch (e) {}   // v2 = consent that includes ads: visitors who only agreed to Analytics are asked again
</script>
@endif
