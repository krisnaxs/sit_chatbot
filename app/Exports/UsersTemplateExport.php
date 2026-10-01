<?php

namespace App\Exports;

use App\Models\Department;
use App\Models\Location;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Parent export — WAJIB implements FromArray (atau source lain)
 * walau sebenarnya datanya ada di sheet-sheet di dalamnya.
 */
class UsersTemplateExport implements WithMultipleSheets, FromArray
{
    public function sheets(): array
    {
        return [
            new UsersTemplateGuideSheet(),
            new UsersTemplateDataSheet(),
        ];
    }

    /**
     * Method ini WAJIB ada (dari FromArray), tapi tidak akan dipakai
     * karena semua data ada di sheet-sheet.
     */
    public function array(): array
    {
        return [];
    }
}

/**
 * Sheet 1: PETUNJUK
 */
class UsersTemplateGuideSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Petunjuk';
    }

    public function array(): array
    {
        $departments = Department::active()->orderBy('name')->pluck('code', 'name');
        $locations = Location::active()->orderBy('full_name')->pluck('full_name');

        $deptList = $departments->map(fn($code, $name) => "{$name} ({$code})")->values()->all();
        $locList = $locations->all();

        $rows = [
            ['📋 PETUNJUK IMPORT USER — SIAM'],
            [''],
            ['Kolom', 'Wajib?', 'Contoh', 'Keterangan'],
            ['name', ' WAJIB', 'Budi Santoso', 'Nama lengkap user'],
            ['email', ' WAJIB', 'budi@perusahaan.com', 'Unik, format email valid'],
            ['password', ' WAJIB', 'Password123!', 'Minimal 8 karakter'],
            ['role', ' WAJIB', 'user', 'Pilih: admin / support / user'],
            ['nip', '⬜ Opsional', '20000001', 'NIP karyawan (unik jika diisi)'],
            ['phone', '⬜ Opsional', '08123456789', 'Nomor HP'],
            ['department_code', '⬜ Opsional', 'IT', 'Kode departemen (lihat daftar di bawah)'],
            ['location_code', '⬜ Opsional', 'Gedung A - Lt. 1 - Ruang IT', 'Full name lokasi (lihat daftar di bawah)'],
            ['position', '⬜ Opsional', 'Programmer', 'Jabatan'],
            ['is_active', '⬜ Opsional', '1', '1=aktif, 0=nonaktif. Default: 1'],
            ['waktu_pensiun', '⬜ Opsional', '2027-12-31', 'Tanggal pensiun (format: YYYY-MM-DD). Kosongkan jika belum ada jadwal.'],
            [''],
            [' CATATAN PENTING:'],
            ['• Email HARUS unik. Kalau sudah ada di sistem, baris akan dilewati.'],
            ['• Password akan di-hash otomatis (aman).'],
            ['• Username akan di-generate otomatis dari email.'],
            ['• department_code & location_code HARUS PERSIS (case-insensitive).'],
            ['• Kalau kode tidak ditemukan, user tetap dibuat tapi department/location kosong.'],
            ['• waktu_pensiun mendukung format: 2027-12-31, 31/12/2027, 31-12-2027, 31 Desember 2027.'],
            ['• Kalau waktu_pensiun diisi, sistem akan menampilkan pengingat aset 30 hari sebelum tanggal tersebut.'],
            [''],
            ['📌 DAFTAR KODE DEPARTEMEN:'],
        ];

        foreach ($deptList as $d) {
            $rows[] = ['', $d];
        }

        $rows[] = [''];
        $rows[] = ['📌 DAFTAR FULL NAME LOKASI:'];

        foreach ($locList as $l) {
            $rows[] = ['', $l];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 12,
            'C' => 35,
            'D' => 75, // diperlebar untuk keterangan waktu_pensiun
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getStyle('A3:D3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']],
        ]);
        $sheet->getStyle('A14:D14')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '7C2D12']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
        ]);

        foreach (['A5', 'A6', 'A7', 'A8'] as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        return [];
    }
}

/**
 * Sheet 2: DATA USER
 */
class UsersTemplateDataSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Data User';
    }

    public function headings(): array
    {
        return [
            'name',
            'email',
            'password',
            'role',
            'nip',
            'phone',
            'department_code',
            'location_code',
            'position',
            'is_active',
            'waktu_pensiun', //  tambahan
        ];
    }

    public function array(): array
    {
        return [
            [
                'Budi Santoso',
                'budi.santoso@perusahaan.com',
                'Password123!',
                'user',
                '20000001',
                '081234567890',
                'IT',
                'Gedung A - Lt. 1 - Ruang IT',
                'IT Support',
                '1',
                '', //  kosong = tidak ada jadwal pensiun
            ],
            [
                'Siti Aminah',
                'siti.aminah@perusahaan.com',
                'Password123!',
                'user',
                '20000002',
                '081234567891',
                'FIN',
                'Gedung A - Lt. 2 - Ruang Finance',
                'Staff Finance',
                '1',
                '2027-12-31', //  contoh dengan tanggal pensiun
            ],
            [
                'Andi Wijaya',
                'andi.wijaya@perusahaan.com',
                'Password123!',
                'admin',
                '20000003',
                '081234567892',
                'HRD',
                'Gedung A - Lt. 2 - Ruang HRD',
                'HRD Admin',
                '1',
                '', //  kosong
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 35,
            'C' => 20,
            'D' => 10,
            'E' => 15,
            'F' => 15,
            'G' => 18,
            'H' => 35,
            'I' => 20,
            'J' => 12,
            'K' => 18, //  tambahan untuk waktu_pensiun
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F59E0B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->freezePane('A2');

        return [];
    }
}
