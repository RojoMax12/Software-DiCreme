<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registra quién accede o modifica datos personales (usuarios_dicreme,
 * usuarios_distribuidores) y cuándo. Esto es evidencia mínima de trazabilidad
 * exigible bajo la Ley 21.719: ante una fiscalización o una solicitud ARCO,
 * debe poder acreditarse qué cuenta accedió a qué registro y en qué momento.
 *
 * Los logs se escriben en el canal configurado (ver config/logging.php).
 * Para cumplimiento real, este canal debería apuntar a almacenamiento
 * centralizado y con retención definida (no solo storage/logs local).
 */
class AuditPersonalDataAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        Log::channel(config('logging.default'))->info('acceso_datos_personales', [
            'actor_id'    => $user?->getAuthIdentifier(),
            'actor_email' => $user?->correo_electronico ?? null,
            'metodo'      => $request->method(),
            'ruta'        => $request->path(),
            'recurso_id'  => $request->route('id'),
            'ip'          => $request->ip(),
            'status'      => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
            'fecha'       => now()->toIso8601String(),
        ]);

        return $response;
    }
}