<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Aset — SIAM</title>
    <style>
        @page {
            margin: 20px 15px 45px 15px;
        }

        * {
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            font-size: 9px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        /* ============ HEADER ============ */
        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #4f46e5;
        }

        .header h1 {
            font-size: 16px;
            margin: 0 0 4px;
            color: #1e293b;
            letter-spacing: 0.5px;
        }

        .header .subtitle {
            color: #64748b;
            font-size: 9px;
            font-style: italic;
        }

        /* ============ SUMMARY ============ */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 12px;
        }

        .summary-table td {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 5px 8px;
            background: #f8fafc;
            text-align: center;
            width: 16.66%;
        }

        .summary-table strong {
            display: block;
            font-size: 13px;
            color: #1e293b;
        }

        .summary-table span {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
        }

        /* ============ TABLE ============ */
        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.data th {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 6px 3px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            border: 1px solid #4338ca;
        }

        table.data td {
            padding: 5px 3px;
            border: 1px solid #e2e8f0;
            font-size: 8px;
            word-wrap: break-word;
            vertical-align: top;
        }

        table.data tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* ============ BADGE ============ */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            white-space: nowrap;
        }

        .badge-green {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-blue {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-yellow {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-orange {
            background-color: #ffedd5;
            color: #9a3412;
        }

        .badge-gray {
            background-color: #f1f5f9;
            color: #334155;
        }

        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* ============ FOOTER ============ */
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 20px;
            text-align: center;
            font-size: 7px;
            color: #94a3b8;
        }

        .page-number:before {
            content: counter(page);
        }

        .page-total:before {
            content: counter(pages);
        }

        /* ============ KOLOM WIDTH ============ */
        .col-no {
            width: 3%;
        }

        .col-pemakai {
            width: 9%;
        }

        .col-nip {
            width: 7%;
        }

        .col-jabatan {
            width: 8%;
        }

        .col-lokasi {
            width: 11%;
        }

        .col-kode {
            width: 7%;
        }

        .col-sn {
            width: 7%;
        }

        .col-brand {
            width: 8%;
        }

        .col-kategori {
            width: 7%;
        }

        .col-ownership {
            width: 14%;
        }

        /* 🆕 diperlebar */
        .col-status {
            width: 7%;
        }

        .col-tahun {
            width: 5%;
        }
    </style>
</head>

<body>

    {{-- ============ HEADER ============ --}}
    <div class="header">
        <h1>DAFTAR ASET IT — SIAM</h1>
        <div class="subtitle">
            Dicetak: {{ now()->format('d/m/Y H:i') }} WIB
            @if (request('status'))
                • Status: {{ ucfirst(request('status')) }}
            @endif
            @if (request('brand'))
                • Brand: {{ request('brand') }}
            @endif
            @if (request('year'))
                • Tahun: {{ request('year') }}
            @endif
            @if (request('search'))
                • Pencarian: "{{ request('search') }}"
            @endif
        </div>
    </div>

    {{-- ============ SUMMARY ============ --}}
    <table class="summary-table">
        <tr>
            <td>
                <strong>{{ $summary['total'] }}</strong>
                <span>Total</span>
            </td>
            <td>
                <strong>{{ $summary['available'] }}</strong>
                <span>Tersedia</span>
            </td>
            <td>
                <strong>{{ $summary['in_use'] }}</strong>
                <span>Dipakai</span>
            </td>
            <td>
                <strong>{{ $summary['maintenance'] }}</strong>
                <span>Perbaikan</span>
            </td>
            <td>
                <strong>{{ $summary['owned'] }}</strong>
                <span>Milik</span>
            </td>
            <td>
                <strong>{{ $summary['leased'] }}</strong>
                <span>Sewa</span>
            </td>
        </tr>
    </table>

    {{-- ============ TABEL DATA ============ --}}
    <table class="data">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-pemakai">Pemakai</th>
                <th class="col-nip">NIP</th>
                <th class="col-jabatan">Jabatan</th>
                <th class="col-lokasi">Lokasi</th>
                <th class="col-kode">Kode</th>
                <th class="col-sn">Serial Number</th>
                <th class="col-brand">Brand / Model</th>
                <th class="col-kategori">Kategori</th>
                <th class="col-ownership">Kepemilikan</th>
                <th class="col-status">Status</th>
                <th class="col-tahun">Tahun</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assets as $i => $asset)
                @php
                    $statusClass = match ($asset->status) {
                        'available' => 'badge-green',
                        'in_use' => 'badge-blue',
                        'loaned' => 'badge-yellow',
                        'maintenance' => 'badge-orange',
                        'lost' => 'badge-red',
                        default => 'badge-gray',
                    };
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $asset->currentUser?->name ?? '-' }}</td>
                    <td>{{ $asset->currentUser?->nip ?? '-' }}</td>
                    <td>{{ $asset->currentUser?->position ?? '-' }}</td>
                    <td>{{ $asset->currentLocation?->full_name ?? '-' }}</td>
                    <td>{{ $asset->asset_code ?? '-' }}</td>
                    <td>{{ $asset->serial_number }}</td>
                    <td>{{ $asset->brand }} {{ $asset->model }}</td>
                    <td>{{ $asset->category?->name ?? '-' }}</td>
                    <td>{{ $asset->ownership_label_with_vendor }}</td> {{-- 🆕 GANTI --}}
                    <td><span class="badge {{ $statusClass }}">{{ $asset->status_label }}</span></td>
                    <td>{{ $asset->purchase_date?->format('Y') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align:center;padding:20px;color:#94a3b8;">
                        Tidak ada data aset ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ============ FOOTER ============ --}}
    <div class="footer">
        SIAM — Sistem Informasi Aset Manajemen
        • Halaman <span class="page-number"></span> dari <span class="page-total"></span>
    </div>

</body>

</html>
