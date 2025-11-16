<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'correo',
        'clave',
        'estado',
        'id_rol'
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'usuario_id');
    }

    public function vendedor()
    {
        return $this->hasOne(Vendedor::class, 'usuario_id');
    }
}
