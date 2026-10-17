<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['username' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.']);
        }

        $userRole = $request->user()->role;

        // Kolektor is the supreme role holding all administrative, financial, cashier, and oversight permissions
        if ($userRole === 'kolektor') {
            return $next($request);
        }

        // Backward compatibility for existing tests/seeders: admin and bendahara map to supreme/kolektor permissions
        if (in_array($userRole, ['admin', 'bendahara'], true) && (in_array('kolektor', $roles, true) || in_array($userRole, $roles, true))) {
            return $next($request);
        }

        if (empty($roles) || in_array($userRole, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki hak akses untuk membuka halaman ini.');
    }
}
