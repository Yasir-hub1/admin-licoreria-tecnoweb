<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contador extends Model
{
    protected $table = 'contador';

    protected $fillable = [
        'tipo',
        'prefijo',
        'valor_actual',
        'longitud',
        'descripcion'
    ];

    protected $casts = [
        'valor_actual' => 'integer',
        'longitud' => 'integer'
    ];

    /**
     * Obtener el siguiente número formateado
     */
    public function getSiguienteNumeroAttribute(): string
    {
        return $this->prefijo . str_pad($this->valor_actual + 1, $this->longitud, '0', STR_PAD_LEFT);
    }

    /**
     * Incrementar el contador de forma thread-safe
     */
    public function incrementar(): int
    {
        $this->increment('valor_actual');
        $this->refresh();
        return $this->valor_actual;
    }

    /**
     * Obtener o crear un contador por tipo
     */
    public static function obtenerPorTipo(string $tipo): self
    {
        return static::firstOrCreate(
            ['tipo' => $tipo],
            [
                'prefijo' => self::getDefaultPrefix($tipo),
                'valor_actual' => 0,
                'longitud' => 6,
                'descripcion' => self::getDefaultDescription($tipo)
            ]
        );
    }

    /**
     * Obtener prefijo por defecto según el tipo
     */
    private static function getDefaultPrefix(string $tipo): string
    {
        return match($tipo) {
            'venta' => 'V-',
            'compra' => 'C-',
            'producto' => 'PROD-',
            default => ''
        };
    }

    /**
     * Obtener descripción por defecto según el tipo
     */
    private static function getDefaultDescription(string $tipo): string
    {
        return match($tipo) {
            'venta' => 'Contador para números de venta',
            'compra' => 'Contador para números de compra',
            'producto' => 'Contador para códigos de producto',
            default => 'Contador ' . $tipo
        };
    }
}
