<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UsuarioController extends BaseController
{
    public function index()
    {
        // El middleware ya verifica usuarios.listar, pero verificamos aquí también por seguridad
        $this->verificarPermiso('usuarios.listar');
        
        $usuarios = Usuario::with('rol')->orderBy('id', 'desc')->paginate(15);
        return Inertia::render('Admin/Usuarios/Index', [
            'usuarios' => $usuarios
        ]);
    }

    public function create()
    {
        // El middleware ya verifica usuarios.crear, pero verificamos aquí también por seguridad
        $this->verificarPermiso('usuarios.crear');
        
        $roles = Rol::all();
        return Inertia::render('Admin/Usuarios/Create', [
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        // El middleware ya verifica usuarios.crear, pero verificamos aquí también por seguridad
        $this->verificarPermiso('usuarios.crear');
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'email' => 'required|email|unique:usuario,email|unique:usuario,correo',
            'password' => 'required|string|min:8',
            'id_rol' => 'required|exists:rol,id',
            'estado' => 'required|in:activo,inactivo'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['correo'] = $validated['email']; // Sincronizar email y correo

        Usuario::create($validated);

        return redirect('/admin/usuarios')
            ->with('success', 'Usuario creado exitosamente');
    }

    public function show(string $id)
    {
        // El middleware ya verifica usuarios.ver, pero verificamos aquí también por seguridad
        $this->verificarPermiso('usuarios.ver');
        
        $usuario = Usuario::with('rol')->findOrFail($id);
        return Inertia::render('Admin/Usuarios/Show', [
            'usuario' => $usuario
        ]);
    }

    public function edit(string $id)
    {
        // El middleware ya verifica usuarios.editar, pero verificamos aquí también por seguridad
        $this->verificarPermiso('usuarios.editar');
        
        $usuario = Usuario::findOrFail($id);
        $roles = Rol::all();
        return Inertia::render('Admin/Usuarios/Edit', [
            'usuario' => $usuario,
            'roles' => $roles
        ]);
    }

    public function update(Request $request, string $id)
    {
        // El middleware ya verifica usuarios.editar, pero verificamos aquí también por seguridad
        $this->verificarPermiso('usuarios.editar');
        
        $usuario = Usuario::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'email' => 'required|email|unique:usuario,email,' . $id . '|unique:usuario,correo,' . $id,
            'password' => 'nullable|string|min:8',
            'id_rol' => 'required|exists:rol,id',
            'estado' => 'required|in:activo,inactivo'
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['correo'] = $validated['email'];
        $usuario->update($validated);

        return redirect('/admin/usuarios')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        // El middleware ya verifica usuarios.eliminar, pero verificamos aquí también por seguridad
        $this->verificarPermiso('usuarios.eliminar');
        
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();

        return redirect('/admin/usuarios')
            ->with('success', 'Usuario eliminado exitosamente');
    }
}
