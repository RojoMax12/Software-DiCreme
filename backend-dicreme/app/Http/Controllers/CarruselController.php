<?php

namespace App\Http\Controllers;

use App\Services\CarruselService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
                $data['imagen_url'] = $this->procesarImagen($request, 'imagen_url');
            } else {
                if (isset($data['imagen_url']) && str_starts_with($data['imagen_url'], '/storage/')) {
                    // Mantener la misma URL si ya venía
                    // $data['imagen_url'] = $data['imagen_url'];
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

            // Opcional: Eliminar la imagen del disco
            // si empieza con /storage/
            // if (str_starts_with($carrusel->imagen_url, '/storage/')) {
            //     $path = str_replace('/storage/', '', $carrusel->imagen_url);
            //     Storage::disk('public')->delete($path);
            // }

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
     * Procesa la imagen, guardándola en disco público si es File o Base64.
     */
    private function procesarImagen(Request $request, $fieldName = 'imagen_url')
    {
        $uploadedFile = null;
        if ($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);
            if ($file && $file->isValid()) {
                $uploadedFile = $file;
            }
        } else if ($request->$fieldName instanceof \Illuminate\Http\UploadedFile && $request->$fieldName->isValid()) {
            $uploadedFile = $request->$fieldName;
        }

        if ($uploadedFile) {
            $path = $uploadedFile->store('carruseles', 'public');
            return '/storage/' . $path;
        } else {
            $fotoInput = $request->input($fieldName);
            if (is_string($fotoInput) && !empty($fotoInput) && str_starts_with($fotoInput, 'data:image')) {
                $base64Image = $fotoInput;
                @list($type, $file_data) = explode(';', $base64Image);
                @list(, $file_data) = explode(',', $file_data);
                if ($file_data) {
                    $fileName = 'carruseles/carrusel_' . time() . '_' . uniqid() . '.webp';
                    Storage::disk('public')->put($fileName, base64_decode($file_data));
                    return '/storage/' . $fileName;
                }
            }
        }

        // Retorna el mismo input si era un enlace externo o falló la conversión
        return $request->input($fieldName);
    }
}
