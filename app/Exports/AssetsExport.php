<?php

namespace App\Exports;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = Asset::with([
            'category',
            'currentUser',
            'currentLocation',
            'ownership.vendor',
            'activeAssignment',
            'activeAssignment.user',
            'assignments' => fn($q) => $q->orderBy('assigned_at', 'asc'),
            'assignments.user',
            'maintenances' => fn($q) => $q->orderBy('start_date', 'asc'),
            'maintenances.vendor',
        ]);

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('serial_number', 'like', "%{$s}%")
                    ->orWhere('asset_code', 'like', "%{$s}%")
                    ->orWhere('hostname', 'like', "%{$s}%")
                    ->orWhere('brand', 'like', "%{$s}%")
                    ->orWhere('model', 'like', "%{$s}%")
                    ->orWhereHas('currentUser', fn($qu) => $qu->where('name', 'like', "%{$s}%"));
            });
        }

        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }
        if (!empty($this->filters['ownership_type'])) {
            $query->where('ownership_type', $this->filters['ownership_type']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['pemakai_status'])) {
            match ($this->filters['pemakai_status']) {
                'perlu_ditarik' => $query->perluDitarik(30),
                'sudah_pensiun' => $query->dipegangPensiunan(),
                'akan_pensiun' => $query->akanDitarik(30),
                default => null,
            };
        }
        if (!empty($this->filters['brand'])) {
            $query->where('brand', $this->filters['brand']);
        }
        if (!empty($this->filters['model'])) {
            $query->where('model', $this->filters['model']);
        }
        if (!empty($this->filters['location_id'])) {
            $query->where('current_location_id', $this->filters['location_id']);
        }
        if (!empty($this->filters['user_id'])) {
            $query->where('current_user_id', $this->filters['user_id']);
        }
        if (!empty($this->filters['year'])) {
            $query->whereYear('purchase_date', $this->filters['year']);
        }

        return $query->orderBy('asset_code');
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Pemakai (Saat Ini)',
            'NIP Pemakai',
            'Jabatan',
            'Lokasi',
            'Tgl Serah Terima',
            'Lama Pakai (hari)',
            'Kode Aset',
            'Serial Number',
            'Hostname',
            'Brand',
            'Model',
            'Kategori',
            'Kepemilikan',
            'Status',
            'Waktu Pensiun',
            'Sisa Hari',
            'History Pemakai Sebelumnya',
            'Total Pernah Dipakai',
            'History Perbaikan',
            'Total Perbaikan',
            'Tahun Pembelian',
            'Harga Beli',
            'Kondisi (%)',
            'Catatan',
        ];
    }

    public function map($asset): array
    {
        static $no = 0;
        $no++;

        $assignment = $asset->activeAssignment;
        $assignedAt = $assignment?->assigned_at;
        $lamaPakai = $assignedAt ? (int) $assignedAt->diffInDays(now()) : null;

        $waktuPensiun = $asset->currentUser?->waktu_pensiun;
        $sisaHari = $asset->sisa_hari_pensiun;

        $sisaLabel = '-';
        if ($sisaHari !== null) {
            if ($sisaHari < 0) {
                $sisaLabel = 'Pensiun ' . abs($sisaHari) . ' hari lalu';
            } elseif ($sisaHari === 0) {
                $sisaLabel = 'Pensiun hari ini';
            } else {
                $sisaLabel = $sisaHari . ' hari lagi';
            }
        }
        $historyLines = [];
        $assignmentCount = 0;

        foreach ($asset->assignments as $a) {
            $assignmentCount++;
            $nama = $a->user?->name ?? 'Tanpa Nama';
            $tglSerah = $a->assigned_at?->format('d/m/Y') ?? '?';
            $tglKembali = $a->returned_at?->format('d/m/Y') ?? 'sekarang';

            $historyLines[] = "{$nama} ({$tglSerah} → {$tglKembali})";
        }

        $historyText = !empty($historyLines)
            ? implode("\n", $historyLines)
            : '-';
        $maintenanceLines = [];
        $maintenanceCount = 0;

        foreach ($asset->maintenances as $m) {
            $maintenanceCount++;

            $tgl = $m->start_date?->format('d/m/Y') ?? '?';
            $issue = $m->issue ?? 'Tanpa keterangan';
            $issue = strlen($issue) > 40 ? substr($issue, 0, 40) . '...' : $issue;
            $statusLabel = match ($m->status ?? '') {
                'open' => 'open',
                'in_progress' => 'proses',
                'done' => 'selesai',
                'cancelled' => 'batal',
                default => $m->status ?? '-',
            };
            $vendor = $m->vendor?->name ?? $m->technician ?? '-';

            $maintenanceLines[] = "{$tgl} | {$issue} | {$statusLabel} | {$vendor}";
        }

        $maintenanceText = !empty($maintenanceLines)
            ? implode("\n", $maintenanceLines)
            : '-';

        return [
            $no,
            $asset->currentUser?->name ?? '-',
            $asset->currentUser?->nip ?? '-',
            $asset->currentUser?->position ?? '-',
            $asset->currentLocation?->full_name ?? '-',
            $assignedAt?->format('d/m/Y') ?? '-',
            $lamaPakai !== null ? $lamaPakai : '-',
            $asset->asset_code,
            $asset->serial_number,
            $asset->hostname ?? '-',
            $asset->brand,
            $asset->model,
            $asset->category?->name ?? '-',
            $asset->ownership_label_with_vendor,
            $asset->status_label,
            $waktuPensiun?->format('d/m/Y') ?? '-',
            $sisaLabel,
            $historyText,
            $assignmentCount,
            $maintenanceText,
            $maintenanceCount,
            $asset->purchase_date?->format('Y') ?? '-',
            $asset->purchase_price ? number_format($asset->purchase_price, 0, ',', '.') : '-',
            $asset->condition_percent ?? '-',
            $asset->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [];
    }

    public function title(): string
    {
        return 'Daftar Aset';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'Y';

                $sheet->insertNewRowBefore(1, 3);
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'DAFTAR ASET IT — SIAM');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1E293B']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->mergeCells("A2:{$lastCol}2");
                $waktu = now()->format('d/m/Y H:i') . ' WIB';
                $subtitle = "Diexport pada: {$waktu}";
                $filterInfo = $this->buildFilterInfo();
                if ($filterInfo) {
                    $subtitle .= "  •  Filter: {$filterInfo}";
                }
                $sheet->setCellValue('A2', $subtitle);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(6);
                $headerRange = "A4:{$lastCol}4";
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(24);
                $sheet->getStyle('F4:G4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '14B8A6']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('P4:Q4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F59E0B']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('R4:S4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B5CF6']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('T4:U4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F97316']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A4:{$lastCol}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);
                $sheet->freezePane('A5');
                $sheet->setAutoFilter("A4:{$lastCol}{$highestRow}");
                $sheet->getStyle("R5:R{$highestRow}")->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_TOP);

                $sheet->getStyle("T5:T{$highestRow}")->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_TOP);
                for ($row = 5; $row <= $highestRow; $row++) {
                    $historyPemakai = $sheet->getCell("R{$row}")->getValue();
                    $historyPerbaikan = $sheet->getCell("T{$row}")->getValue();

                    $linePemakai = ($historyPemakai && $historyPemakai !== '-')
                        ? substr_count($historyPemakai, "\n") + 1
                        : 0;

                    $linePerbaikan = ($historyPerbaikan && $historyPerbaikan !== '-')
                        ? substr_count($historyPerbaikan, "\n") + 1
                        : 0;

                    $maxLines = max($linePemakai, $linePerbaikan, 1);
                    $sheet->getRowDimension($row)->setRowHeight(max(20, $maxLines * 15));
                }
                for ($row = 5; $row <= $highestRow; $row++) {
                    $waktuPensiun = $sheet->getCell("P{$row}")->getValue();
                    $sisaHari = $sheet->getCell("Q{$row}")->getValue();

                    if (
                        $waktuPensiun && $waktuPensiun !== '-' &&
                        is_string($sisaHari) &&
                        (str_contains($sisaHari, 'Pensiun') || str_contains($sisaHari, 'hari lagi'))
                    ) {
                        if (str_contains($sisaHari, 'hari lagi')) {
                            $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FEF3C7'],
                                ],
                            ]);
                        }
                        if (str_contains($sisaHari, 'Pensiun')) {
                            $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FEE2E2'],
                                ],
                            ]);
                        }
                    }
                }
            },
        ];
    }

    protected function buildFilterInfo(): string
    {
        $parts = [];

        if (!empty($this->filters['search']))
            $parts[] = 'Cari: "' . $this->filters['search'] . '"';
        if (!empty($this->filters['status']))
            $parts[] = 'Status: ' . $this->filters['status'];

        if (!empty($this->filters['pemakai_status'])) {
            $label = match ($this->filters['pemakai_status']) {
                'perlu_ditarik' => 'Perlu Ditarik',
                'sudah_pensiun' => 'Sudah Pensiun',
                'akan_pensiun' => 'Akan Pensiun',
                default => $this->filters['pemakai_status'],
            };
            $parts[] = 'Status Pemakai: ' . $label;
        }

        if (!empty($this->filters['brand']))
            $parts[] = 'Brand: ' . $this->filters['brand'];
        if (!empty($this->filters['model']))
            $parts[] = 'Model: ' . $this->filters['model'];
        if (!empty($this->filters['year']))
            $parts[] = 'Tahun: ' . $this->filters['year'];
        if (!empty($this->filters['ownership_type']))
            $parts[] = 'Kepemilikan: ' . $this->filters['ownership_type'];

        return implode(' | ', $parts);
    }
}
