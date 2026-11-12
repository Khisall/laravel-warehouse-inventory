<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'system_stock',
        'physical_stock',
        'variance',
        'cost_price',
        'variance_value',
        'is_counted',
        'notes',
        'scanned_at',
    ];

    protected $casts = [
        'system_stock' => 'integer',
        'physical_stock' => 'integer',
        'variance' => 'integer',
        'cost_price' => 'decimal:2',
        'variance_value' => 'decimal:2',
        'is_counted' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    public function opname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Set physical stock and compute variance
     */
    public function recordCount(int $physicalQty, ?string $notes = null): void
    {
        $this->physical_stock = $physicalQty;
        $this->variance = $this->physical_stock - $this->system_stock;
        $this->variance_value = $this->variance * $this->cost_price;
        $this->is_counted = true;
        $this->scanned_at = now();

        if ($notes !== null) {
            $this->notes = $notes;
        }

        $this->save();
        $this->opname->recalculateSummary();
    }
}
