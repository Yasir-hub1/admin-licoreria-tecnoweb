<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    protected $table = 'vendedor';
    public $timestamps = false;

    protected $fillable = [
        'ci',
        'nombre',
        'usuario_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'vendedor_id');
    }
}
