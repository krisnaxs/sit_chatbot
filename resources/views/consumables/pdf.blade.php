<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Stok Konsumable — SIAM</title>
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

        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #65a30d;
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
            width: 25%;
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

        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.data th {
            background-color: #65a30d;
            color: #ffffff;
            padding: 6px 3px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            border: 1px solid #4d7c0f;
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

        /* 🆕 Kolom Keluar — highlight amber */
        table.data td.keluar {
            background-color: #FEF3C7;
            color: #92400E;
            font-weight: bold;
            text-align: center;
        }

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

        .badge-amber {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
        }

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

        /* Kolom width — disesuaikan setelah tambah kolom Keluar */
        .col-no {
            width: 4%;
        }

        .col-nama {
            width: 16%;
        }

        /* dari 18% */

        .col-kategori {
            width: 10%;
        }

        /* dari 11% */

        .col-brand {
            width: 9%;
        }

        /* dari 10% */

        .col-model {
            width: 11%;
        }

        /* dari 12% */

        .col-unit {
            width: 5%;
        }

        /* dari 6% */

        .col-total {
            width: 7%;
        }

        /* tetap */

        .col-tersedia {
            width: 7%;
        }

        /* dari 8% */

        .col-keluar {
            width: 7%;
        }

        /* 🆕 */

        .col-min {
            width: 6%;
        }

        /* tetap */

        .col-harga {
            width: 9%;
        }

        /* tetap */

        .col-status {
            width: 9%;
        }

        /* tetap */
    </style>
</head>

<body>

    <div class="header">
        <h1>STOK KONSUMABLE — SIAM</h1>
        <div class="subtitle">
            Dicetak: {{ now()->format('d/m/Y H:i') }} WIB
            @if (request('category_id'))
                • Kategori ID: {{ request('category_id') }}
            @endif
            @if (request('low_stock'))
                • Hanya Low Stock
            @endif
            @if (request('search'))
                • Pencarian: "{{ request('search') }}"
            @endif
        </div>
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <strong>{{ $summary['total'] }}</strong>
                <span>Total Item</span>
            </td>
            <td>
                <strong>{{ $summary['available'] }}</strong>
                <span>Tersedia</span>
            </td>
            <td>
                <strong>{{ $summary['low_stock'] }}</strong>
                <span>Low Stock</span>
            </td>
            <td>
                <strong>{{ $summary['out_stock'] }}</strong>
                <span>Habis</span>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-nama">Nama Item</th>
                <th class="col-kategori">Kategori</th>
                <th class="col-brand">Brand</th>
                <th class="col-model">Model</th>
                <th class="col-unit">Unit</th>
                <th class="col-total">Total</th>
                <th class="col-tersedia">Tersedia</th>
                <th class="col-keluar">Keluar</th> {{-- 🆕 --}}
                <th class="col-min">Min</th>
                <th class="col-harga">Harga</th>
                <th class="col-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($consumables as $i => $c)
                @php
                    $statusClass = match (true) {
                        $c->stock_available == 0 => 'badge-red',
                        $c->is_low_stock => 'badge-amber',
                        default => 'badge-green',
                    };
                    $statusLabel = match (true) {
                        $c->stock_available == 0 => 'HABIS',
                        $c->is_low_stock => 'LOW',
                        default => 'TERSEDIA',
                    };

                    // 🆕 Hitung net keluar dari transaksi
                    $keluar = ($c->total_out ?? 0) - ($c->total_return ?? 0);
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->category?->name ?? '-' }}</td>
                    <td>{{ $c->brand ?? '-' }}</td>
                    <td>{{ $c->model ?? '-' }}</td>
                    <td>{{ $c->unit }}</td>
                    <td>{{ $c->stock_total }}</td>
                    <td>{{ $c->stock_available }}</td>
                    <td class="keluar">{{ $keluar }}</td> {{-- 🆕 --}}
                    <td>{{ $c->stock_minimum }}</td>
                    <td>{{ $c->last_price ? number_format($c->last_price, 0, ',', '.') : '-' }}</td>
                    <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align:center;padding:20px;color:#94a3b8;"> {{-- 🆕 dari 11 ke 12 --}}
                        Tidak ada data konsumable.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        SIAM — Sistem Informasi Aset Manajemen
        • Halaman <span class="page-number"></span> dari <span class="page-total"></span>
    </div>

</body>

</html>
