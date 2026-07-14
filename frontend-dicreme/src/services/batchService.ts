import api from './api';

export default {

    getBatches() {
        return api.get('/lotes');
    },

    getBatchById(batchId: number) {
        return api.get(`/lotes/${batchId}`);
    },

    getBatchesByProductId(productId: number) {
        return api.get(`/lotes/producto/${productId}`);
    },

    getLatestBatch() {
        return api.get('/lotes/reciente');
    },

    getBatchesByWarehouseId(warehouseId: number) {
        return api.get(`/lotes/almacen/${warehouseId}`);
    },

    getBatchesByStock(stockId: number) {
        return api.get(`/lotes/stock/${stockId}`);
    },

    createBatch(batchData: any) {
        return api.post('/lotes', batchData);
    },

    updateBatch(batchId: number, batchData: any) {
        return api.put(`/lotes/${batchId}`, batchData);
    },

    updateBatchQuantity(batchId: number, quantity: number) {
        return api.put(`/lotes/${batchId}/cantidad_producto`, { cantidad_producto: quantity });
    },

    deleteBatch(batchId: number) {
        return api.delete(`/lotes/${batchId}`);
    }
}
