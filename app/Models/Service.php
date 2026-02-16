<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'estado',
    ];

    /**
     * Relación muchos-a-muchos con inventarios.
     * Cada producto tiene una cantidad que el servicio consume.
     */
    public function inventories()
    {
        return $this->belongsToMany(Inventory::class, 'inventory_service')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Accessor: calcula cuántas veces se puede realizar el servicio
     * basándose en el stock disponible de todos sus productos.
     * Retorna el mínimo de floor(stockActual / pivot.quantity) entre todos los productos.
     */
    public function getCantidadAttribute(): int
    {
        $inventories = $this->inventories;

        if ($inventories->isEmpty()) {
            return 0;
        }

        return $inventories->min(function ($inventory) {
            $requiredQty = $inventory->pivot->quantity;
            if ($requiredQty <= 0) {
                return 0;
            }
            return (int) floor($inventory->stockActual / $requiredQty);
        });
    }

    public function salesItems()
    {
        return $this->hasMany(SalesItem::class);
    }
}
