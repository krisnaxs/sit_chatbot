<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar User & Aset — SIAM</title>
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
            font-size: 7.5px;
            text-transform: uppercase;
            font-weight: bold;
            border: 1px solid #4338ca;
        }

        table.data td {
            padding: 5px 3px;
            border: 1px solid #e2e8f0;
            font-size: 7.5px;
            word-wrap: break-word;
            vertical-align: top;
        }

        tr.row-user-start td {
            border-top: 2px solid #4f46e5;
        }

        tr.row-user-start:nth-of-type(odd) td,
        tr.row-user-cont:nth-of-type(odd) td {
            background-color: #ffffff;
        }

        tr.row-user-start:nth-of-type(even) td,
        tr.row-user-cont:nth-of-type(even) td {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: bold;
            white-space: nowrap;
        }

        .badge-blue {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-amber {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-pink {
            background-color: #fce7f3;
            color: #9d174d;
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

        .col-no {
            width: 3%;
        }

        .col-user {
            width: 11%;
        }

        .col-nip {
            width: 6.5%;
        }

        .col-dept {
            width: 9%;
        }

        .col-jabatan {
            width: 9%;
        }

        .col-jenis {
            width: 6.5%;
        }

        .col-item {
            width: 13%;
        }

        .col-brand {
            width: 13%;
        }

        .col-kategori {
            width: 8%;
        }

        .col-qty {
            width: 5%;
        }

        .col-status {
            width: 10%;
        }

        .col-lokasi {
            width: 10%;
        }

        .col-ket {
            width: 6%;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <h1>DAFTAR USER & ASET — SIAM</h1>
        <div class="subtitle">
            Dicetak: {{ now()->format('d/m/Y H:i') }} WIB
            @if (request('has_asset') === 'yes')
                • Filter: Pegang Aset
            @elseif (request('has_asset') === 'no')
                • Filter: Tidak Pegang Aset
            @endif
            @if (request('department_id'))
                • Departemen: {{ $departments->firstWhere('id', request('department_id'))?->name }}
            @endif
            @if (request('location_id'))
                • Lokasi: {{ \App\Models\Location::find(request('location_id'))?->full_name }}
            @endif
            @if (request('search'))
                • Pencarian: "{{ request('search') }}"
            @endif
        </div>
    </div>

    {{-- SUMMARY --}}
    <table class="summary-table">
        <tr>
            <td><strong>{{ $summary['total_users'] }}</strong><span>Total User</span></td>
            <td><strong>{{ $summary['users_with_assets'] }}</strong><span>Pegang Aset</span></td>
            <td><strong>{{ $summary['users_without_assets'] }}</strong><span>Tidak Pegang</span></td>
            <td><strong>{{ $summary['total_assets_held'] }}</strong><span>Aset Aktif</span></td>
            <td><strong>{{ $summary['total_loans_active'] }}</strong><span>Pinjaman</span></td>
            <td><strong>{{ $summary['total_consumables_out'] }}</strong><span>Konsumabel</span></td>
        </tr>
    </table>

    {{-- TABEL --}}
    <table class="data">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-user">Nama User</th>
                <th class="col-nip">NIP</th>
                <th class="col-dept">Departemen</th>
                <th class="col-jabatan">Jabatan</th>
                <th class="col-jenis">Jenis</th>
                <th class="col-item">SN / Item</th>
                <th class="col-brand">Brand / Model</th>
                <th class="col-kategori">Kategori</th>
                <th class="col-qty">Qty</th>
                <th class="col-status">Status / Tanggal</th>
                <th class="col-lokasi">Lokasi</th>
                <th class="col-ket">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $rowNumber = 0; @endphp
            @forelse ($users as $user)
                @php
                    $totalAssets = $user->currentAssets->count();
                    $outTrx = $user->consumableTransactions->where('type', 'out');
                    $totalLoans = $user->activeLoans->count();
                    $isEmpty = $totalAssets === 0 && $outTrx->isEmpty() && $totalLoans === 0;
                @endphp

                @if ($isEmpty)
                    @php $rowNumber++; @endphp
                    <tr class="row-user-start">
                        <td>{{ $rowNumber }}</td>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->nip ?? '-' }}</td>
                        <td>{{ $user->department?->name ?? '-' }}</td>
                        <td>{{ $user->position ?? '-' }}</td>
                        <td colspan="8" style="text-align:center;color:#94a3b8;font-style:italic;">
                            Tidak pegang aset, pinjaman, atau konsumabel
                        </td>
                    </tr>
                @else
                    @php $isFirst = true; @endphp

                    {{-- Baris ASET --}}
                    @foreach ($user->currentAssets as $asset)
                        @php
                            if ($isFirst) {
                                $rowNumber++;
                            }

                            $statusText = $asset->status_label;
                            if ($asset->status === 'in_use') {
                                $asg = $asset->assignments->first();
                                if ($asg && $asg->assigned_at) {
                                    $statusText = 'Dipakai (' . $asg->assigned_at->format('d M Y') . ')';
                                }
                            } elseif ($asset->status === 'loaned') {
                                $ln = $asset->loans->first() ?? $asset->assignments->first();
                                $dt = $ln?->loan_date ?? $ln?->assigned_at;
                                if ($dt) {
                                    $statusText = 'Dipinjam (' . $dt->format('d M Y') . ')';
                                }
                            } elseif ($asset->status === 'maintenance') {
                                $mt = $asset->maintenances->first();
                                if ($mt && $mt->start_date) {
                                    $statusText = 'Perbaikan (' . $mt->start_date->format('d M Y') . ')';
                                }
                            }
                        @endphp
                        <tr class="{{ $isFirst ? 'row-user-start' : 'row-user-cont' }}">
                            <td>{{ $isFirst ? $rowNumber : '' }}</td>
                            <td>
                                @if ($isFirst)
                                    <strong>{{ $user->name }}</strong>
                                @endif
                            </td>
                            <td>{{ $isFirst ? $user->nip ?? '-' : '' }}</td>
                            <td>{{ $isFirst ? $user->department?->name ?? '-' : '' }}</td>
                            <td>{{ $isFirst ? $user->position ?? '-' : '' }}</td>
                            <td><span class="badge badge-blue">Aset</span></td>
                            <td>{{ $asset->serial_number }}</td>
                            <td>{{ $asset->brand }} {{ $asset->model }}</td>
                            <td>{{ $asset->category?->name ?? '-' }}</td>
                            <td>1 unit</td>
                            <td>{{ $statusText }}</td>
                            <td>{{ $asset->currentLocation?->full_name ?? '-' }}</td>
                            <td>-</td>
                        </tr>
                        @php $isFirst = false; @endphp
                    @endforeach

                    {{-- Baris PINJAMAN --}}
                    @foreach ($user->activeLoans as $loan)
                        @php
                            if ($isFirst) {
                                $rowNumber++;
                            }
                        @endphp
                        <tr class="{{ $isFirst ? 'row-user-start' : 'row-user-cont' }}">
                            <td>{{ $isFirst ? $rowNumber : '' }}</td>
                            <td>
                                @if ($isFirst)
                                    <strong>{{ $user->name }}</strong>
                                @endif
                            </td>
                            <td>{{ $isFirst ? $user->nip ?? '-' : '' }}</td>
                            <td>{{ $isFirst ? $user->department?->name ?? '-' : '' }}</td>
                            <td>{{ $isFirst ? $user->position ?? '-' : '' }}</td>
                            <td><span class="badge badge-amber">Pinjaman</span></td>
                            <td>{{ $loan->asset?->serial_number ?? '-' }}</td>
                            <td>{{ $loan->asset ? $loan->asset->brand . ' ' . $loan->asset->model : '-' }}</td>
                            <td>{{ $loan->asset?->category?->name ?? '-' }}</td>
                            <td>1 unit</td>
                            <td>
                                Dipinjam ({{ $loan->loan_date?->format('d M Y') ?? '-' }})
                                @if ($loan->is_overdue)
                                    <span class="badge badge-red">TERLAMBAT</span>
                                @endif
                            </td>
                            <td>{{ $loan->asset?->currentLocation?->full_name ?? '-' }}</td>
                            <td>Jatuh tempo: {{ $loan->due_date?->format('d M Y') ?? '-' }}</td>
                        </tr>
                        @php $isFirst = false; @endphp
                    @endforeach

                    {{-- Baris KONSUMABEL --}}
                    @foreach ($outTrx as $trx)
                        @php
                            if ($isFirst) {
                                $rowNumber++;
                            }
                        @endphp
                        <tr class="{{ $isFirst ? 'row-user-start' : 'row-user-cont' }}">
                            <td>{{ $isFirst ? $rowNumber : '' }}</td>
                            <td>
                                @if ($isFirst)
                                    <strong>{{ $user->name }}</strong>
                                @endif
                            </td>
                            <td>{{ $isFirst ? $user->nip ?? '-' : '' }}</td>
                            <td>{{ $isFirst ? $user->department?->name ?? '-' : '' }}</td>
                            <td>{{ $isFirst ? $user->position ?? '-' : '' }}</td>
                            <td><span class="badge badge-pink">Konsumabel</span></td>
                            <td>{{ $trx->consumable?->name ?? '-' }}</td>
                            <td>{{ $trx->consumable?->category?->name ?? '-' }}</td>
                            <td>{{ $trx->consumable?->category?->name ?? '-' }}</td>
                            <td>{{ $trx->quantity }} {{ $trx->consumable?->unit }}</td>
                            <td>{{ $trx->transaction_date?->format('d M Y') ?? '-' }}</td>
                            <td>{{ $trx->location?->full_name ?? ($trx->asset?->serial_number ?? '-') }}</td>
                            <td>{{ Str::limit($trx->purpose ?? '-', 30) }}</td>
                        </tr>
                        @php $isFirst = false; @endphp
                    @endforeach
                @endif
            @empty
                <tr>
                    <td colspan="13" style="text-align:center;padding:20px;color:#94a3b8;">
                        Tidak ada data user ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        SIAM — Sistem Informasi Aset Manajemen
        • Total: {{ $summary['total_users'] }} user
        • {{ $summary['total_assets_held'] }} aset
        • {{ $summary['total_loans_active'] }} pinjaman
        • {{ $summary['total_consumables_out'] }} konsumabel
        • Hal. <span class="page-number"></span>/<span class="page-total"></span>
    </div>

</body>

</html>
