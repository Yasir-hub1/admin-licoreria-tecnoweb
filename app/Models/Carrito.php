<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $table = 'carrito';

    protected $fillable = [
        'session_id',
        'usuario_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function items()
    {
        return $this->hasMany(ItemCarrito::class, 'carrito_id');
    }

    public function getTotalAttribute()
    {
        return $this->items->sum(function($item) {
            return $item->precio * $item->cantidad;
        });
    }
}

