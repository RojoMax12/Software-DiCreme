<?php

namespace App\Services;

use App\Repositories\LoteRepository;

class LoteServices
{
    protected $loteRepository;

    # Constructor de una instancia de LoteRepository
    public function __construct(LoteRepository $loteRepository)
    {
        $this->loteRepository = $loteRepository;
    }

    # Creators
    public function createLote(array $data)
{   
    // Asignamos la cantidad producida a la cantidad actual del producto
    // Usamos el operador ?? 0 para evitar errores si el campo viene vacío
    $data['cantidad_producto'] = $data['cantidad_producida'] ?? 0;
    
    return $this->loteRepository->createLote($data);
    }

    # Geters
    public function getAllLotes()
    {
        return $this->loteRepository->getAllLotes();
    }

    public function getLoteById($id)
    {
        return $this->loteRepository->getLoteById($id);
    }

    public function getLoteMasReciente()
    {
        return $this->loteRepository->getLoteMasReciente();
    }

    public function getLotesByProductoId($idProducto)
{
    $lotes = $this->loteRepository->getLotesByProductoId($idProducto);

    // Transformamos la colección de lotes
    $lotes->transform(function ($lote) {
        return [
            'id' => $lote->id,
            'id_producto' => $lote->id_producto,
            'id_bodega' => $lote->id_bodega,
            'cantidad_producida' => $lote->cantidad_producida,
            'cantidad_producto' => $lote->cantidad_producto,
            'fecha_vencimiento' => $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : null,
            'fecha_emision' => $lote->fecha_emision ? $lote->fecha_emision->format('d/m/Y') : null,
            'fecha_actualizacion' => $lote->updated_at ? $lote->updated_at->format('d/m/Y') : null,
            // Agregamos la bodega aquí mismo si la traes con 'with'
            'bodega' => $lote->bodega ? $lote->bodega->nombre_bodega : null 
        ];
    });

    return $lotes;
    }


    public function getLotesByStockId($idStock)
    {
        return $this->loteRepository->getLotesByStockId($idStock);
    }

    public function getLotesByBodegaId($idBodega)
    {
        return $this->loteRepository->getLotesByBodegaId($idBodega);
    }

    public function getLotesPorVencer($dias = 30)
    {
        $lotes = $this->loteRepository->getLotesPorVencer($dias);
        $hoy = \Carbon\Carbon::now()->startOfDay();

        return $lotes->map(function ($lote) use ($hoy) {
            $fechaVenc = $lote->fecha_vencimiento ? \Carbon\Carbon::parse($lote->fecha_vencimiento)->startOfDay() : null;
            $diasRestantes = $fechaVenc ? (int)$hoy->diffInDays($fechaVenc, false) : 999;
            
            $pillClass = 'pill-yellow';
            $statusText = "Vence en {$diasRestantes} días";

            if ($diasRestantes <= 0) {
                $pillClass = 'pill-red';
                $statusText = "Vencido";
            } else if ($diasRestantes <= 7) {
                $pillClass = 'pill-red';
                $statusText = "Vence en {$diasRestantes} días (Urgente)";
            } else if ($diasRestantes <= 15) {
                $pillClass = 'pill-orange';
            }

            $nombreProd = $lote->producto ? $lote->producto->nombre_producto : 'Producto';
            $formatoProd = $lote->producto && $lote->producto->formato ? $lote->producto->formato->nombre_formato : '';

            return [
                'id' => $lote->id,
                'id_producto' => $lote->id_producto,
                'nombre_producto' => $nombreProd,
                'formato' => $formatoProd,
                'name' => "{$nombreProd} - {$formatoProd}",
                'batchNumber' => "Lote N°{$lote->id}",
                'cantidad_producto' => $lote->cantidad_producto,
                'fecha_vencimiento' => $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('Y-m-d') : null,
                'dias_restantes' => $diasRestantes,
                'pillClass' => $pillClass,
                'statusText' => $statusText,
                'bodega' => $lote->bodega ? $lote->bodega->nombre_bodega : null
            ];
        });
    }

    public function verificarDisponibilidadStock(array $items)
    {
        $idProductos = array_column($items, 'id_producto');
        $lotesAgrupados = $this->loteRepository->getLotesDeProductos($idProductos);
        $productosModel = \App\Models\Producto::with('formato')->whereIn('id', $idProductos)->get()->keyBy('id');

        $desglose = [];
        $totalFaltantes = 0;
        $mensajesAlerta = [];

        foreach ($items as $item) {
            $idProd = $item['id_producto'];
            $cantRequerida = (int)($item['cantidad'] ?? 0);

            $prod = $productosModel->get($idProd);
            $nombreProd = $prod ? $prod->nombre_producto : 'Producto #' . $idProd;
            $formatoProd = $prod && $prod->formato ? $prod->formato->nombre_formato : '';
            $nombreCompleto = "{$nombreProd} ({$formatoProd})";

            $lotesDisponibles = $lotesAgrupados->get($idProd) ?? collect();
            $stockDisponible = $lotesDisponibles->sum('cantidad_producto');

            $cantFaltante = max(0, $cantRequerida - $stockDisponible);
            $totalFaltantes += $cantFaltante;

            $desgloseLotes = [];
            $porDescontar = $cantRequerida;

            foreach ($lotesDisponibles as $lote) {
                if ($porDescontar <= 0) break;

                $usado = min($lote->cantidad_producto, $porDescontar);
                $porDescontar -= $usado;

                $desgloseLotes[] = [
                    'id_lote' => $lote->id,
                    'numero_lote' => "Lote N°{$lote->id}",
                    'cantidad_disponible_lote' => $lote->cantidad_producto,
                    'cantidad_a_usar' => $usado,
                    'fecha_vencimiento' => $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('Y-m-d') : null
                ];
            }

            if ($cantFaltante > 0) {
                $mensajesAlerta[] = "Faltan {$cantFaltante} unidades de {$nombreCompleto} para completar el pedido.";
            }

            $desglose[] = [
                'id_producto' => $idProd,
                'nombre_producto' => $nombreProd,
                'formato' => $formatoProd,
                'nombre_completo' => $nombreCompleto,
                'cantidad_requerida' => $cantRequerida,
                'cantidad_disponible' => $stockDisponible,
                'cantidad_faltante' => $cantFaltante,
                'suficiente' => ($cantFaltante === 0),
                'desglose_lotes' => $desgloseLotes
            ];
        }

        $viable = ($totalFaltantes === 0);
        $resumen = $viable 
            ? "Stock 100% disponible para todos los productos solicitados." 
            : "Déficit total de {$totalFaltantes} unidades para completar la orden.";

        return [
            'viable' => $viable,
            'total_faltantes' => $totalFaltantes,
            'resumen' => $resumen,
            'mensajes_alerta' => $mensajesAlerta,
            'desglose_productos' => $desglose
        ];
    }

    # Seters
    public function updateLote($id, $data)
    {
        return $this->loteRepository->updateLote($id, $data);
    }

    # Delete
    public function deleteLote($id)
    {
        return $this->loteRepository->deleteLote($id);
    }

    public function updateCantidadProducto($id, $cantidad)
    {
        return $this->loteRepository->updateLoteCantidadProducto($id, $cantidad);
    }

    
}