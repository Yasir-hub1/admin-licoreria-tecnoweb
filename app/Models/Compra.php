<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compra';
    public $timestamps = false;

    protected $fillable = [
        'nro_compra',
        'descripcion',
        'proveedor_id',
        'fecha',
        'estado'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }

    public function inventarios()
    {
        return $this->hasManyThrough(Inventario::class, DetalleCompra::class, 'compra_id', 'detalle_compra_id');
    }
}
