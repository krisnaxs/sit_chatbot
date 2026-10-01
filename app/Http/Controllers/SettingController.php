<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\Request;

class SettingController extends Controller
{

    public function index()
    {
        $status = [
            'whatsapp' => [
                'enabled' => SettingsService::get('whatsapp', 'enabled', false),
                'provider' => SettingsService::get('whatsapp', 'provider', 'log'),
            ],
            'email' => [
                'enabled' => SettingsService::get('mail', 'enabled', false),
                'transport' => SettingsService::get('mail', 'transport', 'log'),
            ],
        ];

        return view('settings.index', compact('status'));
    }
    public function whatsapp()
    {
        $settings = [
            'enabled' => SettingsService::get('whatsapp', 'enabled', false),
            'provider' => SettingsService::get('whatsapp', 'provider', 'log'),
            'token' => SettingsService::get('whatsapp', 'token', ''),
            'phone_number_id' => SettingsService::get('whatsapp', 'phone_number_id', ''),
            'admin_numbers' => SettingsService::get('whatsapp', 'admin_numbers', ''),
            'whitelist' => SettingsService::get('whatsapp', 'whitelist', ''),
        ];
        $providers = [
            'log' => [
                'label' => 'Log Only (Testing)',
                'desc' => 'Tidak kirim WA asli. Hanya ditulis ke laravel.log. Cocok untuk testing.',
                'color' => 'secondary',
                'badge' => 'Gratis',
            ],
            'fonnte' => [
                'label' => 'Fonnte',
                'desc' => 'Layanan tidak resmi. Ada paket gratis 1000 pesan/bulan.',
                'color' => 'success',
                'badge' => 'Gratis 1000/bln',
            ],
            'wablas' => [
                'label' => 'Wablas',
                'desc' => 'Layanan tidak resmi. Trial 7 hari, lalu berbayar bulanan.',
                'color' => 'warning',
                'badge' => 'Trial 7 hari',
            ],
            'meta' => [
                'label' => 'Meta Cloud API (Resmi)',
                'desc' => 'API resmi WhatsApp. Stabil & tidak berisiko diblokir. Bayar per pesan.',
                'color' => 'primary',
                'badge' => 'Berbayar',
            ],
        ];

        return view('settings.whatsapp', compact('settings', 'providers'));
    }

    public function updateWhatsapp(Request $request)
    {
        $data = $request->validate([
            'provider' => 'required|in:log,fonnte,wablas,meta',
            'token' => 'nullable|string|max:1000',
            'phone_number_id' => 'nullable|string|max:100',
            'admin_numbers' => 'nullable|string|max:1000',
            'whitelist' => 'nullable|string|max:1000',
            'enabled' => 'nullable|boolean',
        ]);
        $data['token'] = trim($data['token'] ?? '');
        $data['phone_number_id'] = trim($data['phone_number_id'] ?? '');
        $data['admin_numbers'] = $this->cleanNumbers($data['admin_numbers'] ?? '');
        $data['whitelist'] = $this->cleanNumbers($data['whitelist'] ?? '');
        $data['enabled'] = $request->boolean('enabled') ? '1' : '0';

        SettingsService::putMany('whatsapp', $data);

        return back()->with('success', '✅ Setting WhatsApp berhasil disimpan.');
    }

    public function testWhatsapp(WhatsAppService $wa)
    {
        $result = $wa->testConnection();

        return back()->with(
            $result['success'] ? 'success' : 'error',
            ($result['success'] ? '✅ ' : '❌ ') . $result['message']
        );
    }

    public function testSend(Request $request, WhatsAppService $wa)
    {
        $request->validate([
            'test_number' => 'required|string|max:20',
            'test_message' => 'required|string|max:500',
        ]);

        $ok = $wa->send($request->test_number, $request->test_message);

        return back()->with(
            $ok ? 'success' : 'error',
            $ok
            ? '✅ Pesan test berhasil dikirim ke ' . $request->test_number
            : '❌ Gagal kirim. Cek log untuk detail.'
        );
    }

    /**
     * Bersihkan nomor: buang spasi, titik, tanda hubung.
     * Format output: 628123456789,628987654321
     */
    protected function cleanNumbers(string $input): string
    {
        $numbers = preg_split('/[\s,;]+/', $input);

        $cleaned = collect($numbers)
            ->map(function ($n) {
                $n = preg_replace('/[^0-9]/', '', $n);
                if (str_starts_with($n, '0')) {
                    $n = '62' . substr($n, 1);
                } elseif (str_starts_with($n, '8')) {
                    $n = '62' . $n;
                }
                return $n;
            })
            ->filter(fn($n) => strlen($n) >= 10 && strlen($n) <= 15)
            ->unique()
            ->values()
            ->toArray();

        return implode(',', $cleaned);
    }

    public function email()
    {
        $settings = [
            'enabled' => SettingsService::get('mail', 'enabled', false),
            'transport' => SettingsService::get('mail', 'transport', 'smtp'),
            'host' => SettingsService::get('mail', 'host', 'smtp.gmail.com'),
            'port' => SettingsService::get('mail', 'port', 587),
            'username' => SettingsService::get('mail', 'username', ''),
            'password' => SettingsService::get('mail', 'password', ''),
            'encryption' => SettingsService::get('mail', 'encryption', 'tls'),
            'from_address' => SettingsService::get('mail', 'from_address', config('mail.from.address')),
            'from_name' => SettingsService::get('mail', 'from_name', config('mail.from.name')),
        ];

        $current = [
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'from' => config('mail.from.address'),
        ];

        return view('settings.email', compact('settings', 'current'));
    }

    public function updateEmail(Request $request)
    {
        $data = $request->validate([
            'transport' => 'required|in:smtp,log,array',
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:500',
            'encryption' => 'nullable|in:tls,ssl,none',
            'from_address' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'enabled' => 'nullable|boolean',
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['enabled'] = $request->boolean('enabled') ? '1' : '0';

        SettingsService::putMany('mail', $data);

        return back()->with('success', '✅ Setting Email berhasil disimpan.');
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            $this->applyMailConfigFromSettings();

            \Illuminate\Support\Facades\Mail::raw(
                "Test email dari SIMASET ✅\n\n" .
                "Waktu: " . now()->format('d M Y H:i:s') . "\n" .
                "Mailer: " . config('mail.default') . "\n" .
                "Host: " . config('mail.mailers.smtp.host') . ":" . config('mail.mailers.smtp.port'),
                function ($message) use ($request) {
                    $message->to($request->test_email)
                        ->subject('[SIMASET] Test Email - ' . now()->format('d M Y H:i'));
                }
            );

            return back()->with('success', "✅ Email test berhasil dikirim ke {$request->test_email}");
        } catch (\Throwable $e) {
            return back()->with('error', '❌ Gagal kirim: ' . $e->getMessage());
        }
    }

    /**
     * Terapkan config mail dari DB untuk request ini (dipakai sebelum test).
     */
    protected function applyMailConfigFromSettings(): void
    {
        $transport = SettingsService::get('mail', 'transport', 'smtp');

        if ($transport === 'log') {
            config(['mail.default' => 'log']);
            return;
        }

        if ($transport === 'array') {
            config(['mail.default' => 'array']);
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'host' => SettingsService::get('mail', 'host', 'smtp.gmail.com'),
                'port' => (int) SettingsService::get('mail', 'port', 587),
                'username' => SettingsService::get('mail', 'username', ''),
                'password' => SettingsService::get('mail', 'password', ''),
                'encryption' => SettingsService::get('mail', 'encryption', 'tls'),
                'timeout' => null,
                'auth_mode' => null,
            ],
            'mail.from' => [
                'address' => SettingsService::get('mail', 'from_address', config('mail.from.address')),
                'name' => SettingsService::get('mail', 'from_name', config('mail.from.name')),
            ],
        ]);
    }
}
