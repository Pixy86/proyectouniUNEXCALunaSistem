<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    public $timestamps = false;

    // Campos asignables: usuario, acción, modelo, descripción, IP, datos anteriores/nuevos
    protected $fillable = [
        'user_id',
        'accion',
        'modelo',
        'modelo_id',
        'descripcion',
        'ip_address',
        'user_agent',
        'datos_anteriores',
        'datos_nuevos',
        'created_at',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'created_at' => 'datetime',
    ];

    // Constantes que definen los tipos de acciones auditables
    public const ACCION_LOGIN = 'LOGIN';
    public const ACCION_LOGOUT = 'LOGOUT';
    public const ACCION_CREATE = 'CREAR';
    public const ACCION_UPDATE = 'ACTUALIZAR';
    public const ACCION_DELETE = 'ELIMINAR';
    public const ACCION_VIEW = 'VER';

    // Relationships
    // Relación: log pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to log actions
    // Método helper para registrar acciones de auditoría en el sistema
    public static function registrar(
        string $accion,
        ?string $descripcion = null,
        ?string $modelo = null,
        ?int $modeloId = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null
    ): self {
        // Si es una actualización y tenemos datos, generamos el detalle de cambios
        if ($accion === self::ACCION_UPDATE && !empty($datosAnteriores) && !empty($datosNuevos)) {
            $cambios = [];
            foreach ($datosNuevos as $key => $value) {
                // Solo incluimos campos que realmente cambiaron y no son técnicos (como updated_at)
                if (array_key_exists($key, $datosAnteriores) && 
                    $datosAnteriores[$key] != $value && 
                    !in_array($key, ['updated_at', 'password', 'remember_token'])) {
                    
                    $old = is_array($datosAnteriores[$key]) ? json_encode($datosAnteriores[$key]) : ($datosAnteriores[$key] ?? 'vacio');
                    $new = is_array($value) ? json_encode($value) : ($value ?? 'vacio');
                    
                    // Traducir nombres de campos comunes para que el usuario entienda
                    $label = match($key) {
                        'nombre' => 'Nombre',
                        'apellido' => 'Apellido',
                        'email' => 'Email',
                        'telefono' => 'Teléfono',
                        'cedula_rif' => 'Cédula/RIF',
                        'direccion' => 'Dirección',
                        'precio' => 'Precio',
                        'stockActual' => 'Stock',
                        'estado' => 'Estado',
                        'role' => 'Cargo',
                        'status' => 'Estatus',
                        default => $key
                    };

                    $cambios[] = "{$label}: '{$old}' → '{$new}'";
                }
            }
            
            if (!empty($cambios)) {
                $descripcion .= " | Cambios: " . implode(", ", $cambios);
            }
        }

        return self::create([
            'user_id' => Auth::id(),
            'accion' => $accion,
            'modelo' => $modelo,
            'modelo_id' => $modeloId,
            'descripcion' => $descripcion,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'created_at' => now(),
        ]);
    }

    // Accessors
    // Retorna el color badge según el tipo de acción
    public function getAccionColorAttribute(): string
    {
        return match ($this->accion) {
            self::ACCION_LOGIN => 'success',
            self::ACCION_LOGOUT => 'gray',
            self::ACCION_CREATE => 'info',
            self::ACCION_UPDATE => 'warning',
            self::ACCION_DELETE => 'danger',
            self::ACCION_VIEW => 'gray',
            default => 'gray',
        };
    }

    // Retorna el icono heroicon según el tipo de acción
    public function getAccionIconAttribute(): string
    {
        return match ($this->accion) {
            self::ACCION_LOGIN => 'heroicon-m-arrow-right-on-rectangle',
            self::ACCION_LOGOUT => 'heroicon-m-arrow-left-on-rectangle',
            self::ACCION_CREATE => 'heroicon-m-plus-circle',
            self::ACCION_UPDATE => 'heroicon-m-pencil-square',
            self::ACCION_DELETE => 'heroicon-m-trash',
            self::ACCION_VIEW => 'heroicon-m-eye',
            default => 'heroicon-m-document',
        };
    }
}
