import api from './api';

export default {
    getDistributors() {
        return api.get('/usuarios_distribuidores');
    },
    getDistributorById(id: number) {
        return api.get(`/usuarios_distribuidores/${id}`);
    },

    toggledistristatus(id: number) {
        return api.put(`/usuarios_distribuidores/${id}/toggle-estado`)
    }
}
