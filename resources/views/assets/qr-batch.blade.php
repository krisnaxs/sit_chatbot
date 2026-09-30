<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch QR Code — SIAM</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/qrcode.min.js') }}"></script>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* ═══════════════════════════════════════════════════════ */
        /*  QR Container — paksa hanya 1 elemen yang tampil        */
        /* ═══════════════════════════════════════════════════════ */
        .qr-target {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        /* Sembunyikan canvas — qrcodejs render canvas + img, pakai img saja */
        .qr-target canvas {
            display: none !important;
        }

        .qr-target img {
            display: block !important;
            margin: 0 auto !important;
            width: 100% !important;
            height: auto !important;
            max-width: 120px;
        }

        /* ═══════════════════════════════════════════════════════ */
        /*  A4 LAYOUT — optimal untuk cetak banyak QR              */
        /* ═══════════════════════════════════════════════════════ */
        @media print {
            .no-print {
                display: none !important;
            }

            html,
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 210mm;
            }

            @page {
                size: A4 portrait;
                margin: 8mm;
            }

            .qr-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 2mm !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .qr-item {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                border: 0.5pt solid #cbd5e1 !important;
                border-radius: 2mm !important;
                padding: 1.5mm 1mm !important;
                background: white !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: flex-start !important;
                min-height: 33mm !important;
            }

            .qr-target {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                width: 100% !important;
            }

            .qr-target img {
                display: block !important;
                width: 30mm !important;
                height: 30mm !important;
                max-width: 30mm !important;
                max-height: 30mm !important;
                margin: 0 auto !important;
            }

            .qr-target canvas {
                display: none !important;
            }

            .label-code {
                font-size: 6.5pt !important;
                font-weight: 700 !important;
                color: #1f2937 !important;
                margin-top: 1mm !important;
                line-height: 1.1 !important;
            }

            .label-sn {
                font-size: 5.5pt !important;
                font-family: 'Courier New', monospace !important;
                color: #4b5563 !important;
                line-height: 1.1 !important;
            }

            .label-name {
                font-size: 5pt !important;
                color: #6b7280 !important;
                line-height: 1.1 !important;
                margin-top: 0.3mm !important;
            }

            body,
            .qr-grid,
            .qr-item {
                background: white !important;
            }
        }

        /* ═══════════════════════════════════════════════════════ */
        /*  SCREEN PREVIEW — tampilan normal di layar              */
        /* ═══════════════════════════════════════════════════════ */
        @media screen {
            .qr-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 12px;
            }

            .qr-target img {
                max-width: 140px;
            }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen p-4">

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- HEADER (tidak muncul saat print) --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="max-w-[210mm] mx-auto mb-4 flex items-center justify-between flex-wrap gap-3 no-print">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Batch QR Code Aset</h1>
            <p class="text-sm text-gray-500">
                {{ $assets->count() }} aset siap dicetak — Layout A4
            </p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="window.close()"
                class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition">
                ✕ Tutup
            </button>
            <button type="button" onclick="window.print()"
                class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                🖨️ Cetak Semua
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- INFO (tidak muncul saat print) --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="max-w-[210mm] mx-auto mb-4 p-3 bg-indigo-50 border border-indigo-100 rounded-lg no-print">
        <p class="text-xs text-indigo-700 leading-relaxed">
            <strong>💡 Info:</strong> Halaman ini dioptimalkan untuk <strong>kertas A4</strong>.
            Setiap lembar memuat <strong>hingga 25 QR</strong> (5 kolom × 5 baris).
            Saat print, pilih ukuran <strong>A4</strong>, orientasi <strong>Portrait</strong>,
            dan centang <strong>"Background graphics"</strong>.
        </p>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- GRID QR --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if ($assets->isEmpty())
        <div class="max-w-[210mm] mx-auto bg-white rounded-2xl p-12 text-center">
            <p class="text-gray-400 italic">Tidak ada aset yang dipilih.</p>
        </div>
    @else
        <div class="max-w-[210mm] mx-auto qr-grid">
            @foreach ($assets as $asset)
                <div class="qr-item bg-white rounded-xl border border-gray-200 p-3 text-center">
                    <div class="qr-target mb-2" data-url="{{ $asset->public_url }}"
                        data-sn="{{ $asset->serial_number }}"></div>
                    <div class="label-code text-xs font-bold text-gray-800 truncate">
                        {{ $asset->asset_code ?? '-' }}
                    </div>
                    <div class="label-sn text-[10px] text-gray-500 font-mono truncate">
                        {{ $asset->serial_number }}
                    </div>
                    <div class="label-name text-[9px] text-gray-400 truncate mt-0.5">
                        {{ $asset->brand }} {{ $asset->model }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- SCRIPT — Generate QR (sekali saja, no double) --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <script>
        (function() {
            let generated = false;

            function generateAllQR() {
                if (generated) return;
                generated = true;

                if (typeof QRCode === 'undefined') {
                    document.querySelectorAll('.qr-target').forEach(el => {
                        el.innerHTML = '<p class="text-xs text-red-500 p-4">QR library tidak tersedia.</p>';
                    });
                    return;
                }

                document.querySelectorAll('.qr-target').forEach(el => {
                    const url = el.dataset.url;
                    if (!url) return;

                    // 🔧 Clear dulu biar tidak double
                    el.innerHTML = '';

                    try {
                        new QRCode(el, {
                            text: url,
                            width: 200,
                            height: 200,
                            colorDark: '#000000',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel ? QRCode.CorrectLevel.H : 2
                        });
                    } catch (e) {
                        console.error('QR error:', e);
                        el.innerHTML = '<p class="text-xs text-red-500 p-2">Error</p>';
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', generateAllQR);
            } else {
                generateAllQR();
            }
        })();
    </script>
</body>

</html>
