<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // 🔥 LOG: Login berhasil
            activity('auth')
                ->causedBy(Auth::user())
                ->withProperties([
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
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('Login gagal');

        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah',
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            // 🔥 LOG: Logout
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
