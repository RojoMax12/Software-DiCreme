<?php

namespace App\Http\Controllers;

use App\Models\Usuario_dicreme;
use App\Models\Usuario_distribuidores;
use App\Services\JwtService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request, JwtService $jwtService): JsonResponse
    {
        $credentials = $request->validate([
            'tipo_usuario' => ['required', 'string'],
            'correo_electronico' => ['required', 'email'],
            'contrasena' => ['required', 'string'],
        ]);

        $user = $this->resolveUser($credentials['tipo_usuario'], $credentials['correo_electronico']);

        if (! $user || ! Hash::check($credentials['contrasena'], $user->contrasena)) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        return response()->json($jwtService->issueForUser($user, $this->tokenClaimsForUser($user, $credentials['tipo_usuario'])));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
            'claims' => $request->attributes->get('jwt_claims'),
        ]);
    }

    public function refresh(Request $request, JwtService $jwtService): JsonResponse
    {
        $claims = $request->attributes->get('jwt_claims');

        if (! $claims) {
            return response()->json([
                'message' => 'No se pudieron leer los datos del token actual.',
            ], 401);
        }

        $jwtService->blacklist($claims);

        return response()->json($jwtService->issueForUser($request->user()));
    }

    public function logout(Request $request, JwtService $jwtService): JsonResponse
    {
        $claims = $request->attributes->get('jwt_claims');

        if ($claims) {
            $jwtService->blacklist($claims);
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    private function resolveUser(string $tipoUsuario, string $correoElectronico): Authenticatable|null
    {
        $tipo = Str::lower(trim($tipoUsuario));

        return match (true) {
            Str::contains($tipo, 'dicreme') => Usuario_dicreme::where('correo_electronico', $correoElectronico)->first(),
            Str::contains($tipo, 'distribuidor') => Usuario_distribuidores::where('correo_electronico', $correoElectronico)->first(),
            default => null,
        };
    }

    private function tokenClaimsForUser(Authenticatable $user, string $tipoUsuario): array
    {
        return [
            'user_type' => Str::contains(Str::lower($tipoUsuario), 'dicreme') ? 'dicreme' : 'distribuidor',
            'name' => $user instanceof Usuario_dicreme ? $user->nombre_usuario : ($user instanceof Usuario_distribuidores ? $user->nombre_empresa : null),
            'email' => $user->correo_electronico ?? null,
        ];
    }
}