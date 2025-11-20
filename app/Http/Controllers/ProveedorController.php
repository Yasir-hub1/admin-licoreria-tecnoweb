<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProveedorController extends BaseController
{
    public function index()
    {
        $this->verificarPermiso('proveedores.listar');

        $proveedores = Proveedor::with(['usuario', 'usuario.rol'])->withCount('compras')->orderBy('id', 'desc')->paginate(15);
        return Inertia::render('Admin/Proveedores/Index', [
            'proveedores' => $proveedores
        ]);
    }

    public function create()
    {
        $this->verificarPermiso('proveedores.crear');

        // Obtener usuarios sin proveedor asignado y roles disponibles
        $usuariosDisponibles = Usuario::whereDoesntHave('proveedor')->with('rol')->get();
        $roles = Rol::all();

        return Inertia::render('Admin/Proveedores/Create', [
            'usuariosDisponibles' => $usuariosDisponibles,
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $this->verificarPermiso('proveedores.crear');

        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'nit' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:50',
            'direccion' => 'nullable|string|max:20',
            // Opciones para usuario
            'crear_usuario' => 'nullable|boolean',
            'usuario_id' => 'nullable|exists:usuario,id',
            'usuario_email' => 'nullable|email|max:255|required_if:crear_usuario,true|unique:usuario,email|unique:usuario,correo',
            'usuario_password' => 'nullable|string|min:8|required_if:crear_usuario,true',
            'usuario_rol_id' => 'nullable|exists:rol,id|required_if:crear_usuario,true',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $usuarioId = null;

                // Si se solicita crear usuario nuevo
                if (!empty($validated['crear_usuario']) && $validated['crear_usuario']) {
                    $usuario = Usuario::create([
                        'nombre' => $validated['nombre'],
                        'email' => $validated['usuario_email'],
                        'correo' => strlen($validated['usuario_email']) <= 20 ? $validated['usuario_email'] : substr($validated['usuario_email'], 0, 20),
                        'password' => Hash::make($validated['usuario_password']),
                        'estado' => 'activo',
                        'id_rol' => $validated['usuario_rol_id'],
                    ]);
                    $usuarioId = $usuario->id;
                }
                // Si se asigna un usuario existente
                elseif (!empty($validated['usuario_id'])) {
                    // Verificar que el usuario no tenga otro proveedor
                    $usuarioExistente = Usuario::with('proveedor')->findOrFail($validated['usuario_id']);
                    if ($usuarioExistente->proveedor) {
                        throw new \Exception('El usuario seleccionado ya tiene un proveedor asignado.');
                    }
                    $usuarioId = $validated['usuario_id'];
                }

                // Crear proveedor
                $proveedorData = [
                    'nombre' => $validated['nombre'],
                    'telefono' => $validated['telefono'] ?? null,
                    'nit' => $validated['nit'] ?? null,
                    'correo' => $validated['correo'] ?? null,
                    'direccion' => $validated['direccion'] ?? null,
                ];

                if ($usuarioId) {
                    $proveedorData['usuario_id'] = $usuarioId;
                }

                Proveedor::create($proveedorData);
            });

            return redirect('/admin/proveedores')
                ->with('success', 'Proveedor creado exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al crear proveedor: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $this->verificarPermiso('proveedores.ver');

        $proveedor = Proveedor::with(['compras', 'usuario.rol'])->findOrFail($id);
        return Inertia::render('Admin/Proveedores/Show', [
            'proveedor' => $proveedor
        ]);
    }

    public function edit(string $id)
    {
        $this->verificarPermiso('proveedores.editar');

        $proveedor = Proveedor::with('usuario.rol')->findOrFail($id);

        // Obtener usuarios sin proveedor asignado (o el usuario actual del proveedor)
        $usuariosDisponibles = Usuario::where(function($query) use ($proveedor) {
            $query->whereDoesntHave('proveedor')
                  ->orWhere('id', $proveedor->usuario_id);
        })->with('rol')->get();

        $roles = Rol::all();

        return Inertia::render('Admin/Proveedores/Edit', [
            'proveedor' => $proveedor,
            'usuariosDisponibles' => $usuariosDisponibles,
            'roles' => $roles
        ]);
    }

    public function update(Request $request, string $id)
    {
        $this->verificarPermiso('proveedores.editar');

        $proveedor = Proveedor::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'telefono' => 'nullable|string|max:20',
            'nit' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:20',
            'direccion' => 'nullable|string|max:20',
            // Opciones para usuario
            'crear_usuario' => 'nullable|boolean',
            'usuario_id' => 'nullable|exists:usuario,id',
            'usuario_email' => 'nullable|email|max:255|required_if:crear_usuario,true|unique:usuario,email,' . ($proveedor->usuario_id ?? 'NULL') . ',id|unique:usuario,correo,' . ($proveedor->usuario_id ?? 'NULL') . ',id',
            'usuario_password' => 'nullable|string|min:8|required_if:crear_usuario,true',
            'usuario_rol_id' => 'nullable|exists:rol,id|required_if:crear_usuario,true',
            'eliminar_usuario' => 'nullable|boolean',
        ]);

        try {
            DB::transaction(function () use ($validated, $proveedor) {
                $usuarioId = $proveedor->usuario_id;

                // Si se solicita eliminar usuario
                if (!empty($validated['eliminar_usuario']) && $validated['eliminar_usuario']) {
                    $usuarioId = null;
                }
                // Si se solicita crear usuario nuevo
                elseif (!empty($validated['crear_usuario']) && $validated['crear_usuario']) {
                    // Si ya tiene usuario, no crear otro (mejor mostrar error)
                    if ($proveedor->usuario_id) {
                        throw new \Exception('El proveedor ya tiene un usuario asignado. Puede asignar uno diferente o eliminar el actual primero.');
                    }

                    $usuario = Usuario::create([
                        'nombre' => $validated['nombre'],
                        'email' => $validated['usuario_email'],
                        'correo' => strlen($validated['usuario_email']) <= 20 ? $validated['usuario_email'] : substr($validated['usuario_email'], 0, 20),
                        'password' => Hash::make($validated['usuario_password']),
                        'estado' => 'activo',
                        'id_rol' => $validated['usuario_rol_id'],
                    ]);
                    $usuarioId = $usuario->id;
                }
                // Si se asigna un usuario existente diferente
                elseif (!empty($validated['usuario_id']) && $validated['usuario_id'] != $proveedor->usuario_id) {
                    // Verificar que el usuario no tenga otro proveedor
                    $usuarioExistente = Usuario::with('proveedor')->findOrFail($validated['usuario_id']);
                    if ($usuarioExistente->proveedor && $usuarioExistente->proveedor->id != $proveedor->id) {
                        throw new \Exception('El usuario seleccionado ya tiene otro proveedor asignado.');
                    }
                    $usuarioId = $validated['usuario_id'];
                }

                // Actualizar proveedor
                $proveedorData = [
                    'nombre' => $validated['nombre'],
                    'telefono' => $validated['telefono'] ?? null,
                    'nit' => $validated['nit'] ?? null,
                    'correo' => $validated['correo'] ?? null,
                    'direccion' => $validated['direccion'] ?? null,
                ];

                if ($usuarioId !== $proveedor->usuario_id) {
                    $proveedorData['usuario_id'] = $usuarioId;
                }

                $proveedor->update($proveedorData);
            });

            return redirect('/admin/proveedores')
                ->with('success', 'Proveedor actualizado exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al actualizar proveedor: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $this->verificarPermiso('proveedores.eliminar');

        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();

        return redirect('/admin/proveedores')
            ->with('success', 'Proveedor eliminado exitosamente');
    }
}
