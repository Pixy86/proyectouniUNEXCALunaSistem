<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderItem extends Model
{
    use HasFactory;

    // Campos asignables: orden de servicio, servicio, cantidad y precio
    protected $fillable = [
        'service_order_id',
        'service_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // Relationships
    // Relación: item pertenece a una orden de servicio
    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    // Relación: item pertenece a un servicio
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Computed
    // Calcula el subtotal multiplicando precio por cantidad
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }
}
