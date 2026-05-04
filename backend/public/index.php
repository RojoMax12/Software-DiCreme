<?php
// api/productos.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controller/productocontroller.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$db = $database->getConnection();

$controller = new ProductoController($db);

// ELIMINAMOS la llamada suelta de aquí: $controller->store(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->store(); // Ahora solo se ejecuta una vez cuando es POST
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(["message" => "Método GET detectado"]);
}