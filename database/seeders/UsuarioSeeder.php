<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Cliente;
use App\Models\Empleado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener roles
        $rolPropietario = Rol::where('nombre', 'propietario')->first();
        $rolEmpleado = Rol::where('nombre', 'empleado')->first();
        $rolCliente = Rol::where('nombre', 'cliente')->first();

        if (!$rolPropietario || !$rolEmpleado || !$rolCliente) {
            $this->command->error('Los roles no existen. Ejecuta primero RolSeeder.');
            return;
        }

        // Crear usuario propietario
        $propietario = Usuario::updateOrCreate(
            ['email' => 'admin@tecnoweb.com'],
            [
                'nombre' => 'Administrador',
                'correo' => 'admin@tecnoweb.com', // 18 caracteres - OK
                'email' => 'admin@tecnoweb.com',
                'password' => Hash::make('admin123'),
                'estado' => 'activo',
                'id_rol' => $rolPropietario->id,
            ]
        );

        // Crear usuario empleado (usar email más corto para correo)
        $empleado = Usuario::updateOrCreate(
            ['email' => 'empleado@tecnoweb.com'],
            [
                'nombre' => 'Empleado',
                'correo' => 'empleado@tecno.com', // 19 caracteres - OK
                'email' => 'empleado@tecnoweb.com',
                'password' => Hash::make('empleado123'),
                'estado' => 'activo',
                'id_rol' => $rolEmpleado->id,
            ]
        );

        // Crear registro de empleado
        if ($empleado) {
            Empleado::updateOrCreate(
                ['usuario_id' => $empleado->id],
                [
                    'ci' => '12345678',
                    'nombre' => 'Empleado',
                ]
            );
        }

        // Crear usuario cliente de prueba
        $cliente = Usuario::updateOrCreate(
            ['email' => 'cliente@test.com'],
            [
                'nombre' => 'Cliente Test',
                'correo' => 'cliente@test.com',
                'email' => 'cliente@test.com',
                'password' => Hash::make('cliente123'),
                'estado' => 'activo',
                'id_rol' => $rolCliente->id,
            ]
        );

        // Crear registro de cliente
        if ($cliente) {
            Cliente::updateOrCreate(
                ['usuario_id' => $cliente->id],
                [
                    'ci' => '87654321',
                    'nombre' => 'Cliente Test',
                    'telefono' => '1234567890',
                    'direccion' => 'Dir. Prueba',
                    'estado' => 'A',
                    'credito_aprobado' => true,
                    'limite_credito' => 10000.00,
                ]
            );
        }

        $this->command->info('Usuarios creados exitosamente:');
        $this->command->info('Propietario: admin@tecnoweb.com / admin123');
        $this->command->info('Empleado: empleado@tecnoweb.com / empleado123');
        $this->command->info('Cliente: cliente@test.com / cliente123');
    }
}

