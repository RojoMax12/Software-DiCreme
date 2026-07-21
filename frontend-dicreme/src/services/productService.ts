import { create } from 'axios';
import api from './api';

export default {
    getProducts(){
        return api.get('/productos');
    },

    getInventory(){
        return api.get('/productos/resumen_totales');
    },

    getProductById(id: number){
        return api.get(`/productos/${id}`);
    },

    updateProduct(id: number, data: unknown){
        if (data instanceof FormData) {
            return api.post(`/productos/${id}`, data);
        }
        return api.put(`/productos/${id}`, data);
    },

    deleteProduct(id: number){
        return api.delete(`/productos/${id}`);
    },

    createProduct(data: unknown){
        return api.post('/productos', data);
    },

    getLowStockProducts(threshold: number = 10) {
        return api.get(`/productos/poco-stock?umbral=${threshold}`);
    },

    getCategories() {
        return api.get('/categorias');
    },

    createCategory(data: unknown) {
        return api.post('/categorias', data);
    },

    updateCategory(id: number, data: unknown) {
        return api.put(`/categorias/${id}`, data);
    },

    deleteCategory(id: number) {
        return api.delete(`/categorias/${id}`);
    },

    getFormats() {
        return api.get('/formatos');
    },

    createFormat(data: unknown) {
        return api.post('/formatos', data);
    },

    updateFormat(id: number, data: unknown) {
        return api.put(`/formatos/${id}`, data);
    },

    deleteFormat(id: number) {
        return api.delete(`/formatos/${id}`);
    },

    toggleProductState(nombreProducto: string) {
        return api.put(`/productos/${encodeURIComponent(nombreProducto)}/toggle-estado`);
    }
}