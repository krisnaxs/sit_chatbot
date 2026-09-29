<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator — SIAM</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- qrcode LOKAL --}}
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
</head>

<body class="bg-slate-100 font-sans">
    <x-header title="QR Code Generator" />
    <x-sidebar />

    <div x-data="pageLayout()" :class="collapsed ? 'lg:ml-16' : 'lg:ml-64'"
        class="max-w-7xl mx-auto p-6 mt-4 lg:max-w-none transition-all duration-300">

        <div class="max-w-3xl mx-auto space-y-6">

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

                <div id="qrcode"
                    class="inline-block p-4 bg-white border-2 border-dashed border-gray-300 rounded-2xl my-4">
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

        </div>
    </div>

    <script>
        function pageLayout() {
            return {
                collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
                init() {
                    window.addEventListener('sidebar-toggled', e => this.collapsed = e.detail.collapsed);
                }
            }
        }

        let currentUrl = '';

        // Set preset link
        function setPreset(url) {
            document.getElementById('inputUrl').value = url;
            generateQR();
        }

        // Reset form
        function resetForm() {
            document.getElementById('inputUrl').value = '';
            document.getElementById('qrResult').classList.add('hidden');
            document.getElementById('qrcode').innerHTML = '';
            currentUrl = '';
            window.__QR_HD_URL = null;
            window.__QR_HD_CANVAS = null;
        }

        // ═══════════════════════════════════════════════════════════
        //  GENERATE QR
        // ═══════════════════════════════════════════════════════════
        function generateQR() {
            const input = document.getElementById('inputUrl');
            const url = input.value.trim();

            if (!url) {
                alert('Masukkan link terlebih dahulu.');
                input.focus();
                return;
            }

            if (typeof QRCode === 'undefined') {
                alert('Library QR belum load. Refresh halaman.');
                return;
            }

            currentUrl = url;

            const result = document.getElementById('qrResult');
            result.classList.remove('hidden');

            document.getElementById('qrUrlDisplay').textContent = url;

            const container = document.getElementById('qrcode');
            container.innerHTML = '';

            // Reset HD variables
            window.__QR_HD_URL = null;
            window.__QR_HD_CANVAS = null;

            if (typeof QRCode.toDataURL === 'function') {
                // 🅰️ Library 'qrcode' (soldair)
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
                        return;
                    }

                    // Tampilkan preview 280x280
                    const img = document.createElement('img');
                    img.src = dataUrl;
                    img.alt = 'QR Code';
                    img.width = 280;
                    img.height = 280;
                    img.style.display = 'block';

                    container.appendChild(img);
                    window.__QR_HD_URL = dataUrl;

                    console.log('✅ QR berhasil');
                });
            } else {
                // 🅱️ Library 'qrcodejs'
                new QRCode(container, {
                    text: url,
                    width: 280,
                    height: 280,
                    colorDark: '#1f2937',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel ? QRCode.CorrectLevel.H : 2
                });

                // HD version hidden
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
                    console.log('✅ QR HD siap');
                }, 300);
            }

            setTimeout(() => {
                result.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 100);
        }

        // ═══════════════════════════════════════════════════════════
        //  DOWNLOAD QR — PNG dengan frame (judul + QR + URL)
        // ═══════════════════════════════════════════════════════════
        async function downloadQR() {
            // ─── 1. Ambil QR source ───
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
                alert('QR belum siap. Tunggu sebentar lalu coba lagi.');
                return;
            }

            // ─── 2. Setup ukuran canvas akhir ───
            const Q = 800;
            const PADDING = 50;
            const QR_SIZE = 440;
            const TITLE_SPACE = 90;
            const URL_SPACE = 80;
            const BORDER_RADIUS = 20;
            const TOTAL_H = PADDING + TITLE_SPACE + QR_SIZE + URL_SPACE + PADDING;

            // ─── 3. Buat canvas baru ───
            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = Q;
            finalCanvas.height = TOTAL_H;
            const ctx = finalCanvas.getContext('2d');

            // Background putih rounded
            ctx.fillStyle = '#ffffff';
            roundRect(ctx, 0, 0, Q, TOTAL_H, BORDER_RADIUS);
            ctx.fill();

            // ─── 4. Judul ───
            ctx.fillStyle = '#1f2937';
            ctx.font = 'bold 32px -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('Scan QR Code', Q / 2, PADDING + 40);

            // ─── 5. Border dashed ───
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

            // ─── 6. QR Code ───
            const qrX = (Q - QR_SIZE) / 2;
            const qrY = boxY + 20;
            ctx.drawImage(qrSource, qrX, qrY, QR_SIZE, QR_SIZE);

            // ─── 7. URL (potong kalau panjang) ───
            let urlText = currentUrl || '—';
            if (urlText.length > 60) {
                urlText = urlText.substring(0, 57) + '...';
            }
            ctx.fillStyle = '#6b7280';
            ctx.font = '16px "Courier New", monospace';
            ctx.fillText(urlText, Q / 2, qrY + QR_SIZE + 45);

            // ─── 8. Download ───
            const dataUrl = finalCanvas.toDataURL('image/png');

            const link = document.createElement('a');
            link.download = 'qr-code-' + Date.now() + '.png';
            link.href = dataUrl;
            link.click();

            console.log('✅ QR PNG dengan frame ter-download');
        }

        // ═══════════════════════════════════════════════════════════
        //  PRINT QR
        // ═══════════════════════════════════════════════════════════
        function printQR() {
            const img = document.querySelector('#qrcode img');
            const canvas = document.querySelector('#qrcode canvas');

            let qrSrc = window.__QR_HD_URL || (img && img.src) || (canvas && canvas.toDataURL('image/png'));

            if (!qrSrc) {
                alert('QR belum siap.');
                return;
            }

            const logoUrl = '{{ asset('images/plnip.png') }}';

            const printWindow = window.open('', '_blank', 'width=800,height=900');
            if (!printWindow) {
                alert('Popup diblokir. Izinkan popup untuk situs ini.');
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
        }

        // ═══════════════════════════════════════════════════════════
        //  HELPER FUNCTIONS
        // ═══════════════════════════════════════════════════════════
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

        // Auto-generate kalau URL di query string
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const urlParam = params.get('url');
            if (urlParam) {
                document.getElementById('inputUrl').value = urlParam;
                generateQR();
            }
        });
    </script>
</body>

</html>
