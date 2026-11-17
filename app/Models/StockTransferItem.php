<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_id',
        'source_product_id',
        'destination_product_id',
        'sku',
        'product_title',
        'quantity',
        'received_quantity',
        'unit',
        'batch_number',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer',
        'expiry_date' => 'date',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    public function sourceProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'source_product_id');
    }

    public function destinationProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'destination_product_id');
    }
}
