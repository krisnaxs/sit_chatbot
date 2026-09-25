<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // 🔥 Trim input
        $login = trim($credentials['email']);

        // 🔥 Deteksi field login
        $loginField = match (true) {
            filter_var($login, FILTER_VALIDATE_EMAIL) => 'email',
            is_numeric($login) => 'nip',
            default => 'username',
        };

        // 🔥 Coba login dengan field yang terdeteksi
        $attempted = Auth::attempt([
            $loginField => $login,
            'password' => $credentials['password'],
        ], $request->boolean('remember'));

        // 🔥 Fallback: kalau gagal, coba cari di semua field (email, username, nip)
        if (!$attempted) {
            $user = \App\Models\User::where('email', $login)
                ->orWhere('username', $login)
                ->orWhere('nip', $login)
                ->first();

            if ($user && \Hash::check($credentials['password'], $user->password)) {
                Auth::login($user, $request->boolean('remember'));
                $attempted = true;
            }
        }

        if ($attempted) {
            $request->session()->regenerate();

            // Cek user aktif
            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                activity('auth')
                    ->withProperties([
                        'login' => $login,
                        'ip' => $request->ip(),
                    ])
                    ->log('Login gagal - akun tidak aktif');

                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Hubungi admin.',
                ], 403);
            }

            // LOG: Login berhasil
            activity('auth')
                ->causedBy(Auth::user())
                ->withProperties([
                    'login_via' => $loginField,
                    'login' => $login,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log('Login berhasil');

            return response()->json([
                'success' => true,
                'redirect' => route('apps.index'),
            ]);
        }

        // LOG: Login gagal
        activity('auth')
            ->withProperties([
                'login' => $login,
                'login_via' => $loginField,
                'ip' => $request->ip(),
            ])
            ->log('Login gagal');

        return response()->json([
            'success' => false,
            'message' => 'Email/username/NIP atau password salah',
        ], 401);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            activity('auth')
                ->causedBy(Auth::user())
                ->withProperties(['ip' => $request->ip()])
                ->log('Logout');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
