<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'opname_number',
        'title',
        'warehouse_id',
        'category_id',
        'status',
        'notes',
        'created_by',
        'completed_by',
        'total_items',
        'matched_items',
        'variance_items',
        'total_variance_value',
        'completed_at',
    ];

    protected $casts = [
        'total_items' => 'integer',
        'matched_items' => 'integer',
        'variance_items' => 'integer',
        'total_variance_value' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    /**
     * Recalculates stats from items
     */
    public function recalculateSummary(): void
    {
        $items = $this->items;
        $total = $items->count();
        $matched = $items->where('is_counted', true)->where('variance', 0)->count();
        $variance = $items->where('is_counted', true)->where('variance', '!=', 0)->count();
        $varianceValue = $items->where('is_counted', true)->sum('variance_value');

        $this->update([
            'total_items' => $total,
            'matched_items' => $matched,
            'variance_items' => $variance,
            'total_variance_value' => $varianceValue,
        ]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draf / Persiapan',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai & Direkonsiliasi',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-slate-700/60 text-slate-300 border-slate-600',
            'in_progress' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            'cancelled' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
            default => 'bg-slate-700 text-slate-300',
        };
    }
}
