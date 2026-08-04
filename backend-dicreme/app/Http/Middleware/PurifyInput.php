<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PurifyInput
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Si es un método de lectura (GET, HEAD, DELETE), no hay datos que sanitizar. Pasamos de largo.
        if ($request->isMethod('GET') || $request->isMethod('HEAD') || $request->isMethod('DELETE')) {
            return $next($request);
        }

        // 2. Obtenemos todos los datos de la petición
        $input = $request->all();

        // 3. Definimos qué campos queremos proteger (contraseñas, confirmaciones y archivos de imagen)
        $camposIgnorados = [
            'contrasena', 
            'password', 
            'password_confirmation', 
            'contrasena_confirmation',
            'foto_producto',
            'foto_perfil',
            'foto_comprobante',
            'avatar',
            'foto'
        ];

        // 4. Limpieza manual y segura del mapa de datos
        foreach ($input as $key => $value) {
            // Ignorar contraseñas y objetos de archivos subidos
            if (in_array($key, $camposIgnorados) || $value instanceof \Illuminate\Http\UploadedFile) {
                continue;
            }

            // Si el campo es un texto, le quitamos las etiquetas HTML/JS
            if (is_string($value)) {
                $input[$key] = strip_tags($value);
            } 
            // Si es un array secundario (por ejemplo, la lista de productos en una cotización)
            elseif (is_array($value)) {
                array_walk_recursive($value, function (&$subValue) {
                    if (is_string($subValue)) {
                        $subValue = strip_tags($subValue);
                    }
                });
                $input[$key] = $value;
            }
        }

        // 5. Reemplazamos los datos usando merge() de forma segura en el Request
        $request->merge($input);

        return $next($request);
    }
}