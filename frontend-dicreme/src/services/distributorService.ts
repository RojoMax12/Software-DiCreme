import api from './api';

export default {
    getDistributors() {
        return api.get('/usuarios_distribuidores');
    },
    getDistributorById(id: number) {
        return api.get(`/usuarios_distribuidores/${id}`);
    },

    updateDistributor(id: number, data: any) {
        return api.put(`/usuarios_distribuidores/${id}`, data);
    },
    toggledistristatus(id: number) {
        return api.put(`/usuarios_distribuidores/${id}/toggle-estado`);
    }
};
