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
     * Primero verifica la colección cargada en memoria, luego consulta la BD si es necesario
     */
    public function tienePermiso($permiso)
    {
        // Si los permisos ya están cargados, usar la colección en memoria
        if ($this->relationLoaded('permisos')) {
            if (is_string($permiso)) {
                return $this->permisos->contains('slug', $permiso);
            }

            if ($permiso instanceof Permiso) {
                return $this->permisos->contains('id', $permiso->id);
            }

            return false;
        }

        // Si no están cargados, hacer consulta a la BD
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
        
        // Recargar la relación para que esté actualizada en memoria
        $this->load('permisos');
        
        // Invalidar la caché de permisos de todos los usuarios con este rol
        // Esto asegura que los usuarios autenticados reciban los nuevos permisos
        $this->usuarios()->each(function ($usuario) {
            // Limpiar la relación cargada del usuario para forzar recarga
            $usuario->unsetRelation('rol');
        });
    }
}
