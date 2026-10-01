@extends('layouts.app')

@section('title', 'QR Code Generator — SIAM')

@push('styles')
    {{-- qrcode LOKAL --}}
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
@endpush

@section('content')
    <div class="max-w-3xl mx-auto space-y-6" x-data="qrGenerator()">

        {{-- HEADER --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-800">QR Code Generator</h1>
            <p class="text-sm text-gray-500">Buat QR code dari link apapun — untuk poster, form, grup WA, dll.</p>
        </div>

        {{-- FORM INPUT --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Link / URL <span class="text-red-500">*</span>
                </label>
                <input type="text" id="inputUrl" placeholder="https://example.com atau link apapun"
                    class="w-full border rounded-lg px-4 py-3 text-sm font-mono
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                <p class="text-xs text-gray-400 mt-1">
                    Bisa juga: teks biasa, nomor WA (62812xxx), email, dll.
                </p>
            </div>

            {{-- PRESET LINK --}}
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    Link Cepat
                </p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="setPreset('{{ url('/request/quick') }}')"
                        class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold transition">
                        🎯 Form Pengajuan
                    </button>
                    <button type="button" onclick="setPreset('{{ url('/portal') }}')"
                        class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold transition">
                        🏠 Portal
                    </button>
                    <button type="button" onclick="setPreset('{{ url('/chat') }}')"
                        class="px-3 py-1.5 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 rounded-lg text-xs font-semibold transition">
                        💬 Chatbot
                    </button>
                </div>
            </div>

            {{-- TOMBOL GENERATE --}}
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="generateQR()"
                    class="flex-1 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold
                       shadow-lg shadow-indigo-500/30 transition inline-flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    Generate QR Code
                </button>

                <button type="button" onclick="resetForm()"
                    class="px-5 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-semibold
                       transition">
                    Reset
                </button>
            </div>
        </div>

        {{-- HASIL QR --}}
        <div id="qrResult" class="hidden bg-white rounded-2xl shadow-lg border border-gray-200 p-8 text-center">

            <div class="mb-4">
                <img src="{{ asset('images/plnip.png') }}" class="h-12 mx-auto mb-2" alt="Logo">
                <h2 class="font-bold text-lg text-gray-800">QR Code</h2>
            </div>

            <div id="qrcode" class="inline-block p-4 bg-white border-2 border-dashed border-gray-300 rounded-2xl my-4">
            </div>

            <div class="mt-3 mb-4">
                <p class="text-xs text-gray-400 mb-1">Berisi:</p>
                <p id="qrUrlDisplay" class="font-mono text-xs bg-gray-100 px-3 py-1.5 rounded-lg break-all">
                    —
                </p>
            </div>

            {{-- TOMBOL AKSI --}}
            <div class="flex gap-2 mt-6 pt-6 border-t">
                <button type="button" onclick="downloadQR()"
                    class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium
                       shadow-md shadow-indigo-500/30 transition inline-flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-4-4m4 4l4-4M12 3v9" />
                    </svg>
                    Download PNG HD
                </button>

                <button type="button" onclick="printQR()"
                    class="flex-1 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium
                       transition inline-flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
            </div>
        </div>

        {{-- 🔔 TOAST --}}
        <div x-show="toast.show" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            :class="{
                'bg-emerald-600 border-emerald-400/40 shadow-emerald-500/50': toast.type === 'success',
                'bg-red-600 border-red-400/40 shadow-red-500/50': toast.type === 'error',
                'bg-blue-600 border-blue-400/40 shadow-blue-500/50': toast.type === 'info',
                'bg-amber-600 border-amber-400/40 shadow-amber-500/50': toast.type === 'warning',
            }"
            class="fixed top-24 right-6 z-[100] flex items-center gap-3
                   min-w-[280px] max-w-sm
                   px-4 py-3 rounded-xl
                   text-white shadow-2xl border backdrop-blur-md"
            style="display: none;">

            {{-- Icon --}}
            <div class="shrink-0 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                {{-- Success icon --}}
                <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                {{-- Error icon --}}
                <svg x-show="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                {{-- Info icon --}}
                <svg x-show="toast.type === 'info'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                {{-- Warning icon --}}
                <svg x-show="toast.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            {{-- Message --}}
            <div class="flex-1 text-sm font-medium" x-text="toast.message"></div>

            {{-- Close button --}}
            <button @click="toast.show = false" class="shrink-0 p-1 rounded hover:bg-white/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function qrGenerator() {
            return {
                toast: {
                    show: false,
                    message: '',
                    type: 'success',
                },

                showToast(message, type = 'success') {
                    this.toast.message = message;
                    this.toast.type = type;
                    this.toast.show = true;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },
            };
        }
        let currentUrl = '';

        function getAlpine() {
            return Alpine.$data(document.querySelector('[x-data="qrGenerator()"]'));
        }

        function notify(message, type = 'success') {
            const comp = getAlpine();
            if (comp) comp.showToast(message, type);
        }

        function setPreset(url) {
            document.getElementById('inputUrl').value = url;
            generateQR();
        }

        function resetForm() {
            document.getElementById('inputUrl').value = '';
            document.getElementById('qrResult').classList.add('hidden');
            document.getElementById('qrcode').innerHTML = '';
            currentUrl = '';
            window.__QR_HD_URL = null;
            window.__QR_HD_CANVAS = null;
            notify('Form berhasil di-reset', 'info');
        }

        function generateQR() {
            const input = document.getElementById('inputUrl');
            const url = input.value.trim();

            if (!url) {
                notify('Masukkan link terlebih dahulu', 'warning');
                input.focus();
                return;
            }

            if (typeof QRCode === 'undefined') {
                notify('Library QR belum load. Refresh halaman', 'error');
                return;
            }

            currentUrl = url;

            const result = document.getElementById('qrResult');
            result.classList.remove('hidden');

            document.getElementById('qrUrlDisplay').textContent = url;

            const container = document.getElementById('qrcode');
            container.innerHTML = '';
            window.__QR_HD_URL = null;
            window.__QR_HD_CANVAS = null;

            if (typeof QRCode.toDataURL === 'function') {
                QRCode.toDataURL(url, {
                    width: 1000,
                    margin: 6,
                    color: {
                        dark: '#1f2937',
                        light: '#ffffff'
                    },
                    errorCorrectionLevel: 'H',
                }, function(error, dataUrl) {
                    if (error) {
                        console.error('QR error:', error);
                        container.innerHTML = '<p class="text-red-500 text-sm p-4">Gagal generate QR</p>';
                        notify('Gagal generate QR Code', 'error');
                        return;
                    }
                    const img = document.createElement('img');
                    img.src = dataUrl;
                    img.alt = 'QR Code';
                    img.width = 280;
                    img.height = 280;
                    img.style.display = 'block';

                    container.appendChild(img);
                    window.__QR_HD_URL = dataUrl;

                    notify('QR Code berhasil dibuat!', 'success');
                    console.log(' QR berhasil');
                });
            } else {
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
                    if (hdCanvas) {
                        window.__QR_HD_CANVAS = hdCanvas;
                    }
                    notify('QR Code berhasil dibuat!', 'success');
                    console.log(' QR HD siap');
                }, 300);
            }

            setTimeout(() => {
                result.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 100);
        }
        async function downloadQR() {
            let qrSource = null;

            if (window.__QR_HD_URL) {
                qrSource = await loadImage(window.__QR_HD_URL);
            } else if (window.__QR_HD_CANVAS) {
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
                notify('QR belum siap. Tunggu sebentar lalu coba lagi', 'warning');
                return;
            }
            const Q = 800;
            const PADDING = 50;
            const QR_SIZE = 440;
            const TITLE_SPACE = 90;
            const URL_SPACE = 80;
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
            let urlText = currentUrl || '—';
            if (urlText.length > 60) {
                urlText = urlText.substring(0, 57) + '...';
            }
            ctx.fillStyle = '#6b7280';
            ctx.font = '16px "Courier New", monospace';
            ctx.fillText(urlText, Q / 2, qrY + QR_SIZE + 45);
            const dataUrl = finalCanvas.toDataURL('image/png');

            const link = document.createElement('a');
            link.download = 'qr-code-' + Date.now() + '.png';
            link.href = dataUrl;
            link.click();

            notify('QR Code berhasil di-download!', 'success');
            console.log(' QR PNG dengan frame ter-download');
        }

        function printQR() {
            const img = document.querySelector('#qrcode img');
            const canvas = document.querySelector('#qrcode canvas');

            let qrSrc = window.__QR_HD_URL || (img && img.src) || (canvas && canvas.toDataURL('image/png'));

            if (!qrSrc) {
                notify('QR belum siap untuk di-print', 'warning');
                return;
            }

            const logoUrl = '{{ asset('images/plnip.png') }}';

            const printWindow = window.open('', '_blank', 'width=800,height=900');
            if (!printWindow) {
                notify('Popup diblokir. Izinkan popup untuk situs ini', 'error');
                return;
            }

            printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Print QR Code</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                        display: flex; justify-content: center; align-items: flex-start;
                        min-height: 100vh; padding: 40px 20px; background: white;
                    }
                    .qr-card {
                        border: 3px solid #000;
                        border-radius: 20px;
                        padding: 40px 30px;
                        text-align: center;
                        max-width: 520px;
                        width: 100%;
                    }
                    .logo { height: 60px; margin-bottom: 16px; }
                    .title { font-size: 20px; font-weight: 700; color: #1f2937; margin-bottom: 20px; }
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
                    }
                    .url {
                        font-family: 'Courier New', monospace;
                        font-size: 12px;
                        background: #f3f4f6;
                        padding: 8px 14px;
                        border-radius: 8px;
                        display: inline-block;
                        color: #374151;
                        max-width: 100%;
                        word-break: break-all;
                    }
                    @media print {
                        body { padding: 20px; background: white; }
                        .qr-card { page-break-inside: avoid; }
                        @page { size: A4; margin: 15mm; }
                    }
                </style>
            </head>
            <body>
                <div class="qr-card">
                    <img src="${logoUrl}" class="logo" alt="Logo">
                    <div class="title">Scan QR Code</div>
                    <div class="qr-box">
                        <img src="${qrSrc}" class="qr-img" alt="QR Code">
                    </div>
                    <div class="url">${currentUrl}</div>
                </div>
                <script>
                    window.onload = function() {
                        setTimeout(function() { window.print(); }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);

            printWindow.document.close();

            notify('Membuka dialog print...', 'info');
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

        function loadImage(src) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => resolve(img);
                img.onerror = reject;
                img.src = src;
            });
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
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const urlParam = params.get('url');
            if (urlParam) {
                document.getElementById('inputUrl').value = urlParam;
                generateQR();
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush
