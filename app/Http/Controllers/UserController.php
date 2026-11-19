<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Listado de usuarios con su rol y cantidad de pedidos
    public function index(): JsonResponse
    {
        $usuarios = User::with('roles:id,name')
            ->withCount('pedidos')
            ->orderByDesc('id')
            ->get()
            ->map(fn (User $u) => $this->formatear($u));

        return response()->json($usuarios);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|min:3|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'rol'      => 'required|in:admin,client',
        ], $this->mensajes());

        $usuario = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'activo'   => true,
        ]);
        $usuario->assignRole($validated['rol']);

        return response()->json([
            'message' => 'Usuario creado con éxito',
            'data'    => $this->formatear($usuario->load('roles:id,name')->loadCount('pedidos')),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $usuario = User::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validated = $request->validate([
            'name'     => 'required|string|min:3|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => 'nullable|string|min:8',
            'rol'      => 'required|in:admin,client',
            'activo'   => 'required|boolean',
        ], $this->mensajes());

        $cambiaRol = !$usuario->hasRole($validated['rol']);
        $seDesactiva = $usuario->activo && !$validated['activo'];

        // Reglas de seguridad: el admin no se quita el acceso a sí mismo
        if ($usuario->id === $request->user()->id && ($cambiaRol || $seDesactiva)) {
            return response()->json([
                'message' => 'No puedes cambiar tu propio rol ni desactivar tu propia cuenta.'
            ], 400);
        }
        // ...y siempre debe quedar al menos un admin activo
        if ($usuario->hasRole('admin') && ($cambiaRol || $seDesactiva) && $this->esUltimoAdmin($usuario)) {
            return response()->json([
                'message' => 'Debe existir al menos un administrador activo.'
            ], 400);
        }

        $usuario->fill([
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'activo' => $validated['activo'],
        ]);
        if (!empty($validated['password'])) {
            $usuario->password = $validated['password'];
        }
        $usuario->save();

        if ($cambiaRol) {
            $usuario->syncRoles([$validated['rol']]);
        }

        // Un usuario desactivado pierde sus sesiones abiertas
        if ($seDesactiva) {
            $usuario->tokens()->delete();
        }

        return response()->json([
            'message' => 'Usuario actualizado con éxito',
            'data'    => $this->formatear($usuario->load('roles:id,name')->loadCount('pedidos')),
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $usuario = User::withCount('pedidos')->find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        if ($usuario->id === $request->user()->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta.'], 400);
        }
        if ($usuario->hasRole('admin') && $this->esUltimoAdmin($usuario)) {
            return response()->json(['message' => 'Debe existir al menos un administrador activo.'], 400);
        }
        // Evita perder el historial de compras
        if ($usuario->pedidos_count > 0) {
            return response()->json([
                'message' => 'No se puede eliminar un usuario con pedidos. Puedes desactivarlo.'
            ], 400);
        }

        $usuario->tokens()->delete();
        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado'], 200);
    }

    // ¿Es el único administrador activo?
    private function esUltimoAdmin(User $usuario): bool
    {
        return !User::role('admin')
            ->where('activo', true)
            ->where('id', '!=', $usuario->id)
            ->exists();
    }

    private function formatear(User $u): array
    {
        return [
            'id'            => $u->id,
            'name'          => $u->name,
            'email'         => $u->email,
            'activo'        => (bool) $u->activo,
            'rol'           => $u->roles->first()?->name ?? 'client',
            'pedidos_count' => $u->pedidos_count ?? 0,
            'created_at'    => $u->created_at,
        ];
    }

    private function mensajes(): array
    {
        return [
            'email.unique' => 'Este correo ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
