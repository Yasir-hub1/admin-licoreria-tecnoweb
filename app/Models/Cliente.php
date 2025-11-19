<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'cliente';
    public $timestamps = false;

    protected $fillable = [
        'ci',
        'nombre',
        'telefono',
        'direccion',
        'estado',
        'usuario_id',
        'credito_aprobado',
        'limite_credito',
        'carnet_anverso',
        'carnet_reverso',
        'foto_luz',
        'foto_agua',
        'foto_garantia',
        'estado_verificacion',
        'observaciones_verificacion',
        'fecha_verificacion',
        'verificado_por'
    ];

    protected $casts = [
        'fecha_verificacion' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }

    public function verificador()
    {
        return $this->belongsTo(Usuario::class, 'verificado_por');
    }

    /**
     * Verificar si el cliente tiene todos los documentos subidos
     */
    public function tieneDocumentosCompletos()
    {
        return !empty($this->carnet_anverso) &&
               !empty($this->carnet_reverso) &&
               !empty($this->foto_luz) &&
               !empty($this->foto_agua) &&
               !empty($this->foto_garantia);
    }

    /**
     * Verificar si el cliente está verificado y aprobado para crédito
     */
    public function estaVerificadoParaCredito()
    {
        return $this->tieneDocumentosCompletos() &&
               $this->estado_verificacion === 'aprobado' &&
               $this->credito_aprobado === true;
    }
}
