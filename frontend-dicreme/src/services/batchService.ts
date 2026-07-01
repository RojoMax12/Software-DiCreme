import api from './api';

export default {
    getBatchesByProductId(productId: number) {
        return api.get(`/lotes/producto/${productId}`);
    }
}
