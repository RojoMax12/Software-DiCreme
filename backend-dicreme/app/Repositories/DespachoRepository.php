<?php

namespace App\Repositories;
use App\Models\Despacho;

class DespachoRepository
{
    public function getAllDespachos()
    {
        return Despacho::all();
    }

    public function getDespachoById($id)
    {
        return Despacho::find($id);
    }

    public function createDespacho($data)
    {
        return Despacho::create($data);
    }

    public function updateDespacho($id, $data)
    {
        $despacho = Despacho::find($id);
        if ($despacho) {
            $despacho->update($data);
            return $despacho;
        }
        return null;
    }

    public function deleteDespacho($id)
    {
        $despacho = Despacho::find($id);
        if ($despacho) {
            $despacho->delete();
            return true;
        }
        return false;
    }

    public function getDespachoByIdpedido($id){

        return Despacho::where("id_pedido", $id)->first();

    }

    public function getDespachosDisponibles()
    {
        // 1. Obtener todos los pedidos con estado_pedido = 3 (Listo para Despacho)
        $pedidos = \App\Models\Pedido::where('id_estado_pedido', 3)->get();

        $disponibles = [];
        foreach ($pedidos as $pedido) {
            $despacho = Despacho::where('id_pedido', $pedido->id)->first();
            
            // Si el despacho no existe, lo creamos por defecto para este pedido
            if (!$despacho) {
                $cotizacion = \App\Models\Cotizacion::find($pedido->id_cotizacion);
                $distribuidor = \App\Models\Usuario_distribuidores::find($pedido->id_usuario_distribuidor);

                $despacho = Despacho::create([
                    'id_pedido' => $pedido->id,
                    'direccion_entrega' => $distribuidor ? $distribuidor->direccion : 'Dirección no registrada',
                    'comuna' => $distribuidor ? $distribuidor->comuna : 'Comuna no registrada',
                    'persona_recibe' => $cotizacion ? $cotizacion->persona_recibe : ($distribuidor ? $distribuidor->nombre_empresa : 'Persona no indicada'),
                    'id_estado_despacho' => 1, // Pendiente asignacion
                    'id_usuario_dicreme' => null
                ]);
            }

            // Si el despacho no tiene despachador asignado o está en estado 1
            if (!$despacho->id_usuario_dicreme || $despacho->id_estado_despacho == 1) {
                // Cargar detalles para respuesta estructurada
                $distribuidor = \App\Models\Usuario_distribuidores::find($pedido->id_usuario_distribuidor);
                $pivotes = \App\Models\Pedido_producto::where('id_pedido', $pedido->id)->get();

                $cantidadProductos = 0;
                $productosList = [];
                foreach ($pivotes as $pivote) {
                    $cantidadProductos += $pivote->cantidad;
                    $prod = \App\Models\Producto::find($pivote->id_producto);
                    if ($prod) {
                        $cat = \App\Models\Categoria::find($prod->id_categoria);
                        $form = \App\Models\Formato::find($prod->id_formato);
                        $productosList[] = [
                            'id' => $prod->id,
                            'nombre_producto' => $prod->nombre_producto,
                            'categoria' => $cat ? $cat->nombre_categoria : '',
                            'formato' => $form ? $form->nombre_formato : '',
                            'cantidad' => $pivote->cantidad
                        ];
                    }
                }

                $disponibles[] = [
                    'id_despacho' => $despacho->id,
                    'id_pedido' => $pedido->id,
                    'nombre_distribuidor' => $distribuidor ? $distribuidor->nombre_empresa : 'Distribuidor',
                    'direccion_entrega' => $despacho->direccion_entrega,
                    'comuna' => $despacho->comuna,
                    'persona_recibe' => $despacho->persona_recibe,
                    'telefono_contacto' => $distribuidor ? $distribuidor->telefono : '',
                    'cantidad_productos' => $cantidadProductos,
                    'monto_total' => $pedido->monto_final,
                    'fecha_creacion' => $pedido->fecha_creacion ? $pedido->fecha_creacion->format('Y-m-d') : null,
                    'hora_creacion' => $pedido->hora_creacion ? $pedido->hora_creacion->format('H:i') : null,
                    'created_at' => $pedido->created_at ? $pedido->created_at->toISOString() : null,
                    'productos' => $productosList
                ];
            }
        }

        return $disponibles;
    }

    public function getDespachoByIdUsuarioDiCreme($id)
    {
        $despachos = Despacho::where('id_usuario_dicreme', $id)->get();

        $resultado = [];
        foreach ($despachos as $despacho) {
            $pedido = \App\Models\Pedido::find($despacho->id_pedido);
            if (!$pedido) continue;

            $distribuidor = \App\Models\Usuario_distribuidores::find($pedido->id_usuario_distribuidor);
            $estadoDespacho = \App\Models\Estado_despacho::find($despacho->id_estado_despacho);
            $pivotes = \App\Models\Pedido_producto::where('id_pedido', $pedido->id)->get();

            $cantidadProductos = 0;
            $productosList = [];
            foreach ($pivotes as $pivote) {
                $cantidadProductos += $pivote->cantidad;
                $prod = \App\Models\Producto::find($pivote->id_producto);
                if ($prod) {
                    $cat = \App\Models\Categoria::find($prod->id_categoria);
                    $form = \App\Models\Formato::find($prod->id_formato);
                    $productosList[] = [
                        'id' => $prod->id,
                        'nombre_producto' => $prod->nombre_producto,
                        'categoria' => $cat ? $cat->nombre_categoria : '',
                        'formato' => $form ? $form->nombre_formato : '',
                        'cantidad' => $pivote->cantidad
                    ];
                }
            }

            $resultado[] = [
                'id_despacho' => $despacho->id,
                'id_pedido' => $pedido->id,
                'id_estado_despacho' => $despacho->id_estado_despacho,
                'nombre_estado_despacho' => $estadoDespacho ? $estadoDespacho->nombre_estado : '',
                'nombre_distribuidor' => $distribuidor ? $distribuidor->nombre_empresa : 'Distribuidor',
                'direccion_entrega' => $despacho->direccion_entrega,
                'comuna' => $despacho->comuna,
                'persona_recibe' => $despacho->persona_recibe,
                'telefono_contacto' => $distribuidor ? $distribuidor->telefono : '',
                'cantidad_productos' => $cantidadProductos,
                'monto_total' => $pedido->monto_final,
                'fecha_creacion' => $pedido->fecha_creacion ? $pedido->fecha_creacion->format('Y-m-d') : null,
                'hora_creacion' => $pedido->hora_creacion ? $pedido->hora_creacion->format('H:i') : null,
                'fecha_entrega' => $despacho->fecha_entrega ? $despacho->fecha_entrega->format('Y-m-d H:i') : null,
                'foto_comprobante' => $despacho->foto_comprobante,
                'notas_entrega' => $despacho->notas_entrega,
                'created_at' => $pedido->created_at ? $pedido->created_at->toISOString() : null,
                'productos' => $productosList
            ];
        }

        return $resultado;
    }

    public function asignardespachoundespachador($id_despacho, $id_despachador){

        $despacho = Despacho::find($id_despacho);
        if($despacho){
            $despacho->id_usuario_dicreme = $id_despachador;
            $despacho->id_estado_despacho = 2; // Asignado
            $despacho->save();

            return $despacho;
        }
        return null;
    }
}