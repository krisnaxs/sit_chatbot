<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * Halaman utama backup — list backup yang ada + tombol create.
     */
    public function index()
    {
        $backups = $this->listBackups();

        $diskInfo = [
            'total' => disk_total_space(base_path()),
            'free' => disk_free_space(base_path()),
        ];
        $diskInfo['used'] = $diskInfo['total'] - $diskInfo['free'];
        $diskInfo['percent'] = $diskInfo['total'] > 0
            ? round(($diskInfo['used'] / $diskInfo['total']) * 100, 1)
            : 0;

        return view('backup.index', compact('backups', 'diskInfo'));
    }

    /**
     * List semua file backup di storage/app/backups.
     */
    private function listBackups(): array
    {
        $dir = storage_path('app/backups');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $files = File::files($dir);
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'size_human' => $this->humanSize($file->getSize()),
                'modified' => Carbon::createFromTimestamp($file->getMTime())->format('d M Y H:i'),
                'timestamp' => $file->getMTime(),
                'type' => str_contains($file->getFilename(), '_full_') ? 'full' : 'db',
                'download_url' => route('siam.backup.download', $file->getFilename()),
            ];
        }

        // sort terbaru dulu
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Buat backup baru (DB saja atau full termasuk file uploads).
     */
    public function create(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $type = $request->input('type', 'db'); // 'db' | 'full'

        try {
            $timestamp = now()->format('Ymd-His');
            $backupsDir = storage_path('app/backups');
            if (!File::exists($backupsDir)) {
                File::makeDirectory($backupsDir, 0755, true);
            }

            if ($type === 'full') {
                $filename = "backup_full_{$timestamp}.zip";
                $zipPath = "{$backupsDir}/{$filename}";

                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    return back()->with('error', 'Gagal membuat file ZIP.');
                }

                // 1) Dump database → temp .sql
                $sqlContent = $this->generateDatabaseDump();
                $zip->addFromString("database.sql", $sqlContent);

                // 2) Tambahkan folder storage/app/public (uploads)
                $publicStorage = storage_path('app/public');
                if (File::exists($publicStorage)) {
                    $this->addFolderToZip($zip, $publicStorage, 'storage/app/public');
                }

                // 3) Tambahkan .env (opsional — pindahkan ke nonaktif kalau tidak mau)
                $envPath = base_path('.env');
                if (File::exists($envPath)) {
                    $zip->addFile($envPath, '.env');
                }

                // 4) Metadata
                $meta = [
                    'app_name' => config('app.name'),
                    'app_env' => config('app.env'),
                    'app_url' => config('app.url'),
                    'created_at' => now()->toDateTimeString(),
                    'laravel' => app()->version(),
                    'php' => PHP_VERSION,
                    'database' => config('database.default'),
                    'type' => 'full',
                ];
                $zip->addFromString("metadata.json", json_encode($meta, JSON_PRETTY_PRINT));

                $zip->close();
            } else {
                // DB only
                $filename = "backup_db_{$timestamp}.sql";
                $path = "{$backupsDir}/{$filename}";
                File::put($path, $this->generateDatabaseDump());
            }

            return redirect()
                ->route('siam.backup.index')
                ->with('success', "Backup berhasil dibuat: {$filename}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Backup gagal: ' . $e->getMessage());
        }
    }

    /**
     * Download file backup.
     */
    public function download(string $filename)
    {
        $filename = basename($filename); // security: cegah path traversal
        $path = storage_path("app/backups/{$filename}");

        if (!File::exists($path)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        return response()->download($path);
    }

    /**
     * Hapus file backup.
     */
    public function destroy(string $filename)
    {
        $filename = basename($filename);
        $path = storage_path("app/backups/{$filename}");

        if (File::exists($path)) {
            File::delete($path);
            return back()->with('success', "Backup {$filename} dihapus.");
        }

        return back()->with('error', 'File tidak ditemukan.');
    }

    /**
     * Upload & restore database dari file .sql.
     * (Restore full ZIP belum didukung — manual via SSH.)
     */
    public function restore(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt|max:51200', // max 50MB
        ]);

        set_time_limit(0);

        try {
            $sql = file_get_contents($request->file('sql_file')->getRealPath());

            // Nonaktifkan foreign key check sementara
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Split by semicolon at end of line
            $statements = array_filter(
                array_map('trim', preg_split('/;\s*[\r\n]+/', $sql)),
                fn($s) => $s !== '' && !str_starts_with($s, '--')
            );

            DB::beginTransaction();
            foreach ($statements as $statement) {
                if (trim($statement) === '')
                    continue;
                DB::statement($statement);
            }
            DB::commit();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return back()->with('success', 'Database berhasil di-restore.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Restore gagal: ' . $e->getMessage());
        }
    }

    /**
     * Generate SQL dump dari database aktif.
     * Portable: pakai PHP murni (tanpa mysqldump binary).
     */
    private function generateDatabaseDump(): string
    {
        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();

        $output = "-- ============================================\n";
        $output .= "-- Database Backup: {$dbName}\n";
        $output .= "-- Generated: " . now()->toDateTimeString() . "\n";
        $output .= "-- Laravel: " . app()->version() . " | PHP: " . PHP_VERSION . "\n";
        $output .= "-- ============================================\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $output .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

        // Ambil semua tabel
        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . $dbName;

        foreach ($tables as $t) {
            $table = $t->$key;

            // ===== Struktur tabel =====
            $create = DB::select("SHOW CREATE TABLE `{$table}`");
            $output .= "--\n-- Struktur tabel `{$table}`\n--\n";
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $output .= $create[0]->{'Create Table'} . ";\n\n";

            // ===== Data =====
            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $output .= "--\n-- Data untuk tabel `{$table}` ({$rows->count()} baris)\n--\n";

                $columns = array_keys((array) $rows->first());
                $colList = '`' . implode('`,`', $columns) . '`';

                // chunk untuk hindari terlalu besar
                foreach ($rows->chunk(100) as $chunk) {
                    $values = [];
                    foreach ($chunk as $row) {
                        $rowVals = [];
                        foreach ((array) $row as $val) {
                            if ($val === null) {
                                $rowVals[] = 'NULL';
                            } elseif (is_numeric($val)) {
                                $rowVals[] = $val;
                            } else {
                                $rowVals[] = $pdo->quote((string) $val);
                            }
                        }
                        $values[] = '(' . implode(',', $rowVals) . ')';
                    }
                    $output .= "INSERT INTO `{$table}` ({$colList}) VALUES\n";
                    $output .= implode(",\n", $values) . ";\n";
                }
                $output .= "\n";
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $output .= "-- === SELESAI ===\n";

        return $output;
    }

    /**
     * Tambahkan isi folder ke ZIP secara rekursif.
     */
    private function addFolderToZip(ZipArchive $zip, string $folder, string $zipPrefix = ''): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isFile())
                continue;

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($folder) + 1);
            $zipPath = $zipPrefix ? "{$zipPrefix}/{$relativePath}" : $relativePath;

            $zip->addFile($filePath, $zipPath);
        }
    }

    /**
     * Human-readable file size.
     */
    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
