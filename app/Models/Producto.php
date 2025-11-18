<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'producto';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'precio',
        'marca',
        'categoria_id'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function detallesCompra()
    {
        return $this->hasMany(DetalleCompra::class, 'producto_id');
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    public function itemsCarrito()
    {
        return $this->hasMany(ItemCarrito::class, 'producto_id');
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'producto_id');
    }
}
