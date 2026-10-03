<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Otorisasi berbasis peran RBAC: role:admin  |  role:operator,admin  |  role:admin,viewer
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            Auth::logout();

            return redirect()->route('login')->withErrors(['email' => 'Akun Anda dinonaktifkan.']);
        }

        if ($roles !== [] && ! $user->hasRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke modul ini.'], 403);
            }

            return back()->with('error', 'Akses ditolak: peran "'.($user->role?->display_name ?? 'Tanpa Peran').'" tidak berwenang membuka modul ini.');
        }

        return $next($request);
    }
}
