<?php

namespace App\Http\Controllers;

use App\Services\CategoriaServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    protected $categoriaServices;

    public function __construct(CategoriaServices $categoriaServices)
    {
        $this->categoriaServices = $categoriaServices;
    }

    public function store(Request $request):JsonResponse
    {
        $data = $request->validate([
            'nombre_categoria' => 'required|string|max:255',
            'descripcion_categoria' => 'required|string|max:255',
        ]);

        try {
            $Categoria_creada = $this->categoriaServices->createCategoria($data);

            \App\Models\HistorialMovimiento::registrar(
                'producto',
                $Categoria_creada->id,
                'creacion_categoria',
                "Se creó la categoría '{$Categoria_creada->nombre_categoria}'",
                null
            );

            return response()->json([
                'status' => 'success',
                'data' => $Categoria_creada,
                'message' => 'categoria creada correctamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar al crear la categoria ' . $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id):JsonResponse
    {
        $data = $request->validate([
            'nombre_categoria' => 'sometimes|required|string|max:255',
            'descripcion_categoria' => 'sometimes|required|string|max:255',
        ]);

        try {
            $categoria_updateada = $this->categoriaServices->updateCategoria($id, $data);
            
            \App\Models\HistorialMovimiento::registrar(
                'producto',
                $categoria_updateada->id,
                'modificacion_categoria',
                "Se actualizó la categoría '{$categoria_updateada->nombre_categoria}'",
                null
            );

            return response()->json([
                'status' => 'success',
                'data' => $categoria_updateada,
                'message' => 'categoria actualizada correctamente'
                
            ], 200);
        } catch (\Exception $e) {
            
        return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar la categoria' . $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id):JsonResponse
    {   

        try {
            $categoria = $this->categoriaServices->getCategoriaById($id);
            $categoria_destruida = $this->categoriaServices->deleteCategoria($id);

            if ($categoria) {
                \App\Models\HistorialMovimiento::registrar(
                    'producto',
                    $id,
                    'eliminacion_categoria',
                    "Se eliminó la categoría '{$categoria->nombre_categoria}'",
                    null
                );
            }

            return response()->json([
                'status' => 'success',
                'data' => $categoria_destruida,
                'message' => 'categoria eliminada correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                    'status' => 'error',
                    'message' => 'Error al eliminar la categoria' . $e->getMessage()
                ], 400);

            }
    }

    

    public function index():JsonResponse
    {
        try {
           $categorias = $this->categoriaServices->getAllCategorias();

           return response()->json([
                'status' => 'success',
                'data' => $categorias,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                    'status' => 'error',
                    'message' => 'Error al obtener las categoria' . $e->getMessage()
                ], 400);

            }
        }
    

    public function show($id):JsonResponse
    {   
        try {
            $categoria = $this->categoriaServices->getCategoriaById($id);

            return response()->json([
                'status' => 'success',
                'data' => $categoria,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                    'status' => 'error',
                    'message' => 'Error al obtener la categoria' . $e->getMessage()
                ], 400);

            }
        }

    

    
}