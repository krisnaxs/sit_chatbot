<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Transaksi Konsumable — SIAM</title>
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
            border-bottom: 2px solid #db2777;
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
            background-color: #db2777;
            color: #ffffff;
            padding: 6px 3px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            border: 1px solid #be185d;
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

        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-blue {
            background-color: #dbeafe;
            color: #1e40af;
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

        .col-no {
            width: 3%;
        }

        .col-tgl {
            width: 10%;
        }

        .col-item {
            width: 13%;
        }

        .col-tipe {
            width: 7%;
        }

        .col-qty {
            width: 5%;
        }

        .col-user {
            width: 11%;
        }

        .col-lokasi {
            width: 12%;
        }

        .col-aset {
            width: 11%;
        }

        .col-keperluan {
            width: 15%;
        }

        .col-catatan {
            width: 13%;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>TRANSAKSI KONSUMABLE — SIAM</h1>
        <div class="subtitle">
            Dicetak: {{ now()->format('d/m/Y H:i') }} WIB
            @if (request('type'))
                • Tipe: {{ ucfirst(request('type')) }}
            @endif
            @if (request('date_from'))
                • Dari: {{ request('date_from') }}
            @endif
            @if (request('date_to'))
                • Sampai: {{ request('date_to') }}
            @endif
        </div>
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <strong>{{ $summary['total'] }}</strong>
                <span>Total Transaksi</span>
            </td>
            <td>
                <strong>{{ $summary['total_in'] }}</strong>
                <span>Total Masuk</span>
            </td>
            <td>
                <strong>{{ $summary['total_out'] }}</strong>
                <span>Total Keluar</span>
            </td>
            <td>
                <strong>{{ $summary['total_return'] }}</strong>
                <span>Total Kembali</span>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tgl">Tanggal</th>
                <th class="col-item">Item</th>
                <th class="col-tipe">Tipe</th>
                <th class="col-qty">Qty</th>
                <th class="col-user">User</th>
                <th class="col-lokasi">Lokasi</th>
                <th class="col-aset">Aset</th>
                <th class="col-keperluan">Keperluan</th>
                <th class="col-catatan">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $i => $t)
                @php
                    $typeClass = match ($t->type) {
                        'in' => 'badge-green',
                        'out' => 'badge-red',
                        'return' => 'badge-blue',
                        default => '',
                    };
                    $typeLabel = match ($t->type) {
                        'in' => 'MASUK',
                        'out' => 'KELUAR',
                        'return' => 'KEMBALI',
                        default => '-',
                    };
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $t->transaction_date?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $t->consumable?->name ?? '-' }}</td>
                    <td><span class="badge {{ $typeClass }}">{{ $typeLabel }}</span></td>
                    <td>{{ $t->quantity }} {{ $t->consumable?->unit }}</td>
                    <td>{{ $t->user?->name ?? '-' }}</td>
                    <td>{{ $t->location?->full_name ?? '-' }}</td>
                    <td>{{ $t->asset?->serial_number ?? '-' }}</td>
                    <td>{{ $t->purpose ?? '-' }}</td>
                    <td>{{ $t->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:20px;color:#94a3b8;">
                        Tidak ada data transaksi.
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
