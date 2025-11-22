<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
            'email' => 'required|string',
            'password' => 'required',
        ]);

        // Buscar usuario por email o correo
        $usuario = Usuario::where('email', $credentials['email'])
            ->orWhere('correo', $credentials['email'])
            ->first();

        if (!$usuario) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        // Verificar estado activo
        if ($usuario->estado !== 'activo') {
            return back()->withErrors([
                'email' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ])->onlyInput('email');
        }

        // Verificar password (puede estar en 'password' o 'clave')
        $passwordValid = false;
        if ($usuario->password && Hash::check($credentials['password'], $usuario->password)) {
            $passwordValid = true;
        } elseif ($usuario->clave && Hash::check($credentials['password'], $usuario->clave)) {
            $passwordValid = true;
            // Migrar clave a password si existe
            if (!$usuario->password) {
                $usuario->password = Hash::make($credentials['password']);
                $usuario->save();
            }
        }

        if ($passwordValid) {
            // Cargar rol y permisos antes de hacer login
            $usuario->load('rol.permisos');
            
            Auth::login($usuario, $request->boolean('remember'));
            $request->session()->regenerate();

            /** @var \App\Models\Usuario $user */
            $user = Auth::user();

            // Asegurar que el rol y permisos estén cargados
            if ($user && !$user->relationLoaded('rol')) {
                $user->load('rol.permisos');
            }

            // Redirigir según el rol y permisos
            if ($user) {
                // Si puede acceder al dashboard (propietario o empleado)
                if ($user->puedeAccederDashboard()) {
                    return redirect()->intended('/admin/dashboard');
                }

                // Si tiene acceso al admin pero no al dashboard (permisos personalizados)
                if ($user->tieneAccesoAdmin()) {
                    return redirect()->intended('/admin/bienvenida');
                }
            }

            // Si no tiene acceso al admin, redirigir al shop
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
            // Campos de usuario
            'nombre' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:usuario,email|unique:usuario,correo',
            'password' => 'required|string|min:8|confirmed',
            // Campos de cliente
            'ci' => 'required|string|max:20|unique:cliente,ci',
            'telefono' => 'nullable|string|max:15',
            'direccion' => 'nullable|string|max:20', // Límite de la tabla
        ]);

        // Obtener rol de cliente
        $rolCliente = Rol::where('nombre', 'cliente')->first();

        if (!$rolCliente) {
            return back()->withErrors(['error' => 'Rol de cliente no configurado en el sistema']);
        }

        try {
            DB::transaction(function () use ($request, $rolCliente) {
                // Crear usuario (sincronizar email y correo)
                $usuario = Usuario::create([
                    'nombre' => $request->nombre,
                    'email' => $request->email,
                    'correo' => strlen($request->email) <= 20 ? $request->email : substr($request->email, 0, 20),
                    'password' => Hash::make($request->password),
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
                    'credito_aprobado' => false, // Por defecto sin crédito
                    'limite_credito' => 0,
                ]);
            });

            // Buscar usuario creado para hacer login
            $usuario = Usuario::where('email', $request->email)->first();
            Auth::login($usuario);

            return redirect('/shop')->with('success', '¡Registro exitoso! Bienvenido');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Error al registrar: ' . $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
