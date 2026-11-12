<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan oleh administrator.');
        }

        // If user is super_admin, always permit
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Check if user's role is in the allowed list
        if (!in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized action. Akses ditolak.'], 403);
            }

            return redirect()->route('inventory.index')->with('error', 'Akses Ditolak: Peran akun Anda (' . $user->role_badge_label . ') tidak memiliki izin untuk membuka halaman tersebut.');
        }

        return $next($request);
    }
}
