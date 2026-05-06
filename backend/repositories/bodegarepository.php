<?php
namespace BACKEND\repositories;

class bodegarepository {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function save($bodega) {
        $sql = "INSERT INTO bodega (nombre_bodega) VALUES (:nombre)";
        $stmt = $this->db->prepare($sql); // Sentencia preparada por seguridad [cite: 36, 175, 311]
        return $stmt->execute([':nombre' => $bodega->getNombre()]);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM bodega");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
