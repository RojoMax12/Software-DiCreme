<?php

class bodega {
    
    private $id_bodega;
    private $nombre_bodega;

    public function getIdBodega() {
        return $this->id_bodega;
    }

    public function setIdBodega($id_bodega) {
        $this->id_bodega = $id_bodega;
    }

    public function getNombreBodega() {
        return $this->nombre_bodega;
    }

    public function setNombreBodega($nombre_bodega) {
        $this->nombre_bodega = $nombre_bodega;
    }

}

