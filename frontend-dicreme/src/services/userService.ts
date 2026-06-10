import api from './api';

export default {
    getUsers() {
        return api.get('/usuarios_dicreme');
    },
    getUserById(id: number) {
        return api.get(`/usuarios_dicreme/${id}`);
    }
    ,
    createUser(userData: { nombre: string; email: string; password: string; rol: string }) {
        return api.post('/usuarios_dicreme', userData);
    },

    deleteUser(id: number) {
        return api.delete(`/usuarios_dicreme/${id}`);
    }
}
