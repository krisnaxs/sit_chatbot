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


    protected function buildDetailTambahan(Asset $asset): array
    {
        $detail = [];
        $spec = $asset->specification ?? [];
        $kategoriKey = strtolower($asset->category?->name ?? '');
        $fields = [];
        foreach ($this->kategoriFields as $key => $fieldList) {
            if (str_contains($kategoriKey, $key)) {
                $fields = $fieldList;
                break;
            }
        }
        if (empty($fields)) {
            return $spec;
        }
        foreach ($fields as $field) {
            $value = null;
            if (isset($spec[$field]) && $spec[$field] !== '') {
                $value = $spec[$field];
            } elseif ($field === 'lisensi_office') {
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


    public function createSerahTerima(
        AssetAssignment $assignment,
        User $pihakPertama,
        array $options = []
    ): BeritaAcara {
        return $this->create('serah_terima', $assignment, $pihakPertama, $options);
    }


    public function createPengembalian(
        AssetAssignment $assignment,
        User $pihakPertama,
        array $options = []
    ): BeritaAcara {
        return $this->create('pengembalian', $assignment, $pihakPertama, $options);
    }


    public function hasBeritaAcara(AssetAssignment $assignment, string $jenis): bool
    {
        return BeritaAcara::where('asset_assignment_id', $assignment->id)
            ->where('jenis', $jenis)
            ->exists();
    }


    public function getBeritaAcara(AssetAssignment $assignment, string $jenis): ?BeritaAcara
    {
        return BeritaAcara::where('asset_assignment_id', $assignment->id)
            ->where('jenis', $jenis)
            ->latest()
            ->first();
    }


    public function generateFromAssignment(
        AssetAssignment $assignment,
        User $pihakPertama,
        string $jenis,
        array $options = []
    ): BeritaAcara {
        if (!in_array($jenis, ['serah_terima', 'pengembalian'])) {
            throw new \InvalidArgumentException("Jenis BA tidak valid: {$jenis}");
        }
        if ($jenis === 'pengembalian' && !$assignment->returned_at) {
            throw new \RuntimeException('Aset belum dikembalikan, tidak bisa buat BAP.');
        }
        if (!in_array($pihakPertama->role, ['admin', 'support'])) {
            throw new \RuntimeException('Pihak Pertama harus Admin atau Support.');
        }

        return $this->create($jenis, $assignment, $pihakPertama, $options);
    }


    protected function create(
        string $jenis,
        AssetAssignment $assignment,
        User $pihakPertama,
        array $options
    ): BeritaAcara {
        $asset = $assignment->asset;
        $pihakKedua = $assignment->user;
        $kategoriNama = strtoupper($asset->category?->name ?? 'PERALATAN IT');

        $ba = BeritaAcara::create([
            'nomor_ba' => BeritaAcara::generateNomor($jenis),
            'jenis' => $jenis,
            'asset_id' => $asset->id,
            'asset_assignment_id' => $assignment->id,
            'pihak_pertama_id' => $pihakPertama->id,
            'pihak_pertama_jabatan' => $options['pihak_pertama_jabatan'] ?? $pihakPertama->position,
            'pihak_pertama_nip' => $options['pihak_pertama_nip'] ?? $pihakPertama->nip,
            'pihak_kedua_id' => $pihakKedua->id,
            'pihak_kedua_jabatan' => $pihakKedua->position,
            'pihak_kedua_nip' => $pihakKedua->nip,
            'kategori_aset' => $kategoriNama,
            'merk' => strtoupper($asset->brand ?? ''),
            'model' => strtoupper($asset->model ?? ''),
            'serial_number' => $asset->serial_number,
            'hostname' => $assignment->hostname ?? $asset->hostname,
            'detail_tambahan' => $this->buildDetailTambahan($asset),
            'jumlah' => 1,
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
