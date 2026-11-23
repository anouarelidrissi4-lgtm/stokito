@props(['title' => '', 'ref' => ''])
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>{{ $title }} | Stokito</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=barlow:400,500,600,700|barlow-condensed:700,800|ibm-plex-mono:400,500&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Barlow', sans-serif; }
        .legal h2 { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 26px; text-transform: uppercase; margin: 34px 0 10px; }
        .legal p, .legal li { color: #333; line-height: 1.75; }
        .legal ul { list-style: square; padding-left: 22px; margin: 8px 0 14px; }
        .legal a { text-decoration: underline; text-decoration-color: #ffcc00; text-decoration-thickness: 2px; }
    </style>
</head>
<body class="bg-[#f4f2ec] text-[#111]">
    <header class="bg-[#111] text-white border-b-4 border-[#ffcc00]">
        <div class="max-w-3xl mx-auto h-16 px-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3"><span class="grid place-items-center w-9 h-9 bg-[#ffcc00] text-[#111]"><x-sp-icon name="cube" class="w-5 h-5" /></span><span class="ind-h text-2xl tracking-wide">STOKITO</span></a>
            <a href="{{ route('register') }}" class="ind-h uppercase tracking-wider text-[#ffcc00]">Créer un compte</a>
        </div>
    </header>
    <main class="max-w-3xl mx-auto px-4 py-12 legal">
        <p class="ind-mono text-xs text-[#666]">DOC · {{ $ref }} · Mise à jour : 23/11/2026</p>
        <h1 class="ind-h text-5xl uppercase leading-none mt-2">{{ $title }}</h1>
        {{ $slot }}
    </main>
</body>
</html>
