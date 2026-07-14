<?php

namespace App\Repositories;
use App\Models\Estado_pago;

class Estado_pagoRepository  {

    public function getAllEstadosPago(){
        return Estado_pago::all();
    }

    public function getEstadoPagoById($id){
        return Estado_pago::find($id);
    }
    public function createEstadoPago($data){
        return Estado_pago::create($data);
    }

    public function updateEstadoPago($id, $data){
        $estadoPago = Estado_pago::find($id);
        if ($estadoPago) {
            $estadoPago->update($data);
            return $estadoPago;
        }
        return null;
    }

    public function deleteEstadoPago($id){
        $estadoPago = Estado_pago::find($id);
        if ($estadoPago) {
            $estadoPago->delete();
            return true;
        }
        return false;
    }





}