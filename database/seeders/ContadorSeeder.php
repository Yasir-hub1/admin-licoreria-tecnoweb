<?php

namespace Database\Seeders;

use App\Models\Contador;
use Illuminate\Database\Seeder;
use App\Services\CounterService;

class ContadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $counterService = app(CounterService::class);

        // Crear contadores iniciales
        Contador::firstOrCreate(
            ['tipo' => 'venta'],
            [
                'prefijo' => 'V-',
                'valor_actual' => 0,
                'longitud' => 6,
                'descripcion' => 'Contador para números de venta'
            ]
        );

        Contador::firstOrCreate(
            ['tipo' => 'compra'],
            [
                'prefijo' => 'C-',
                'valor_actual' => 0,
                'longitud' => 6,
                'descripcion' => 'Contador para números de compra'
            ]
        );

        Contador::firstOrCreate(
            ['tipo' => 'producto'],
            [
                'prefijo' => 'PROD-',
                'valor_actual' => 0,
                'longitud' => 6,
                'descripcion' => 'Contador para códigos de producto'
            ]
        );

        // Sincronizar con valores existentes en la base de datos
        $this->command->info('Sincronizando contadores con valores existentes...');
        $resultados = $counterService->sincronizarDesdeBaseDatos();

        if (!empty($resultados)) {
            foreach ($resultados as $tipo => $valor) {
                $this->command->info("Contador {$tipo} sincronizado a valor: {$valor}");
            }
        } else {
            $this->command->info('No se encontraron valores existentes para sincronizar.');
        }

        $this->command->info('Contadores inicializados correctamente.');
    }
}
