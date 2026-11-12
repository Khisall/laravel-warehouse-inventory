<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'title',
        'slug',
        'category_id',
        'warehouse_id',
        'warehouse_location',
        'quantity',
        'min_stock',
        'unit',
        'cost_price',
        'price',
        'image',
        'variation_text',
        'batch_number',
        'expiry_date',
        'variations_count',
        'status_code',
        'status',
        'is_marketplace_listed',
        'description',
        'last_modified',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_stock' => 'integer',
        'cost_price' => 'decimal:2',
        'price' => 'decimal:2',
        'variations_count' => 'integer',
        'expiry_date' => 'date',
        'is_marketplace_listed' => 'boolean',
        'last_modified' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($product) {
            if (empty($product->slug) && !empty($product->title)) {
                $product->slug = Str::slug($product->title) . '-' . strtolower(Str::random(4));
            }
            if ($product->quantity <= 0) {
                $product->status = 'out_of_stock';
            } elseif ($product->quantity <= $product->min_stock) {
                $product->status = 'low_stock';
            } else {
                $product->status = 'in_stock';
            }
            $product->last_modified = now();
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function marketplaceListings(): HasMany
    {
        return $this->hasMany(MarketplaceListing::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            if (str_starts_with($this->image, 'http')) {
                return $this->image;
            }
            return asset('storage/' . $this->image);
        }

        // Return themed SVG placeholder based on product title
        return 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=100&auto=format&fit=crop&q=80';
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getFormattedCostPriceAttribute(): string
    {
        return '$' . number_format($this->cost_price, 2);
    }
}
