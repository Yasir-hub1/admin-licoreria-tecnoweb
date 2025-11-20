<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'venta';
    public $timestamps = false;

    protected $fillable = [
        'nro_venta',
        'fecha',
        'tipo',
        'metodo_pago',
        'monto_total',
        'saldo',
        'numero_cuotas',
        'estado',
        'estado_pago',
        'cliente_id',
        'usuario_id'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Mantener compatibilidad temporal (deprecated)
    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function credito()
    {
        return $this->hasOne(Credito::class, 'venta_id');
    }

    public function inventarios()
    {
        return $this->hasManyThrough(Inventario::class, DetalleVenta::class, 'venta_id', 'detalle_venta_id');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'venta_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'venta_id');
    }
}
