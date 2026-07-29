<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditPersonalDataAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        $method = $request->method();

        // No auditar si la respuesta fue un error de ruta no encontrada (404) para evitar spam de logs
        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() === 404) {
            return $response;
        }

        // Definir el tipo de acción para facilitar la lectura en herramientas de logs
        $accion = 'lectura_individual';
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $accion = 'modificacion';
        } elseif ($method === 'DELETE') {
            $accion = 'eliminacion';
        } elseif ($method === 'GET' && !$request->route('id')) {
            $accion = 'lectura_masiva';
        }

        // Sanitizar el input: si es una modificación, guardar qué campos se intentaron alterar (EXCEPTUANDO datos críticos y archivos binarios)
        $payload = [];
        if ($accion === 'modificacion') {
            $input = $request->except(['password', 'password_confirmation', 'token', 'current_password', 'contrasena']);
            foreach ($input as $key => $value) {
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $payload[$key] = '[Archivo: ' . $value->getClientOriginalName() . ']';
                } else {
                    $payload[$key] = $value;
                }
            }
        }

        Log::channel(config('logging.default'))->info('acceso_datos_personales', [
            'accion'      => $accion,
            'actor_id'    => $user?->getAuthIdentifier(),
            'actor_email' => $user?->correo_electronico ?? null,
            'metodo'      => $method,
            'ruta'        => $request->path(),
            'recurso_id'  => $request->route('id') ?? ($accion === 'lectura_masiva' ? 'todos' : null),
            'payload_safe'=> !empty($payload) ? json_encode($payload) : null,
            'ip'          => $request->ip(),
            'status'      => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
            'fecha'       => now()->toIso8601String(),
        ]);

        return $response;
    }
}