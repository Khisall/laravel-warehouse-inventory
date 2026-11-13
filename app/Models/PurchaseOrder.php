<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'warehouse_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'payment_status',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'notes',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('subtotal');
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_cost;
        $this->save();
    }

    public function updateReceivingStatus(): void
    {
        $items = $this->items;
        $totalOrdered = $items->sum('quantity_ordered');
        $totalReceived = $items->sum('quantity_received');

        if ($totalReceived >= $totalOrdered && $totalOrdered > 0) {
            $this->status = 'received';
        } elseif ($totalReceived > 0) {
            $this->status = 'partial';
        }
        $this->save();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draf Konsep',
            'ordered' => 'PO Diterbitkan (Menunggu Kiriman)',
            'partial' => 'Diterima Sebagian',
            'received' => 'Diterima Lengkap',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-slate-700/60 text-slate-300 border-slate-600',
            'ordered' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
            'partial' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            'received' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
            'cancelled' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
            default => 'bg-slate-700 text-slate-300',
        };
    }
}
