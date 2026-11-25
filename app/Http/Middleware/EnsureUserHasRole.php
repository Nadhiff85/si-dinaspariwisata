<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request and verify role authorization on the server.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.');
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->with('error', 'Akun Anda sedang dinonaktifkan oleh Admin Dinas.');
        }

        if (! empty($roles) && ! in_array($user->peran, $roles, true)) {
            abort(403, 'Akses ditolak: Peran akun Anda tidak memiliki izin untuk membuka modul ini.');
        }

        return $next($request);
    }
}

