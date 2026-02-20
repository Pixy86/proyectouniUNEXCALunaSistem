<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryFactory> */
    use HasFactory;

    protected $fillable = [
        'nombreProducto',
        'descripcion',
        'stockActual',
        'estado',
    ];

    /**
     * Servicios que utilizan este producto.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'inventory_service')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function hasLinkedRecords(): bool
    {
        return $this->stockActual > 0 || $this->services()->exists();
    }
}
