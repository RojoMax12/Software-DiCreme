import api from './api';

export interface MovementHistoryItem {
  id: number;
  tipo_entidad: 'lote' | 'usuario' | 'producto' | string;
  id_entidad: number | null;
  accion: string;
  descripcion: string;
  usuario_responsable: string | null;
  detalles_json: any;
  created_at: string;
  updated_at: string;
}

export default {
  getHistory(params?: { tipo_entidad?: string; id_entidad?: number; search?: string }) {
    return api.get('/historial-movimientos', { params });
  },

  logMovement(data: {
    tipo_entidad: string;
    id_entidad?: number;
    accion: string;
    descripcion: string;
    usuario_responsable?: string;
    detalles_json?: any;
  }) {
    return api.post('/historial-movimientos', data);
  }
};
