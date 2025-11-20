<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedor';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'telefono',
        'nit',
        'correo',
        'direccion',
        'usuario_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'proveedor_id');
    }
}
