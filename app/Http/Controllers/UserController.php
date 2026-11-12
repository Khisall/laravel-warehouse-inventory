<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::latest();

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'super_admin' => User::where('role', 'super_admin')->count(),
            'warehouse_manager' => User::where('role', 'warehouse_manager')->count(),
            'warehouse_staff' => User::where('role', 'warehouse_staff')->count(),
            'sales_staff' => User::where('role', 'sales_staff')->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email',
            'role' => 'required|in:super_admin,warehouse_manager,warehouse_staff,sales_staff',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return redirect()->route('users.index')->with('success', 'Anggota tim baru (' . $validated['name'] . ') berhasil ditambahkan.');
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'role' => 'required|in:super_admin,warehouse_manager,warehouse_staff,sales_staff',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
        ]);

        // Prevent super_admin from demoting self if only one
        if ($user->id === Auth::id() && $validated['role'] !== 'super_admin') {
            return redirect()->back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri dari Super Admin.');
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('users.index')->with('success', 'Data profil tim (' . $user->name . ') berhasil diperbarui.');
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return redirect()->route('users.index')->with('success', 'Kata sandi untuk ' . $user->name . ' berhasil direset.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna (' . $name . ') berhasil dihapus dari sistem.');
    }
}
