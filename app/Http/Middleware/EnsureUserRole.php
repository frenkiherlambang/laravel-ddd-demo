<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureUserRole — middleware otorisasi berbasis peran.
 *
 * Dipakai di rute untuk membatasi akses, mis. `->middleware('role:admin')`
 * atau `->middleware('role:student')`. Menjaga pemisahan area Admin vs
 * Mahasiswa meski keduanya berbagi satu tabel users.
 */
class EnsureUserRole
{
    /**
     * @param  string  $role  Peran yang diizinkan mengakses rute.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // Tolak bila belum login atau perannya tidak sesuai.
        if ($user === null || $user->role->value !== $role) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
