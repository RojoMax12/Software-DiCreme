<?php

namespace App\Http\Controllers;

use App\Services\ProductoServices;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    protected $productoServices;

    public function __construct(ProductoServices $productoServices)
    {
        $this->productoServices = $productoServices;
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'id_categoria'    => 'required|integer|exists:categorias,id',
            'id_formato'      => 'nullable|integer|exists:formatos,id',
            'nombre_producto' => 'required|string|max:255',
            'precio_producto' => 'nullable|integer|min:0',
            'estado_producto' => 'required|boolean',
            'foto_producto'   => 'nullable'
        ]);

        if (empty($data['precio_producto']) && !empty($data['id_formato'])) {
            $formato = \App\Models\Formato::find($data['id_formato']);
            if ($formato) {
                $data['precio_producto'] = $formato->precio_formato;
            }
        }

        try {
            $uploadedFile = null;
            if ($request->hasFile('foto_producto')) {
                $file = $request->file('foto_producto');
                if ($file && $file->isValid()) {
                    $uploadedFile = $file;
                }
            } else if ($request->foto_producto instanceof \Illuminate\Http\UploadedFile && $request->foto_producto->isValid()) {
                $uploadedFile = $request->foto_producto;
            }

            if ($uploadedFile) {
                $path = $uploadedFile->store('productos', 'public');
                $data['foto_producto'] = '/storage/' . $path;
            } else {
                $fotoInput = $request->input('foto_producto');
                if (is_string($fotoInput) && !empty($fotoInput) && str_starts_with($fotoInput, 'data:image')) {
                    $base64Image = $fotoInput;
                    @list($type, $file_data) = explode(';', $base64Image);
                    @list(, $file_data) = explode(',', $file_data);
                    if ($file_data) {
                        $fileName = 'productos/prod_' . time() . '_' . uniqid() . '.webp';
                        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, base64_decode($file_data));
                        $data['foto_producto'] = '/storage/' . $fileName;
                    }
                } else if (is_string($fotoInput) && !empty($fotoInput) && str_starts_with($fotoInput, '/storage/')) {
                    $data['foto_producto'] = $fotoInput;
                } else {
                    $saborName = mb_strtolower($data['nombre_producto'] ?? '');
                    $data['foto_producto'] = \Database\Factories\ProductoFactory::getFotoForSabor($saborName);
                }
            }

            // Al crear un nuevo helado, generamos automáticamente todos los formatos disponibles
            $formatos = \App\Models\Formato::all();
            $productosCreados = [];

            if ($formatos->count() > 0) {
                foreach ($formatos as $fmt) {
                    $itemData = $data;
                    $itemData['id_formato'] = $fmt->id;
                    $itemData['precio_producto'] = $fmt->precio_formato;

                    $prod = \App\Models\Producto::updateOrCreate(
                        [
                            'nombre_producto' => $itemData['nombre_producto'],
                            'id_formato' => $fmt->id,
                        ],
                        $itemData
                    );
                    $productosCreados[] = $prod;
                }
                \Illuminate\Support\Facades\Cache::forget(\App\Repositories\ProductoRepository::CACHE_KEY);
                $producto_creado = $productosCreados[0];
            } else {
                $producto_creado = $this->productoServices->createProducto($data);
            }

            \App\Models\HistorialMovimiento::registrar(
                'producto',
                $producto_creado->id,
                'creacion',
                "Se creó el producto '{$producto_creado->nombre_producto}' con todos sus formatos",
                null,
                ['categoria_id' => $producto_creado->id_categoria]
            );

            return response()->json([
                'status' => 'success', 
                'data' =>  $producto_creado,
                'message' => "Producto creado correctamente con todos sus formatos"
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el producto: ' . $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_categoria'    => 'sometimes|required|integer|exists:categorias,id',
            'id_formato'      => 'nullable|integer|exists:formatos,id',
            'nombre_producto' => 'sometimes|required|string|max:255',
            'precio_producto' => 'nullable|integer|min:0',
            'estado_producto' => 'sometimes|required|boolean',
            'foto_producto'   => 'nullable'
        ]);

        if (isset($data['id_formato'])) {
            $formato = \App\Models\Formato::find($data['id_formato']);
            if ($formato) {
                $data['precio_producto'] = $formato->precio_formato;
            }
        }

        try {
            $productoAnterior = $this->productoServices->getProductoById($id);
            $uploadedFile = null;
            if ($request->hasFile('foto_producto')) {
                $file = $request->file('foto_producto');
                if ($file && $file->isValid()) {
                    $uploadedFile = $file;
                }
            } else if ($request->foto_producto instanceof \Illuminate\Http\UploadedFile && $request->foto_producto->isValid()) {
                $uploadedFile = $request->foto_producto;
            }

            if ($uploadedFile) {
                $path = $uploadedFile->store('productos', 'public');
                $data['foto_producto'] = '/storage/' . $path;
            } else {
                $fotoInput = $request->input('foto_producto');
                if (is_string($fotoInput) && !empty($fotoInput) && str_starts_with($fotoInput, 'data:image')) {
                    $base64Image = $fotoInput;
                    @list($type, $file_data) = explode(';', $base64Image);
                    @list(, $file_data) = explode(',', $file_data);
                    if ($file_data) {
                        $fileName = 'productos/prod_' . $id . '_' . time() . '.webp';
                        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, base64_decode($file_data));
                        $data['foto_producto'] = '/storage/' . $fileName;
                    }
                } else if (is_string($fotoInput) && !empty($fotoInput) && str_starts_with($fotoInput, '/storage/')) {
                    $data['foto_producto'] = $fotoInput;
                } else if ($productoAnterior && !empty($productoAnterior->foto_producto)) {
                    $data['foto_producto'] = $productoAnterior->foto_producto;
                } else {
                    $saborName = mb_strtolower($data['nombre_producto'] ?? ($productoAnterior->nombre_producto ?? ''));
                    $data['foto_producto'] = \Database\Factories\ProductoFactory::getFotoForSabor($saborName);
                }
            }

            // Sincronizar todos los formatos del mismo sabor
            if ($productoAnterior) {
                $updateFields = array_intersect_key($data, array_flip(['id_categoria', 'estado_producto', 'foto_producto']));
                if (!empty($data['nombre_producto'])) {
                    $updateFields['nombre_producto'] = $data['nombre_producto'];
                }
                \App\Models\Producto::where('nombre_producto', $productoAnterior->nombre_producto)->update($updateFields);
            }

            $producto_actualizado = $this->productoServices->updateProducto($id, $data);

            $producto_actualizado = $this->productoServices->updateProducto($id, $data);

            $accion = 'modificacion';
            $desc = "Se actualizó el producto '{$producto_actualizado->nombre_producto}'";
            if ($productoAnterior && isset($data['precio_producto']) && $productoAnterior->precio_producto != $data['precio_producto']) {
                $accion = 'cambio_precio';
                $desc = "Se modificó el precio de '{$producto_actualizado->nombre_producto}' de $" . number_format($productoAnterior->precio_producto, 0, ',', '.') . " a $" . number_format($producto_actualizado->precio_producto, 0, ',', '.');
            }

            \App\Models\HistorialMovimiento::registrar(
                'producto',
                $producto_actualizado->id,
                $accion,
                $desc,
                null,
                ['anterior' => $productoAnterior ? $productoAnterior->toArray() : null, 'nuevo' => $producto_actualizado->toArray()]
            );

            return response()->json([
                'status' => 'success', 
                'data' =>  $producto_actualizado,
                'message' => "Producto actualizado correctamente"
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el producto: ' . $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {   
        try {
            $producto = $this->productoServices->getProductoById($id);
            $producto_destroy = $this->productoServices->deleteProducto($id);

            if ($producto) {
                \App\Models\HistorialMovimiento::registrar(
                    'producto',
                    $id,
                    'eliminacion',
                    "Se eliminó el producto '{$producto->nombre_producto}'",
                    null
                );
            }

            return response()->json([
                'status' => 'success', 
                'data' =>  $producto_destroy,
                'message' => "Producto eliminado correctamente"
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el producto: ' . $e->getMessage()
            ], 400);
        }
    }

    public function index()
    {
        return response()->json($this->productoServices->getAllProductos());
    }

    public function show($id)
    {
        return response()->json($this->productoServices->getProductoById($id));
    }

    public function getCantidadTotal($id){
        return response()->json($this->productoServices->getCantidadTotalProductoFromAllLotes($id));
    }

    public function getResumenTodosLosProductos()
    {
        return response()->json($this->productoServices->getResumenTodosLosProductos());
    }

    public function toggleestadoproducto($name)
    {
        $name = urldecode($name);
        $resultado = $this->productoServices->activarydesactivar($name);

        if (!$resultado) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo encontrar el producto.',
            ], 404);
        }

        $isActivo = (bool)$resultado->estado_producto;
        $accion = $isActivo ? 'activacion' : 'desactivacion';
        $estadoTxt = $isActivo ? 'Activado' : 'Desactivado';

        \App\Models\HistorialMovimiento::registrar(
            'producto',
            $resultado->id,
            $accion,
            "Se cambió el estado del producto '{$resultado->nombre_producto}' a {$estadoTxt}",
            null,
            ['estado_nuevo' => $isActivo]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Estado del producto cambiado correctamente.',
            'data' => [
                'id' => $resultado->id,
                'nombre_producto' => $resultado->nombre_producto,
                'estado_producto' => $isActivo
            ]
        ], 200);
    }

    public function getProductosPocoStock(Request $request)
    {
        $umbral = $request->query('umbral', 10);
        $productos = $this->productoServices->getProductosPocoStock($umbral);

        return response()->json([
            'status' => 'success',
            'data' => $productos
        ], 200);
    }
}