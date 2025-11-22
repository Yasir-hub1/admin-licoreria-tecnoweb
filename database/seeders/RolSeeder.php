<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'propietario',
                'descripcion' => 'Acceso completo admin'
            ],
            [
                'nombre' => 'cliente',
                'descripcion' => 'Cliente ecommerce'
            ]
        ];

        foreach ($roles as $rol) {
            $rolModel = Rol::updateOrCreate(
                ['nombre' => $rol['nombre']],
                $rol
            );

            // Si es el rol propietario, asignarle todos los permisos
            if ($rolModel->nombre === 'propietario') {
                $todosLosPermisos = Permiso::all();
                $rolModel->permisos()->sync($todosLosPermisos->pluck('id')->toArray());
                $this->command->info('✅ Todos los permisos asignados al rol propietario (' . $todosLosPermisos->count() . ' permisos).');
            }
        }

        $this->command->info('✅ Roles creados/actualizados exitosamente.');
    }
}
