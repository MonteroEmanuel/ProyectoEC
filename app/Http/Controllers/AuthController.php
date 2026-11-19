<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{
        // Registro: asignar rol 'Cliente' por defecto al registrarse
        public function register(Request $request)
        { 
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ], [
                'email.unique' => 'Este correo ya está registrado.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Asignar rol 'Cliente' al usuario recién creado
            $user->assignRole('client');

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Cuenta creada con éxito. Bienvenido ' . $user->name,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'roles' => $user->getRoleNames()], 201);
        }

        public function login(Request $request)
        {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Las credenciales proporcionadas son incorrectas.'],
                ]);
            }

            // Cuenta desactivada por un administrador
            if (!$user->activo) {
                return response()->json(['message' => 'Tu cuenta está desactivada. Contacta al administrador.'], 403);
            }

            // Borrar tokens existentes para el usuario antes de crear uno nuevo
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message'=>'Bienvenido ' . $user->name,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user, 'roles' => $user->getRoleNames()]);
        }


        // Cerrar sesión: revocar el token actual
        public function logout(Request $request)
        {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Sesión cerrada correctamente.']);
        }
}

        