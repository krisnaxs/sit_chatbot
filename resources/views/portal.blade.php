<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SURALAYA INFORMATION CENTER</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <style>
        /* ===== Floating Orbs (subtle, agar tetap terlihat di atas gambar) ===== */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.25;
            pointer-events: none;
            animation: float 14s ease-in-out infinite;
            z-index: 1;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: #3b82f6;
            top: -100px;
            left: -100px;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: #8b5cf6;
            bottom: -150px;
            right: -150px;
            animation-delay: -4s;
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: #06b6d4;
            top: 40%;
            left: 50%;
            animation-delay: -8s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(40px, -40px) scale(1.05);
            }

            66% {
                transform: translate(-30px, 30px) scale(0.95);
            }
        }

        /* ===== Glassmorphism ===== */
        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        /* ===== Slide Title Glow ===== */
        .slide-title-glow {
            text-shadow:
                0 0 20px rgba(59, 130, 246, 0.8),
                0 0 40px rgba(59, 130, 246, 0.5),
                0 2px 4px rgba(0, 0, 0, 0.5);
        }

        /* ===== Nav Buttons ===== */
        .nav-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 0 25px rgba(59, 130, 246, 0.7);
            background: rgba(59, 130, 246, 0.25) !important;
        }

        /* ===== Swiper Pagination Custom ===== */
        .swiper-pagination-bullet {
            background: rgba(255, 255, 255, 0.5) !important;
            width: 10px !important;
            height: 10px !important;
            opacity: 1 !important;
            transition: all 0.3s ease;
        }

        .swiper-pagination-bullet-active {
            background: #3b82f6 !important;
            width: 32px !important;
            border-radius: 6px !important;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.8);
        }

        /* ===== Swiper Slide Fade In ===== */
        .swiper-slide {
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .swiper-slide-active {
            opacity: 1;
        }

        .swiper-slide-active .grid>* {
            animation: cardIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) backwards;
        }

        .swiper-slide-active .grid>*:nth-child(1) {
            animation-delay: 0.05s;
        }

        .swiper-slide-active .grid>*:nth-child(2) {
            animation-delay: 0.10s;
        }

        .swiper-slide-active .grid>*:nth-child(3) {
            animation-delay: 0.15s;
        }

        .swiper-slide-active .grid>*:nth-child(4) {
            animation-delay: 0.20s;
        }

        .swiper-slide-active .grid>*:nth-child(5) {
            animation-delay: 0.25s;
        }

        .swiper-slide-active .grid>*:nth-child(6) {
            animation-delay: 0.30s;
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ===== Scrollbar ===== */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
            border-radius: 4px;
        }
    </style>
</head>

<body class="font-sans relative min-h-screen overflow-x-hidden">

    <!-- Background Image + Overlay -->
    <div class="fixed inset-0 -z-10">
        <img src="{{ asset('images/bg_ubpsla.jpeg') }}" class="w-full h-full object-cover brightness-50 opacity-70"
            alt="Background">

        <div class="absolute inset-0 bg-gray-100/30"></div>
    </div>

    <!-- Floating Orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <!-- HEADER COMPONENT (termasuk Chat Widget FAB) -->
    <x-header title="Portal Aplikasi" placeholder="Cari aplikasi..." />

    <!-- SIDEBAR (hanya muncul kalau login) -->
    <x-sidebar />

    {{-- 🆕 Link Mode Sederhana (untuk Firefox lama / pilihan manual) --}}
    <div class="fixed top-20 right-4 z-50">
        <a href="{{ route('portal.legacy') }}"
            class="glass text-white text-xs px-3 py-1.5 rounded-lg hover:bg-white/20 transition inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Mode sederhana
        </a>
    </div>

    @php
        $firstSlideKey = $slides->keys()->first();
        $slidesKeys = $slides->keys()->toArray();
    @endphp

    <!-- CONTENT — margin-left reaktif terhadap sidebar collapse -->
    <div x-data="pageLayout()"
        :class="{
            'lg:ml-16': @auth true @else false @endauth && collapsed,
            'lg:ml-64': @auth true @else false @endauth && !collapsed
        }"
        class="max-w-6xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <!-- SLIDE TITLE + NAV BUTTONS — padding-left reaktif -->
        <div x-data="pageLayout()"
            :class="{
                'lg:pl-16': @auth true @else false @endauth && collapsed,
                'lg:pl-64': @auth true @else false @endauth && !collapsed
            }"
            class="fixed top-20 left-0 w-full z-40 py-4 flex items-center justify-center gap-6 pointer-events-none transition-all duration-300">

            <!-- Tombol Prev -->
            <button id="prevButton"
                class="nav-btn pointer-events-auto glass text-white p-3 rounded-full hover:text-blue-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Judul Slide -->
            <div id="slideTitle" class="text-center transition-all duration-500 pointer-events-auto min-w-[240px]">
                <h2 id="slideHeading"
                    class="slide-title-glow text-white text-3xl md:text-4xl font-black tracking-[0.2em] uppercase">
                    {{ strtoupper($firstSlideKey) }}
                </h2>
                <div
                    class="mt-3 h-[3px] w-24 mx-auto bg-gradient-to-r from-transparent via-blue-400 to-transparent rounded-full shadow-lg shadow-blue-500/50">
                </div>
            </div>

            <script>
                const slideTitle = document.getElementById('slideTitle');
                window.addEventListener('scroll', () => {
                    const scrollY = window.scrollY;
                    slideTitle.style.opacity = Math.max(1 - scrollY / 200, 0);
                    slideTitle.style.transform = `translateY(-${scrollY / 5}px)`;
                });
            </script>

            <!-- Tombol Next -->
            <button id="nextButton"
                class="nav-btn pointer-events-auto glass text-white p-3 rounded-full hover:text-blue-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>

        </div>

        <!-- SWIPER -->
        <div class="swiper mt-24">
            <div class="swiper-wrapper pb-16">

                @foreach ($slides as $apps)
                    <div class="swiper-slide">
                        <div
                            class="grid grid-cols-4 gap-6 max-lg:grid-cols-3 max-md:grid-cols-2 max-sm:grid-cols-1 px-2">
                            @foreach ($apps as $app)
                                <x-app-card :app="$app" :index="$loop->index" />
                            @endforeach
                        </div>
                    </div>
                @endforeach

            </div>

            <!-- Pagination -->
            <div class="swiper-pagination !bottom-6"></div>
        </div>

    </div>

    <!-- SCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        const slidesKeys = @json($slidesKeys);
        const slideTitleEl = document.getElementById('slideHeading');

        const swiper = new Swiper('.swiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            speed: 700,
            pagination: {
                el: '.swiper-pagination',
                clickable: true
            },
            navigation: {
                nextEl: '#nextButton',
                prevEl: '#prevButton',
            },
            on: {
                slideChange: function() {
                    const currentSlide = slidesKeys[this.activeIndex];
                    if (currentSlide) {
                        slideTitleEl.style.opacity = 0;
                        slideTitleEl.style.transform = 'translateY(-10px)';
                        setTimeout(() => {
                            slideTitleEl.textContent = currentSlide.toUpperCase();
                            slideTitleEl.style.opacity = 1;
                            slideTitleEl.style.transform = 'translateY(0)';
                        }, 150);
                    }

                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            }
        });

        slideTitleEl.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

        // ===== SEARCH FUNCTION =====
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const keyword = this.value.toLowerCase();
                let firstMatchedSlide = null;

                document.querySelectorAll('.swiper-slide').forEach((slide, slideIndex) => {
                    let slideHasResult = false;

                    slide.querySelectorAll('[data-name]').forEach(card => {
                        const name = card.dataset.name.toLowerCase();
                        if (name.includes(keyword)) {
                            card.classList.remove('hidden');
                            slideHasResult = true;
                        } else {
                            card.classList.add('hidden');
                        }
                    });

                    if (slideHasResult && firstMatchedSlide === null) {
                        firstMatchedSlide = slideIndex;
                    }
                });

                if (firstMatchedSlide !== null) {
                    swiper.slideTo(firstMatchedSlide);
                }

                swiper.update();
            });
        }

        // 🆕 pageLayout — handle sidebar collapse
        function pageLayout() {
            return {
                collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
                init() {
                    window.addEventListener('sidebar-toggled', (e) => {
                        this.collapsed = e.detail.collapsed;
                    });
                }
            }
        }
    </script>

</body>

</html>
