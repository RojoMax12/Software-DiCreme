import api from './api';

export interface RegisterData {
  rut_empresa: string;
  nombre_empresa: string;
  correo_electronico: string;
  telefono: string;
  comuna: string;
  direccion: string;
  contrasena: string;
}

export const authService = {
  async registerDistribuidor(data: RegisterData) {
    // Aquí preparamos el objeto final para el backend
    const payload = {
      ...data,
      telefono: data.telefono.startsWith('+56') ? data.telefono : `+56${data.telefono}`,
      contrasena_confirmation: data.contrasena_confirmation || data.contrasena
    };

    try {
      const response = await api.post('/usuarios_distribuidores', payload);
      return response.data;
    } catch (error) {
      throw error;
    }
  },

  async login(correo: string, contrasena: string) {
    const response = await api.post('/auth/login', {
      correo_electronico: correo,
      contrasena: contrasena
    });
    return response.data;
  },

  async forgotPassword(email: string) {
    const response = await api.post('/forgot-password', { email });
    return response.data;
  },

  async resetPassword(token: string, password: string, passwordConfirmation: string) {
    const response = await api.post('/reset-password', {
      token,
      password,
      password_confirmation: passwordConfirmation,
    });
    return response.data;
  }
};
