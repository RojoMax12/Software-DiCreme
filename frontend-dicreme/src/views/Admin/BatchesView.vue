<template>
  <div class="batch-detail-container">
    <div class="header-section">
      <h2 class="title">Detalle {{ productName }} - {{ productFormat }}</h2>
      <hr class="divider" />
    </div>

    <div class="controls-bar">
      <div class="left-controls">
        <button class="btn-filter">
          Filtrar
          <Filter :size="18" class="icon" />
        </button>

        <div class="search-wrapper">
          <input 
            type="text" 
            placeholder="Buscar lote" 
            v-model="searchQuery"
          />
          <Search :size="18" class="search-icon" color="#999" />
        </div>
      </div>

      <button class="btn-add-batch">
        AGREGAR LOTE
      </button>
    </div>

    <div class="table-card">
      <table class="batches-table">
        <thead>
          <tr>
            <th>N° Lote</th>
            <th>Unidades<br>producidas</th>
            <th>Bodega</th>
            <th>Fecha<br>ingreso</th>
            <th>Fecha<br>vencimiento</th>
            <th>Última<br>actualización</th>
            <th>Unidades<br>restantes</th>
            <th></th> </tr>
        </thead>
        <tbody>
          <tr 
            v-for="batch in filteredBatches" 
            :key="batch.id"
            :class="{ 'row-empty': batch.remainingUnits === 0, 
              'row-warning': batch.remainingUnits > 0 && batch.alertExpiration,
              'row-active': batch.remainingUnits > 0 && !batch.alertExpiration
             }"
          >
            <td class="batch-number">{{ batch.number }}</td>
            <td>{{ batch.producedQuantity }}</td>
            <td>{{ batch.Warehouse }}</td>
            <td>{{ batch.ingressDate }}</td>
            <td :class="{ 'text-danger': batch.alertExpiration && batch.remainingUnits > 0 }">
              {{ batch.expirationDate }}
            </td>
            <td>{{ batch.lastUpdate }}</td>
            <td>{{ batch.remainingUnits }}</td>
            <td class="action-cell">
              <button 
                :class="batch.remainingUnits > 0 ? 'btn-update-active' : 'btn-update-inactive'"
                @click="openUpdateModal(batch)"
              >
                ACTUALIZAR
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <UpdateBatchModal 
      :isOpen="isUpdateModalOpen" 
      :BatchNumber="selectedBatch?.number || ''" 
      :currentUnits="selectedBatch?.remainingUnits || 0"
      :maxUnits="selectedBatch?.producedQuantity || 0"
      @close="isUpdateModalOpen = false"
      @update="handleUpdateBatch"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { Filter, Search, Warehouse } from 'lucide-vue-next';
import batchService from '@/services/batchService';
import UpdateBatchModal from '@/components/UpdateBatchModal.vue';

const route = useRoute();

const productId= route.params.id;
const productName = route.query.product_name || 'Producto Desconocido';
const productFormat = route.query.format_name || 'Formato Desconocido';

const isUpdateModalOpen = ref(false);
const selectedBatch = ref<any>(null);

const searchQuery = ref('');
const batchesData = ref<any[]>([]);
const isLoading = ref(true);

const formatBatchNumber = (id: number) => {
  return `N°${id.toString()}`;
};

const CheckIfExpiringSoon = (expirationDate: string) => {
  try{
    const parts = expirationDate.split('/');
    if(parts.length !== 3) return false;

    const expiration = new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
    const today = new Date();

    const diffTime = expiration.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    return diffDays > 0 && diffDays <= 90; 

  } catch (error){
    return false;
  }
};

const openUpdateModal = (batch: any) => {
  if (batch.remainingUnits > 0) {
  selectedBatch.value = batch;
    isUpdateModalOpen.value = true;
  }
};

const handleUpdateBatch = async (newQuantity: number) => {
  if (!selectedBatch.value) return;

  try {
    const response = await batchService.updateBatchQuantity(selectedBatch.value.id, newQuantity);
    if (response.status === 200) {
      selectedBatch.value.remainingUnits = newQuantity;
      if (newQuantity === 0) {
        selectedBatch.value.alertExpiration = false;
      }

    } else {
      console.error('Error updating batch:', response);
    }
  } catch (error) {
    console.error('Error updating batch:', error);
  }finally {
    isUpdateModalOpen.value = false;
  }
};

onMounted(async () => {
  try {
    isLoading.value = true;
    const response = await batchService.getBatchesByProductId(Number(productId));
    const rawBatches = response.data.data|| response.data || [];

    batchesData.value = rawBatches.map((batch: any) => ({
      id: batch.id,
      number: formatBatchNumber(batch.id),
      producedQuantity: batch.cantidad_producida,
      Warehouse: batch.bodega || '-',
      ingressDate: batch.fecha_emision,
      expirationDate: batch.fecha_vencimiento,
      lastUpdate: batch.fecha_actualizacion,
      remainingUnits: batch.cantidad_producto,
      alertExpiration: CheckIfExpiringSoon(batch.fecha_vencimiento)
    }));
  } catch (error) {
    console.error('Error fetching batches:', error);
  } finally {
    isLoading.value = false;
  }
});



// Lógica para el buscador
const filteredBatches = computed(() => {
  if (!searchQuery.value) return batchesData.value;
  
  const lowerCaseQuery = searchQuery.value.toLowerCase();
  return batchesData.value.filter(batch => 
    batch.number.toLowerCase().includes(lowerCaseQuery) ||
    batch.Warehouse.toLowerCase().includes(lowerCaseQuery)
  );
});
</script>

<style scoped>
/* Contenedor Principal */
.batch-detail-container {
  padding: 2rem;
  font-family: 'Arial', sans-serif; /* Ajusta a la fuente de tu proyecto */
  color: #333;
  background-color: #f8f9fb; /* Fondo gris claro de la pantalla */
  min-height: 100vh;
}

/* Cabecera */
.header-section {
  margin-bottom: 1.5rem;
}

.title {
  font-size: 1.25rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
  color: #1a1a1a;
}

.divider {
  border: none;
  height: 2px;
  background-color: #df889f; /* Color rosado de la línea */
  margin: 0;
}

/* Barra de Controles */
.controls-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.left-controls {
  display: flex;
  gap: 1rem;
}

.btn-filter {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background-color: white;
  border: 1px solid #ddd;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: bold;
  cursor: pointer;
  color: #333;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-wrapper input {
  padding: 0.5rem 1rem 0.5rem 2.5rem;
  border: 1px solid #ddd;
  border-radius: 20px;
  width: 250px;
  outline: none;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.search-icon {
  position: absolute;
  left: 10px;
}

.btn-add-batch {
  background-color: #df889f;
  color: white;
  border: none;
  padding: 0.6rem 1.5rem;
  border-radius: 20px;
  font-weight: bold;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(223, 136, 159, 0.3);
  transition: background-color 0.2s;
}

.btn-add-batch:hover {
  background-color: #c97389;
}

/* Contenedor de la tabla */
.table-card {
  background-color: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Estilos de la tabla */
.batches-table {
  width: 100%;
  border-collapse: collapse;
  text-align: center;
}

.batches-table th {
  background-color: #d77f98; /* Rosado oscuro del header */
  color: white;
  padding: 1rem;
  font-weight: bold;
  font-size: 0.9rem;
  line-height: 1.2;
}

.batches-table td {
  padding: 1rem;
  font-size: 0.95rem;
}

.row-active {
  background-color: #ffffff; /* Rosado muy claro para los que tienen stock */
}

.row-warning {
  background-color: #fbe6ec; /* Rosado muy claro para los que tienen stock pero están por vencer */
}

.row-empty {
  background-color: #f3f4f6; /* Gris muy claro para los vacíos */
  color: #6b7280;
}

.batch-number {
  font-weight: 500;
}

/* Utilidades de texto */
.text-danger {
  color: #ef4444; /* Rojo para alertas de vencimiento */
  font-weight: bold;
}

/* Botones de acción dentro de la tabla */
.btn-update-active {
  background-color: #df889f;
  color: white;
  border: none;
  padding: 0.4rem 1rem;
  border-radius: 15px;
  font-weight: bold;
  font-size: 0.8rem;
  cursor: pointer;
}

.btn-update-inactive {
  background-color: #9ca3af; 
  color: white;
  border: none;
  padding: 0.4rem 1rem;
  border-radius: 15px;
  font-weight: bold;
  font-size: 0.8rem;
  cursor: not-allowed;
}
</style>