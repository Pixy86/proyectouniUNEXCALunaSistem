<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cedula_rif',
        'nombre',
        'apellido',
        'telefono',
        'telefono_secundario',
        'email',
        'direccion',
        'estado',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function sales()
    {
        return $this->hasMany(related: Sale::class);
    }
}

