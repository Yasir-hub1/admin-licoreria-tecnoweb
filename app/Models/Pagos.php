<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagos extends Model
{
    protected $table = 'pagos';
    public $timestamps = false;

    protected $fillable = [
        'credito_id',
        'pago_id',
        'numero_cuota',
        'fecha_pago',
        'monto',
        'metodo',
        'nro_transaccion',
        'observacion'
    ];

    protected $casts = [
        'fecha_pago' => 'date'
    ];

    public function credito()
    {
        return $this->belongsTo(Credito::class, 'credito_id');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }
}
