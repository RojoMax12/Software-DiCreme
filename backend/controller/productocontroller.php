<?php
// app/Controllers/ProductoController.php

require_once __DIR__ . '/../models/producto.php';

class ProductoController {
    private $productoModel;

    public function __construct($db) {
        // Inyectamos la conexión al modelo [cite: 378, 390]
        $this->productoModel = new Producto($db);
    }

    /**
     * Procesa la creación o actualización de un producto
     */
    public function store() {
        // 1. Recibir el JSON del frontend [cite: 24, 25, 139]
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);

        // 2. Validación básica de seguridad [cite: 241, 242, 573]
        if (!$datos || empty($datos['nombre_producto']) || empty($datos['precio'])) {
            http_response_code(400);
            echo json_encode(["ok" => false, "message" => "Datos incompletos"]);
            return;
        }

        // 3. Llamar al modelo para procesar la lógica (Upsert) [cite: 115, 172, 353]
        $resultado = $this->productoModel->createOrUpdate(
            $datos['nombre_producto'],
            $datos['categoria'],
            $datos['fecha_emision'],
            $datos['fecha_vencimiento'],
            $datos['tipo_litraje'],
            $datos['lugar_de_guardado'],
            $datos['precio'],
            $datos['cantidad'] ?? 1 // Cantidad por defecto si no viene [cite: 568]
        );

        // 4. Responder al frontend con JSON [cite: 26, 121, 354]
        if ($resultado) {
            echo json_encode(["ok" => true, "message" => "Operación exitosa"]);
        } else {
            http_response_code(500);
            echo json_encode(["ok" => false, "message" => "Error interno en el servidor"]);
        }
    }
}