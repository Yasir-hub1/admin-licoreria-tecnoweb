<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCarrito extends Model
{
    protected $table = 'item_carrito';

    protected $fillable = [
        'carrito_id',
        'producto_id',
        'cantidad',
        'precio'
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function getSubtotalAttribute()
    {
        return $this->precio * $this->cantidad;
    }
}

