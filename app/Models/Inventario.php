<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'inventario';
    public $timestamps = false;

    protected $fillable = [
        'tipo_movimiento',
        'cantidad',
        'fecha',
        'stock_actual',
        'glosa',
        'usuario_id',
        'detalle_compra_id',
        'detalle_venta_id',
        'producto_id'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalleCompra()
    {
        return $this->belongsTo(DetalleCompra::class, 'detalle_compra_id');
    }

    public function detalleVenta()
    {
        return $this->belongsTo(DetalleVenta::class, 'detalle_venta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
