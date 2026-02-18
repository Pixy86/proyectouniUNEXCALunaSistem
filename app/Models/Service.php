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

    public static function boot()
    {
        parent::boot();

        static::saving(function ($service) {
            // Auto-desactivar si el stock es 0 (solo si tiene inventario vinculado)
            if (!$service->isLaborOnly() && $service->cantidad <= 0) {
                $service->estado = false;
            }
        });
    }

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
            return -1; // Usaremos -1 para indicar que es solo Mano de Obra (sin stock limitado)
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

    public function items()
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function hasLinkedRecords(): bool
    {
        // Solo contamos items vinculados a órdenes que NO estén en la papelera
        return $this->items()->whereHas('serviceOrder', function ($query) {
            $query->withoutTrashed();
        })->exists() || $this->salesItems()->exists();
    }

    public function isLaborOnly(): bool
    {
        return $this->inventories()->doesntExist();
    }

    public function hasOpenOrders(): bool
    {
        return $this->items()->whereHas('serviceOrder', function ($query) {
            $query->whereIn('status', [ServiceOrder::STATUS_ABIERTA, ServiceOrder::STATUS_EN_PROCESO])
                  ->withoutTrashed();
        })->exists();
    }

    public function syncStatusWithStock(): void
    {
        if (!$this->isLaborOnly() && $this->cantidad <= 0) {
            $this->update(['estado' => false]);
        }
    }
}
