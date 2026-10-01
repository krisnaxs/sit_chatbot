<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UsersImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    use SkipsFailures;

    public int $successCount = 0;
    public int $skipCount = 0;

    /** Cache biar tidak query berulang */
    protected array $departmentCache = [];
    protected array $locationCache = [];
    protected array $existingEmails = [];

    public function __construct()
    {
        Department::select('id', 'code')->get()->each(function ($d) {
            $this->departmentCache[strtolower($d->code)] = $d->id;
        });
        Location::select('id', 'full_name')->get()->each(function ($l) {
            $this->locationCache[strtolower($l->full_name)] = $l->id;
        });
        User::select('email')->get()->each(function ($u) {
            $this->existingEmails[strtolower($u->email)] = true;
        });
    }

    public function model(array $row): ?Model
    {
        $email = strtolower(trim($row['email'] ?? ''));
        if (isset($this->existingEmails[$email])) {
            $this->skipCount++;
            return null;
        }

        $departmentId = null;
        if (!empty($row['department_code'])) {
            $code = strtolower(trim($row['department_code']));
            $departmentId = $this->departmentCache[$code] ?? null;
        }

        $locationId = null;
        if (!empty($row['location_code'])) {
            $locKey = strtolower(trim($row['location_code']));
            $locationId = $this->locationCache[$locKey] ?? null;
        }

        $isActive = true;
        if (isset($row['is_active'])) {
            $val = trim((string) $row['is_active']);
            $isActive = in_array($val, ['1', 'true', 'ya', 'yes', 'aktif'], true);
        }

        $role = strtolower(trim($row['role'] ?? 'user'));
        if (!in_array($role, ['admin', 'support', 'user'])) {
            $role = 'user';
        }
        $waktuPensiun = $this->parseWaktuPensiun($row['waktu_pensiun'] ?? null);

        $this->successCount++;
        $this->existingEmails[$email] = true;

        return new User([
            'nip' => !empty($row['nip']) ? trim($row['nip']) : null,
            'username' => User::generateUsername($email),
            'name' => trim($row['name']),
            'email' => $email,
            'password' => Hash::make($row['password']),
            'phone' => !empty($row['phone']) ? trim($row['phone']) : null,
            'department_id' => $departmentId,
            'position' => !empty($row['position']) ? trim($row['position']) : null,
            'location_id' => $locationId,
            'role' => $role,
            'is_active' => $isActive,
            'waktu_pensiun' => $waktuPensiun, //  tambahan
        ]);
    }

    /**
     * Parse nilai waktu_pensiun dari Excel.
     *
     * Mendukung:
     * - Format tanggal Excel (numeric serial) → auto-convert
     * - String: "2026-12-31", "31/12/2026", "31-12-2026"
     * - String: "31 Desember 2026", "December 31, 2026"
     * - Kosong / null / "-" / "tidak ada" → null
     */
    protected function parseWaktuPensiun($value): ?string
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        $value = trim((string) $value);
        if (in_array(strtolower($value), ['tidak ada', 'n/a', 'na', 'none', '-', 'null'], true)) {
            return null;
        }
        if (is_numeric($value) && (int) $value > 1000 && (int) $value < 100000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value)
                    ->format('Y-m-d');
            } catch (\Exception $e) {
            }
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'in:admin,support,user'],
            'nip' => ['nullable', 'max:30'],
            'phone' => ['nullable', 'max:30'],
            'department_code' => ['nullable', 'max:20'],
            'location_code' => ['nullable', 'max:255'],
            'position' => ['nullable', 'max:100'],
            'is_active' => ['nullable'],
            'waktu_pensiun' => ['nullable'], //  tambahan (bebas format, di-parse di model())
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.in' => 'Role harus salah satu dari: admin, support, user.',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
