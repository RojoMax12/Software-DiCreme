<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    /**
     * Procesa y guarda una imagen (UploadedFile o string Base64/URL) asegurando la extensión .webp.
     * Retorna la ruta relativa '/storage/folder/filename.webp'.
     */
    public static function storeAsWebp(mixed $fileInput, string $folder = 'uploads'): ?string
    {
        if (!$fileInput) return null;

        $fileName = $folder . '/' . time() . '_' . uniqid() . '.webp';

        // 1. Si es un archivo UploadedFile
        if ($fileInput instanceof UploadedFile && $fileInput->isValid()) {
            $content = file_get_contents($fileInput->getRealPath());
            Storage::disk('public')->put($fileName, $content);
            return '/storage/' . $fileName;
        }

        // 2. Si es una cadena Base64
        if (is_string($fileInput) && str_starts_with($fileInput, 'data:image')) {
            @list(, $file_data) = explode(';', $fileInput);
            @list(, $file_data) = explode(',', $file_data);
            if ($file_data) {
                Storage::disk('public')->put($fileName, base64_decode($file_data));
                return '/storage/' . $fileName;
            }
        }

        // 3. Si ya es una ruta string '/storage/...' o URL externa
        if (is_string($fileInput)) {
            return $fileInput;
        }

        return null;
    }

    /**
     * Elimina una imagen del servidor (disco público) si pertenece a '/storage/'.
     */
    public static function deleteOldImage(?string $path): void
    {
        if (!$path) return;

        if (str_starts_with($path, '/storage/')) {
            $relativePath = str_replace('/storage/', '', $path);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }
    }
}
