<?php
// Incluir la configuración y cargar variables de entorno
require_once __DIR__ . '/config/database.php';

// Si usas Composer para cargar el .env
// require_once __DIR__ . '/vendor/autoload.php';
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        echo "✅ ¡Conexión exitosa a PostgreSQL! \n";
        
        // Consultar la versión de la base de datos para confirmar
        $stmt = $db->query('SELECT version()');
        $version = $stmt->fetch();
        echo "Versión de DB: " . $version['version'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error en la prueba: " . $e->getMessage() . "\n";
}