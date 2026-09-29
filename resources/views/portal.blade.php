<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <title>SURALAYA INFORMATION CENTER</title>

    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <style>
        /* =========================================================
           FLOATING ORBS
        ========================================================= */

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


        /* =========================================================
           GLASSMORPHISM
        ========================================================= */

        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }


        /* =========================================================
           SLIDE TITLE
        ========================================================= */

        .slide-title-glow {
            text-shadow:
                0 0 20px rgba(59, 130, 246, 0.8),
                0 0 40px rgba(59, 130, 246, 0.5),
                0 2px 4px rgba(0, 0, 0, 0.5);
        }


        /* =========================================================
           NAV BUTTON
        ========================================================= */

        .portal-nav-button {
            width: 42px;
            height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease,
                background 0.25s ease,
                opacity 0.25s ease;
        }

        .portal-nav-button:hover {
            transform: scale(1.08);

            box-shadow:
                0 0 25px rgba(59, 130, 246, 0.7);

            background: rgba(59, 130, 246, 0.25) !important;
        }

        .portal-nav-button:active {
            transform: scale(0.94);
        }

        @media (min-width: 640px) {

            .portal-nav-button {
                width: 48px;
                height: 48px;
            }
        }


        /* =========================================================
           FORCE NAV BUTTON VISIBLE
        ========================================================= */

        #prevButton,
        #nextButton {
            display: flex !important;
            visibility: visible !important;

            position: relative;

            z-index: 9999 !important;
        }


        /* Swiper disabled tetap terlihat */
        #prevButton.swiper-button-disabled,
        #nextButton.swiper-button-disabled {
            display: flex !important;
            visibility: visible !important;

            opacity: 0.4 !important;

            pointer-events: auto;
        }


        /* =========================================================
           SWIPER
        ========================================================= */

        .portal-swiper {
            width: 100%;
            overflow: hidden;
        }

        .swiper-slide {
            opacity: 0;

            transition:
                opacity 0.5s ease;
        }

        .swiper-slide-active {
            opacity: 1;
        }


        /* =========================================================
           CARD ANIMATION
        ========================================================= */

        .swiper-slide-active .grid>* {
            animation:
                cardIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) backwards;
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

        @keyframes cardIn {

            from {
                opacity: 0;
                transform:
                    translateY(30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform:
                    translateY(0) scale(1);
            }
        }


        /* =========================================================
           PAGINATION
        ========================================================= */

        .portal-pagination {
            position: relative !important;

            left: auto !important;
            right: auto !important;

            bottom: auto !important;

            margin-top: 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            width: 100% !important;

            z-index: 20;
        }

        .portal-pagination .swiper-pagination-bullet {
            background: rgba(255, 255, 255, 0.5) !important;

            width: 8px !important;
            height: 8px !important;

            opacity: 1 !important;

            transition:
                all 0.3s ease;
        }

        .portal-pagination .swiper-pagination-bullet-active {
            background: #3b82f6 !important;

            width: 24px !important;

            border-radius: 6px !important;

            box-shadow:
                0 0 15px rgba(59, 130, 246, 0.8);
        }

        @media (min-width: 640px) {

            .portal-pagination .swiper-pagination-bullet {
                width: 10px !important;
                height: 10px !important;
            }

            .portal-pagination .swiper-pagination-bullet-active {
                width: 32px !important;
            }
        }


        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 639px) {

            body {
                overflow-x: hidden;
            }

            .orb-1 {
                width: 250px;
                height: 250px;
            }

            .orb-2 {
                width: 300px;
                height: 300px;
            }

            .orb-3 {
                width: 200px;
                height: 200px;
            }

            .portal-nav-wrapper {
                padding-left: 10px !important;
                padding-right: 10px !important;
            }

            .portal-nav-grid {
                grid-template-columns:
                    42px minmax(0, 1fr) 42px !important;

                gap: 8px !important;
            }

            .portal-title {
                max-width: 100%;
                overflow: hidden;
            }

            .portal-title h2 {
                font-size: 11px !important;
                letter-spacing: 0.05em !important;
            }

            .portal-swiper-container {
                margin-top: 108px !important;
            }
        }


        /* =========================================================
           TABLET / DESKTOP
        ========================================================= */

        @media (min-width: 640px) {

            .portal-swiper-container {
                margin-top: 120px;
            }
        }


        /* =========================================================
           SCROLLBAR
        ========================================================= */

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

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.8);
        }


        /* =========================================================
           SAFE AREA - ANDROID / IOS
        ========================================================= */

        @supports (padding: max(0px)) {

            .portal-nav-wrapper {
                padding-left:
                    max(10px, env(safe-area-inset-left));

                padding-right:
                    max(10px, env(safe-area-inset-right));
            }
        }
    </style>
</head>


<body class="font-sans relative min-h-screen overflow-x-hidden">


    <!-- =========================================================
         BACKGROUND
    ========================================================== -->

    <div class="fixed inset-0 -z-10">

        <img src="{{ asset('images/bg_ubpsla.jpeg') }}" class="w-full h-full object-cover brightness-50 opacity-70"
            alt="Background">

        <div class="absolute inset-0 bg-gray-100/30"></div>

    </div>


    <!-- =========================================================
         FLOATING ORBS
    ========================================================== -->

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>


    <!-- =========================================================
         HEADER
    ========================================================== -->

    <x-header title="Portal Aplikasi" placeholder="Cari aplikasi..." />


    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

    <x-sidebar />


    <!-- =========================================================
         LEGACY MODE
         DESKTOP ONLY
    ========================================================== -->

    <div class="hidden lg:block fixed top-20 right-4 z-50">

        <a href="{{ route('portal.legacy') }}"
            class="glass
                   text-white
                   text-xs
                   px-3
                   py-1.5
                   rounded-lg
                   hover:bg-white/20
                   transition
                   inline-flex
                   items-center
                   gap-1.5">

            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">

                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />

            </svg>

            Mode sederhana

        </a>

    </div>


    @php

        $firstSlideKey = $slides->keys()->first();

        $slidesKeys = $slides->keys()->values()->toArray();

    @endphp


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <div x-data="pageLayout()"
        :class="{
            'lg:ml-16': @auth true @else false @endauth && collapsed,
        
            'lg:ml-64': @auth true @else false @endauth && !collapsed
        }"
        class="w-full
               max-w-6xl
               mx-auto
               px-3
               sm:px-6
               py-4
               mt-2
               sm:mt-4
               lg:max-w-none
               transition-all
               duration-300">


        <!-- =====================================================
             TOP NAVIGATION
        ====================================================== -->

        <div :class="{
            'lg:pl-16': @auth true @else false @endauth && collapsed,
        
            'lg:pl-64': @auth true @else false @endauth && !collapsed
        }"
            class="portal-nav-wrapper
                   fixed
                   top-16
                   sm:top-20
                   left-0
                   right-0
                   z-[60]
                   py-2
                   sm:py-4
                   px-3
                   sm:px-6
                   pointer-events-none
                   transition-all
                   duration-300">


            <div
                class="portal-nav-grid
                       w-full
                       grid
                       grid-cols-[42px_minmax(0,1fr)_42px]
                       sm:grid-cols-[48px_minmax(0,1fr)_48px]
                       items-center
                       gap-2
                       sm:gap-6">


                <!-- =================================================
                     PREVIOUS
                ================================================== -->

                <button id="prevButton" type="button" aria-label="Aplikasi sebelumnya"
                    class="portal-nav-button
                           pointer-events-auto
                           glass
                           text-white
                           rounded-full
                           hover:text-blue-300">

                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 sm:w-6 sm:h-6" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">

                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />

                    </svg>

                </button>


                <!-- =================================================
                     TITLE
                ================================================== -->

                <div id="slideTitle"
                    class="portal-title
                           min-w-0
                           w-full
                           text-center
                           pointer-events-none
                           transition-all
                           duration-500">


                    <h2 id="slideHeading"
                        class="slide-title-glow
                               text-white
                               text-xs
                               sm:text-2xl
                               md:text-3xl
                               lg:text-4xl
                               font-black
                               tracking-[0.05em]
                               sm:tracking-[0.15em]
                               md:tracking-[0.2em]
                               uppercase
                               leading-tight
                               truncate">

                        {{ strtoupper($firstSlideKey) }}

                    </h2>


                    <div
                        class="mt-1.5
                               sm:mt-3
                               h-[2px]
                               sm:h-[3px]
                               w-12
                               sm:w-24
                               mx-auto
                               bg-gradient-to-r
                               from-transparent
                               via-blue-400
                               to-transparent
                               rounded-full
                               shadow-lg
                               shadow-blue-500/50">
                    </div>

                </div>


                <!-- =================================================
                     NEXT
                ================================================== -->

                <button id="nextButton" type="button" aria-label="Aplikasi berikutnya"
                    class="portal-nav-button
                           pointer-events-auto
                           glass
                           text-white
                           rounded-full
                           hover:text-blue-300">

                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 sm:w-6 sm:h-6" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">

                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />

                    </svg>

                </button>

            </div>

        </div>


        <!-- =====================================================
             SWIPER
        ====================================================== -->

        <div
            class="portal-swiper-container
                   swiper
                   portal-swiper
                   mt-[108px]
                   sm:mt-24
                   px-1
                   sm:px-2">


            <div class="swiper-wrapper pb-4 sm:pb-8">


                @foreach ($slides as $apps)
                    <div class="swiper-slide">

                        <div
                            class="grid
                                   grid-cols-1
                                   sm:grid-cols-2
                                   md:grid-cols-3
                                   lg:grid-cols-4
                                   gap-3
                                   sm:gap-4
                                   md:gap-6">


                            @foreach ($apps as $app)
                                <x-app-card :app="$app" :index="$loop->index" />
                            @endforeach


                        </div>

                    </div>
                @endforeach


            </div>


            <!-- =================================================
                 PAGINATION
            ================================================== -->

            <div class="swiper-pagination
                       portal-pagination">
            </div>

        </div>

    </div>


    <!-- =========================================================
         SWIPER JS
    ========================================================== -->

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>


    <script>
        /* =========================================================
               SLIDE DATA
            ========================================================== */

        const slidesKeys = @json($slidesKeys);

        const slideTitleEl =
            document.getElementById('slideHeading');


        /* =========================================================
           SWIPER
        ========================================================== */

        const swiper = new Swiper('.swiper', {

            slidesPerView: 1,

            spaceBetween: 30,

            speed: 700,

            watchOverflow: false,

            observer: true,

            observeParents: true,

            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },

            navigation: {

                nextEl: '#nextButton',

                prevEl: '#prevButton',

            },

            on: {

                init: function() {

                    updateNavigationState(this);

                },


                slideChange: function() {

                    const currentSlide =
                        slidesKeys[this.activeIndex];


                    if (currentSlide) {

                        slideTitleEl.style.opacity = 0;

                        slideTitleEl.style.transform =
                            'translateY(-10px)';


                        setTimeout(() => {

                            slideTitleEl.textContent =
                                currentSlide.toUpperCase();

                            slideTitleEl.style.opacity = 1;

                            slideTitleEl.style.transform =
                                'translateY(0)';

                        }, 150);

                    }


                    updateNavigationState(this);


                    window.scrollTo({

                        top: 0,

                        behavior: 'smooth'

                    });

                }

            }

        });


        /* =========================================================
           NAVIGATION STATE
        ========================================================== */

        function updateNavigationState(swiperInstance) {

            const prevButton =
                document.getElementById('prevButton');

            const nextButton =
                document.getElementById('nextButton');


            if (!prevButton || !nextButton) {
                return;
            }


            /*
             * Tetap tampil walaupun Swiper disabled.
             */

            prevButton.style.display = 'flex';

            nextButton.style.display = 'flex';

            prevButton.style.visibility = 'visible';

            nextButton.style.visibility = 'visible';


            /*
             * Jika hanya satu slide,
             * tombol tetap terlihat tetapi dibuat redup.
             */

            if (swiperInstance.slides.length <= 1) {

                prevButton.style.opacity = '0.4';

                nextButton.style.opacity = '0.4';

                return;

            }


            prevButton.style.opacity =
                swiperInstance.isBeginning ?
                '0.4' :
                '1';


            nextButton.style.opacity =
                swiperInstance.isEnd ?
                '0.4' :
                '1';

        }


        /* =========================================================
           TITLE TRANSITION
        ========================================================== */

        slideTitleEl.style.transition =
            'opacity 0.3s ease, transform 0.3s ease';


        /* =========================================================
           SEARCH
        ========================================================== */

        const searchInput =
            document.getElementById('searchInput') ||
            document.getElementById('searchInputAuth');


        function runSearch(keyword) {

            keyword =
                (keyword || '')
                .toLowerCase()
                .trim();


            let firstMatchedSlide = null;


            document
                .querySelectorAll('.swiper-slide')
                .forEach((slide, slideIndex) => {

                    let slideHasResult = false;


                    slide
                        .querySelectorAll('[data-name]')
                        .forEach(card => {

                            const name =
                                (card.dataset.name || '')
                                .toLowerCase();


                            if (
                                keyword === '' ||
                                name.includes(keyword)
                            ) {

                                card.classList.remove('hidden');

                                slideHasResult = true;

                            } else {

                                card.classList.add('hidden');

                            }

                        });


                    if (
                        slideHasResult &&
                        firstMatchedSlide === null
                    ) {

                        firstMatchedSlide =
                            slideIndex;

                    }

                });


            if (firstMatchedSlide !== null) {

                swiper.slideTo(
                    firstMatchedSlide
                );

            }


            swiper.update();

            updateNavigationState(swiper);

        }


        if (searchInput) {

            searchInput.addEventListener(
                'input',
                function() {

                    runSearch(this.value);

                }
            );


            searchInput.addEventListener(
                'keydown',
                function(e) {

                    if (e.key === 'Escape') {

                        this.value = '';

                        runSearch('');

                        this.blur();

                    }

                }
            );

        }


        /* =========================================================
           GLOBAL KEYBOARD SEARCH
        ========================================================== */

        document.addEventListener(
            'keydown',
            function(e) {

                if (!searchInput) {
                    return;
                }


                const tag =
                    (
                        document
                        .activeElement
                        ?.tagName || ''
                    ).toLowerCase();


                const isEditing =

                    tag === 'input' ||

                    tag === 'textarea' ||

                    tag === 'select' ||

                    document
                    .activeElement
                    ?.isContentEditable;


                /*
                 * /
                 */

                if (
                    e.key === '/' &&
                    !isEditing
                ) {

                    e.preventDefault();

                    searchInput.focus();

                    searchInput.select();

                    return;

                }


                /*
                 * CTRL + K / CMD + K
                 */

                if (
                    (e.ctrlKey || e.metaKey) &&
                    e.key.toLowerCase() === 'k'
                ) {

                    e.preventDefault();

                    searchInput.focus();

                    searchInput.select();

                    return;

                }


                /*
                 * ESC
                 */

                if (
                    e.key === 'Escape' &&
                    document.activeElement === searchInput
                ) {

                    searchInput.value = '';

                    runSearch('');

                    searchInput.blur();

                    return;

                }


                if (isEditing) {
                    return;
                }


                /*
                 * Ketikan biasa
                 */

                const isPrintable =

                    e.key.length === 1 &&

                    !e.ctrlKey &&

                    !e.metaKey &&

                    !e.altKey;


                if (isPrintable) {

                    e.preventDefault();

                    searchInput.focus();

                    searchInput.value =
                        e.key;

                    runSearch(e.key);


                    const len =
                        searchInput.value.length;


                    searchInput.setSelectionRange(
                        len,
                        len
                    );

                }

            }
        );


        /* =========================================================
           PAGE LAYOUT / SIDEBAR
        ========================================================== */

        function pageLayout() {

            return {

                collapsed: localStorage.getItem(
                    'sidebar-collapsed'
                ) === 'true',


                init() {

                    window.addEventListener(
                        'sidebar-toggled',
                        (e) => {

                            this.collapsed =
                                e.detail.collapsed;

                        }
                    );

                }

            };

        }
    </script>

</body>

</html>
