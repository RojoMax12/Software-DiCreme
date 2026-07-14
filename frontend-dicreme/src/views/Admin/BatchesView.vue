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

      <button class="btn-add-batch" @click="isAddBatchModalOpen = true">
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
    <Transition name="toast">
      <div v-if="showSuccessToast" class="success-toast">
        <div class="toast-icon-square">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="check-svg">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        </div>
        <span class="toast-text">{{ toastMessage }}</span>
      </div>
    </Transition>
  </div>
  <add-batch-modal 
    :isOpen="isAddBatchModalOpen"  
    @close="isAddBatchModalOpen = false"
    @add="handleAddNewBatch"
  />
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { Filter, Search, Warehouse } from 'lucide-vue-next';
import batchService from '@/services/batchService';
import UpdateBatchModal from '@/components/UpdateBatchModal.vue';
import AddBatchModal from '@/components/AddBAtchModal.vue';

const route = useRoute();

const productId= route.params.id;
const productName = route.query.product_name || 'Producto Desconocido';
const productFormat = route.query.format_name || 'Formato Desconocido';

const isUpdateModalOpen = ref(false);
const selectedBatch = ref<any>(null);

const searchQuery = ref('');
const batchesData = ref<any[]>([]);
const isLoading = ref(true);
const isAddBatchModalOpen = ref(false);

const showSuccessToast = ref(false);
const toastMessage = ref('');

const triggerToast = (message: string) => {
  toastMessage.value = message;
  showSuccessToast.value = true;
  
  // Se oculta automáticamente después de 3 segundos
  setTimeout(() => {
    showSuccessToast.value = false;
  }, 3000);
};

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

const handleAddNewBatch = async (batchData: any) => {
  try {
    // 1. Calculamos la fecha de emisión (HOY) en formato YYYY-MM-DD
    const today = new Date();
    const fechaEmision = today.toISOString().split('T')[0];

    // 2. Calculamos la fecha de vencimiento (Ejemplo: 1 año desde hoy)
    const vencimiento = new Date();
    vencimiento.setFullYear(vencimiento.getFullYear() + 1); // Suma 1 año
    const fechaVencimiento = vencimiento.toISOString().split('T')[0];

    // 3. Armamos el objeto con todos los datos que exige Laravel
    const newBatchData = {
      id_producto: productId, // Asegúrate de usar la variable correcta de tu ID
      cantidad_producida: batchData.cantidad_producida,
      id_bodega: batchData.id_bodega,
      fecha_emision: fechaEmision,       // ¡Nuevo!
      fecha_vencimiento: fechaVencimiento // ¡Nuevo!
    };

    // 4. Enviamos a la API
    await batchService.createBatch(newBatchData);

    isAddBatchModalOpen.value = false;

    triggerToast("Lote agregado exitosamente");
    
    await loadBatches();
    
  } catch (error) {
    console.error("Error al crear el lote", error);
  }
};

const loadBatches = async () => {
  try {
    isLoading.value = true;
    const response = await batchService.getBatchesByProductId(Number(productId));
    const rawBatches = response.data.data || response.data || [];

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
};

// 2. Llamamos a la función cuando se monta la vista
onMounted(() => {
  loadBatches();
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

/* --- ESTILOS DEL TOAST --- */
.success-toast {
  position: fixed;
  top: 80px; /* Un poco más abajo para no tapar el navbar, ajusta si es necesario */
  right: 30px;
  background-color: #f0fdf4; /* Fondo verde menta muy claro */
  color: #166534; /* Texto verde oscuro */
  padding: 16px 24px;
  border-radius: 8px; /* Bordes menos redondeados, más cuadrados */
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); /* Sombra suave */
  border: 1px solid #dcfce7; /* Borde sutil para darle forma */
  display: flex;
  align-items: center;
  gap: 15px;
  z-index: 9999;
}

.toast-icon-square {
  background-color: #22c55e; /* Verde brillante del cuadrado */
  min-width: 20px;
  height: 20px;
  border-radius: 4px; /* Cuadrado con puntas ligeramente redondeadas */
  display: flex;
  justify-content: center;
  align-items: center;
}

.check-svg {
  width: 12px;
  height: 12px;
}

.toast-text {
  font-size: 0.95rem;
  font-weight: 500;
  line-height: 1.3;
}

/* --- ANIMACIÓN VUE (Transition) --- */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-20px); /* En tu imagen parece que baja/sube sutilmente */
}

</style>