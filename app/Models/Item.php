<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    /** @use HasFactory<\Database\Factories\ItemFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'price',
        'status',
    
    ];
    public function Inventory(): HasOne

    {
        return $this->hasOne(Inventory::class);
    }

    public function salesItems()
    {
        return $this->hasMany(SalesItem::class);
    }
}
