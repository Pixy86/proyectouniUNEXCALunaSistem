<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesItem extends Model
{
    /** @use HasFactory<\Database\Factories\SalesItemFactory> */
    use HasFactory;
    protected $fillable = [
        'sale_id',
        'service_id',
        'quantity',
        'price',
    ];
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
