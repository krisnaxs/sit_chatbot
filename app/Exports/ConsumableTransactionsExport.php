<?php

namespace App\Exports;

use App\Models\ConsumableTransaction;
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

class ConsumableTransactionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = ConsumableTransaction::with([
            'consumable',
            'user',
            'location',
            'asset',
            'requestedBy',
            'approvedBy',
        ]);

        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }
        if (!empty($this->filters['consumable_id'])) {
            $query->where('consumable_id', $this->filters['consumable_id']);
        }
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->where('transaction_date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->where('transaction_date', '<=', $this->filters['date_to'] . ' 23:59:59');
        }

        return $query->orderByDesc('transaction_date');
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Item',
            'Tipe',
            'Qty',
            'Satuan',
            'User',
            'Lokasi',
            'Aset',
            'Keperluan',
            'Catatan',
            'Diminta Oleh',
            'Disetujui Oleh',
        ];
    }

    public function map($t): array
    {
        static $no = 0;
        $no++;

        $typeLabel = match ($t->type) {
            'in' => 'MASUK',
            'out' => 'KELUAR',
            'return' => 'KEMBALI',
            default => '-',
        };

        return [
            $no,
            $t->transaction_date?->format('d/m/Y H:i') ?? '-',
            $t->consumable?->name ?? '-',
            $typeLabel,
            $t->quantity,
            $t->consumable?->unit ?? '-',
            $t->user?->name ?? '-',
            $t->location?->full_name ?? '-',
            $t->asset?->serial_number ?? '-',
            $t->purpose ?? '-',
            $t->notes ?? '-',
            $t->requestedBy?->name ?? '-',
            $t->approvedBy?->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [];
    }

    public function title(): string
    {
        return 'Transaksi Konsumable';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'M'; // 13 kolom (A-M)
    
                // Insert 3 baris untuk judul
                $sheet->insertNewRowBefore(1, 3);

                // === Judul (baris 1) ===
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'TRANSAKSI KONSUMABLE — SIAM');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1E293B']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // === Sub-judul (baris 2) ===
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

                // === Spacer (baris 3) ===
                $sheet->getRowDimension(3)->setRowHeight(6);

                // === Header (baris 4) ===
                $headerRange = "A4:{$lastCol}4";
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DB2777'], // pink-600
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(24);

                // === Border seluruh tabel ===
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A4:{$lastCol}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                // === Freeze pane ===
                $sheet->freezePane('A5');

                // === Auto filter ===
                $sheet->setAutoFilter("A4:{$lastCol}{$highestRow}");
            },
        ];
    }

    protected function buildFilterInfo(): string
    {
        $parts = [];

        if (!empty($this->filters['type']))
            $parts[] = 'Tipe: ' . $this->filters['type'];
        if (!empty($this->filters['consumable_id']))
            $parts[] = 'Item ID: ' . $this->filters['consumable_id'];
        if (!empty($this->filters['user_id']))
            $parts[] = 'User ID: ' . $this->filters['user_id'];
        if (!empty($this->filters['date_from']))
            $parts[] = 'Dari: ' . $this->filters['date_from'];
        if (!empty($this->filters['date_to']))
            $parts[] = 'Sampai: ' . $this->filters['date_to'];

        return implode(' | ', $parts);
    }
}
