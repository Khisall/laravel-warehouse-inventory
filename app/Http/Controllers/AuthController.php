<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('inventory.index');
        }

        return view('auth.login');
    }

    /**
     * Process authentication.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi Super Admin.');
            }

            $user->update(['last_login_at' => now()]);
            $request->session()->regenerate();

            return redirect()->intended(route('inventory.index'))->with('success', 'Selamat datang kembali, ' . $user->name . ' (' . $user->role_badge_label . ')');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Quick demo switch for easy testing between roles.
     */
    public function demoLogin(Request $request, $role)
    {
        $allowedRoles = ['super_admin', 'warehouse_manager', 'warehouse_staff', 'sales_staff'];

        if (!in_array($role, $allowedRoles)) {
            abort(404);
        }

        $user = User::where('role', $role)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Pengguna demo untuk peran ' . $role . ' tidak ditemukan.');
        }

        Auth::login($user);
        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->route('inventory.index')->with('success', 'Anda sekarang masuk sebagai ' . $user->name . ' (' . $user->role_badge_label . ')');
    }

    /**
     * Log out the authenticated user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
