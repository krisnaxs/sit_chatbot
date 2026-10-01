@extends('layouts.app')

@section('title', 'QR Code — ' . $asset->serial_number)

@php
    $publicUrl = $asset->public_url ?? '';
    $safeSerial = str_replace(['/', '\\', ' ', '#'], '-', $asset->serial_number ?? 'asset');
    $brand = $asset->brand ?? '';
    $model = $asset->model ?? '';
    $assetCode = $asset->asset_code ?? '-';
    $serialNum = $asset->serial_number ?? '';
    $logoUrl = asset('images/plnip.png');
@endphp

@push('styles')
    <script src="{{ asset('js/qrcode.min.js') }}"></script>

    <style>
        @media print {
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            nav,
            aside,
            header,
            footer,
            .no-print {
                display: none !important;
            }

            [class*="lg:ml-"] {
                margin-left: 0 !important;
            }

            #qrCard {
                box-shadow: none !important;
                border: 2px solid #000 !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="max-w-md mx-auto p-4">

        {{-- CARD QR --}}
        <div id="qrCard" class="bg-white rounded-2xl shadow-sm p-6 text-center">

            {{-- Header --}}
            <div class="mb-4">
                <img src="{{ asset('images/plnip.png') }}" class="h-12 mx-auto mb-2" alt="Logo">
                <h1 class="text-lg font-bold text-gray-800">
                    {{ $brand }} {{ $model }}
                </h1>
                <p class="text-xs text-gray-500 font-mono mt-0.5">
                    {{ $assetCode }}
                </p>
                <p class="text-[11px] text-gray-400 font-mono mt-1">
                    SN: {{ $serialNum ?: '(kosong)' }}
                </p>
            </div>

            @if ($publicUrl)
                {{-- QR Container --}}
                <div id="qrcode" class="inline-block p-4 bg-white border-2 border-dashed border-gray-300 rounded-2xl">
                </div>

                {{-- URL --}}
                <p class="text-[10px] text-gray-400 mt-3 font-mono break-all leading-relaxed">
                    {{ $publicUrl }}
                </p>
            @else
                <div class="p-6 bg-red-50 border border-red-200 rounded-2xl">
                    <p class="text-sm text-red-700 font-semibold"> QR tidak bisa dibuat</p>
                    <p class="text-xs text-red-600 mt-1">Serial number kosong.</p>
                </div>
            @endif

            {{-- Tombol --}}
            <div class="grid grid-cols-2 gap-2 mt-6 no-print">
                <a href="{{ route('siam.assets.show', $asset) }}"
                    class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm transition text-center">
                    ← Kembali
                </a>
                <button type="button" onclick="downloadQR()"
                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm transition
                           {{ $publicUrl ? '' : 'opacity-50 cursor-not-allowed' }}"
                    @if (!$publicUrl) disabled @endif>
                    Download PNG
                </button>
            </div>

            <button type="button" onclick="printQR()"
                class="w-full mt-2 px-4 py-2.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-sm transition no-print
                       {{ $publicUrl ? '' : 'opacity-50 cursor-not-allowed' }}"
                @if (!$publicUrl) disabled @endif>
                🖨️ Print QR Code
            </button>
        </div>

        {{-- Info --}}
        <div class="mt-4 p-4 bg-indigo-50 border border-indigo-100 rounded-2xl no-print">
            <p class="text-xs text-indigo-700 leading-relaxed">
                <strong> Info:</strong> QR ini berisi link permanen ke halaman publik aset.
                QR <strong>tidak akan berubah</strong> meski data aset diupdate.
            </p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const qrUrl = {{ Js::from($publicUrl) }};
        const safeFilename = {{ Js::from($safeSerial) }};
        const assetBrand = {{ Js::from($brand) }};
        const assetModel = {{ Js::from($model) }};
        const assetCode = {{ Js::from($assetCode) }};
        const assetSN = {{ Js::from($serialNum) }};
        const logoUrl = {{ Js::from($logoUrl) }};

        function generateQR() {
            const container = document.getElementById('qrcode');
            if (!container) return;

            if (typeof QRCode === 'undefined') {
                console.warn('QRCode belum load, retry...');
                setTimeout(generateQR, 200);
                return;
            }

            if (!qrUrl) return;

            if (typeof QRCode.toCanvas === 'function') {
                QRCode.toCanvas(container, qrUrl, {
                    width: 260,
                    margin: 4,
                    color: {
                        dark: '#1f2937',
                        light: '#ffffff'
                    },
                    errorCorrectionLevel: 'H',
                }, function(error) {
                    if (error) {
                        container.innerHTML = '<p class="text-red-500 text-xs">Gagal generate QR</p>';
                        return;
                    }
                    QRCode.toDataURL(qrUrl, {
                        width: 1000,
                        margin: 4,
                        color: {
                            dark: '#1f2937',
                            light: '#ffffff'
                        },
                        errorCorrectionLevel: 'H',
                    }, function(err, hdUrl) {
                        if (!err) window.__QR_HD_URL = hdUrl;
                    });
                });
            } else {
                container.innerHTML = '';

                new QRCode(container, {
                    text: qrUrl,
                    width: 260,
                    height: 260,
                    colorDark: '#1f2937',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel ? QRCode.CorrectLevel.H : 2
                });

                const hdContainer = document.createElement('div');
                hdContainer.style.display = 'none';
                document.body.appendChild(hdContainer);

                new QRCode(hdContainer, {
                    text: qrUrl,
                    width: 1000,
                    height: 1000,
                    colorDark: '#1f2937',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel ? QRCode.CorrectLevel.H : 2
                });

                setTimeout(() => {
                    const hdCanvas = hdContainer.querySelector('canvas');
                    const hdImg = hdContainer.querySelector('img');
                    if (hdCanvas) window.__QR_HD_CANVAS = hdCanvas;
                    else if (hdImg) window.__QR_HD_IMG = hdImg;
                }, 300);
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
                if (img && img.src) qrSrc = img.src;
                else if (canvas) qrSrc = canvas.toDataURL('image/png');
            }

            if (!qrSrc) {
                alert('QR belum siap. Tunggu sebentar lalu coba lagi.');
                return;
            }

            const printWindow = window.open('', '_blank', 'width=900,height=1000');
            if (!printWindow) {
                alert('Popup diblokir. Izinkan popup untuk situs ini, lalu coba lagi.');
                return;
            }

            let html = '';
            html += '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Print QR Code — SIAM</title>';
            html += '<style>';
            html += '* { margin: 0; padding: 0; box-sizing: border-box; }';
            html +=
                'html, body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: white; padding: 30px; }';
            html +=
                '.qr-card { border: 3px solid #000; border-radius: 20px; padding: 40px 30px; text-align: center; max-width: 560px; margin: 0 auto; page-break-inside: avoid; }';
            html += '.logo { height: 70px; margin-bottom: 16px; }';
            html += '.title { font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 6px; }';
            html += '.asset-name { font-size: 16px; color: #4b5563; margin-bottom: 4px; }';
            html +=
                '.asset-code { font-family: "Courier New", monospace; font-size: 13px; color: #6b7280; margin-bottom: 4px; }';
            html +=
                '.asset-sn { font-family: "Courier New", monospace; font-size: 13px; color: #6b7280; margin-bottom: 24px; }';
            html +=
                '.qr-box { display: inline-block; padding: 16px; border: 2px dashed #d1d5db; border-radius: 16px; margin: 8px 0 20px; background: white; }';
            html += '.qr-img { display: block; width: 320px; height: 320px; image-rendering: pixelated; }';
            html +=
                '.url { font-family: "Courier New", monospace; font-size: 12px; background: #f3f4f6; padding: 8px 16px; border-radius: 8px; display: inline-block; color: #374151; margin-bottom: 24px; word-break: break-all; max-width: 100%; }';
            html +=
                '.steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb; text-align: left; }';
            html +=
                '.step-num { width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-weight: 700; font-size: 15px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }';
            html += '.step-title { font-weight: 600; font-size: 14px; color: #1f2937; }';
            html += '.step-desc { font-size: 12px; color: #6b7280; margin-top: 3px; }';
            html +=
                '@media print { body { padding: 15px; background: white; } .qr-card { page-break-inside: avoid; } @page { size: A4; margin: 15mm; } }';
            html += '</style></head><body>';

            html += '<div class="qr-card">';
            html += '<img src="' + logoUrl + '" class="logo" alt="Logo">';
            html += '<div class="title">SIAM — Aset</div>';
            html += '<div class="asset-name">' + assetBrand + ' ' + assetModel + '</div>';
            html += '<div class="asset-code">' + assetCode + '</div>';
            html += '<div class="asset-sn">SN: ' + assetSN + '</div>';

            html += '<div class="qr-box">';
            html += '<img src="' + qrSrc + '" class="qr-img" alt="QR Code">';
            html += '</div>';

            html += '<div class="url">' + qrUrl + '</div>';

            html += '<div class="steps">';
            html +=
                '<div><div class="step-num">1</div><div class="step-title">Scan QR</div><div class="step-desc">Pakai kamera HP</div></div>';
            html +=
                '<div><div class="step-num">2</div><div class="step-title">Lihat Data</div><div class="step-desc">Info aset terkini</div></div>';
            html +=
                '<div><div class="step-num">3</div><div class="step-title">Hubungi Admin</div><div class="step-desc">Kalau ada masalah</div></div>';
            html += '</div>';
            html += '</div>';
            html += '<scr' + 'ipt>';
            html += 'window.onload = function() { setTimeout(function() { window.print(); }, 600); };';
            html += '</scr' + 'ipt>';

            html += '</body></html>';

            printWindow.document.write(html);
            printWindow.document.close();
        }
        async function downloadQR() {
            let qrSource = null;

            if (window.__QR_HD_CANVAS) {
                qrSource = window.__QR_HD_CANVAS;
            } else {
                const canvas = document.querySelector('#qrcode canvas');
                const img = document.querySelector('#qrcode img');

                if (canvas) qrSource = canvas;
                else if (img && img.src) qrSource = await imgToCanvas(img);
            }

            if (!qrSource) {
                alert('QR belum siap. Tunggu sebentar lalu coba lagi.');
                return;
            }

            const Q = 800;
            const PADDING = 50;
            const QR_SIZE = 440;
            const TITLE_SPACE = 130;
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
            ctx.fillText('SIAM — Aset', Q / 2, PADDING + 30);
            ctx.fillStyle = '#4b5563';
            ctx.font = '18px -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
            ctx.fillText(assetBrand + ' ' + assetModel, Q / 2, PADDING + 70);
            ctx.fillStyle = '#6b7280';
            ctx.font = '14px "Courier New", monospace';
            ctx.fillText(assetCode, Q / 2, PADDING + 100);
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
            ctx.fillStyle = '#6b7280';
            ctx.font = '16px "Courier New", monospace';
            ctx.fillText(qrUrl, Q / 2, qrY + QR_SIZE + 55);
            const dataUrl = finalCanvas.toDataURL('image/png');

            const link = document.createElement('a');
            link.download = 'qr-' + safeFilename + '.png';
            link.href = dataUrl;
            link.click();
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
