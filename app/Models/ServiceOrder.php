<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrder extends Model
{
    use HasFactory, SoftDeletes;

    // Campos asignables: cliente, vehículo, usuario asignado, estado, notas y fecha de completado
    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'user_id',
        'status',
        'notes',
        'completed_at',
        'started_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
    ];

    // Constantes que definen los posibles estados de una orden de servicio
    public const STATUS_ABIERTA = 'Abierta';
    public const STATUS_EN_PROCESO = 'En Proceso';
    public const STATUS_TERMINADO = 'Terminado';
    public const STATUS_CANCELADA = 'Cancelada';

    // Retorna array con todos los estados posibles de una orden
    public static function statuses(): array
    {
        return [
            self::STATUS_ABIERTA,
            self::STATUS_EN_PROCESO,
            self::STATUS_TERMINADO,
            self::STATUS_CANCELADA,
        ];
    }

    // Relationships
    // Relación: orden pertenece a un cliente
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relación: orden pertenece a un vehículo
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Relación: orden asignada a un usuario/mecánico
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: orden tiene múltiples items/servicios
    public function items()
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    // Relación: orden puede tener una venta asociada
    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    // Scopes
    // Scope: filtra órdenes con estado Terminado
    public function scopeTerminado($query)
    {
        return $query->where('status', self::STATUS_TERMINADO);
    }

    // Scope: filtra órdenes terminadas que aún no tienen venta asociada
    public function scopePendingPayment($query)
    {
        return $query->terminado()->whereDoesntHave('sale');
    }

    // Computed
    // Calcula el monto total sumando precio por cantidad de cada item
    public function getTotalAmountAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    // Retorna el color badge según el estado de la orden
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ABIERTA => 'warning',
            self::STATUS_EN_PROCESO => 'info',
            self::STATUS_TERMINADO => 'success',
            self::STATUS_CANCELADA => 'danger',
            default => 'gray',
        };
    }
}
