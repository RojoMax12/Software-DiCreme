<?php

namespace App\Http\Controllers;

use App\Services\CarruselService;
use App\Helpers\ImageHelper;
use App\Models\Aviso;
use Illuminate\Http\Request;

class CarruselController extends Controller
{
    protected $carruselService;

    public function __construct(CarruselService $carruselService)
    {
        $this->carruselService = $carruselService;
    }

    public function index(Request $request)
    {
        $soloActivos = filter_var($request->query('activos', false), FILTER_VALIDATE_BOOLEAN);
        $carruseles = $this->carruselService->getAllCarruseles($soloActivos);
        return response()->json($carruseles);
    }

    public function show($id)
    {
        $carrusel = $this->carruselService->getCarruselById($id);
        if (!$carrusel) {
            return response()->json(['status' => 'error', 'message' => 'Carrusel no encontrado'], 404);
        }
        return response()->json($carrusel);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'enlace' => 'nullable|string|max:255',
            'orden' => 'nullable|integer',
            'estado' => 'nullable|boolean',
            'imagen_url' => 'required'
        ]);

        try {
            $data['imagen_url'] = $this->procesarImagen($request, 'imagen_url');

            $carrusel = $this->carruselService->createCarrusel($data);

            return response()->json([
                'status' => 'success',
                'data' => $carrusel,
                'message' => 'Elemento de carrusel creado correctamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear: ' . $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'titulo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'enlace' => 'nullable|string|max:255',
            'orden' => 'nullable|integer',
            'estado' => 'nullable|boolean',
            'imagen_url' => 'nullable'
        ]);

        try {
            $carruselAnterior = $this->carruselService->getCarruselById($id);
            if (!$carruselAnterior) {
                return response()->json(['status' => 'error', 'message' => 'No encontrado'], 404);
            }

            if ($request->hasFile('imagen_url') || (isset($data['imagen_url']) && str_starts_with($data['imagen_url'], 'data:image'))) {
                // Eliminar imagen vieja del servidor si se subió una nueva
                ImageHelper::deleteOldImage($carruselAnterior->imagen_url);
                $data['imagen_url'] = $this->procesarImagen($request, 'imagen_url');
            } else {
                if (isset($data['imagen_url']) && str_starts_with($data['imagen_url'], '/storage/')) {
                    $data['imagen_url'] = $data['imagen_url'];
                } else {
                    $data['imagen_url'] = $carruselAnterior->imagen_url;
                }
            }

            $carruselActualizado = $this->carruselService->updateCarrusel($id, $data);

            return response()->json([
                'status' => 'success',
                'data' => $carruselActualizado,
                'message' => 'Carrusel actualizado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $carrusel = $this->carruselService->getCarruselById($id);
            if (!$carrusel) {
                return response()->json(['status' => 'error', 'message' => 'No encontrado'], 404);
            }

            // Eliminar archivo de la imagen del servidor
            ImageHelper::deleteOldImage($carrusel->imagen_url);

            $this->carruselService->deleteCarrusel($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Carrusel eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 400);
        }
    }

    public function getAvisos()
    {
        $avisos = Aviso::where('estado', true)
            ->orderBy('orden', 'asc')
            ->pluck('mensaje');

        if ($avisos->isEmpty()) {
            return response()->json([
                "📢 Aviso: Horario de atención hasta las 17:00 hrs.",
                "🚛 Envíos gratuitos a toda la Región Metropolitana por compras sobre $50.000.",
                "🍦 Descuentos especiales para distribuidores registrados."
            ]);
        }

        return response()->json($avisos);
    }

    public function saveAvisos(Request $request)
    {
        $data = $request->validate([
            'mensajes' => 'required|array'
        ]);

        // Truncar avisos existentes en la BD e insertar los nuevos
        Aviso::truncate();

        foreach ($data['mensajes'] as $index => $mensaje) {
            if (!empty(trim($mensaje))) {
                Aviso::create([
                    'mensaje' => trim($mensaje),
                    'orden' => $index + 1,
                    'estado' => true
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Barra de avisos actualizada con éxito en la base de datos',
            'data' => $data['mensajes']
        ]);
    }

    public function toggleEstado($id)
    {
        try {
            $carrusel = $this->carruselService->toggleEstadoCarrusel($id);
            if (!$carrusel) {
                return response()->json(['status' => 'error', 'message' => 'No encontrado'], 404);
            }

            $estadoTxt = $carrusel->estado ? 'Activado' : 'Desactivado';
            return response()->json([
                'status' => 'success',
                'data' => $carrusel,
                'message' => "Estado del carrusel cambiado a {$estadoTxt}"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Procesa la imagen forzando guardado en formato webp y usando ImageHelper.
     */
    private function procesarImagen(Request $request, $fieldName = 'imagen_url')
    {
        $fileInput = null;
        if ($request->hasFile($fieldName)) {
            $fileInput = $request->file($fieldName);
        } else if ($request->$fieldName instanceof \Illuminate\Http\UploadedFile) {
            $fileInput = $request->$fieldName;
        } else {
            $fileInput = $request->input($fieldName);
        }

        return ImageHelper::storeAsWebp($fileInput, 'carruseles');
    }
}
