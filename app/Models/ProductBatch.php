<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_number',
        'initial_quantity',
        'current_quantity',
        'manufacturing_date',
        'expiry_date',
        'unit_cost',
        'status',
        'location_code',
        'notes',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'initial_quantity' => 'integer',
        'current_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * FEFO Scope: orders active, non-expired batches by earliest expiry date.
     */
    public function scopeAvailableForFefo($query)
    {
        return $query->where('status', 'active')
            ->where('current_quantity', '>', 0)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date', 'asc');
    }

    public function scopeFefoOrder($query)
    {
        return $query->orderBy('expiry_date', 'asc');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        if (!$this->expiry_date) {
            return 999;
        }
        return (int) now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);
    }

    public function getExpiryStatusAttribute(): string
    {
        $days = $this->days_until_expiry;
        if ($days < 0) {
            return 'expired';
        }
        if ($days <= 30) {
            return 'critical';
        }
        if ($days <= 90) {
            return 'warning';
        }
        return 'safe';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Aktif (Siap Pick)',
            'depleted' => 'Habis Terjual',
            'expired' => 'Kedaluwarsa',
            'quarantine' => 'Karantina (Hold)',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
            'depleted' => 'bg-slate-700/40 text-slate-400 border-slate-600',
            'expired' => 'bg-rose-500/15 text-rose-300 border-rose-500/30',
            'quarantine' => 'bg-amber-500/15 text-amber-300 border-amber-500/30 animate-pulse',
            default => 'bg-slate-800 text-slate-400',
        };
    }

    public function getExpiryBadgeClassAttribute(): string
    {
        return match ($this->expiry_status) {
            'expired' => 'bg-rose-500/15 text-rose-300 border-rose-500/30 font-bold',
            'critical' => 'bg-amber-500/20 text-amber-300 border-amber-500/40 animate-pulse font-bold',
            'warning' => 'bg-yellow-500/15 text-yellow-300 border-yellow-500/30',
            'safe' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            default => 'bg-slate-800 text-slate-400',
        };
    }

    public function isAvailableForFefo(): bool
    {
        return $this->status === 'active'
            && $this->current_quantity > 0
            && $this->days_until_expiry >= 0;
    }
}
