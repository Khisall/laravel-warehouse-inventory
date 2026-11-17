<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'source_warehouse_id',
        'destination_warehouse_id',
        'transfer_date',
        'expected_arrival_date',
        'received_date',
        'status',
        'driver_name',
        'vehicle_plate_number',
        'expedition',
        'tracking_number',
        'notes',
        'total_items_count',
        'total_quantity',
        'total_received_quantity',
        'created_by',
        'dispatched_by',
        'dispatched_at',
        'received_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'expected_arrival_date' => 'date',
        'received_date' => 'datetime',
        'dispatched_at' => 'datetime',
        'total_items_count' => 'integer',
        'total_quantity' => 'integer',
        'total_received_quantity' => 'integer',
    ];

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'stock_transfer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft Pengajuan',
            'in_transit' => 'Dalam Pengiriman',
            'received' => 'Selesai Diterima',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-slate-700/40 text-slate-300 border-slate-600',
            'in_transit' => 'bg-amber-500/20 text-amber-300 border-amber-500/40 animate-pulse',
            'received' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
            'cancelled' => 'bg-rose-500/20 text-rose-300 border-rose-500/40',
            default => 'bg-slate-800 text-slate-400',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'fa-file-lines',
            'in_transit' => 'fa-truck-fast',
            'received' => 'fa-circle-check',
            'cancelled' => 'fa-ban',
            default => 'fa-circle-question',
        };
    }

    public function canBeDispatched(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeReceived(): bool
    {
        return $this->status === 'in_transit';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'in_transit']);
    }

    /**
     * Helper to recalculate summary numbers
     */
    public function recalculateTotals(): void
    {
        $this->update([
            'total_items_count' => $this->items()->count(),
            'total_quantity' => $this->items()->sum('quantity'),
            'total_received_quantity' => $this->items()->sum('received_quantity'),
        ]);
    }
}
