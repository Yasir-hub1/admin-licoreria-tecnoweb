# Guía de Implementación - E-Commerce Licorería

## Estado Actual del Proyecto

### ✅ Completado

1. **Estructura Base**
   - 14 Migraciones creadas (tablas del sistema)
   - 14 Modelos creados con relaciones
   - 14 Controladores resource creados
   - Inertia.js + Vue 3 configurado
   - Vite configurado con plugin Vue

2. **Configuración Inicial**
   - Laravel Breeze instalado
   - Middleware Inertia configurado
   - Template raíz Blade creado
   - Rutas base definidas

3. **Modelos y Migraciones**
   - Usuario adaptado como Authenticatable
   - Migración de autenticación creada
   - Helpers de roles en Usuario

### 🚧 Pendiente de Implementación

---

## PASO 1: Configurar Base de Datos y Ejecutar Migraciones

### 1.1 Configurar .env

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tecnoweb_licoreria
DB_USERNAME=root
DB_PASSWORD=tu_password
```

O para PostgreSQL:
```bash
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tecnoweb_licoreria
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

### 1.2 Crear Base de Datos

```sql
CREATE DATABASE tecnoweb_licoreria;
```

### 1.3 Ejecutar Migraciones

```bash
php artisan migrate
```

---

## PASO 2: Crear Seeders (Datos Iniciales)

### 2.1 Seeder de Roles

```bash
php artisan make:seeder RolSeeder
```

**database/seeders/RolSeeder.php:**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run()
    {
        DB::table('rol')->insert([
            [
                'nombre' => 'propietario',
                'descripcion' => 'Administrador del sistema'
            ],
            [
                'nombre' => 'vendedor',
                'descripcion' => 'Empleado vendedor'
            ],
            [
                'nombre' => 'cliente',
                'descripcion' => 'Cliente del e-commerce'
            ]
        ]);
    }
}
```

### 2.2 Seeder de Usuario Admin

```bash
php artisan make:seeder AdminUserSeeder
```

**database/seeders/AdminUserSeeder.php:**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $rolPropietario = Rol::where('nombre', 'propietario')->first();

        Usuario::create([
            'nombre' => 'Administrador',
            'email' => 'admin@licoreria.com',
            'password' => Hash::make('password123'),
            'correo' => 'admin@licoreria.com',
            'estado' => 'activo',
            'id_rol' => $rolPropietario->id
        ]);
    }
}
```

### 2.3 Seeder de Categorías

```bash
php artisan make:seeder CategoriaSeeder
```

**database/seeders/CategoriaSeeder.php:**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run()
    {
        $categorias = [
            'Vinos',
            'Cervezas',
            'Whiskys',
            'Rones',
            'Vodkas',
            'Tequilas',
            'Licores',
            'Espumantes',
            'Ginebras',
            'Cocktails'
        ];

        foreach ($categorias as $cat) {
            Categoria::create(['nombre' => $cat]);
        }
    }
}
```

### 2.4 Actualizar DatabaseSeeder

**database/seeders/DatabaseSeeder.php:**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolSeeder::class,
            AdminUserSeeder::class,
            CategoriaSeeder::class,
        ]);
    }
}
```

### 2.5 Ejecutar Seeders

```bash
php artisan db:seed
```

---

## PASO 3: Crear Middleware de Roles

### 3.1 Crear Middleware

```bash
php artisan make:middleware RoleMiddleware
```

**app/Http/Middleware/RoleMiddleware.php:**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permisos para acceder a esta página');
    }
}
```

### 3.2 Registrar Middleware

**bootstrap/app.php:**
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);

    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
```

---

## PASO 4: Configurar Autenticación en config/auth.php

**config/auth.php:**
```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\Usuario::class, // Cambiar de User a Usuario
    ],
],
```

---

## PASO 5: Crear Controladores de Autenticación

### 5.1 AuthController

```bash
php artisan make:controller AuthController
```

**app/Http/Controllers/AuthController.php:**
```php
<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirigir según el rol
            if ($user->isPropietario() || $user->isVendedor()) {
                return redirect()->intended('/admin/dashboard');
            }

            return redirect()->intended('/shop');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:usuario',
            'password' => 'required|string|min:8|confirmed',
            'ci' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:15',
            'direccion' => 'nullable|string|max:100',
        ]);

        // Obtener rol de cliente
        $rolCliente = Rol::where('nombre', 'cliente')->first();

        // Crear usuario
        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'correo' => $request->email,
            'estado' => 'activo',
            'id_rol' => $rolCliente->id,
        ]);

        // Crear registro de cliente
        Cliente::create([
            'ci' => $request->ci,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'estado' => 'A', // Activo
            'usuario_id' => $usuario->id,
        ]);

        Auth::login($usuario);

        return redirect()->route('shop.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
```

---

## PASO 6: Actualizar Modelo Cliente

Agregar relación con usuario:

**app/Models/Cliente.php:**
```php
public function usuario()
{
    return $this->belongsTo(Usuario::class, 'usuario_id');
}
```

Y agregar migración:

```bash
php artisan make:migration add_usuario_id_to_cliente_table
```

**Contenido de la migración:**
```php
public function up()
{
    Schema::table('cliente', function (Blueprint $table) {
        $table->foreignId('usuario_id')->nullable()->after('id')->constrained('usuario');
        $table->boolean('credito_aprobado')->default(false)->after('estado');
        $table->decimal('limite_credito', 10, 2)->default(0)->after('credito_aprobado');
    });
}

public function down()
{
    Schema::table('cliente', function (Blueprint $table) {
        $table->dropForeign(['usuario_id']);
        $table->dropColumn(['usuario_id', 'credito_aprobado', 'limite_credito']);
    });
}
```

---

## PASO 7: Crear Páginas de Autenticación (Vue)

### 7.1 Login.vue

**resources/js/Pages/Auth/Login.vue:**
```vue
<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-center mb-6">Iniciar Sesión</h2>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Email
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.email" class="text-red-500 text-sm">
                        {{ form.errors.email }}
                    </span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Contraseña
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.password" class="text-red-500 text-sm">
                        {{ form.errors.password }}
                    </span>
                </div>

                <div class="mb-4 flex items-center">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="mr-2"
                    />
                    <label class="text-sm text-gray-700">Recordarme</label>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50"
                >
                    Iniciar Sesión
                </button>

                <div class="mt-4 text-center">
                    <Link :href="route('register')" class="text-blue-500 hover:underline">
                        ¿No tienes cuenta? Regístrate
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'));
};

defineOptions({
    layout: null
});
</script>
```

### 7.2 Register.vue

**resources/js/Pages/Auth/Register.vue:**
```vue
<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-center mb-6">Registrarse</h2>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nombre Completo</label>
                    <input
                        v-model="form.nombre"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.nombre" class="text-red-500 text-sm">{{ form.errors.nombre }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">CI</label>
                    <input
                        v-model="form.ci"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.ci" class="text-red-500 text-sm">{{ form.errors.ci }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Teléfono</label>
                    <input
                        v-model="form.telefono"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                    />
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Dirección</label>
                    <input
                        v-model="form.direccion"
                        type="text"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                    />
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Contraseña</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                    <span v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Confirmar Contraseña</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                        required
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50"
                >
                    Registrarse
                </button>

                <div class="mt-4 text-center">
                    <Link :href="route('login')" class="text-blue-500 hover:underline">
                        ¿Ya tienes cuenta? Inicia sesión
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    nombre: '',
    ci: '',
    email: '',
    telefono: '',
    direccion: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'));
};

defineOptions({
    layout: null
});
</script>
```

---

## PASO 8: Actualizar Rutas

**routes/web.php:**
```php
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;

// Rutas públicas
Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas autenticadas - CLIENTES
Route::middleware(['auth', 'role:cliente'])->group(function () {
    Route::get('/shop', function () {
        return Inertia::render('Shop/Index');
    })->name('shop.index');
});

// Rutas autenticadas - ADMIN (Propietario/Vendedor)
Route::middleware(['auth', 'role:propietario,vendedor'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    // Resources (ya definidos anteriormente)
    // ...
});
```

---

## PASO 9: Próximos Pasos

1. Ejecutar migraciones y seeders
2. Probar login/register
3. Crear catálogo de productos (Shop)
4. Implementar carrito de compras
5. Crear proceso de checkout
6. Implementar sistema de créditos
7. Crear CRUDs admin
8. Implementar compras a proveedores
9. Sistema de inventario
10. Reportes

---

## Comandos para Continuar

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# Compilar assets
npm run dev

# Iniciar servidor
php artisan serve
```

**Acceso inicial:**
- URL: http://localhost:8000
- Admin: admin@licoreria.com / password123

---

Este documento es la fase 1. Continúa con los siguientes pasos según necesites.
