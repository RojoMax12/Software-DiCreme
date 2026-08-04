import api from './api';

export default {
  getCarruseles(activosOnly?: boolean) {
    return api.get('/carruseles', { params: activosOnly ? { activos: true } : {} });
  },

  createCarrusel(formData: FormData) {
    return api.post('/carruseles', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
  },

  deleteCarrusel(id: number | string) {
    return api.delete(`/carruseles/${id}`);
  },

  toggleEstado(id: number | string) {
    return api.put(`/carruseles/${id}/toggle-estado`);
  },

  getAvisos() {
    return api.get('/avisos');
  },

  saveAvisos(mensajes: string[]) {
    return api.post('/avisos', { mensajes });
  }
};
