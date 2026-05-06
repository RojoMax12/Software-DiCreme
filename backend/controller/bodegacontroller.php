<?php
namespace App\Controllers;

use App\Core\Container;

class BodegaController {
    private $service;

    public function __construct() {
        $this->service = Container::get('BodegaService');
    }

    public function guardar() {
        $datos = json_decode(file_get_contents('php://input'), true);
        
        try {
            $this->service->registrarBodega($datos);
            echo json_encode(["status" => "success"]);
        } catch (\Exception $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}