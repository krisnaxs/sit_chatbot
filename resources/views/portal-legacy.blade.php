<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SURALAYA INFORMATION CENTER</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #1e293b;
            color: #fff;
            min-height: 100vh;
            position: relative;
        }

        /* Background */
        .bg-layer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .bg-layer img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.4;
            filter: brightness(0.5);
        }

        .bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.5);
        }

        /* Header */
        .header {
            background: rgba(15, 23, 42, 0.85);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 16px 24px;
            position: relative;
            z-index: 10;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            letter-spacing: 1px;
        }

        .header-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 13px;
        }

        .header-nav a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .header-nav a:hover {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }

        /* Notice */
        .legacy-notice {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            color: #92400e;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 12px;
            margin: 16px auto;
            max-width: 1200px;
            text-align: center;
            line-height: 1.5;
        }

        .legacy-notice a {
            color: #92400e;
            font-weight: bold;
            text-decoration: underline;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 24px 40px;
            position: relative;
            z-index: 5;
        }

        /* Search */
        .search-box {
            max-width: 500px;
            margin: 0 auto 32px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            outline: none;
            font-family: inherit;
        }

        .search-box input:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #3b82f6;
        }

        .search-box input::placeholder {
            color: #94a3b8;
        }

        /* Section title */
        .section-title {
            text-align: center;
            margin: 24px 0 32px;
        }

        .section-title h2 {
            font-size: 26px;
            font-weight: bold;
            color: #fff;
            letter-spacing: 4px;
            text-transform: uppercase;
            text-shadow:
                0 0 20px rgba(59, 130, 246, 0.8),
                0 0 40px rgba(59, 130, 246, 0.5),
                0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .section-title .divider {
            width: 80px;
            height: 3px;
            margin: 12px auto 0;
            background: linear-gradient(to right, transparent, #60a5fa, transparent);
            border-radius: 2px;
        }

        /* Group */
        .group {
            margin-bottom: 40px;
        }

        .group-title {
            font-size: 14px;
            font-weight: bold;
            color: #60a5fa;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 16px;
            padding-left: 12px;
            border-left: 3px solid #3b82f6;
        }

        /* Grid aplikasi */
        .app-grid {
            display: flex;
            flex-wrap: wrap;
            margin: -10px;
        }

        .app-item {
            width: 25%;
            padding: 10px;
            box-sizing: border-box;
        }

        @media (max-width: 1024px) {
            .app-item {
                width: 33.333%;
            }
        }

        @media (max-width: 768px) {
            .app-item {
                width: 50%;
            }
        }

        @media (max-width: 480px) {
            .app-item {
                width: 100%;
            }
        }

        /* Card */
        .app-card {
            display: block;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 20px 16px;
            text-align: center;
            text-decoration: none;
            color: #fff;
            transition: all 0.25s;
            height: 100%;
            min-height: 150px;
        }

        .app-card:hover {
            background: rgba(59, 130, 246, 0.25);
            border-color: rgba(59, 130, 246, 0.5);
            transform: translateY(-4px);
        }

        .app-card .icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 12px;
            background: rgba(59, 130, 246, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: #60a5fa;
            overflow: hidden;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .app-card .icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .app-card .name {
            font-size: 13px;
            font-weight: bold;
            line-height: 1.3;
            color: #fff;
            word-wrap: break-word;
        }

        /* Empty state */
        .empty {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .empty .emoji {
            font-size: 48px;
            margin-bottom: 12px;
        }

        /* Footer */
        .footer {
            text-align: center;
            color: #64748b;
            font-size: 11px;
            padding: 20px;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <!-- Background -->
    <div class="bg-layer">
        <img src="{{ asset('images/bg_ubpsla.jpeg') }}" alt="Background">
        <div class="bg-overlay"></div>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="header-inner">
            <h1>SURALAYA INFORMATION CENTER</h1>
            <div class="header-nav">
                @auth
                    <a href="/dashboard">Dashboard</a>
                    <a href="/knowledge">Knowledge</a>
                    <a href="/chat">Chat</a>
                    <a href="/pending">Pending</a>
                @else
                    <a href="/login">Login</a>
                @endauth
                <a href="{{ route('portal') }}">Mode Modern</a>
            </div>
        </div>
    </div>

    <div class="container">

        <!-- Search -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Cari aplikasi...">
        </div>

        <!-- Title -->
        <div class="section-title">
            <h2>Portal Aplikasi</h2>
            <div class="divider"></div>
        </div>

        <!-- Groups -->
        @forelse ($slides as $slideName => $apps)
            <div class="group" data-group>
                <div class="group-title">{{ strtoupper($slideName) }}</div>

                <div class="app-grid">
                    @foreach ($apps as $app)
                        <div class="app-item" data-name="{{ strtolower($app->nama) }}">
                            <a href="{{ route('app.click', $app->id) }}" class="app-card">
                                <div class="icon">
                                    @if (!empty($app->gambar))
                                        <img src="{{ asset('storage/' . $app->gambar) }}" alt="{{ $app->nama }}">
                                    @else
                                        {{ strtoupper(substr($app->nama, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="name">{{ $app->nama }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="empty">
                <div class="emoji">📭</div>
                <p>Belum ada aplikasi</p>
            </div>
        @endforelse

    </div>

    <div class="footer">
        SIS Assistant · {{ now()->format('Y') }}
    </div>

    <!-- Search script — ES5 only -->
    <script>
        (function() {
            var searchInput = document.getElementById('searchInput');
            if (!searchInput) return;

            searchInput.addEventListener('keyup', function() {
                var keyword = this.value.toLowerCase();

                // Loop setiap group
                var groups = document.querySelectorAll('[data-group]');
                for (var g = 0; g < groups.length; g++) {
                    var group = groups[g];
                    var items = group.querySelectorAll('[data-name]');
                    var visibleCount = 0;

                    for (var i = 0; i < items.length; i++) {
                        var item = items[i];
                        var name = (item.getAttribute('data-name') || '').toLowerCase();

                        if (keyword === '' || name.indexOf(keyword) !== -1) {
                            item.style.display = '';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    }

                    // Sembunyikan group kalau tidak ada hasil
                    if (visibleCount === 0 && keyword !== '') {
                        group.style.display = 'none';
                    } else {
                        group.style.display = '';
                    }
                }
            });
        })();
    </script>

</body>

</html>
