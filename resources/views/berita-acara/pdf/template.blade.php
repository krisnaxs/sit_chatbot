<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">

    <title>{{ $ba->judul }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #000;
            padding: 10mm 20mm 10mm 25mm;
        }

        /* =========================================================
       KOP SURAT
       ========================================================= */

        .kop {
            width: 100%;
            border-bottom: 1px solid #000;
            padding-bottom: 2mm;
        }

        .kop-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-table td {
            border: none;
            vertical-align: top;
            padding: 0;
        }

        .logo-cell {
            padding: 0;
        }

        .logo {
            width: 200px;
            height: auto;
            display: block;
        }

        .unit-cell {
            padding: 0;
            padding-top: 1mm;
        }

        .unit {
            width: 200px;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            font-weight: bold;
            line-height: 2.1;
        }

        /* =========================================================
       JUDUL
       ========================================================= */

        .judul {
            text-align: center;
            margin-top: 5mm;
            margin-bottom: 1mm;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.5;
        }

        .nomor-ba {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 8mm;
            color: #000;
        }

        /* =========================================================
       PARAGRAF
       ========================================================= */

        .paragraf {
            font-size: 10pt;
            line-height: 1.5;
            text-align: justify;
            margin-bottom: 5mm;
        }

        .paragraf:last-child {
            margin-bottom: 0;
        }

        /* =========================================================
       PIHAK
       ========================================================= */

        .pihak {
            margin-left: 13mm;
            margin-bottom: 5mm;
        }

        .pihak-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pihak-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            font-size: 10pt;
            line-height: 1.2;
        }

        .pihak-table .label {
            width: 25mm;
        }

        .pihak-table .colon {
            width: 4mm;
        }

        .pihak-table .value {
            width: auto;
        }

        .pihak-sebutan {
            margin-top: 4mm;
            font-size: 10pt;
            line-height: 1.2;
        }

        /* =========================================================
       NARASI BARANG
       ========================================================= */

        .narasi {
            margin-top: 5mm;
            margin-bottom: 2mm;
            font-size: 10pt;
            line-height: 1.5;
            text-align: justify;
        }

        /* =========================================================
       TABEL BARANG
       ========================================================= */

        .barang {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 1mm;
            margin-bottom: 6mm;
            font-size: 10pt;
            page-break-inside: avoid;
        }

        .barang th,
        .barang td {
            border: 1px solid #000;
            padding: 4px 7px;
            vertical-align: middle;
            line-height: 1.25;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .barang th {
            text-align: center;
            font-weight: bold;
            height: 11mm;
        }

        .barang td {
            text-align: center;
        }

        .col-jenis {
            width: 16%;
        }

        .col-merk {
            width: 14%;
        }

        .col-type {
            width: 32%;
        }

        .col-lisensi {
            width: 18%;
        }

        .col-jumlah {
            width: 20%;
        }

        .barang .left {
            text-align: left;
        }

        .type-list {
            margin: 0;
            padding-left: 4mm;
            text-align: left;
            list-style-type: disc;
        }

        .type-list li {
            margin: 0;
            padding: 0;
            line-height: 1.35;
        }

        /* =========================================================
       PENUTUP
       ========================================================= */

        .penutup {
            font-size: 10pt;
            line-height: 1.5;
            text-align: justify;
            margin-bottom: 2mm;
        }

        .kesepakatan {
            font-size: 10pt;
            line-height: 1.5;
            text-align: justify;
            margin-top: 0;
        }

        /* =========================================================
       TANGGAL
       ========================================================= */

        .tanggal {
            width: 100%;
            text-align: right;
            font-size: 10pt;
            line-height: 1.5;
            margin-top: 8mm;
        }

        /* =========================================================
       TANDA TANGAN
       ========================================================= */

        .ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2mm;
            page-break-inside: avoid;
        }

        .ttd td {
            width: 50%;
            border: none;
            text-align: center;
            vertical-align: top;
            font-size: 10pt;
            line-height: 1.25;
        }

        .ttd-jabatan {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 1mm;
        }

        .ttd-space {
            height: 24mm;
        }

        .ttd-name {
            font-weight: bold;
            white-space: nowrap;
        }

        .footer {
            display: none;
        }
    </style>
</head>

<body>

    {{-- =========================================================
         KOP SURAT
         ========================================================= --}}

    <div class="kop">

        <table class="kop-table">
            <tr>
                <td class="logo-cell">

                    @php
                        $logoPath = public_path('images/plnip.png');
                        $logoSrc = null;

                        if (file_exists($logoPath) && is_readable($logoPath)) {
                            $logoData = base64_encode(file_get_contents($logoPath));
                            $logoSrc = 'data:image/png;base64,' . $logoData;
                        }
                    @endphp

                    @if ($logoSrc)
                        <img src="{{ $logoSrc }}" class="logo" alt="PLN Indonesia Power">
                    @endif

                </td>
            </tr>

            <tr>
                <td class="unit-cell">
                    <div class="unit">
                        UBP SURALAYA
                    </div>
                </td>
            </tr>
        </table>

    </div>


    {{-- =========================================================
     JUDUL
     ========================================================= --}}

    <div class="judul">
        {{ strtoupper($ba->judul) }}
    </div>

    {{-- 🆕 NOMOR BA --}}
    <div class="nomor-ba">
        Nomor: {{ $ba->nomor_ba }}
    </div>

    <p class="paragraf">
        Pada hari Ini
        {{ $ba->tanggal_ba->translatedFormat('l') }},
        tanggal
        {{ $ba->tanggal_ba->translatedFormat('d F Y') }}
        kami yang bertanda tangan dibawah ini :
    </p>


    {{-- =========================================================
         PIHAK PERTAMA
         ========================================================= --}}

    <div class="pihak">

        <table class="pihak-table">

            <tr>
                <td class="label">Nama</td>
                <td class="colon">:</td>
                <td class="value">
                    {{ $ba->pihakPertama->name ?? '-' }}
                </td>
            </tr>

            <tr>
                <td class="label">Nip</td>
                <td class="colon">:</td>
                <td class="value">
                    {{ $ba->pihak_pertama_nip ?? '-' }}
                </td>
            </tr>

            <tr>
                <td class="label">Jabatan</td>
                <td class="colon">:</td>
                <td class="value">
                    {{ $ba->pihak_pertama_jabatan ?? '-' }}
                </td>
            </tr>

        </table>

        <div class="pihak-sebutan">
            Selanjutnya disebut
            <strong>PIHAK PERTAMA</strong>
        </div>

    </div>


    {{-- =========================================================
         PIHAK KEDUA
         ========================================================= --}}

    <div class="pihak">

        <table class="pihak-table">

            <tr>
                <td class="label">Nama</td>
                <td class="colon">:</td>
                <td class="value">
                    {{ $ba->pihakKedua->name ?? '-' }}
                </td>
            </tr>

            <tr>
                <td class="label">Nip</td>
                <td class="colon">:</td>
                <td class="value">
                    {{ $ba->pihak_kedua_nip ?? '-' }}
                </td>
            </tr>

            <tr>
                <td class="label">Jabatan</td>
                <td class="colon">:</td>
                <td class="value">
                    {{ $ba->pihak_kedua_jabatan ?? '-' }}
                </td>
            </tr>

        </table>

        <div class="pihak-sebutan">
            Selanjutnya disebut
            <strong>PIHAK KEDUA</strong>
        </div>

    </div>


    {{-- =========================================================
         NARASI
         ========================================================= --}}

    <p class="narasi">

        @if ($ba->jenis === 'serah_terima')
            PIHAK PERTAMA menyerahkan barang kepada PIHAK KEDUA,
            dan PIHAK KEDUA menyatakan telah menerima barang
            dari PIHAK PERTAMA berupa:
        @else
            PIHAK KEDUA menyerahkan kembali barang kepada
            PIHAK PERTAMA, dan PIHAK PERTAMA menyatakan telah
            menerima barang dari PIHAK KEDUA berupa:
        @endif

    </p>


    {{-- =========================================================
         DATA TABEL BARANG
         ========================================================= --}}

    @php

        $typeList = [];

        if ($ba->model) {
            $typeList[] = strtoupper($ba->model);
        }

        if ($ba->hostname) {
            $typeList[] = strtoupper($ba->hostname);
        }

        if ($ba->serial_number) {
            $typeList[] = strtoupper($ba->serial_number);
        }

        $lisensiText = '-';

        if (!empty($ba->detail_list)) {
            $lisensi = $ba->detail_list['lisensi_office'] ?? null;

            if ($lisensi) {
                $lisensiText = $lisensi;
            } else {
                $firstDetail = reset($ba->detail_list);

                if ($firstDetail) {
                    $lisensiText = $firstDetail;
                }
            }
        }

    @endphp


    {{-- =========================================================
         TABEL
         ========================================================= --}}

    <table class="barang">

        <colgroup>
            <col class="col-jenis">
            <col class="col-merk">
            <col class="col-type">
            <col class="col-lisensi">
            <col class="col-jumlah">
        </colgroup>

        <thead>
            <tr>

                <th>
                    Jenis Peralatan
                </th>

                <th>
                    Merk
                </th>

                <th>
                    Type / Host / SN
                </th>

                <th>
                    Lisensi<br>
                    Office
                </th>

                <th>
                    Jumlah
                </th>

            </tr>
        </thead>

        <tbody>

            <tr>

                <td>
                    <strong>
                        {{ $ba->kategori_aset }}
                    </strong>
                </td>

                <td>
                    {{ $ba->merk }}
                </td>

                <td class="left">

                    @if (count($typeList) > 0)

                        <ul class="type-list">

                            @foreach ($typeList as $item)
                                <li>
                                    {{ $item }}
                                </li>
                            @endforeach

                        </ul>
                    @else
                        -

                    @endif

                </td>

                <td>
                    {{ $lisensiText }}
                </td>

                <td>
                    {{ $ba->jumlah }} Unit
                </td>

            </tr>

        </tbody>

    </table>


    {{-- =========================================================
         PENUTUP
         ========================================================= --}}

    @if ($ba->jenis === 'serah_terima')

        <p class="penutup">

            Demikianlah berita acara serah terima barang ini di
            perbuat oleh kedua belah pihak, adapun barang-barang
            tersebut dalam keadaan baik dan cukup, sejak
            penandatanganan berita acara ini, maka barang tersebut,
            menjadi tanggung jawab PIHAK KEDUA.

            <strong>
                Dengan Kesepakatan Penggunaan Peralatan:
            </strong>

        </p>

        <p class="kesepakatan">

            Selama PIHAK KEDUA menggunakan fasilitas peralatan kerja
            ini, PIHAK KEDUA akan mengikuti ketentuan dan aturan
            yang ada di perusahaan. PIHAK KEDUA akan menjaga,
            memelihara dan merawat dengan baik serta dipergunakan
            untuk keperluan kedinasan dan bertanggung jawab terhadap
            data-data yang ada didalamnya. Dan PIHAK KEDUA juga
            memahami bahwa peralatan kerja tersebut digunakan
            terkait dengan penugasan PIHAK KEDUA dan dapat
            dievaluasi bila ada perubahan penugasan. Apabila PIHAK
            KEDUA melanggar kesepakatan ini, maka PIHAK KEDUA
            bersedia mendapat sanksi sesuai ketentuan yang berlaku
            di perusahaan.

        </p>
    @else
        <p class="penutup">

            Demikianlah berita acara pengembalian barang ini
            diperbuat oleh kedua belah pihak, adapun barang-barang
            tersebut telah dikembalikan dengan kondisi sebagaimana
            tercatat. Sejak penandatanganan berita acara ini,
            tanggung jawab barang berpindah dari PIHAK KEDUA
            kepada PIHAK PERTAMA.

        </p>

        <div class="kesepakatan">

            <strong>Catatan Kondisi Pengembalian:</strong>

            <br>

            • Kondisi aset saat dikembalikan:
            <strong>{{ $ba->condition_percent ?? 100 }}%</strong>

            <br>

            • Seluruh kelengkapan aset telah diperiksa dan sesuai
            dengan kondisi saat diserahkan.

            @if ($ba->notes)
                <br>

                • Catatan:
                {{ $ba->notes }}
            @endif

        </div>

    @endif


    {{-- =========================================================
         TANGGAL
         ========================================================= --}}

    <div class="tanggal">
        {{ $ba->tempat_ba }},
        {{ $ba->tanggal_ba->translatedFormat('d F Y') }}
    </div>
    <br>


    {{-- =========================================================
         TANDA TANGAN
         ========================================================= --}}

    <table class="ttd">

        <tr>

            <td>

                <div>
                    Diserahkan Oleh,
                </div>

                <div class="ttd-jabatan">
                    {{ $ba->pihak_pertama_jabatan ?? 'PEJABAT' }}
                </div>

            </td>

            <td>

                <div>
                    Diterima oleh,
                </div>

                <div class="ttd-jabatan">
                    {{ $ba->pihak_kedua_jabatan ?? 'PEGAWAI' }}
                </div>

            </td>

        </tr>


        <tr>

            <td colspan="2">
                <div class="ttd-space"></div>
            </td>

        </tr>


        <tr>

            <td>

                <div class="ttd-name">
                    ( {{ $ba->pihakPertama->name ?? '-' }} )
                </div>

            </td>

            <td>

                <div class="ttd-name">
                    ( {{ $ba->pihakKedua->name ?? '-' }} )
                </div>

            </td>

        </tr>

    </table>

</body>

</html>
