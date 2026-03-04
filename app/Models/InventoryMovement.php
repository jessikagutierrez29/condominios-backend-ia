<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'movement_date',
        'registered_by_id',
        'observations',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'movement_date' => 'date',
    ];

    /* Relationships*/

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by_id');
    }

    /*Boot Logic (Auto stock update)*/

    protected static function booted()
    {
        static::creating(function ($movement) {
            if (empty($movement->movement_date)) {
                $movement->movement_date = Carbon::today()->toDateString();
            }
        });

        static::created(function ($movement) {

            $product = $movement->product;
            if (! $product || $product->isAsset()) {
                return;
            }

            if ($movement->type === 'entry') {
                $product->increaseStock($movement->quantity);
            }

            if ($movement->type === 'exit') {

                if ($product->stock < $movement->quantity) {
                    throw new \Exception('Insufficient stock.');
                }

                $product->decreaseStock($movement->quantity);
            }
        });
    }
}
