<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true], $remember)) {
            $request->session()->regenerate();

            AuditLog::log('login', 'Pengguna berhasil masuk ke sistem');

            return redirect()->intended(route('dashboard'));
        }

        // Check if user exists but inactive
        $inactiveUser = \App\Models\User::where('username', $credentials['username'])->where('is_active', false)->first();
        if ($inactiveUser) {
            return back()->withInput($request->only('username'))->withErrors([
                'username' => 'Akun dinonaktifkan. Silakan hubungi Administrator Desa.',
            ]);
        }

        return back()->withInput($request->only('username'))->withErrors([
            'username' => 'Username atau password yang Anda masukkan tidak sesuai.',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditLog::log('logout', 'Pengguna keluar dari sistem');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
