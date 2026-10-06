<?php

namespace App\Exceptions;

class DuplicateMessageParser
{
    /**
     * Mapping index unique MySQL → label ramah user.
     * Format key: {table}.{index_name}
     *
     * Tambahkan sesuai tabel di project kamu.
     */
    protected array $labels = [
        // Assets
        'assets.assets_hostname_unique' => 'Hostname',
        'assets.assets_serial_number_unique' => 'Serial Number',
        'assets.assets_kode_asset_unique' => 'Kode Asset',
        'assets.assets_kode_unique' => 'Kode Asset',

        // Users
        'users.users_email_unique' => 'Email',
        'users.users_nip_unique' => 'NIP',
        'users.users_username_unique' => 'Username',

        // Departments
        'departments.departments_name_unique' => 'Nama Departemen',
        'departments.departments_code_unique' => 'Kode Departemen',

        // Locations
        'locations.locations_name_unique' => 'Nama Lokasi',
        'locations.locations_code_unique' => 'Kode Lokasi',

        // Categories
        'categories.categories_name_unique' => 'Nama Kategori',
        'categories.categories_code_unique' => 'Kode Kategori',

        // Consumables
        'consumables.consumables_code_unique' => 'Kode Consumable',
        'consumables.consumables_name_unique' => 'Nama Consumable',

        // Berita Acara
        'berita_acaras.berita_acaras_nomor_ba_unique' => 'Nomor Berita Acara',

        // Asset Loans
        'asset_loans.asset_loans_code_unique' => 'Kode Peminjaman',

        // Asset Assignments (kalau ada unique constraint)
        'asset_assignments.asset_assignments_unique' => 'Assignment',
    ];

    /**
     * Parse pesan MySQL duplicate jadi bahasa manusia.
     *
     * Input : "SQLSTATE[23000]: Integrity constraint violation: 1062
     *          Duplicate entry 'SLA-RSO-DEWANT' for key 'assets.assets_hostname_unique'"
     *
     * Output: "Hostname "SLA-RSO-DEWANT" sudah dipakai. Silakan gunakan nilai lain."
     */
    public function parse(string $rawMessage): string
    {
        if (preg_match("/Duplicate entry '(.+?)' for key '(.+?)'/", $rawMessage, $m)) {
            $value = $m[1];
            $key = $m[2];

            $label = $this->labels[$key] ?? $this->guessLabel($key);

            return "{$label} \"{$value}\" sudah dipakai. Silakan gunakan nilai lain.";
        }

        return 'Data duplikat terdeteksi. Periksa kembali input Anda.';
    }

    /**
     * Kalau key tidak ada di mapping, coba tebak dari nama index.
     * Contoh: "assets.assets_foobar_unique" → "Foobar"
     */
    protected function guessLabel(string $key): string
    {
        $parts = explode('.', $key);
        $indexName = end($parts);

        // Hapus suffix "_unique"
        $indexName = preg_replace('/_unique$/', '', $indexName);

        // Buang nama tabel (kata pertama)
        $chunks = explode('_', $indexName);
        array_shift($chunks);

        $label = implode(' ', $chunks);

        return $label !== '' ? ucwords($label) : 'Data';
    }
}
