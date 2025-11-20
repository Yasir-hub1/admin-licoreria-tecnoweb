#!/bin/bash
cd /Users/dev/Documents/proyectos-externos/tecnoweb-herika

echo "=== Ejecutando prueba de pasarela de pagos ==="
echo ""

# Ejecutar migraciones primero
echo "1. Verificando migraciones..."
php artisan migrate --force

echo ""
echo "2. Ejecutando prueba..."
php test_payment_direct.php

echo ""
echo "=== Prueba completada ==="

