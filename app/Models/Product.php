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

    /**
     * Scopes for Inventory Alerts
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->where('quantity', '>', 0)
                     ->whereColumn('quantity', '<=', 'min_stock');
    }

    public function scopeNeedsReorder($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_stock');
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '<', now()->startOfDay());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '>=', now()->startOfDay())
                     ->where('expiry_date', '<=', now()->addDays($days)->endOfDay());
    }

    public function scopeExpiringWarning($query, int $fromDays = 31, int $toDays = 90)
    {
        return $query->whereNotNull('expiry_date')
                     ->where('expiry_date', '>', now()->addDays($fromDays)->startOfDay())
                     ->where('expiry_date', '<=', now()->addDays($toDays)->endOfDay());
    }

    /**
     * Helpers for Expiry Status
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);
    }

    public function getExpiryStatusAttribute(): ?string
    {
        if (!$this->expiry_date) {
            return null;
        }

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

    public function getExpiryBadgeClassAttribute(): string
    {
        return match ($this->expiry_status) {
            'expired' => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
            'critical' => 'bg-amber-500/15 text-amber-400 border-amber-500/30 animate-pulse',
            'warning' => 'bg-yellow-500/10 text-yellow-300 border-yellow-500/20',
            'safe' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            default => 'bg-slate-800 text-slate-400',
        };
    }

    public function getSuggestedReorderQtyAttribute(): int
    {
        $targetStock = max(10, $this->min_stock * 2);
        return max(5, $targetStock - max(0, $this->quantity));
    }
}
