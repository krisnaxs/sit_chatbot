<?php

namespace App\Exports;

use App\Models\Consumable;
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

class ConsumablesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        // 🆕 Eager load total_out & total_return dari transaksi
        $query = Consumable::with('category')
            ->withSum([
                'transactions as total_out' => function ($q) {
                    $q->where('type', 'out');
                }
            ], 'quantity')
            ->withSum([
                'transactions as total_return' => function ($q) {
                    $q->where('type', 'return');
                }
            ], 'quantity');

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('brand', 'like', "%{$s}%")
                    ->orWhere('model', 'like', "%{$s}%");
            });
        }

        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (!empty($this->filters['low_stock'])) {
            $query->lowStock();
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Item',
            'Kategori',
            'Brand',
            'Model',
            'Satuan',
            'Stok Total',
            'Stok Tersedia',
            'Jumlah Keluar',    // 🆕
            'Stok Minimum',
            'Harga Terakhir (Rp)',
            'Status',
            'Catatan',
        ];
    }

    public function map($c): array
    {
        static $no = 0;
        $no++;

        $status = match (true) {
            $c->stock_available == 0 => 'HABIS',
            $c->is_low_stock => 'LOW STOCK',
            default => 'TERSEDIA',
        };

        // 🆕 Net keluar = total out - total return
        $jumlahKeluar = ($c->total_out ?? 0) - ($c->total_return ?? 0);

        return [
            $no,
            $c->name,
            $c->category?->name ?? '-',
            $c->brand ?? '-',
            $c->model ?? '-',
            $c->unit ?? '-',
            $c->stock_total,
            $c->stock_available,
            $jumlahKeluar,                                       // 🆕
            $c->stock_minimum,
            $c->last_price ? number_format($c->last_price, 0, ',', '.') : '-',
            $status,
            $c->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [];
    }

    public function title(): string
    {
        return 'Stok Konsumable';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'M'; // 🆕 13 kolom (A-M)
    
                // Insert 3 baris untuk judul
                $sheet->insertNewRowBefore(1, 3);

                // === Judul (baris 1) ===
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'STOK KONSUMABLE — SIAM');
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
                        'startColor' => ['rgb' => '65A30D'], // lime-600
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(24);

                // === Border ===
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A4:{$lastCol}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                // 🆕 Highlight kolom "Jumlah Keluar" (kolom I) — amber tipis
                $sheet->getStyle("I5:I{$highestRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '92400E']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'], // amber-100
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
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

        if (!empty($this->filters['search']))
            $parts[] = 'Cari: "' . $this->filters['search'] . '"';
        if (!empty($this->filters['low_stock']))
            $parts[] = 'Hanya Low Stock';

        return implode(' | ', $parts);
    }
}
