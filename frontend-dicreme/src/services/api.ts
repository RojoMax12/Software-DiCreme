import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// 🚨 INTERCEPTOR DE PETICIÓN: Inyecta el token automáticamente en cada llamada
api.interceptors.request.use(
  (config) => {
    // Buscamos el token tal cual lo guardas en tu Login y Navbar
    const token = localStorage.getItem('token');
    
    // Si el token existe, se lo pegamos al encabezado Authorization
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// 🚨 INTERCEPTOR DE RESPUESTA: Estructura JSend y Manejo de Errores Globales
api.interceptors.response.use(
  (response) => {

    if (response.data && response.data.status === 'success') {
      // Verificamos si existe la llave "data" anidada.
      // Si existe, reemplazamos la respuesta de Axios directamente por tu arreglo/objeto real.
      if (response.data.data !== undefined) {
        response.data = response.data.data;
      }
    }
    
    // Devolvemos la respuesta modificada hacia tus componentes (Vue)
    return response;
  },
  (error) => {
    const status = error.response?.status;
    const isAuthError = status === 401 || status === 419;

    if (isAuthError) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');

      if (typeof window !== 'undefined' && window.location.pathname !== '/login') {
        window.location.replace('/login');
      }
    }

    console.error('API Error:', error.response?.data || error.message);
    return Promise.reject(error);
  }
);

export default api;