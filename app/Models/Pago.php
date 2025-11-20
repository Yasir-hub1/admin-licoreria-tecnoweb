<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pago';
    
    protected $fillable = [
        'venta_id',
        'nro_pago',
        'tipo_pago',
        'estado',
        'monto',
        'qr_image',
        'nro_transaccion',
        'nombre_persona',
        'email',
        'telefono',
        'nit',
        'detalles_pago',
        'fecha_pago',
        'fecha_confirmacion',
        'observaciones'
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'fecha_confirmacion' => 'datetime',
        'detalles_pago' => 'array',
        'monto' => 'decimal:2'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function cuotaPago()
    {
        return $this->hasOne(Pagos::class, 'pago_id');
    }

    /**
     * Verificar si el pago está completado
     */
    public function estaCompletado()
    {
        return $this->estado === 'completado';
    }

    /**
     * Verificar si el pago está pendiente
     */
    public function estaPendiente()
    {
        return $this->estado === 'pendiente';
    }

    /**
     * Verificar si el pago requiere pasarela (QR)
     */
    public function requierePasarela()
    {
        return $this->tipo_pago === 'qr';
    }
}

