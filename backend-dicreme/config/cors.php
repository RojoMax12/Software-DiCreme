<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí se define explícitamente qué orígenes (dominios) pueden llamar a
    | esta API desde el navegador. NUNCA usar '*' en producción cuando la API
    | maneja datos personales y credenciales (JWT) — permitir cualquier origen
    | facilita ataques de robo de datos vía JavaScript malicioso en otro sitio.
    |
    */

    'paths' => ['api/*', 'up'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Se define en .env como CORS_ALLOWED_ORIGINS=https://app.tudominio.cl,https://otro.dominio.cl
    'allowed_origins' => array_filter(array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173'))
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'Accept', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 3600,

    // false: el JWT viaja en el header Authorization, no en cookies, así que no
    // se necesitan credenciales de cookie entre dominios.
    'supports_credentials' => false,

];