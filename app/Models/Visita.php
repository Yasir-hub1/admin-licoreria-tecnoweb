<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    protected $table = 'visita';

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'ruta',
        'nombre_pagina',
        'usuario_id',
        'ip',
        'user_agent'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * Relación con usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Obtener el total de visitas
     */
    public static function obtenerTotal(): int
    {
        return static::count();
    }

    /**
     * Obtener el total de visitas de hoy
     */
    public static function obtenerTotalHoy(): int
    {
        return static::whereDate('created_at', today())->count();
    }

    /**
     * Obtener el total de visitas de la sesión actual
     */
    public static function obtenerTotalSesion(string $sessionId): int
    {
        return static::where('session_id', $sessionId)->count();
    }
}
