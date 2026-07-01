<template>
  <div class="inventory-view">
    <header class="page-header">
      <h1 class="page-title">Inventario</h1>
      <button class="btn-add-product" @click="isAddModalOpen = true">
        AGREGAR PRODUCTO
      </button>
    </header>

    <div class="header-divider"></div>

    <main class="main-content-grid">
      
      <section class="table-column">
        
        <div class="toolbar">
          <button class="btn-filter">
            Filtrar
            <Filter :size="16" />
          </button>
          
          <div class="search-input-wrapper">
            <input 
              type="text" 
              placeholder="Busca por helado" 
              class="search-input"
            />
            <Search :size="18" class="search-icon" />
          </div>
        </div>

        <div class="table-container">
          <table class="inventory-table">
            <thead>
              <tr>
                <th style="text-align: left; padding-left: 20px; width: 25%;">Helado</th>
                <th style="width: 15%;">Formato</th>
                <th style="width: 15%;">Stock</th>
                <th style="width: 20%;">última<br>actualización</th>
                <th style="width: 15%;">Detalle<br>lotes</th>
              </tr>
            </thead>
            
            <tbody v-if="isLoading">
              <tr>
                <td colspan="5" style="padding: 30px; color: #9793a0; font-style: italic;">
                  Cargando inventario desde la base de datos...
                </td>
              </tr>
            </tbody>
            
            <tbody v-else>
              <tr v-for="item in productsInventory" :key="item.id">
                <td style="text-align: left; padding-left: 20px;">{{ item.nombre_producto || '-' }}</td>
                <td>{{ item.formato?.nombre_formato|| '-' }}</td>
                <td>{{ item.cantidad_total }}</td>
                <td>{{ formatDate(item.ultima_actualizacion_lote) }}</td>
                <td>
                  <button class="btn-details" @click="goToDetails(item)">VER DETALLES</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <aside class="alerts-column">
        
        <div class="alert-card">
          <div class="floating-badge badge-pink">Stock</div>
          <h3 class="alert-title">Déficit en inventario</h3>
          
          <div class="alert-list">
            <div v-if="deficitAlerts.length === 0" class="empty-alerts">
              ¡Inventario saludable! No hay déficit.
            </div>

            <div 
              v-else 
              v-for="alert in deficitAlerts" 
              :key="alert.id" 
              class="alert-item"
            >
              <span class="item-name">{{ alert.name }}</span>
              <span class="status-pill" :class="alert.pillClass">
                {{ alert.statusText }}
              </span>
            </div>
          </div>
        </div>

        <div class="alert-card mt-4">
          <div class="floating-badge badge-pink">lotes</div>
          <h3 class="alert-title">Atención a lotes por vencer</h3>
          
          <div class="alert-list">
            <div v-if="expiringbatchesAlerts.length === 0" class="empty-alerts">
              No hay lotes por vencer pronto.
            </div>

            <div 
              v-else 
              v-for="batch in expiringbatchesAlerts" 
              :key="batch.id" 
              class="alert-item"
            >
              <span class="item-name">{{ batch.name }}</span>
              <span class="status-pill" :class="batch.pillClass">
                {{ batch.batchNumber }}
              </span>
            </div>
          </div>
        </div>

      </aside>
    </main>
    
    <AddProductModal 
      v-if="isAddModalOpen" 
      @close="isAddModalOpen = false"
      @save="handleSaveProduct"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Filter, Search } from 'lucide-vue-next'
import AddProductModal from '@/components/AddProductModal.vue'
import productService from '@/services/productService'

const router = useRouter()

// Estados generales
const isAddModalOpen = ref(false)
const isLoading = ref(true) 
const productsInventory = ref<any[]>([])

onMounted(async () => {
  try {
    isLoading.value = true
    const response = await productService.getInventory()
    // Guardamos los datos de la respuesta real
    productsInventory.value = response.data.data || response.data
  } catch (error) {
    console.error('Error al cargar el inventario:', error)
  } finally {
    isLoading.value = false
  }
})

// Lógica al guardar un nuevo producto (Por ahora solo cierra el modal)
const handleSaveProduct = (datosSimulados: any) => {
  console.log('Datos del nuevo producto:', datosSimulados)
  isAddModalOpen.value = false
}

const formatDate = (dateString: string | null) => {
  if (!dateString) return 'Sin registros';
  try {
    const parts = dateString.split(/[T ]/);
    const datePart = parts[0]; 
    if (!datePart) return 'Sin registros';
    
    const dateArray = datePart.split('-');
    if (dateArray.length === 3) {
      return `${dateArray[2]}/${dateArray[1]}/${dateArray[0]}`;
    }
    return 'Sin registros';
  } catch (e) {
    return 'Sin registros';
  }
}

const goToDetails = (item: any) => {
  router.push({
    path: `/admin/lotes/${item.id}`,
    query: {
      product_name: item.nombre_producto || '',
      format_name: item.formato?.nombre_formato || '' 
    }
  })
}
// --- LÓGICA PARA LAS ALERTAS ---

// 1. Alertas de Déficit de Stock
const deficitAlerts = computed(() => {
  // Filtramos solo los que tienen 10 o menos unidades
  const lowStockProducts = productsInventory.value.filter(item => item.cantidad_total <= 10)
  
  return lowStockProducts.map(item => {
    let statusText = '';
    let pillClass = '';

    if (item.cantidad_total === 0) {
      statusText = 'Sin stock';
      pillClass = 'pill-orange';
    } else if (item.cantidad_total <= 5) {
      statusText = `Faltan ${10 - item.cantidad_total} unidades`;
      pillClass = 'pill-red';
    } else {
      statusText = 'Pocas unidades';
      pillClass = 'pill-yellow';
    }

    return {
      id: item.id,
      name: `${item.nombre_producto || 'N/A'} - ${item.formato?.nombre_formato || 'N/A'}`,
      statusText,
      pillClass
    }
  })
})

// 2. Alertas de batches por vencer (estáticos por ahora)
const expiringbatchesAlerts = computed(() => {
  return [
    { 
      id: 'mock1', 
      name: 'Chocolate - 5L', 
      batchNumber: 'Lote N°0005', 
      pillClass: 'pill-red' 
    }
  ]
})
</script>

<style scoped>
/* --- ESTILOS GENERALES DE LA VISTA --- */
.inventory-view {
  background-color: #f6f4f6; 
  min-height: 100vh;
  padding: 40px;
  font-family: 'Inter', sans-serif;
  color: #322c44;
}

/* --- CABECERA --- */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.page-title {
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0;
}

.btn-add-product {
  background-color: #e4869f;
  color: white;
  border: none;
  border-radius: 30px;
  padding: 10px 24px;
  font-weight: 800;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(228, 134, 159, 0.3);
  transition: transform 0.2s;
}

.btn-add-product:hover {
  transform: translateY(-2px);
  background-color: #d1728c;
}

.header-divider {
  height: 2px;
  background-color: #e4869f;
  width: 100%;
  margin-bottom: 30px;
}

/* --- GRILLA PRINCIPAL --- */
.main-content-grid {
  display: grid;
  grid-template-columns: 2fr 1.1fr; 
  gap: 40px;
  align-items: start;
}

/* --- COLUMNA IZQUIERDA (TABLA) --- */
.toolbar {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.btn-filter {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: white;
  border: 1px solid #e0dde0;
  border-radius: 20px;
  padding: 8px 20px;
  font-weight: 700;
  font-size: 0.9rem;
  color: #322c44;
  cursor: pointer;
}

.search-input-wrapper {
  position: relative;
  flex: 1;
  max-width: 350px;
}

.search-input {
  width: 100%;
  padding: 10px 16px 10px 40px; 
  border-radius: 25px;
  border: 1px solid #e0dde0;
  background-color: white;
  font-size: 0.9rem;
  outline: none;
}

.search-input::placeholder {
  color: #9793a0;
  font-weight: 500;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #9793a0;
}

.table-container {
  background-color: white;
  border-radius: 20px;
  padding: 10px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

.inventory-table {
  width: 100%;
  border-collapse: collapse;
  text-align: center;
}

.inventory-table th {
  background-color: #e5e5e5;
  color: #322c44;
  font-weight: 700;
  font-size: 0.9rem;
  padding: 16px 10px;
}

.inventory-table th:first-child { border-top-left-radius: 16px; border-bottom-left-radius: 16px; }
.inventory-table th:last-child { border-top-right-radius: 16px; border-bottom-right-radius: 16px; }

.inventory-table td {
  padding: 18px 10px;
  font-size: 0.95rem;
  font-weight: 500;
  border-bottom: 1px solid #f6f4f6;
}

.inventory-table tr:last-child td {
  border-bottom: none;
}

.btn-details {
  background-color: #e4869f;
  color: white;
  border: none;
  border-radius: 20px;
  padding: 8px 16px;
  font-weight: 700;
  font-size: 0.75rem;
  cursor: pointer;
}

/* --- COLUMNA DERECHA (ALERTAS) --- */
.alerts-column {
  display: flex;
  flex-direction: column;
  gap: 30px; 
}

.alert-card {
  background-color: white;
  border-radius: 20px;
  padding: 30px 24px 24px 24px;
  position: relative; 
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

.floating-badge {
  position: absolute;
  top: -14px;
  left: 20px;
  color: white;
  font-weight: 800;
  font-size: 0.85rem;
  padding: 6px 20px;
  border-radius: 20px;
  letter-spacing: 0.5px;
}

.badge-pink {
  background-color: #f7a6bc; 
}

.alert-title {
  margin: 0 0 20px 0;
  font-size: 1.1rem;
  font-weight: 800;
}

.alert-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 280px;
  overflow-y: auto; 
  padding-right: 8px;
}

/* --- DISEÑO DEL SCROLLBAR --- */
.alert-list::-webkit-scrollbar {
  width: 6px;
}

.alert-list::-webkit-scrollbar-track {
  background: #f6f4f6; 
  border-radius: 10px;
}

.alert-list::-webkit-scrollbar-thumb {
  background: #e4869f; 
  border-radius: 10px;
}

.alert-list::-webkit-scrollbar-thumb:hover {
  background: #d1728c;
}

.alert-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid #e0dde0;
  border-radius: 12px;
  padding: 10px 16px;
}

.item-name {
  font-weight: 700;
  font-size: 0.9rem;
}

.status-pill {
  font-weight: 800;
  font-size: 0.8rem;
  padding: 6px 14px;
  border-radius: 20px;
}

/* Colores de las alertas */
.pill-red {
  background-color: #fca5a5; 
  color: #7f1d1d; 
}

.pill-orange {
  background-color: #fdba74; 
  color: #7c2d12; 
}

.pill-yellow {
  background-color: #fde047; 
  color: #713f12; 
}

.empty-alerts {
  font-size: 0.9rem;
  color: #9793a0;
  font-style: italic;
  text-align: center;
  padding: 10px 0;
}

.mt-4 {
  margin-top: 15px;
}
</style>