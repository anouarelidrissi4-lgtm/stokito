@props(['title' => '', 'ref' => '', 'updated' => '23/11/2026'])
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicons />
    <meta name="robots" content="noindex, follow">
    <title>{{ $title }} | Stokito</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .legal h2 { font-weight: 700; letter-spacing: -0.02em; font-size: 22px; margin: 34px 0 10px; }
        .legal p, .legal li { color: #333; line-height: 1.75; }
        .legal ul { list-style: disc; padding-left: 22px; margin: 8px 0 14px; }
        .legal a { text-decoration: underline; text-decoration-color: #ffcc00; text-decoration-thickness: 2px; }
    </style>
</head>
<body class="bg-white text-zinc-900">
    <header class="sp-topbar backdrop-blur border-b border-zinc-200 text-zinc-900">
        <div class="max-w-3xl mx-auto h-16 px-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3"><span class="grid place-items-center w-9 h-9 rounded-lg bg-[#ffcc00] text-zinc-900"><x-sp-icon name="cube" class="w-5 h-5" /></span><span class="text-base font-medium">Stokito</span></a>
            <a href="{{ route('register') }}" class="text-sm font-semibold rounded-lg bg-zinc-900 text-white px-4 py-2">Créer un compte</a>
        </div>
    </header>
    <main class="max-w-3xl mx-auto px-4 py-12 legal">
        <p class="text-sm text-zinc-500">Mise à jour : {{ $updated }}</p>
        <h1 class="ind-h text-4xl leading-tight mt-2">{{ $title }}</h1>
        {{ $slot }}
    </main>
</body>
</html>
