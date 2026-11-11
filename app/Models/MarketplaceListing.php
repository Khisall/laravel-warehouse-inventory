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
        'channel_sku',
        'external_product_id',
        'channel_code',
        'channel_price',
        'synced_stock',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'channel_price' => 'decimal:2',
        'synced_stock' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
