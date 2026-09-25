<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validasi: field 'email' bisa diisi email ATAU username
        $credentials = $request->validate([
            'email' => ['required', 'string'],   // string (bukan email) biar bisa username
            'password' => ['required'],
        ]);

        // 🔥 Deteksi: input berupa email atau username?
        $loginField = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        // 🔥 Coba login
        if (
            Auth::attempt([
                $loginField => $credentials['email'],
                'password' => $credentials['password'],
            ], $request->boolean('remember'))
        ) {

            $request->session()->regenerate();

            // Cek user aktif
            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                activity('auth')
                    ->withProperties([
                        'login' => $credentials['email'],
                        'ip' => $request->ip(),
                    ])
                    ->log('Login gagal - akun tidak aktif');

                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Hubungi admin.',
                ], 403);
            }

            // 🔥 LOG: Login berhasil
            activity('auth')
                ->causedBy(Auth::user())
                ->withProperties([
                    'login_via' => $loginField,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log('Login berhasil');

            return response()->json([
                'success' => true,
                'redirect' => route('apps.index'),
            ]);
        }

        // 🔥 LOG: Login gagal
        activity('auth')
            ->withProperties([
                'login' => $credentials['email'],
                'login_via' => $loginField,
                'ip' => $request->ip(),
            ])
            ->log('Login gagal');

        return response()->json([
            'success' => false,
            'message' => 'Username/email atau password salah',
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
