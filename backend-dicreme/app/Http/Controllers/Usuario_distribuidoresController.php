<?php

namespace App\Http\Controllers;

use App\Services\Usuario_distribuidoresServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Rules\RutChilenoRule;
use App\Rules\TelefonoChilenoRule;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class Usuario_distribuidoresController extends Controller
{
    protected $usuarioDistribuidoresService;

    public function __construct(Usuario_distribuidoresServices $usuarioDistribuidoresService)
    {
        $this->usuarioDistribuidoresService = $usuarioDistribuidoresService;
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'correo_electronico' => 'required|email:rfc,dns|max:255|unique:usuarios_distribuidores,correo_electronico',
            'telefono'           => ['required', new TelefonoChilenoRule],
            'direccion'          => 'required|string|max:255',
            'comuna'             => 'required|string|max:255',
            'contrasena'         => [
                'required',
                'string',
                'min:8',              // Mínimo 8 caracteres
                'confirmed',          // Requiere un campo 'contrasena_confirmation'
                'regex:/[a-z]/',      // Al menos una minúscula
                'regex:/[A-Z]/',      // Al menos una mayúscula
                'regex:/[0-9]/',      // Al menos un número
                'regex:/[@$!%*#?&]/', // Al menos un carácter especial
            ],
            'rut_empresa'        => ['required', new RutChilenoRule, 'unique:usuarios_distribuidores,rut_empresa'],
            'nombre_empresa'     => 'required|string|max:255',
        ]);

        try {
            
            unset($data['contrasena_confirmation']);
            $data = $this->normalizeUserData($data);

            Log::info('Datos limpios recibidos:', $data);

            $usuario = $this->usuarioDistribuidoresService->createUsuarioDistribuidor($data);

            \App\Models\HistorialMovimiento::registrar(
                'distribuidor',
                $usuario->id,
                'creacion_distribuidor',
                "Se registró el distribuidor '{$usuario->nombre_empresa}' (RUT: {$usuario->rut_empresa})",
                null
            );

            return response()->json(['status' => 'success', 'data' => $usuario, 'message' => 'Usuario creado'], 201);
        } catch (Exception $e) {
            return $this->errorResponse('Error al crear usuario', $e);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'correo_electronico' => ['sometimes', 'required', 'email:rfc,dns', Rule::unique('usuarios_distribuidores', 'correo_electronico')->ignore($id)],
            'telefono'           => ['sometimes', 'required', new TelefonoChilenoRule],
            'direccion'          => 'sometimes|required|string|max:255',
            'comuna'             => 'sometimes|required|string|max:255',
            'id_rol'             => 'nullable|integer|exists:rol,id',
            'contrasena'         => 'nullable|string|min:6',
            'rut_empresa'        => ['sometimes', 'required', new RutChilenoRule, Rule::unique('usuarios_distribuidores', 'rut_empresa')->ignore($id)],
            'nombre_empresa'     => 'sometimes|required|string|max:255',
            'foto_perfil'        => 'nullable|file|mimes:jpeg,jpg,png,webp,svg|max:10240',
            'avatar'             => 'nullable|file|mimes:jpeg,jpg,png,webp,svg|max:10240',
        ]);

        if ($request->hasFile('foto_perfil') || $request->hasFile('avatar')) {
            $file = $request->file('foto_perfil') ?? $request->file('avatar');
            $path = $file->store('avatars', 'public');
            $data['foto_perfil'] = '/storage/' . $path;
        }

        try {
            $data = $this->normalizeUserData($data);
            $usuario = $this->usuarioDistribuidoresService->updateUsuarioDistribuidor($id, $data);
            
            \App\Models\HistorialMovimiento::registrar(
                'distribuidor',
                $id,
                'modificacion_distribuidor',
                "Se actualizó el distribuidor '{$usuario->nombre_empresa}'",
                null
            );

            return response()->json(['status' => 'success', 'data' => $usuario, 'message' => 'Usuario actualizado'], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al actualizar usuario', $e);
        }
    }

    public function destroy($id):JsonResponse
    {   
        try {
            $usuario_destroy = $this->usuarioDistribuidoresService->deleteUsuarioDistribuidor($id);

            \App\Models\HistorialMovimiento::registrar(
                'distribuidor',
                $id,
                'eliminacion_distribuidor',
                "Se eliminó el distribuidor #{$id}",
                null
            );

            return response()->json([
            'status' => 'success', 
            'data' =>  $usuario_destroy,
            'message' =>"Usuario eliminado correctamente"], 
            200); 

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el usuario' . $e->getMessage()
            ], 400);
        }
    }


    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->usuarioDistribuidoresService->getAllUsuariosDistribuidores()
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al listar distribuidores', $e);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $usuario = $this->usuarioDistribuidoresService->getUsuarioDistribuidorById($id);
            if (!$usuario) return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado'], 404);
            
            return response()->json(['status' => 'success', 'data' => $usuario], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener usuario', $e);
        }
    }
    

    public function toggleestadousuario($id)
    {
        $resultado = $this->usuarioDistribuidoresService->activarydesactivar($id);

        if (is_null($resultado)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo encontrar el usuario distribuidor.',
            ], 404);
        }

        $isActivo = (bool) $resultado->estado_usuario;

        \App\Models\HistorialMovimiento::registrar(
            'distribuidor',
            $id,
            $isActivo ? 'activacion_distribuidor' : 'desactivacion_distribuidor',
            "Se cambió el estado del distribuidor '{$resultado->nombre_empresa}' a " . ($isActivo ? 'Activo' : 'Inactivo'),
            null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Estado del usuario distribuidor cambiado correctamente.',
            'data' => [
                'id' => $resultado->id,
                'nombre_empresa' => $resultado->nombre_empresa,
                'rut_empresa' => $resultado->rut_empresa,
                'correo_electronico' => $resultado->correo_electronico,
                'estado_usuario' => $isActivo
            ]
        ], 200);
    }

    private function normalizeUserData(array $data): array
    {
        if (isset($data['rut_empresa'])) {
            $data['rut_empresa'] = strtoupper(preg_replace('/[^kK0-9]/', '', $data['rut_empresa']));
        }
        
        if (isset($data['telefono'])) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $data['telefono']);
            if (strpos($cleanPhone, '569') === 0 && strlen($cleanPhone) === 11) {
                $cleanPhone = substr($cleanPhone, 2);
            }
            $data['telefono'] = $cleanPhone;
        }
        
        if (isset($data['correo_electronico'])) {
            $data['correo_electronico'] = strtolower(trim($data['correo_electronico']));
        }
        
        return $data;
    }

    private function errorResponse(string $message, Exception $e): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'debug'   => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}