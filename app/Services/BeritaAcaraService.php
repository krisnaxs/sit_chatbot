<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\BeritaAcara;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class BeritaAcaraService
{
    /**
     * Map kategori → field tambahan yang perlu ditampilkan di BA.
     * Bisa di-extend sesuai kebutuhan.
     */
    protected array $kategoriFields = [
        'laptop' => ['lisensi_office', 'os', 'hostname', 'cpu', 'ram', 'storage'],
        'notebook' => ['lisensi_office', 'os', 'hostname', 'cpu', 'ram', 'storage'],
        'pc' => ['lisensi_office', 'os', 'hostname', 'cpu', 'ram', 'storage'],
        'komputer' => ['lisensi_office', 'os', 'hostname', 'cpu', 'ram', 'storage'],
        'printer' => ['tipe', 'koneksi', 'toner'],
        'scanner' => ['tipe', 'koneksi'],
        'monitor' => ['ukuran', 'resolusi', 'koneksi'],
        'proyektor' => ['resolusi', 'brightness'],
        'tablet' => ['os', 'storage', 'imei'],
        'hp' => ['os', 'storage', 'imei'],
        'server' => ['os', 'cpu', 'ram', 'storage', 'ip'],
        'switch' => ['jumlah_port', 'ip'],
        'router' => ['jumlah_port', 'ip'],
        'kamera' => ['resolusi', 'koneksi'],
        'cctv' => ['resolusi', 'koneksi', 'ip'],
        'ups' => ['kapasitas'],
    ];

    /**
     * Ambil detail tambahan sesuai kategori aset.
     */
    protected function buildDetailTambahan(Asset $asset): array
    {
        $detail = [];
        $spec = $asset->specification ?? [];
        $kategoriKey = strtolower($asset->category?->name ?? '');

        // Cari field yang cocok untuk kategori ini
        $fields = [];
        foreach ($this->kategoriFields as $key => $fieldList) {
            if (str_contains($kategoriKey, $key)) {
                $fields = $fieldList;
                break;
            }
        }

        // Kalau kategori tidak dikenali, tampilkan semua spec yang ada
        if (empty($fields)) {
            return $spec;
        }

        // Ambil dari spec/manual sesuai field yang relevan
        foreach ($fields as $field) {
            $value = null;

            // Cek di specification JSON
            if (isset($spec[$field]) && $spec[$field] !== '') {
                $value = $spec[$field];
            }
            // Cek di kolom khusus asset
            elseif ($field === 'lisensi_office') {
                $value = $asset->os_license ?? 'Office 365';
            } elseif ($field === 'os' || $field === 'tipe' || $field === 'ukuran' || $field === 'resolusi') {
                $value = $asset->os ?? null;
            }

            if ($value) {
                $detail[$field] = $value;
            }
        }

        return $detail;
    }

    /**
     * Buat BAST (Berita Acara Serah Terima).
     */
    public function createSerahTerima(
        AssetAssignment $assignment,
        User $pihakPertama,
        array $options = []
    ): BeritaAcara {
        return $this->create('serah_terima', $assignment, $pihakPertama, $options);
    }

    /**
     * Buat BAP (Berita Acara Pengembalian).
     */
    public function createPengembalian(
        AssetAssignment $assignment,
        User $pihakPertama,
        array $options = []
    ): BeritaAcara {
        return $this->create('pengembalian', $assignment, $pihakPertama, $options);
    }

    /**
     * 🆕 Cek apakah assignment sudah punya BA dengan jenis tertentu.
     */
    public function hasBeritaAcara(AssetAssignment $assignment, string $jenis): bool
    {
        return BeritaAcara::where('asset_assignment_id', $assignment->id)
            ->where('jenis', $jenis)
            ->exists();
    }

    /**
     * 🆕 Ambil BA existing untuk assignment + jenis.
     */
    public function getBeritaAcara(AssetAssignment $assignment, string $jenis): ?BeritaAcara
    {
        return BeritaAcara::where('asset_assignment_id', $assignment->id)
            ->where('jenis', $jenis)
            ->latest()
            ->first();
    }

    /**
     * 🆕 Generate BA manual dari assignment yang sudah ada.
     * Dipakai di halaman asset show untuk bikin ulang BA yang terhapus.
     */
    public function generateFromAssignment(
        AssetAssignment $assignment,
        User $pihakPertama,
        string $jenis,
        array $options = []
    ): BeritaAcara {
        // Validasi jenis
        if (!in_array($jenis, ['serah_terima', 'pengembalian'])) {
            throw new \InvalidArgumentException("Jenis BA tidak valid: {$jenis}");
        }

        // Validasi: untuk pengembalian, assignment harus sudah returned
        if ($jenis === 'pengembalian' && !$assignment->returned_at) {
            throw new \RuntimeException('Aset belum dikembalikan, tidak bisa buat BAP.');
        }

        // Validasi role pihak pertama
        if (!in_array($pihakPertama->role, ['admin', 'support'])) {
            throw new \RuntimeException('Pihak Pertama harus Admin atau Support.');
        }

        return $this->create($jenis, $assignment, $pihakPertama, $options);
    }

    /**
     * Core: bikin record BA + generate PDF.
     */
    protected function create(
        string $jenis,
        AssetAssignment $assignment,
        User $pihakPertama,
        array $options
    ): BeritaAcara {
        $asset = $assignment->asset;
        $pihakKedua = $assignment->user;

        // Nama kategori otomatis (uppercase) — fallback kalau kosong
        $kategoriNama = strtoupper($asset->category?->name ?? 'PERALATAN IT');

        $ba = BeritaAcara::create([
            'nomor_ba' => BeritaAcara::generateNomor($jenis),
            'jenis' => $jenis,
            'asset_id' => $asset->id,
            'asset_assignment_id' => $assignment->id,

            // PIHAK PERTAMA
            'pihak_pertama_id' => $pihakPertama->id,
            'pihak_pertama_jabatan' => $options['pihak_pertama_jabatan'] ?? $pihakPertama->position,
            'pihak_pertama_nip' => $options['pihak_pertama_nip'] ?? $pihakPertama->nip,

            // PIHAK KEDUA
            'pihak_kedua_id' => $pihakKedua->id,
            'pihak_kedua_jabatan' => $pihakKedua->position,
            'pihak_kedua_nip' => $pihakKedua->nip,

            // Snapshot aset generic
            'kategori_aset' => $kategoriNama,
            'merk' => strtoupper($asset->brand ?? ''),
            'model' => strtoupper($asset->model ?? ''),
            'serial_number' => $asset->serial_number,
            'hostname' => $assignment->hostname ?? $asset->hostname,
            'detail_tambahan' => $this->buildDetailTambahan($asset),
            'jumlah' => 1,

            // Kondisi & tanggal
            'condition_percent' => $jenis === 'serah_terima'
                ? $assignment->condition_on_assign
                : $assignment->condition_on_return,
            'notes' => $options['notes'] ?? null,
            'tanggal_ba' => $jenis === 'serah_terima'
                ? ($assignment->assigned_at ?? now())
                : ($assignment->returned_at ?? now()),
            'tempat_ba' => $options['tempat_ba'] ?? 'Suralaya',
            'created_by' => auth()->id(),
        ]);

        $this->generatePdf($ba);

        return $ba;
    }


    public function generatePdf(BeritaAcara $ba): void
    {
        $ba->load([
            'asset.category',
            'pihakPertama',
            'pihakKedua',
        ]);

        $pdf = Pdf::loadView(
            'berita-acara.pdf.template',
            ['ba' => $ba]
        )
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'Times',
                'dpi' => 96,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
                'isPhpEnabled' => false,
                'show_warnings' => false,
                'isFontSubsettingEnabled' => true,
            ]);

        if (!Storage::disk('public')->exists('berita-acara')) {
            Storage::disk('public')->makeDirectory('berita-acara');
        }

        $filename = "berita-acara/{$ba->nomor_ba}.pdf";

        if (Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);
        }

        Storage::disk('public')->put(
            $filename,
            $pdf->output()
        );

        $ba->update([
            'pdf_path' => $filename,
        ]);
    }
}
