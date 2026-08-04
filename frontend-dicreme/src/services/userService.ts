import api from './api';

export default {
    getUsers() {
        return api.get('/usuarios_dicreme');
    },
    getUserById(id: number) {
        return api.get(`/usuarios_dicreme/${id}`);
    }
    ,
    createUser(userData: any) {
        return api.post('/usuarios_dicreme', userData);
    },

    deleteUser(id: number) {
        return api.delete(`/usuarios_dicreme/${id}`);
    },

    toggleUserStatus(id: number) {
        return api.put(`/usuarios_dicreme/${id}/toggle-estado`);
    },

    updateuser(id: number, userData: any) {
        return api.put(`/usuarios_dicreme/${id}`, userData);
    },

    updateUserProfile(id: number, data: any) {
        if (data instanceof FormData) {
            return api.post(`/usuarios_dicreme/${id}`, data, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        }
        return api.put(`/usuarios_dicreme/${id}`, data);
    }
}
