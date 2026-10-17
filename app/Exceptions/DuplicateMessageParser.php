<?php

namespace App\Exceptions;

class DuplicateMessageParser
{

    protected array $labels = [
        'assets.assets_hostname_unique' => 'Hostname',
        'assets.assets_serial_number_unique' => 'Serial Number',
        'assets.assets_kode_asset_unique' => 'Kode Asset',
        'assets.assets_kode_unique' => 'Kode Asset',
        'users.users_email_unique' => 'Email',
        'users.users_nip_unique' => 'NIP',
        'users.users_username_unique' => 'Username',
        'departments.departments_name_unique' => 'Nama Departemen',
        'departments.departments_code_unique' => 'Kode Departemen',
        'locations.locations_name_unique' => 'Nama Lokasi',
        'locations.locations_code_unique' => 'Kode Lokasi',
        'categories.categories_name_unique' => 'Nama Kategori',
        'categories.categories_code_unique' => 'Kode Kategori',
        'consumables.consumables_code_unique' => 'Kode Consumable',
        'consumables.consumables_name_unique' => 'Nama Consumable',
        'berita_acaras.berita_acaras_nomor_ba_unique' => 'Nomor Berita Acara',
        'asset_loans.asset_loans_code_unique' => 'Kode Peminjaman',
        'asset_assignments.asset_assignments_unique' => 'Assignment',
    ];


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


    protected function guessLabel(string $key): string
    {
        $parts = explode('.', $key);
        $indexName = end($parts);
        $indexName = preg_replace('/_unique$/', '', $indexName);
        $chunks = explode('_', $indexName);
        array_shift($chunks);

        $label = implode(' ', $chunks);

        return $label !== '' ? ucwords($label) : 'Data';
    }
}
