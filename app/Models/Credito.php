<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    protected $table = 'credito';
    public $timestamps = false;

    protected $fillable = [
        'venta_id',
        'monto_total',
        'saldo',
        'numero_cuotas',
        'fecha_inicio',
        'estado'
    ];

    protected $casts = [
        'fecha_inicio' => 'date'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pagos::class, 'credito_id');
    }
}
