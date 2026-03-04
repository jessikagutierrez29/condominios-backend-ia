<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    public const TYPE_CONSUMABLE = 'consumable';
    public const TYPE_ASSET = 'asset';

    protected $fillable = [
        'inventory_id',
        'category_id',
        'name',
        'category',
        'unit_measure',
        'unit_cost',
        'stock',
        'minimum_stock',
        'type',
        'asset_code',
        'location',
        'is_active',
        'responsible_id',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'minimum_stock' => 'integer',
        'stock' => 'integer',
    ];

    /*Relationships*/

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function inventoryCategory()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    /*Business Logic (Stock handling)*/

    public function increaseStock($quantity)
    {
        $this->increment('stock', $quantity);
    }

    public function decreaseStock($quantity)
    {
        $this->decrement('stock', $quantity);
    }

    public function isConsumable(): bool
    {
        return (string) $this->type !== self::TYPE_ASSET;
    }

    public function isAsset(): bool
    {
        return (string) $this->type === self::TYPE_ASSET;
    }

    public function isBelowMinimumStock(): bool
    {
        return $this->isConsumable() && (int) $this->stock <= (int) $this->minimum_stock;
    }
}
