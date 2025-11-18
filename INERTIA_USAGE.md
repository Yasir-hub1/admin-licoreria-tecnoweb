# Guía de Uso de Inertia.js en el Proyecto

## Introducción

Este proyecto utiliza **Inertia.js** como puente entre Laravel y Vue 3, permitiendo construir aplicaciones monolíticas modernas con experiencia de SPA (Single Page Application) sin necesidad de crear una API REST.

## Índice

1. [Arquitectura del Proyecto](#arquitectura-del-proyecto)
2. [Estructura de Archivos](#estructura-de-archivos)
3. [Configuración](#configuración)
4. [Creación de Páginas](#creación-de-páginas)
5. [Uso de Componentes](#uso-de-componentes)
6. [Layouts](#layouts)
7. [Navegación](#navegación)
8. [Envío de Datos desde el Backend](#envío-de-datos-desde-el-backend)
9. [Formularios](#formularios)
10. [Rutas](#rutas)
11. [Comandos Útiles](#comandos-útiles)

---

## Arquitectura del Proyecto

```
Laravel Backend (PHP)
        ↓
    Inertia.js
        ↓
  Vue 3 Frontend (JavaScript)
```

**Inertia.js** elimina la necesidad de crear APIs REST separadas. El flujo es:
1. Laravel renderiza una respuesta Inertia
2. Inertia envía datos JSON al frontend
3. Vue renderiza el componente correspondiente

---

## Estructura de Archivos

```
resources/
├── js/
│   ├── app.js                    # Punto de entrada de Vue
│   ├── Components/               # Componentes reutilizables
│   │   └── DashboardCard.vue
│   ├── Layouts/                  # Layouts de la aplicación
│   │   └── MainLayout.vue
│   └── Pages/                    # Páginas de la aplicación
│       ├── Welcome.vue
│       ├── Dashboard.vue
│       └── Productos/
│           └── Index.vue
├── views/
│   └── app.blade.php            # Template raíz de Inertia
└── css/
    └── app.css                  # Estilos globales

routes/
└── web.php                      # Definición de rutas

app/
└── Http/
    ├── Controllers/             # Controladores
    └── Middleware/
        └── HandleInertiaRequests.php
```

---

## Configuración

### 1. Archivos Principales Configurados

#### `vite.config.js`
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});
```

#### `resources/js/app.js`
```javascript
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

createInertiaApp({
    title: (title) => title ? `${title} - Sistema Tecnoweb` : 'Sistema Tecnoweb',
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
```

#### `resources/views/app.blade.php`
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
```

#### `bootstrap/app.php`
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);
})
```

---

## Creación de Páginas

### Desde el Controlador (Laravel)

```php
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index()
    {
        return Inertia::render('Productos/Index', [
            'productos' => Producto::with('categoria')->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('Productos/Create', [
            'categorias' => Categoria::all()
        ]);
    }

    public function edit(Producto $producto)
    {
        return Inertia::render('Productos/Edit', [
            'producto' => $producto,
            'categorias' => Categoria::all()
        ]);
    }
}
```

### Componente Vue de la Página

```vue
<!-- resources/js/Pages/Productos/Index.vue -->
<template>
    <MainLayout>
        <h1>Lista de Productos</h1>
        <div v-for="producto in productos.data" :key="producto.id">
            {{ producto.nombre }}
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';

// Recibir props desde Laravel
defineProps({
    productos: Object,
});
</script>
```

---

## Uso de Componentes

### Componente Reutilizable

```vue
<!-- resources/js/Components/Button.vue -->
<template>
    <button
        :type="type"
        :class="classes"
        @click="$emit('click')"
    >
        <slot />
    </button>
</template>

<script setup>
defineProps({
    type: {
        type: String,
        default: 'button'
    },
    variant: {
        type: String,
        default: 'primary'
    }
});

const classes = computed(() => {
    return variant === 'primary'
        ? 'bg-blue-500 text-white'
        : 'bg-gray-500 text-white';
});
</script>
```

### Uso del Componente

```vue
<template>
    <Button variant="primary" @click="guardar">
        Guardar
    </Button>
</template>

<script setup>
import Button from '@/Components/Button.vue';

const guardar = () => {
    // lógica
};
</script>
```

---

## Layouts

### Layout Principal

```vue
<!-- resources/js/Layouts/MainLayout.vue -->
<template>
    <div class="min-h-screen bg-gray-100">
        <nav>
            <Link :href="route('dashboard')">Dashboard</Link>
            <Link :href="route('productos.index')">Productos</Link>
        </nav>

        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
```

### Usar Layout en Página

**Opción 1: Dentro del componente**
```vue
<template>
    <MainLayout>
        <h1>Contenido</h1>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
</script>
```

**Opción 2: Definir layout por defecto**
```vue
<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({
    layout: MainLayout
});
</script>
```

---

## Navegación

### Usando el Componente Link

```vue
<template>
    <!-- Navegación básica -->
    <Link :href="route('productos.index')">Ver Productos</Link>

    <!-- Con clases CSS -->
    <Link
        :href="route('productos.create')"
        class="btn btn-primary"
    >
        Nuevo Producto
    </Link>

    <!-- Con parámetros -->
    <Link :href="route('productos.edit', producto.id)">
        Editar
    </Link>

    <!-- Preservar estado del scroll -->
    <Link
        :href="route('productos.index')"
        preserve-scroll
    >
        Productos
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
```

### Navegación Programática

```vue
<script setup>
import { router } from '@inertiajs/vue3';

const irAProductos = () => {
    router.visit(route('productos.index'));
};

const eliminarProducto = (id) => {
    router.delete(route('productos.destroy', id), {
        onSuccess: () => {
            alert('Producto eliminado');
        }
    });
};
</script>
```

---

## Envío de Datos desde el Backend

### Controlador Laravel

```php
public function index()
{
    return Inertia::render('Productos/Index', [
        'productos' => Producto::with('categoria')->paginate(10),
        'categorias' => Categoria::all(),
        'filtros' => request()->only(['search', 'categoria'])
    ]);
}
```

### Recibir en Vue

```vue
<script setup>
const props = defineProps({
    productos: Object,
    categorias: Array,
    filtros: Object
});

// Usar los datos
console.log(props.productos.data);
</script>
```

---

## Formularios

### Formulario Básico

```vue
<template>
    <form @submit.prevent="submit">
        <input v-model="form.nombre" type="text" />
        <input v-model="form.precio" type="number" />

        <div v-if="form.errors.nombre">
            {{ form.errors.nombre }}
        </div>

        <button type="submit" :disabled="form.processing">
            Guardar
        </button>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    nombre: '',
    precio: 0,
    categoria_id: null
});

const submit = () => {
    form.post(route('productos.store'), {
        onSuccess: () => {
            form.reset();
        },
        onError: (errors) => {
            console.log('Errores:', errors);
        }
    });
};
</script>
```

### Actualizar Datos (PUT/PATCH)

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    producto: Object
});

const form = useForm({
    nombre: props.producto.nombre,
    precio: props.producto.precio,
});

const update = () => {
    form.put(route('productos.update', props.producto.id));
};
</script>
```

### Eliminar (DELETE)

```vue
<script setup>
import { router } from '@inertiajs/vue3';

const eliminar = (id) => {
    if (confirm('¿Está seguro?')) {
        router.delete(route('productos.destroy', id));
    }
};
</script>
```

---

## Rutas

### Definir Rutas en Laravel

```php
// routes/web.php
use Inertia\Inertia;

// Ruta simple
Route::get('/', function () {
    return Inertia::render('Welcome');
});

// Rutas de recursos
Route::resource('productos', ProductoController::class);
Route::resource('clientes', ClienteController::class);
Route::resource('ventas', VentaController::class);

// Rutas con nombres
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');
```

### Usar Rutas en Vue

```vue
<template>
    <!-- Helper route() -->
    <Link :href="route('productos.index')">Productos</Link>
    <Link :href="route('productos.show', 1)">Ver Producto #1</Link>
    <Link :href="route('productos.edit', producto.id)">Editar</Link>
</template>
```

### Verificar Ruta Actual

```vue
<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const esRutaActual = (nombre) => {
    return page.url.startsWith(route(nombre));
};
</script>

<template>
    <Link
        :class="{ active: esRutaActual('productos.index') }"
        :href="route('productos.index')"
    >
        Productos
    </Link>
</template>
```

---

## Comandos Útiles

### Desarrollo

```bash
# Iniciar servidor de desarrollo
npm run dev

# Compilar assets para producción
npm run build

# Servidor Laravel
php artisan serve
```

### Crear Componentes

```bash
# No hay comando artisan para componentes Vue
# Crear manualmente en resources/js/Components/
```

### Ver Rutas

```bash
# Listar todas las rutas
php artisan route:list

# Filtrar rutas de Inertia
php artisan route:list | grep Inertia
```

### Cache

```bash
# Limpiar cache de rutas
php artisan route:clear

# Limpiar cache de configuración
php artisan config:clear
```

---

## Ejemplos Prácticos del Proyecto

### 1. Lista de Productos con Paginación

**Controlador:**
```php
public function index()
{
    return Inertia::render('Productos/Index', [
        'productos' => Producto::with('categoria')
            ->paginate(10)
    ]);
}
```

**Vista Vue:**
```vue
<template>
    <MainLayout>
        <table>
            <tr v-for="producto in productos.data" :key="producto.id">
                <td>{{ producto.nombre }}</td>
                <td>{{ producto.precio }}</td>
                <td>
                    <Link :href="route('productos.edit', producto.id)">
                        Editar
                    </Link>
                </td>
            </tr>
        </table>

        <!-- Paginación -->
        <div>
            <Link
                v-for="link in productos.links"
                :key="link.label"
                :href="link.url"
                v-html="link.label"
            />
        </div>
    </MainLayout>
</template>
```

### 2. Formulario de Creación

**Controlador:**
```php
public function create()
{
    return Inertia::render('Productos/Create', [
        'categorias' => Categoria::all()
    ]);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'nombre' => 'required',
        'precio' => 'required|numeric',
        'categoria_id' => 'required|exists:categoria,id'
    ]);

    Producto::create($validated);

    return redirect()->route('productos.index')
        ->with('success', 'Producto creado exitosamente');
}
```

**Vista Vue:**
```vue
<template>
    <MainLayout>
        <form @submit.prevent="submit">
            <input v-model="form.nombre" placeholder="Nombre" />
            <span v-if="form.errors.nombre">{{ form.errors.nombre }}</span>

            <input v-model="form.precio" type="number" placeholder="Precio" />
            <span v-if="form.errors.precio">{{ form.errors.precio }}</span>

            <select v-model="form.categoria_id">
                <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                    {{ cat.nombre }}
                </option>
            </select>

            <button type="submit" :disabled="form.processing">
                Guardar
            </button>
        </form>
    </MainLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    categorias: Array
});

const form = useForm({
    nombre: '',
    precio: 0,
    categoria_id: null
});

const submit = () => {
    form.post(route('productos.store'));
};
</script>
```

---

## Ventajas de Usar Inertia.js

✅ **Sin necesidad de API REST**: Comunicación directa entre Laravel y Vue
✅ **Routing del lado del servidor**: Rutas controladas por Laravel
✅ **SEO amigable**: Renderizado del lado del servidor
✅ **Experiencia SPA**: Sin recargas de página
✅ **Validación de formularios**: Integrada con Laravel
✅ **Compartir datos globales**: Via middleware HandleInertiaRequests

---

## Recursos Adicionales

- [Documentación oficial de Inertia.js](https://inertiajs.com/)
- [Documentación de Vue 3](https://vuejs.org/)
- [Laravel Inertia](https://github.com/inertiajs/inertia-laravel)
- [Inertia Vue 3 Adapter](https://github.com/inertiajs/inertia-vue3)

---

## Solución de Problemas Comunes

### Error: "Inertia response does not match"
```bash
# Limpiar cache de navegador y recompilar assets
npm run dev
```

### Error: "Target container is not a DOM element"
```bash
# Verificar que @inertia esté en app.blade.php
# Verificar que el div tenga id="app"
```

### Componente no se encuentra
```bash
# Verificar la ruta en resolve de app.js
# Verificar que el archivo .vue exista en Pages/
```

---

**Fecha de creación:** 2025-11-16
**Proyecto:** Sistema Tecnoweb - Laravel + Inertia.js + Vue 3
