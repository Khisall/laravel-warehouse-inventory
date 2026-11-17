<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'manager_name',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'source_warehouse_id');
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'destination_warehouse_id');
    }
}
