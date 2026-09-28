<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserAssetsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithEvents,
    WithTitle
{
    protected array $filters;
    protected array $groupBoundaries = [];
    protected int $currentRow = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Enumerable
    {
        $query = User::with([
            'department',
            'location',
            'currentAssets.category',
            'currentAssets.currentLocation',
            'currentAssets.assignments' => function ($q) {
                $q->whereNull('returned_at')->latest('assigned_at')->limit(1);
            },
            'currentAssets.loans' => function ($q) {
                $q->where('status', 'borrowed')->latest('loan_date')->limit(1);
            },
            'currentAssets.maintenances' => function ($q) {
                $q->whereIn('status', ['open', 'in_progress'])->latest('start_date')->limit(1);
            },
            'activeLoans.asset',
            'consumableTransactions' => function ($q) {
                $q->where('type', 'out')
                    ->with(['consumable.category', 'location', 'asset'])
                    ->orderByDesc('transaction_date');
            },
        ])->withCount([
                    'currentAssets as total_assets',
                    'activeLoans as total_loans',
                    'consumableTransactions as total_consumables' => function ($q) {
                        $q->where('type', 'out');
                    },
                ]);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['department_id'])) {
            $query->where('department_id', $this->filters['department_id']);
        }

        if (!empty($this->filters['location_id'])) {
            $query->where('location_id', $this->filters['location_id']);
        }

        if (!empty($this->filters['has_asset'])) {
            if ($this->filters['has_asset'] === 'yes') {
                $query->has('currentAssets');
            } elseif ($this->filters['has_asset'] === 'no') {
                $query->doesntHave('currentAssets');
            }
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Helper: status + tanggal dengan fallback.
     */
    protected function getAssetStatusWithDate($asset): string
    {
        if ($asset->status === 'in_use') {
            $assignment = $asset->assignments->first();
            if ($assignment) {
                $date = $assignment->assigned_at ?? $assignment->created_at;
                if ($date)
                    return 'Dipakai (' . $date->format('d M Y') . ')';
            }
            return 'Dipakai';
        }

        if ($asset->status === 'loaned') {
            $loan = $asset->loans->first();
            if ($loan) {
                $date = $loan->loan_date ?? $loan->created_at;
                if ($date)
                    return 'Dipinjam (' . $date->format('d M Y') . ')';
            }
            $assignment = $asset->assignments->first();
            if ($assignment) {
                $date = $assignment->assigned_at ?? $assignment->created_at;
                if ($date)
                    return 'Dipinjam (' . $date->format('d M Y') . ')';
            }
            return 'Dipinjam';
        }

        if ($asset->status === 'maintenance') {
            $maintenance = $asset->maintenances->first();
            if ($maintenance) {
                $date = $maintenance->start_date ?? $maintenance->created_at;
                if ($date)
                    return 'Perbaikan (' . $date->format('d M Y') . ')';
            }
            return 'Perbaikan';
        }

        return $asset->status_label;
    }

    public function map($user): array
    {
        $rows = [];
        $rowCount = 0;

        static $userNumber = 0;
        $userNumber++;

        $totalAssets = $user->currentAssets->count();
        $outTrx = $user->consumableTransactions->where('type', 'out');
        $totalLoans = $user->activeLoans->count();

        // Kalau tidak punya aset, pinjaman, & konsumabel
        if ($totalAssets === 0 && $outTrx->isEmpty() && $totalLoans === 0) {
            $rows[] = [
                $userNumber,
                $user->name,
                $user->nip ?? '-',
                $user->department?->name ?? '-',
                $user->position ?? '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
                '-',
            ];
            $rowCount = 1;
        } else {
            $isFirst = true;

            // ── Baris ASET ──
            foreach ($user->currentAssets as $asset) {
                $rows[] = [
                    $isFirst ? $userNumber : '',
                    $isFirst ? $user->name : '',
                    $isFirst ? ($user->nip ?? '-') : '',
                    $isFirst ? ($user->department?->name ?? '-') : '',
                    $isFirst ? ($user->position ?? '-') : '',
                    'Aset',
                    $asset->serial_number,
                    "{$asset->brand} {$asset->model}",
                    $asset->category?->name ?? '-',
                    '1 unit',
                    $this->getAssetStatusWithDate($asset),
                    $asset->currentLocation?->full_name ?? '-',
                    '',
                ];
                $isFirst = false;
                $rowCount++;
            }

            // ── Baris PINJAMAN AKTIF ──
            foreach ($user->activeLoans as $loan) {
                $rows[] = [
                    $isFirst ? $userNumber : '',
                    $isFirst ? $user->name : '',
                    $isFirst ? ($user->nip ?? '-') : '',
                    $isFirst ? ($user->department?->name ?? '-') : '',
                    $isFirst ? ($user->position ?? '-') : '',
                    'Pinjaman',
                    $loan->asset?->serial_number ?? '-',
                    $loan->asset
                    ? "{$loan->asset->brand} {$loan->asset->model}"
                    : '-',
                    $loan->asset?->category?->name ?? '-',
                    '1 unit',
                    'Dipinjam (' . ($loan->loan_date?->format('d M Y') ?? '-') . ')'
                    . ($loan->is_overdue ? ' ⚠️ TERLAMBAT' : ''),
                    $loan->asset?->currentLocation?->full_name ?? '-',
                    'Jatuh tempo: ' . ($loan->due_date?->format('d M Y') ?? '-'),
                ];
                $isFirst = false;
                $rowCount++;
            }

            // ── Baris KONSUMABEL ──
            foreach ($outTrx as $trx) {
                $rows[] = [
                    $isFirst ? $userNumber : '',
                    $isFirst ? $user->name : '',
                    $isFirst ? ($user->nip ?? '-') : '',
                    $isFirst ? ($user->department?->name ?? '-') : '',
                    $isFirst ? ($user->position ?? '-') : '',
                    'Konsumabel',
                    $trx->consumable?->name ?? '-',
                    $trx->consumable?->category?->name ?? '-',
                    $trx->consumable?->category?->name ?? '-',
                    $trx->quantity . ' ' . ($trx->consumable?->unit ?? ''),
                    $trx->transaction_date?->format('d M Y') ?? '-',
                    $trx->location?->full_name ?? ($trx->asset?->serial_number ?? '-'),
                    $trx->purpose ?? '-',
                ];
                $isFirst = false;
                $rowCount++;
            }
        }

        // Simpan boundary grup
        $startRow = $this->currentRow + 2;
        $endRow = $startRow + $rowCount - 1;
        $this->groupBoundaries[] = ['start' => $startRow, 'end' => $endRow];
        $this->currentRow += $rowCount;

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama User',
            'NIP',
            'Departemen',
            'Jabatan',
            'Jenis',
            'SN / Item',
            'Brand / Model',
            'Kategori',
            'Qty',
            'Status / Tanggal',
            'Lokasi',
            'Keterangan',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'M'; // No .. Keterangan
    
                // Border + zebra per user
                foreach ($this->groupBoundaries as $index => $group) {
                    $range = "A{$group['start']}:{$lastColumn}{$group['end']}";

                    $sheet->getStyle($range)->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['rgb' => '4F46E5'],
                            ],
                            'inside' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'E2E8F0'],
                            ],
                        ],
                    ]);

                    // Zebra per user
                    if ($index % 2 === 0) {
                        $sheet->getStyle($range)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('FFFFFF');
                    } else {
                        $sheet->getStyle($range)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8FAFC');
                    }
                }

                // Freeze header
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(24);

                // Alignment center untuk kolom No & Qty
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("J2:J{$highestRow}")->getAlignment()->setHorizontal('center');
            },
        ];
    }

    public function title(): string
    {
        return 'User & Aset';
    }
}
