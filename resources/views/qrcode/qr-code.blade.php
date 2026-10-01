@extends('layouts.app')

@section('title', 'QR Code Pengajuan — SIAM')

@push('styles')
    {{-- qrcode LOKAL --}}
    <script src="{{ asset('js/qrcode.min.js') }}"></script>

    <style>
        @media print {
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            x-header,
            x-sidebar,
            nav,
            aside,
            header,
            .no-print {
                display: none !important;
            }

            [class*="lg:ml-"] {
                margin-left: 0 !important;
            }

            .max-w-7xl,
            .max-w-2xl {
                max-width: 100% !important;
                margin: 0 auto !important;
                padding: 10mm !important;
            }

            #qrCard {
                box-shadow: none !important;
                border: 2px solid #000 !important;
                page-break-inside: avoid !important;
                display: block !important;
            }

            #qrcode {
                display: inline-block !important;
                page-break-inside: avoid !important;
            }

            #qrcode img,
            #qrcode canvas {
                display: block !important;
                max-width: 100% !important;
                height: auto !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">

        {{-- HEADER (disembunyikan saat print) --}}
        <div class="no-print">
            <h1 class="text-2xl font-bold text-gray-800">QR Code Pengajuan Cepat</h1>
            <p class="text-sm text-gray-500">Print dan tempel di kantor/ruangan. User scan → login → ajukan.</p>
        </div>

        {{-- QR CARD --}}
        <div id="qrCard" class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 text-center">

            {{-- LOGO & TITLE --}}
            <div class="mb-4">
                <img src="{{ asset('images/plnip.png') }}" class="h-16 mx-auto mb-3" alt="Logo">
                <h2 class="font-bold text-xl text-gray-800">SIAM — Pengajuan Cepat</h2>
                <p class="text-sm text-gray-500">Scan untuk mengajukan peminjaman atau konsumable</p>
            </div>

            {{-- QR CODE --}}
            <div id="qrcode" class="inline-block p-4 bg-white border-2 border-dashed border-gray-300 rounded-2xl my-6">
            </div>

            {{-- URL --}}
            <div class="text-sm text-gray-600">
                <p class="font-mono text-xs bg-gray-100 inline-block px-3 py-1.5 rounded-lg">
                    {{ url('/request/quick') }}
                </p>
            </div>

            {{-- LANGKAH-LANGKAH --}}
            <div class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-3 gap-4 text-xs text-left">
                <div>
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mb-2">
                        <span class="font-bold text-indigo-600">1</span>
                    </div>
                    <p class="font-semibold text-gray-800">Scan QR</p>
                    <p class="text-gray-500 mt-0.5">Pakai kamera HP</p>
                </div>
                <div>
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mb-2">
                        <span class="font-bold text-indigo-600">2</span>
                    </div>
                    <p class="font-semibold text-gray-800">Login</p>
                    <p class="text-gray-500 mt-0.5">Modal muncul otomatis</p>
                </div>
                <div>
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mb-2">
                        <span class="font-bold text-indigo-600">3</span>
                    </div>
                    <p class="font-semibold text-gray-800">Ajukan</p>
                    <p class="text-gray-500 mt-0.5">Dapat bukti pengajuan</p>
                </div>
            </div>
        </div>

        {{-- TOMBOL AKSI --}}
        <div class="flex gap-3 no-print">
            <button type="button" onclick="printQR()"
                class="flex-1 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold
                   shadow-lg shadow-indigo-500/30 transition inline-flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print QR Code
            </button>

            <button type="button" onclick="downloadQR()"
                class="flex-1 px-5 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-semibold
                   transition inline-flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-4-4m4 4l4-4M12 3v9" />
                </svg>
                Download PNG HD
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function generateQR() {
            const container = document.getElementById('qrcode');
            if (!container) return;

            if (typeof QRCode === 'undefined') {
                console.warn('QRCode belum load, retry...');
                container.innerHTML = '<p class="text-amber-600 text-xs p-4">Memuat library...</p>';
                setTimeout(generateQR, 200);
                return;
            }

            const url = '{{ url('/request/quick') }}';
            console.log(' QRCode loaded:', typeof QRCode);
            console.log('🔗 Generate QR untuk:', url);

            if (typeof QRCode.toCanvas === 'function') {
                console.log('📚 Library: qrcode (soldair)');

                QRCode.toCanvas(container, url, {
                    width: 280,
                    margin: 6,
                    color: {
                        dark: '#1f2937',
                        light: '#ffffff',
                    },
                    errorCorrectionLevel: 'H',
                }, function(error) {
                    if (error) {
                        console.error('❌ QR error:', error);
                        container.innerHTML = '<p class="text-red-500 text-xs">Gagal generate QR</p>';
                        return;
                    }
                    console.log(' QR berhasil (toCanvas)');
                    QRCode.toDataURL(url, {
                        width: 1000,
                        margin: 4,
                        color: {
                            dark: '#1f2937',
                            light: '#ffffff',
                        },
                        errorCorrectionLevel: 'H',
                    }, function(err, hdUrl) {
                        if (err) {
                            console.error('❌ QR HD error:', err);
                        } else {
                            window.__QR_HD_URL = hdUrl;
                            console.log(' QR HD siap');
                        }
                    });
                });
            } else {
                console.log('📚 Library: qrcodejs (davidshimjs)');
                container.innerHTML = '';

                new QRCode(container, {
                    text: url,
                    width: 280,
                    height: 280,
                    colorDark: '#1f2937',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel ? QRCode.CorrectLevel.H : 2
                });
                const hdContainer = document.createElement('div');
                hdContainer.style.display = 'none';
                document.body.appendChild(hdContainer);

                new QRCode(hdContainer, {
                    text: url,
                    width: 1000,
                    height: 1000,
                    colorDark: '#1f2937',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel ? QRCode.CorrectLevel.H : 2
                });

                setTimeout(() => {
                    const hdCanvas = hdContainer.querySelector('canvas');
                    const hdImg = hdContainer.querySelector('img');
                    if (hdCanvas) {
                        window.__QR_HD_CANVAS = hdCanvas;
                    } else if (hdImg) {
                        window.__QR_HD_IMG = hdImg;
                    }
                    console.log(' QR HD siap');
                }, 300);

                console.log(' QR berhasil (constructor)');
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', generateQR);
        } else {
            generateQR();
        }

        function printQR() {
            let qrSrc = '';

            if (window.__QR_HD_URL) {
                qrSrc = window.__QR_HD_URL;
            } else if (window.__QR_HD_CANVAS) {
                qrSrc = window.__QR_HD_CANVAS.toDataURL('image/png');
            } else {
                const img = document.querySelector('#qrcode img');
                const canvas = document.querySelector('#qrcode canvas');
                if (img && img.src) {
                    qrSrc = img.src;
                } else if (canvas) {
                    qrSrc = canvas.toDataURL('image/png');
                }
            }

            if (!qrSrc) {
                alert('QR belum siap. Tunggu sebentar lalu coba lagi.');
                return;
            }

            const quickUrl = '{{ url('/request/quick') }}';
            const logoUrl = '{{ asset('images/plnip.png') }}';

            const printWindow = window.open('', '_blank', 'width=900,height=1000');
            if (!printWindow) {
                alert('Popup diblokir. Izinkan popup untuk situs ini, lalu coba lagi.');
                return;
            }

            printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Print QR Code — SIAM</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    html, body {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        background: white;
                        padding: 30px;
                    }
                    .qr-card {
                        border: 3px solid #000;
                        border-radius: 20px;
                        padding: 40px 30px;
                        text-align: center;
                        max-width: 560px;
                        margin: 0 auto;
                        page-break-inside: avoid;
                    }
                    .logo {
                        height: 70px;
                        margin-bottom: 16px;
                    }
                    .title {
                        font-size: 24px;
                        font-weight: 700;
                        color: #1f2937;
                        margin-bottom: 6px;
                    }
                    .subtitle {
                        font-size: 14px;
                        color: #6b7280;
                        margin-bottom: 24px;
                    }
                    .qr-box {
                        display: inline-block;
                        padding: 16px;
                        border: 2px dashed #d1d5db;
                        border-radius: 16px;
                        margin: 8px 0 20px;
                        background: white;
                    }
                    .qr-img {
                        display: block;
                        width: 320px;
                        height: 320px;
                        image-rendering: pixelated;
                        image-rendering: crisp-edges;
                    }
                    .url {
                        font-family: 'Courier New', monospace;
                        font-size: 13px;
                        background: #f3f4f6;
                        padding: 8px 16px;
                        border-radius: 8px;
                        display: inline-block;
                        color: #374151;
                        margin-bottom: 24px;
                    }
                    .steps {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 16px;
                        margin-top: 24px;
                        padding-top: 24px;
                        border-top: 1px solid #e5e7eb;
                        text-align: left;
                    }
                    .step-num {
                        width: 36px;
                        height: 36px;
                        border-radius: 50%;
                        background: #e0e7ff;
                        color: #4338ca;
                        font-weight: 700;
                        font-size: 15px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 10px;
                    }
                    .step-title {
                        font-weight: 600;
                        font-size: 14px;
                        color: #1f2937;
                    }
                    .step-desc {
                        font-size: 12px;
                        color: #6b7280;
                        margin-top: 3px;
                    }
                    @media print {
                        body { padding: 15px; background: white; }
                        .qr-card { page-break-inside: avoid; }
                        @page {
                            size: A4;
                            margin: 15mm;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="qr-card">
                    <img src="${logoUrl}" class="logo" alt="Logo">
                    <div class="title">SIAM — Pengajuan Cepat</div>
                    <div class="subtitle">Scan untuk mengajukan peminjaman atau konsumable</div>

                    <div class="qr-box">
                        <img src="${qrSrc}" class="qr-img" alt="QR Code">
                    </div>

                    <div class="url">${quickUrl}</div>

                    <div class="steps">
                        <div>
                            <div class="step-num">1</div>
                            <div class="step-title">Scan QR</div>
                            <div class="step-desc">Pakai kamera HP</div>
                        </div>
                        <div>
                            <div class="step-num">2</div>
                            <div class="step-title">Login</div>
                            <div class="step-desc">Modal muncul otomatis</div>
                        </div>
                        <div>
                            <div class="step-num">3</div>
                            <div class="step-title">Ajukan</div>
                            <div class="step-desc">Dapat bukti pengajuan</div>
                        </div>
                    </div>
                </div>
                <script>
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                        }, 600);
                    };
                <\/script>
            </body>
            </html>
        `);

            printWindow.document.close();
        }
        async function downloadQR() {
            let qrSource = null;

            if (window.__QR_HD_CANVAS) {
                qrSource = window.__QR_HD_CANVAS;
            } else {
                const canvas = document.querySelector('#qrcode canvas');
                const img = document.querySelector('#qrcode img');

                if (canvas) {
                    qrSource = canvas;
                } else if (img && img.src) {
                    qrSource = await imgToCanvas(img);
                }
            }

            if (!qrSource) {
                alert('QR belum siap. Tunggu sebentar lalu coba lagi.');
                return;
            }
            const Q = 800;
            const PADDING = 50;
            const QR_SIZE = 440;
            const TITLE_SPACE = 90;
            const URL_SPACE = 60;
            const BORDER_RADIUS = 20;
            const TOTAL_H = PADDING + TITLE_SPACE + QR_SIZE + URL_SPACE + PADDING;
            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = Q;
            finalCanvas.height = TOTAL_H;
            const ctx = finalCanvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            roundRect(ctx, 0, 0, Q, TOTAL_H, BORDER_RADIUS);
            ctx.fill();
            ctx.fillStyle = '#1f2937';
            ctx.font = 'bold 32px -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('Scan QR Code', Q / 2, PADDING + 40);
            const boxX = (Q - QR_SIZE) / 2 - 20;
            const boxY = PADDING + TITLE_SPACE;
            const boxW = QR_SIZE + 40;
            const boxH = QR_SIZE + 40;

            ctx.strokeStyle = '#d1d5db';
            ctx.lineWidth = 2;
            ctx.setLineDash([8, 6]);
            roundRect(ctx, boxX, boxY, boxW, boxH, 16);
            ctx.stroke();
            ctx.setLineDash([]);
            const qrX = (Q - QR_SIZE) / 2;
            const qrY = boxY + 20;
            ctx.drawImage(qrSource, qrX, qrY, QR_SIZE, QR_SIZE);
            const url = '{{ url('/request/quick') }}';
            ctx.fillStyle = '#6b7280';
            ctx.font = '18px "Courier New", monospace';
            ctx.fillText(url, Q / 2, qrY + QR_SIZE + 55);
            const dataUrl = finalCanvas.toDataURL('image/png');

            const link = document.createElement('a');
            link.download = 'qr-pengajuan-siam.png';
            link.href = dataUrl;
            link.click();

            console.log(' QR PNG ter-download (dengan frame)');
        }

        function roundRect(ctx, x, y, w, h, r) {
            ctx.beginPath();
            ctx.moveTo(x + r, y);
            ctx.arcTo(x + w, y, x + w, y + h, r);
            ctx.arcTo(x + w, y + h, x, y + h, r);
            ctx.arcTo(x, y + h, x, y, r);
            ctx.arcTo(x, y, x + w, y, r);
            ctx.closePath();
        }

        function imgToCanvas(img) {
            return new Promise((resolve) => {
                const canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth || img.width;
                canvas.height = img.naturalHeight || img.height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                resolve(canvas);
            });
        }
    </script>
@endpush
