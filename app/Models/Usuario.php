<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';

    // Laravel usa 'email' por defecto para autenticación
    // Pero la tabla tiene 'correo' también, así que sincronizamos ambos
    protected $fillable = [
        'nombre',
        'correo',
        'email',
        'clave',
        'password',
        'estado',
        'id_rol'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'clave'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the name of the unique identifier for the user.
     * Laravel debe usar 'id' como identificador único, no 'email'
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     * Retorna el ID numérico del usuario
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Sincronizar email y correo cuando se actualiza
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($usuario) {
            // Si se actualiza email, también actualizar correo
            if ($usuario->isDirty('email') && $usuario->email) {
                $usuario->correo = $usuario->email;
            }
            // Si se actualiza correo, también actualizar email
            if ($usuario->isDirty('correo') && $usuario->correo && !$usuario->email) {
                $usuario->email = $usuario->correo;
            }
        });
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'usuario_id');
    }

    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'usuario_id');
    }

    // Mantener compatibilidad con código antiguo
    public function vendedor()
    {
        return $this->empleado();
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'usuario_id');
    }

    public function carritos()
    {
        return $this->hasMany(Carrito::class, 'usuario_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    public function proveedor()
    {
        return $this->hasOne(Proveedor::class, 'usuario_id');
    }

    // Helper methods for roles
    public function hasRole($role)
    {
        return $this->rol && $this->rol->nombre === $role;
    }

    public function isCliente()
    {
        return $this->hasRole('cliente');
    }

    public function isPropietario()
    {
        return $this->hasRole('propietario');
    }

    public function isVendedor()
    {
        return $this->hasRole('vendedor') || $this->hasRole('empleado');
    }

    public function isEmpleado()
    {
        return $this->hasRole('empleado') || $this->hasRole('vendedor');
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     * Verifica a través de su rol
     */
    public function tienePermiso($permiso)
    {
        if (!$this->rol) {
            return false;
        }

        // Si es propietario, tiene todos los permisos
        if ($this->isPropietario()) {
            return true;
        }

        return $this->rol->tienePermiso($permiso);
    }

    /**
     * Verificar si el usuario tiene alguno de los permisos dados
     */
    public function tieneAlgunPermiso(array $permisos)
    {
        foreach ($permisos as $permiso) {
            if ($this->tienePermiso($permiso)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si el usuario tiene todos los permisos dados
     */
    public function tieneTodosLosPermisos(array $permisos)
    {
        foreach ($permisos as $permiso) {
            if (!$this->tienePermiso($permiso)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Obtener todos los permisos del usuario a través de su rol
     */
    public function getPermisos()
    {
        if (!$this->rol) {
            return collect([]);
        }

        // Cargar permisos si no están cargados
        if (!$this->rol->relationLoaded('permisos')) {
            $this->rol->load('permisos');
        }

        return $this->rol->permisos;
    }

    /**
     * Obtener los slugs de permisos del usuario
     */
    public function getPermisosSlugs()
    {
        return $this->getPermisos()->pluck('slug')->toArray();
    }

    /**
     * Verificar si el usuario tiene acceso al panel administrativo
     * Tiene acceso si es propietario, empleado, o tiene algún permiso de módulos administrativos
     */
    public function tieneAccesoAdmin()
    {
        // Propietario y empleado siempre tienen acceso
        if ($this->isPropietario() || $this->isEmpleado()) {
            return true;
        }

        // Verificar si tiene algún permiso de módulos administrativos (excluyendo dashboard)
        $permisos = $this->getPermisosSlugs();
        $modulosAdmin = ['productos', 'categorias', 'ventas', 'compras', 'clientes', 'proveedores',
                        'inventario', 'creditos', 'usuarios', 'roles', 'estadisticas'];

        foreach ($permisos as $permiso) {
            foreach ($modulosAdmin as $modulo) {
                if (str_starts_with($permiso, $modulo . '.')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario puede acceder al dashboard
     * Solo propietario y empleado pueden acceder al dashboard completo
     */
    public function puedeAccederDashboard()
    {
        return $this->isPropietario() || $this->isEmpleado();
    }
}
