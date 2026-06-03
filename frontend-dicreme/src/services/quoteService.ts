import api from './api';

export default { 

    getQuotes(){
        return api.get('/cotizaciones');
    },

    getQuotesByDistributor(idDistribuidor: number){
        return api.get(`/cotizaciones/${idDistribuidor}/usuario_distribuidor`);
    },
    
    getQuoteById(id: number){
        return api.get(`/cotizaciones/${id}`);
    },

    updateQuote(id: number, data: unknown){
        return api.put(`/cotizaciones/${id}`, data);
    },

    deleteQuote(id: number){
        return api.delete(`/cotizaciones/${id}`);
    },

    createQuote(data: unknown){
        return api.post('/cotizaciones', data);
    },

    getQuoteProducts(idCotizacion: number | string){
        return api.get(`/cotizacion_producto/cotizacion/${idCotizacion}`);
    },

    transformQuoteToOrder(idCotizacion: number | string){
        return api.post(`/cotizacion/${idCotizacion}/transformar`);
    },

    getQuoteDetails(id: number) {
        return api.get(`/cotizaciones/${id}/details`);
    },

    takeQuote(id: number, idadmin: number) {
        return api.put(`/cotizaciones/${id}/tomarcotizacion/${idadmin}`, { idadmin });

     },

    validateQuote(id: number, idadmin: number, discountData?: any) {
    return api.put(`/cotizaciones/${id}/validarcotizacion/${idadmin}`, discountData);
    },

    cancelQuote(id: number, iduser: number) {
        return api.put(`/cotizaciones/${id}/cancelarcotizacion/${iduser}`, { iduser });
    }

}