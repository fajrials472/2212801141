<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  (Akan diisi dengan 'admin', 'dosen', atau 'mahasiswa' dari file rute)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Memeriksa apakah pengguna sudah login dan apakah role-nya termasuk dalam daftar role yang diizinkan.
        if (!Auth::check() || !in_array(Auth::user()->role, $roles)) {
            // Jika tidak, tolak akses.
            abort(403, 'ANDA TIDAK MEMILIKI AKSES.');
        }

        // Jika role cocok, izinkan akses ke halaman yang dituju.
        return $next($request);
    }
}

