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
        // 🆕 ownership.vendor di-load untuk kolom Kepemilikan
        $query = Asset::with([
            'category',
            'currentUser',
            'currentLocation',
            'ownership.vendor',
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
            'Nama Pemakai',
            'NIP Pemakai',
            'Jabatan',
            'Lokasi',
            'Kode Aset',
            'Serial Number',
            'Hostname',
            'Brand',
            'Model',
            'Kategori',
            'Kepemilikan',
            'Status',
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

        return [
            $no,
            $asset->currentUser?->name ?? '-',
            $asset->currentUser?->nip ?? '-',
            $asset->currentUser?->position ?? '-',
            $asset->currentLocation?->full_name ?? '-',
            $asset->asset_code,
            $asset->serial_number,
            $asset->hostname ?? '-',
            $asset->brand,
            $asset->model,
            $asset->category?->name ?? '-',
            $asset->ownership_label_with_vendor,   // 🆕 pakai accessor baru
            $asset->status_label,
            $asset->purchase_date?->format('Y') ?? '-',
            $asset->purchase_price ? number_format($asset->purchase_price, 0, ',', '.') : '-',
            $asset->condition_percent ?? '-',
            $asset->notes ?? '-',
        ];
    }

    /**
     * styles() dikosongkan — semua styling di registerEvents()
     */
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
                $lastCol = 'Q'; // 17 kolom (A-Q)
    
                // === 1. Insert 3 baris di atas ===
                $sheet->insertNewRowBefore(1, 3);

                // === 2. Judul (baris 1) ===
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

                // === 3. Sub-judul (baris 2) ===
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

                // === 4. Spacer (baris 3) ===
                $sheet->getRowDimension(3)->setRowHeight(6);

                // === 5. HEADER (baris 4) ===
                $headerRange = "A4:{$lastCol}4";
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F46E5'], // indigo
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(24);

                // === 6. Border seluruh tabel ===
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A4:{$lastCol}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                // === 7. Freeze pane ===
                $sheet->freezePane('A5');

                // === 8. Auto filter ===
                $sheet->setAutoFilter("A4:{$lastCol}{$highestRow}");
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
