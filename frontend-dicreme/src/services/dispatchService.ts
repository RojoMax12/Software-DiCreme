import api from './api';

export interface AvailableDispatch {
  id_despacho: number;
  id_pedido: number;
  nombre_distribuidor: string;
  direccion_entrega: string;
  comuna: string;
  persona_recibe: string;
  telefono_contacto: string;
  cantidad_productos: number;
  monto_total: number;
  fecha_creacion: string | null;
  hora_creacion: string | null;
  created_at: string | null;
  productos: {
    id: number;
    nombre_producto: string;
    categoria: string;
    formato: string;
    cantidad: number;
  }[];
}

export interface MyDispatch {
  id_despacho: number;
  id_pedido: number;
  id_estado_despacho: number;
  nombre_estado_despacho: string;
  nombre_distribuidor: string;
  direccion_entrega: string;
  comuna: string;
  persona_recibe: string;
  telefono_contacto: string;
  cantidad_productos: number;
  monto_total: number;
  fecha_creacion: string | null;
  hora_creacion: string | null;
  fecha_entrega: string | null;
  foto_comprobante: string | null;
  notas_entrega: string | null;
  created_at: string | null;
  productos: {
    id: number;
    nombre_producto: string;
    categoria: string;
    formato: string;
    cantidad: number;
  }[];
}

export default {
  getAvailableDispatches() {
    return api.get('/despachos/disponibles');
  },

  getMyDispatches(despachadorId: number) {
    return api.get(`/despachos/${despachadorId}/despachador`);
  },

  takeDispatch(idDespacho: number, idDespachador: number) {
    return api.put(`/despachos/${idDespacho}/despacho/${idDespachador}/despachador`);
  },

  startRoute(idDespacho: number) {
    return api.put(`/despachos/${idDespacho}/iniciar-ruta`);
  },

  completeDelivery(idDespacho: number, formData: FormData) {
    return api.post(`/despachos/${idDespacho}/finalizar-entrega`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },

  getOrderDetail(idPedido: number) {
    return api.get(`/pedidos/${idPedido}/details`);
  },

  releaseDispatch(idDespacho: number) {
    return api.put(`/despachos/${idDespacho}/liberar`);
  }
};
