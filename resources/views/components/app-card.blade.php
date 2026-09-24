@props(['app', 'index' => 0])

@php
    // === Palet warna — tiap card beda ===
    $palette = [
        'rgba(37,99,235,0.45)', // blue
        'rgba(147,51,234,0.45)', // purple
        'rgba(16,185,129,0.45)', // emerald
        'rgba(239,68,68,0.45)', // red
        'rgba(249,115,22,0.45)', // orange
        'rgba(236,72,153,0.45)', // pink
        'rgba(20,184,166,0.45)', // teal
        'rgba(139,92,246,0.45)', // violet
        'rgba(6,182,212,0.45)', // cyan
        'rgba(244,63,94,0.45)', // rose
        'rgba(245,158,11,0.45)', // amber
        'rgba(132,204,22,0.45)', // lime
        'rgba(217,70,239,0.45)', // fuchsia
        'rgba(14,165,233,0.45)', // sky
        'rgba(79,70,229,0.45)', // indigo
    ];

    // Pilih warna berdasarkan index card → tiap card beda warna
    $overlayColor = $palette[$index % count($palette)];
@endphp

<a href="{{ route('app.click', $app->id) }}" target="_blank" data-name="{{ strtolower($app->nama) }}"
    class="group flex flex-col h-72 rounded-2xl overflow-hidden
           shadow-lg transition-all duration-300
           hover:-translate-y-1 hover:shadow-2xl"
    style="--card-color: {{ $overlayColor }};">

    <!-- IMAGE (bagian atas) -->
    <div class="relative w-full flex-1 overflow-hidden">
        <img src="{{ $app->gambar ? asset('storage/' . $app->gambar) : asset('images/bg_ubpsla.jpeg') }}"
            class="absolute inset-0 w-full h-full object-cover
               transition-transform duration-500 ease-in-out
               group-hover:scale-110"
            alt="{{ $app->nama }}">

        <!-- 🎨 LAYER WARNA (beda tiap card) -->
        <div class="absolute inset-0 transition-opacity duration-300 group-hover:opacity-90"
            style="background-color: {{ $overlayColor }};">
        </div>

        <!-- Layer gradient hitam dari bawah -->
        <div class="absolute inset-0"
            style="background: linear-gradient(
                 to top,
                 rgba(0,0,0,0.55),
                 rgba(0,0,0,0.15),
                 transparent
             );">
        </div>

        <!-- Efek kilau saat hover -->
        <div
            class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500
                    bg-gradient-to-tr from-transparent via-white/10 to-transparent">
        </div>
    </div>

    <!-- BAR PUTIH (bagian bawah) -->
    <div class="bg-white px-5 py-4 flex flex-col justify-between gap-3 min-h-[110px]">
        <!-- APP NAME -->
        <div class="text-xl font-bold text-gray-900 leading-tight line-clamp-2">
            {{ $app->nama }}
        </div>

        <!-- CLICKS BADGE (abu-abu pill) -->
        <div
            class="inline-flex self-start items-center
                    px-3.5 py-1.5 rounded-full
                    bg-gray-100 text-gray-500
                    text-sm font-medium">
            {{ number_format($app->clicks) }} klik
        </div>
    </div>

</a>
