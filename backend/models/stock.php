<?php

class Stock {
    private $conn;
    private $table = "stock";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($cantidad, $id_producto) {
        $query = "INSERT INTO " . $this->table . " (cantidad, id_producto) VALUES (:cantidad, :id_producto)";
        
        $stmt = $this->conn->prepare($query);

        $cantidad = strip_tags($cantidad);
        $id_producto = strip_tags($id_producto);
        
        // Bind parameters
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':id_producto', $id_producto);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
        
    }

    public function delete($id_producto) {
        $query = "DELETE FROM " . $this->table . " WHERE id_producto = :id_producto";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([':id_producto' => $id_producto]);
    }

    
    // Dentro de la clase Stock en stock.php
    public function updateIncrement($id_producto, $cantidad) {
        // Usamos SET cantidad = cantidad + :valor para que sea atómico
        $query = "UPDATE stock SET cantidad = cantidad + :cantidad WHERE id_producto = :id_producto";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':cantidad' => $cantidad,
            ':id_producto' => $id_producto
        ]);
    }
}
