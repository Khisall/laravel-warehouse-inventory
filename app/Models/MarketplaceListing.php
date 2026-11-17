<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'channel',
        'store_name',
        'channel_sku',
        'external_product_id',
        'channel_code',
        'channel_price',
        'synced_stock',
        'safety_stock_buffer',
        'sync_status',
        'auto_sync',
        'last_synced_at',
    ];

    protected $casts = [
        'channel_price' => 'decimal:2',
        'synced_stock' => 'integer',
        'safety_stock_buffer' => 'integer',
        'auto_sync' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Compute available sellable stock on marketplace taking safety buffer into account.
     */
    public function getAvailableStockAttribute(): int
    {
        $warehouseStock = $this->product?->quantity ?? 0;
        return max(0, $warehouseStock - $this->safety_stock_buffer);
    }

    public function getChannelColorClassAttribute(): string
    {
        return match (strtolower($this->channel)) {
            'shopee' => 'bg-orange-500/15 text-orange-400 border-orange-500/30',
            'tokopedia' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
            'tiktok', 'tiktok shop' => 'bg-rose-500/15 text-rose-300 border-rose-500/30',
            'lazada' => 'bg-blue-500/15 text-blue-400 border-blue-500/30',
            default => 'bg-slate-700/40 text-slate-300 border-slate-600',
        };
    }

    public function getChannelIconAttribute(): string
    {
        return match (strtolower($this->channel)) {
            'shopee' => 'fa-bag-shopping',
            'tokopedia' => 'fa-store',
            'tiktok', 'tiktok shop' => 'fa-brands fa-tiktok',
            'lazada' => 'fa-heart',
            default => 'fa-globe',
        };
    }
}
