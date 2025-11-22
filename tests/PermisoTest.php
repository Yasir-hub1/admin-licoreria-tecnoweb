<?php

namespace Tests;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermisoTest extends TestCase
{
    use RefreshDatabase;

    public function test_propietario_tiene_todos_los_permisos()
    {
        // Crear permisos
        $this->artisan('db:seed', ['--class' => 'PermisoSeeder']);
        
        // Crear rol propietario
        $this->artisan('db:seed', ['--class' => 'RolSeeder']);
        
        $rolPropietario = Rol::where('nombre', 'propietario')->first();
        $this->assertNotNull($rolPropietario);
        
        // Verificar que tiene todos los permisos
        $totalPermisos = Permiso::count();
        $permisosRol = $rolPropietario->permisos()->count();
        
        $this->assertEquals($totalPermisos, $permisosRol, 'El rol propietario debe tener todos los permisos');
    }

    public function test_usuario_con_permisos_limitados()
    {
        // Crear permisos
        $this->artisan('db:seed', ['--class' => 'PermisoSeeder']);
        
        // Crear rol de prueba
        $rolTest = Rol::create([
            'nombre' => 'test_rol',
            'descripcion' => 'Rol de prueba'
        ]);
        
        // Asignar solo algunos permisos
        $permisosLimitados = Permiso::whereIn('slug', [
            'categorias.listar',
            'categorias.ver',
            'ventas.listar'
        ])->pluck('id')->toArray();
        
        $rolTest->permisos()->sync($permisosLimitados);
        
        // Crear usuario
        $usuario = Usuario::create([
            'nombre' => 'Test User',
            'email' => 'test@test.com',
            'correo' => 'test@test.com',
            'password' => bcrypt('password'),
            'estado' => 'activo',
            'id_rol' => $rolTest->id,
        ]);
        
        // Verificar permisos
        $this->assertTrue($usuario->tienePermiso('categorias.listar'));
        $this->assertTrue($usuario->tienePermiso('categorias.ver'));
        $this->assertTrue($usuario->tienePermiso('ventas.listar'));
        $this->assertFalse($usuario->tienePermiso('productos.listar'));
        $this->assertFalse($usuario->tienePermiso('clientes.listar'));
        
        // Verificar getPermisosSlugs
        $permisos = $usuario->getPermisosSlugs();
        $this->assertCount(3, $permisos);
        $this->assertContains('categorias.listar', $permisos);
        $this->assertContains('categorias.ver', $permisos);
        $this->assertContains('ventas.listar', $permisos);
    }
}

