<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['super_admin', 'warehouse_manager']);
    }

    public function isStaff(): bool
    {
        return $this->role === 'warehouse_staff';
    }

    public function isSales(): bool
    {
        return $this->role === 'sales_staff';
    }

    public function canViewFinancials(): bool
    {
        return in_array($this->role, ['super_admin', 'warehouse_manager']);
    }

    public function canManageUsers(): bool
    {
        return $this->role === 'super_admin';
    }

    public function canDeleteItems(): bool
    {
        return in_array($this->role, ['super_admin', 'warehouse_manager']);
    }

    public function canAdjustStock(): bool
    {
        return in_array($this->role, ['super_admin', 'warehouse_manager', 'warehouse_staff']);
    }

    public function getRoleBadgeLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Owner / Super Admin',
            'warehouse_manager' => 'Kepala Gudang',
            'warehouse_staff' => 'Operator Gudang',
            'sales_staff' => 'Admin Penjualan',
            default => 'Staff',
        };
    }

    public function getRoleBadgeColorAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
            'warehouse_manager' => 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30',
            'warehouse_staff' => 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
            'sales_staff' => 'bg-purple-500/20 text-purple-400 border border-purple-500/30',
            default => 'bg-slate-800 text-slate-300 border border-slate-700',
        };
    }
}
