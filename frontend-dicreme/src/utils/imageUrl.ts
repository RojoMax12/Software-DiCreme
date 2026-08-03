/**
 * Helper dinámico para resolver URLs de imágenes y archivos de almacenamiento (storage)
 * Funciona automáticamente tanto en entorno local (http://localhost:8000)
 * como en entorno de producción (cPanel / VITE_API_URL).
 */
export function getStorageUrl(path: string | null | undefined): string {
  if (!path) return '';
  
  // Si la ruta ya es una URL completa (http/https), la devuelve tal cual
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path;
  }

  // Obtiene la URL base a partir de VITE_API_URL eliminando el prefijo /api
  const rawApiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
  const baseUrl = rawApiUrl.replace(/\/api\/?$/, '');

  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  return `${baseUrl}${cleanPath}`;
}
