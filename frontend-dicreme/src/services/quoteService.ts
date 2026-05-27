import api from './api';

export default { 

    getQuotes(){
        return api.get('/cotizaciones');
    },

    getQuotesByDistributor(idDistribuidor: number){
        return api.get(`/cotizaciones?id_distribuidor=${idDistribuidor}`);
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
    }
}