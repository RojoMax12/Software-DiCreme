<?php

namespace App\Http\Controllers;

use App\Services\Usuario_dicremeServices;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Usuario_dicremeController extends Controller
{
    protected $usuarioDicremeServices;

    //Definición del constructor para inyectar la dependencia del servicio Usuario_dicremeServices
    public function __construct(Usuario_dicremeServices $usuarioDicremeServices)
    {
        $this->usuarioDicremeServices = $usuarioDicremeServices;
    }

    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success', 
                'data' => $this->usuarioDicremeServices->getAllUsuariosDicreme()
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al listar usuarios', $e);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $usuario = $this->usuarioDicremeServices->getUsuarioDicremeById($id);
            if (!$usuario) return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado'], 404);
            
            return response()->json(['status' => 'success', 'data' => $usuario], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener el usuario', $e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre_usuario'     => 'required|string|max:255',
            'correo_electronico' => 'required|string|email|max:255|unique:usuarios_dicreme,correo_electronico',
            'contrasena'         => 'required|string|min:8',
            'id_rol'             => 'required|integer|exists:rol,id',
        ]);

        try {
            $data['correo_electronico'] = strtolower(trim($data['correo_electronico']));
            
            $usuario = $this->usuarioDicremeServices->createUsuarioDicreme($data);
            
            \App\Models\HistorialMovimiento::registrar(
                'usuario',
                $usuario->id,
                'creacion_usuario',
                "Se creó el usuario de sistema '{$usuario->nombre_usuario}' ({$usuario->correo_electronico})",
                null
            );

            return response()->json([
                'status' => 'success', 
                'data' => $usuario,
                'message' => 'Usuario creado correctamente'
            ], 201); 
        } catch (Exception $e) {
            return $this->errorResponse('Error al crear el usuario', $e);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'nombre_usuario'     => 'sometimes|required|string|max:255',
            'correo_electronico' => 'sometimes|required|string|email|max:255|unique:usuarios_dicreme,correo_electronico,' . $id,
            'contrasena'         => 'sometimes|nullable|string|min:8',
            'id_rol'             => 'sometimes|nullable|integer|exists:rol,id',
            'foto_perfil'        => 'sometimes|nullable',
        ]);

        try {
            if ($request->hasFile('foto_perfil')) {
                $file = $request->file('foto_perfil');
                $path = $file->store('avatars', 'public');
                $data['foto_perfil'] = '/storage/' . $path;
            } else if ($request->filled('foto_perfil') && str_starts_with($request->foto_perfil, 'data:image')) {
                $base64Image = $request->foto_perfil;
                @list($type, $file_data) = explode(';', $base64Image);
                @list(, $file_data) = explode(',', $file_data);
                if ($file_data) {
                    $fileName = 'avatars/avatar_dicreme_' . $id . '_' . time() . '.webp';
                    \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, base64_decode($file_data));
                    $data['foto_perfil'] = '/storage/' . $fileName;
                }
            }

            if (empty($data['contrasena'])) {
                unset($data['contrasena']);
            }
            
            if (isset($data['correo_electronico'])) {
                $data['correo_electronico'] = strtolower(trim($data['correo_electronico']));
            }
            
            $usuario = $this->usuarioDicremeServices->updateUsuarioDicreme($id, $data);
            
            \App\Models\HistorialMovimiento::registrar(
                'usuario',
                $id,
                'modificacion_usuario',
                "Se actualizó el usuario de sistema '{$usuario->nombre_usuario}'",
                null
            );

            return response()->json([
                'status' => 'success', 
                'data' => $usuario,
                'message' => 'Usuario actualizado correctamente'
            ], 200); 
        } catch (Exception $e) {
            return $this->errorResponse('Error al actualizar el usuario', $e);
        }
    }

    public function destroy($id):JsonResponse
    {   
        try {
            $usuario_destroy = $this->usuarioDicremeServices->deleteUsuarioDicreme($id);

            \App\Models\HistorialMovimiento::registrar(
                'usuario',
                $id,
                'eliminacion_usuario',
                "Se eliminó el usuario de sistema #{$id}",
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

    public function getusuariodicremedespachadores(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->usuarioDicremeServices->getUsuariosDicremeDespachador()
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener despachadores', $e);
        }
    }

    public function toggleestadousuario($id)
    {
        $resultado = $this->usuarioDicremeServices->activarydesactivar($id);

        if (is_null($resultado)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo encontrar el usuario.',
            ], 404);
        }

        $isActivo = (bool)$resultado->estado_usuario;

        \App\Models\HistorialMovimiento::registrar(
            'usuario',
            $id,
            $isActivo ? 'activacion_usuario' : 'desactivacion_usuario',
            "Se cambió el estado del usuario '{$resultado->nombre_usuario}' a " . ($isActivo ? 'Activo' : 'Inactivo'),
            null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Estado del usuario cambiado correctamente.',
            'data' => [
                'id' => $resultado->id,
                'nombre_usuario' => $resultado->nombre_usuario,
                'correo_electronico' => $resultado->correo_electronico,
                'estado_usuario' => (bool) $resultado->estado_usuario
            ]
        ], 200);
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