<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'cliente';
    public $timestamps = false;

    protected $fillable = [
        'ci',
        'nombre',
        'telefono',
        'direccion',
        'estado',
        'usuario_id',
        'credito_aprobado',
        'limite_credito'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }
}
