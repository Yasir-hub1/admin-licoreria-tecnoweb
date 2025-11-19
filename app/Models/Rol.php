<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rol';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'rol_id', 'permiso_id');
    }

    /**
     * Verificar si el rol tiene un permiso específico
     */
    public function tienePermiso($permiso)
    {
        if (is_string($permiso)) {
            return $this->permisos()->where('slug', $permiso)->exists();
        }

        if ($permiso instanceof Permiso) {
            return $this->permisos()->where('permiso.id', $permiso->id)->exists();
        }

        return false;
    }

    /**
     * Sincronizar permisos del rol
     */
    public function sincronizarPermisos(array $permisoIds)
    {
        $this->permisos()->sync($permisoIds);
    }
}
