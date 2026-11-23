@props(['name' => 'dot'])
@php
    // Outline icons (24x24, stroke). One place for every icon of the app shell.
    $paths = [
        'home' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h5v-6h4v6h5V9.5"/>',
        'cube' => '<path d="M12 2.5 3.5 7v10L12 21.5 20.5 17V7z"/><path d="M3.5 7 12 11.5 20.5 7M12 11.5v10"/>',
        'cart' => '<path d="M3 4h2l2.4 11h10.2L20 7H6.2"/><circle cx="9" cy="19.5" r="1.3"/><circle cx="17" cy="19.5" r="1.3"/>',
        'truck' => '<path d="M2.5 6h11v10h-11z"/><path d="M13.5 9.5h4l3 3.5V16h-7"/><circle cx="6.5" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
        'wallet' => '<path d="M3 7.5h16.5V20H3z"/><path d="M3 7.5 15 4v3.5"/><path d="M15.5 13.5h4"/>',
        'cog' => '<circle cx="12" cy="12" r="3"/><path d="M12 2.5v2.6M12 18.9v2.6M4.6 4.6l1.9 1.9M17.5 17.5l1.9 1.9M2.5 12h2.6M18.9 12h2.6M4.6 19.4l1.9-1.9M17.5 6.5l1.9-1.9"/>',
        'folder' => '<path d="M3 6.5h6l2 2h10V19H3z"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'chevron' => '<path d="m9 6 6 6-6 6"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'moon' => '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'logout' => '<path d="M15 4h4v16h-4"/><path d="M10 8l-4 4 4 4M6 12h10"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
        'doc' => '<path d="M6 2.5h8l4 4V21.5H6z"/><path d="M14 2.5v4h4M9 12h6M9 16h6"/>',
        'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'alert' => '<path d="M12 3 2 20h20z"/><path d="M12 10v4M12 17h.01"/>',
        'coins' => '<ellipse cx="9" cy="7" rx="6" ry="3"/><path d="M3 7v5c0 1.7 2.7 3 6 3s6-1.3 6-3V7"/><path d="M9 15v3c0 1.7 2.7 3 6 3s6-1.3 6-3v-5c0-1.7-2.7-3-6-3"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.5 3 14.5 0 18M12 3c-3 3.5-3 14.5 0 18"/>',
        'dot' => '<circle cx="12" cy="12" r="3"/>',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? $paths['dot'] !!}</svg>
