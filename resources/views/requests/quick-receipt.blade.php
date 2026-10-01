<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pengajuan — {{ $assetRequest->request_number }}</title>
    <link rel="icon" href="{{ asset('images/fav_icon.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{--  QR Code library --}}
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
</head>

<body class="bg-slate-100 font-sans">

    <div class="max-w-2xl mx-auto p-4 sm:p-6 space-y-4">

        {{-- 🎉 BANNER SUKSES --}}
        <div class="text-center py-4">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-800">Pengajuan Berhasil!</h1>
            <p class="text-sm text-gray-500 mt-1">Simpan atau screenshot halaman ini sebagai bukti</p>
        </div>

        {{-- 📄 SURAT PENGAJUAN --}}
        <div id="receipt" class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">

            {{-- KOP SURAT --}}
            <div class="bg-gradient-to-br from-indigo-600 to-violet-700 px-6 py-5 text-white">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/plnip.png') }}" class="h-12 bg-white rounded-lg p-1" alt="Logo">
                    <div>
                        <h2 class="font-bold text-lg">PT PLN Indonesia Power</h2>
                        <p class="text-xs text-white/80">Sistem Informasi Aset Manajemen (SIAM)</p>
                    </div>
                </div>
            </div>

            {{-- TITLE --}}
            <div class="px-6 py-4 text-center border-b border-gray-200">
                <h3 class="font-bold text-gray-800 uppercase tracking-wider text-sm">
                    Bukti Pengajuan {{ $assetRequest->type_label }}
                </h3>
                <p class="text-xs text-gray-500 mt-1 font-mono">{{ $assetRequest->request_number }}</p>
            </div>

            {{-- BODY --}}
            <div class="p-6 space-y-4">

                {{-- DATA PEMOHON --}}
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                        Data Pemohon
                    </h4>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Nama</span>
                            <span class="font-semibold text-gray-800">{{ $assetRequest->user->name }}</span>
                        </div>
                        @if ($assetRequest->user->nip)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">NIP</span>
                                <span class="font-mono text-gray-800">{{ $assetRequest->user->nip }}</span>
                            </div>
                        @endif
                        @if ($assetRequest->user->department)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Departemen</span>
                                <span class="text-gray-800">{{ $assetRequest->user->department->name }}</span>
                            </div>
                        @endif
                        @if ($assetRequest->user->position)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Jabatan</span>
                                <span class="text-gray-800">{{ $assetRequest->user->position }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- DATA ITEM --}}
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                        Item yang Diajukan
                    </h4>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Jenis</span>
                            <span class="font-semibold text-gray-800">
                                {{ $assetRequest->type_icon }} {{ $assetRequest->type_label }}
                            </span>
                        </div>
                        @if ($assetRequest->asset)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Aset</span>
                                <span class="font-semibold text-gray-800 text-right">
                                    {{ $assetRequest->asset->brand }} {{ $assetRequest->asset->model }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Serial Number</span>
                                <span class="font-mono text-gray-800">{{ $assetRequest->asset->serial_number }}</span>
                            </div>
                        @endif
                        @if ($assetRequest->consumable)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Konsumable</span>
                                <span class="font-semibold text-gray-800">{{ $assetRequest->consumable->name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Jumlah</span>
                                <span class="font-semibold text-gray-800">
                                    {{ $assetRequest->quantity }} {{ $assetRequest->consumable->unit }}
                                </span>
                            </div>
                        @endif
                        @if ($assetRequest->due_date)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Tanggal Kembali</span>
                                <span class="font-semibold text-gray-800">
                                    {{ $assetRequest->due_date->format('d/m/Y') }}
                                </span>
                            </div>
                        @endif
                        @if ($assetRequest->needed_date)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Tanggal Dibutuhkan</span>
                                <span class="text-gray-800">{{ $assetRequest->needed_date->format('d/m/Y') }}</span>
                            </div>
                        @endif
                        @if ($assetRequest->location)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Lokasi Penggunaan</span>
                                <span class="text-gray-800">{{ $assetRequest->location->full_name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ALASAN --}}
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                        Alasan / Keperluan
                    </h4>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-sm text-gray-700">{{ $assetRequest->purpose }}</p>
                    </div>
                </div>

                {{-- STATUS --}}
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 shrink-0 mt-0.5"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs text-amber-800">
                            <p class="font-semibold">Status: Menunggu Persetujuan</p>
                            <p class="mt-0.5">Admin akan memproses pengajuan Anda segera. Cek status di <a
                                    href="{{ route('requests.my') }}" class="underline font-semibold">Pengajuan
                                    Saya</a>.</p>
                        </div>
                    </div>
                </div>

                {{-- QR CODE + TTD --}}
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-dashed">
                    <div class="text-center">
                        <p class="text-xs text-gray-500 mb-2">Scan untuk cek status</p>
                        <div id="qrcode" class="inline-block p-2 bg-white border border-gray-200 rounded-lg"></div>
                    </div>
                    <div class="text-center flex flex-col justify-end">
                        <p class="text-xs text-gray-500 mb-2">Diajukan pada</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $assetRequest->created_at->format('d/m/Y') }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $assetRequest->created_at->format('H:i') }} WIB
                        </p>
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 text-center">
                <p class="text-[10px] text-gray-500">
                    Dokumen ini dicetak otomatis oleh SIAM. Tidak memerlukan tanda tangan basah.
                </p>
            </div>
        </div>

        {{-- TOMBOL AKSI --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <button type="button" onclick="downloadPDF()"
                class="px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold
                       shadow-lg shadow-indigo-500/30 transition inline-flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-4-4m4 4l4-4M12 3v9" />
                </svg>
                Download PDF
            </button>

            <a href="{{ route('requests.my') }}"
                class="px-4 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-semibold
                       transition inline-flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Pengajuan Saya
            </a>

            <a href="{{ route('requests.quick') }}"
                class="px-4 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-semibold
                       transition inline-flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Buat Lagi
            </a>
        </div>
    </div>

    <script>
        function generateQR() {
            const qrContainer = document.getElementById('qrcode');
            if (!qrContainer) return;
            if (typeof QRCode === 'undefined') {
                console.warn('QRCode belum load, retry...');
                setTimeout(generateQR, 200);
                return;
            }

            const url = '{{ route('requests.my') }}'; // URL lengkap dari route
            console.log(' Generate QR untuk:', url);

            qrContainer.innerHTML = '';
            if (typeof QRCode.toCanvas === 'function') {
                QRCode.toCanvas(qrContainer, url, {
                    width: 120,
                    margin: 2,
                    color: {
                        dark: '#1f2937',
                        light: '#ffffff',
                    },
                    errorCorrectionLevel: 'H',
                }, function(error) {
                    if (error) {
                        console.error('❌ QR error:', error);
                        qrContainer.innerHTML = '<p class="text-red-500 text-xs">QR gagal</p>';
                    } else {
                        console.log(' QR berhasil');
                    }
                });
            } else {
                new QRCode(qrContainer, {
                    text: url,
                    width: 120,
                    height: 120,
                    colorDark: '#1f2937',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel ? QRCode.CorrectLevel.H : 2
                });
                console.log(' QR berhasil (qrcodejs)');
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', generateQR);
        } else {
            generateQR();
        }

        function downloadPDF() {
            window.print();
        }

        function downloadPDF() {
            window.print();
        }
    </script>

    {{-- Style khusus untuk print --}}
    <style>
        @media print {
            body {
                background: white !important;
            }

            /* Sembunyikan tombol & banner saat print */
            .grid.grid-cols-1.sm\:grid-cols-3,
            .text-center.py-4 {
                display: none !important;
            }

            #receipt {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</body>

</html>
