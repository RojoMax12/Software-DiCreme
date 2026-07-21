<template>
  <div class="inventory-view">
    <header class="page-header">
      <h1 class="page-title">Inventario</h1>
    </header>

    <div class="header-divider"></div>

    <main class="main-content-grid">
      
      <section class="table-column">
        
        <div class="toolbar">

          <div class="filter-wrapper">
            <select v-model="selectedFormat" class="btn-filter-select">
              <option value="todos">Filtrar</option>
              <option value="1L">Formato: 1L</option>
              <option value="2.5L">Formato: 2.5L</option>
              <option value="5L">Formato: 5L</option>
              <option value="10L">Formato: 10L</option>
            </select>
          </div>
          
          <div class="search-input-wrapper">
            <input 
              type="text" 
              placeholder="Busca por helado" 
              class="search-input"
              v-model="searchQuery"
            />
            <Search :size="18" class="search-icon" />
          </div>

          <button class="btn-check-toolbar" @click="openCheckerModal" title="Simular pedido y verificar quiebres de stock">
            <ShieldAlert :size="16" />
            <span>Verificar Disponibilidad</span>
          </button>
        </div>

        <div class="table-container">
          <div class="table-scroll">
            <table class="inventory-table">
            <thead>
              <tr>
                <th style="text-align: left; padding-left: 30px; width: 25%;">Helado</th>
                <th style="width: 15%;">Formato</th>
                <th style="width: 15%;">Stock</th>
                <th style="width: 25%;">última<br>actualización</th>
                <th style="width: 20%;">Detalle<br>lotes</th>
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
              <tr v-if="filteredInventory.length === 0">
                <td colspan="5" style="padding: 30px; color: #9793a0; font-style: italic;">
                  No se encontraron helados que coincidan con "{{ searchQuery }}".
                </td>
              </tr>

              <tr v-else v-for="item in filteredInventory" :key="item.id">
                <td style="text-align: left; padding-left: 30px; font-weight: 500;">{{ item.nombre_producto || '-' }}</td>
                <td>{{ item.formato?.nombre_formato || '-' }}</td>
                <td style="font-weight: 600;">{{ item.cantidad_total }}</td>
                <td>{{ formatDate(item.ultima_actualizacion_lote) }}</td>
                <td>
                  <button class="btn-details" @click="goToDetails(item)">VER DETALLES</button>
                </td>
              </tr>
            </tbody>
            </table>
          </div>
        </div>
      </section>

      <aside class="alerts-column">
        
        <!-- CARD 1: Alertas de Stock Mínimo y Déficit -->
        <div class="alert-card">
          <div class="floating-badge badge-pink">Stock</div>
          <div class="card-header-group">
            <h3 class="alert-title">Déficit en inventario</h3>
          </div>
          
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

        <!-- CARD 2: Alertas de Lotes por Vencer -->
        <div class="alert-card mt-4">
          <div class="floating-badge badge-pink">Lotes</div>
          <h3 class="alert-title">Atención a lotes por vencer</h3>
          
          <div class="alert-list">
            <div v-if="expiringbatchesAlerts.length === 0" class="empty-alerts">
              No hay lotes por vencer en los próximos 30 días.
            </div>

            <div 
              v-else 
              v-for="batch in expiringbatchesAlerts" 
              :key="batch.id" 
              class="alert-item"
            >
              <span class="item-name">{{ batch.name }}</span>
              <span class="status-pill" :class="batch.pillClass">
                {{ batch.statusText || batch.batchNumber }}
              </span>
            </div>
          </div>
        </div>

      </aside>
    </main>

    <!-- Modal Verificador de Disponibilidad y Faltantes por Lote -->
    <Transition name="fade">
      <div v-if="isCheckerOpen" class="checker-modal-overlay" @click.self="isCheckerOpen = false">
        <div class="checker-modal-card">
          <header class="checker-modal-header">
            <div class="checker-title-group">
              <ShieldAlert :size="24" color="#e4869f" />
              <h3>Verificador de Disponibilidad de Lotes</h3>
            </div>
            <button class="btn-close-checker" @click="isCheckerOpen = false">&times;</button>
          </header>

          <div class="checker-modal-body">
            <p class="checker-intro">
              Selecciona un helado e ingresa la cantidad solicitada para comprobar la asignación por lotes y detectar unidades faltantes antes de autorizar un pedido.
            </p>

            <div class="checker-form-row">
              <div class="form-group flex-2">
                <label>Helado a evaluar:</label>
                <select v-model="selectedProductForCheck" class="checker-select">
                  <option :value="null" disabled>-- Selecciona un producto --</option>
                  <option v-for="item in productsInventory" :key="item.id" :value="item.id">
                    {{ item.nombre_producto }} ({{ item.formato?.nombre_formato || 'Sin formato' }}) - Stock actual: {{ item.cantidad_total }} unids.
                  </option>
                </select>
              </div>

              <div class="form-group flex-1">
                <label>Cantidad requerida:</label>
                <input 
                  type="number" 
                  v-model.number="testQuantity" 
                  min="1" 
                  class="checker-input"
                  placeholder="Ej: 15"
                />
              </div>

              <button 
                class="btn-run-check" 
                :disabled="!selectedProductForCheck || testQuantity <= 0 || isChecking"
                @click="runStockCheck"
              >
                {{ isChecking ? 'Verificando...' : 'COMPROBAR' }}
              </button>
            </div>

            <!-- RESULTADO DE LA COMPROBACIÓN -->
            <div v-if="checkResult" class="checker-result-box" :class="{ 'is-viable': checkResult.viable, 'is-deficit': !checkResult.viable }">
              <div class="result-header">
                <div class="result-badge" :class="checkResult.viable ? 'badge-success' : 'badge-danger'">
                  {{ checkResult.viable ? 'STOCK SUFICIENTE' : 'DÉFICIT DETECTADO' }}
                </div>
                <h4 class="result-summary">{{ checkResult.resumen }}</h4>
              </div>

              <div v-if="checkResult.mensajes_alerta && checkResult.mensajes_alerta.length > 0" class="result-alerts">
                <div v-for="(msg, idx) in checkResult.mensajes_alerta" :key="idx" class="alert-msg-item">
                  <AlertTriangle :size="16" class="alert-icon" />
                  <span>{{ msg }}</span>
                </div>
              </div>

              <div v-if="checkResult.desglose_productos && checkResult.desglose_productos.length > 0" class="result-breakdown">
                <h5>Desglose de asignación por lotes:</h5>
                
                <div v-for="prod in checkResult.desglose_productos" :key="prod.id_producto" class="prod-breakdown-card">
                  <div class="prod-breakdown-header">
                    <strong>{{ prod.nombre_completo }}</strong>
                    <span class="stock-info-tag">
                      Requerido: {{ prod.cantidad_requerida }} | Disponible en lotes: {{ prod.cantidad_disponible }}
                    </span>
                  </div>

                  <div v-if="prod.desglose_lotes.length > 0" class="lots-used-list">
                    <div v-for="lote in prod.desglose_lotes" :key="lote.id_lote" class="lot-used-item">
                      <span class="lot-name">{{ lote.numero_lote }} (Vence: {{ formatDate(lote.fecha_vencimiento) }})</span>
                      <span class="lot-calc">Aporta {{ lote.cantidad_a_usar }} unids. (Quedan {{ lote.cantidad_disponible_lote - lote.cantidad_a_usar }} unids. en el lote)</span>
                    </div>
                  </div>
                  <div v-else class="empty-lots-warn">
                    ❌ No hay lotes activos disponibles para este producto.
                  </div>

                  <div v-if="prod.cantidad_faltante > 0" class="deficit-callout">
                    🚨 <strong>Falta de unidades:</strong> Se requieren {{ prod.cantidad_faltante }} unidades adicionales que no existen en ningún lote activo.
                  </div>
                </div>
              </div>

            </div>

          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Search, ShieldAlert, AlertTriangle } from 'lucide-vue-next'
import productService from '@/services/productService'
import batchService from '@/services/batchService'
import { useNotification } from '@/composables/useNotification'

const router = useRouter()
const { notify } = useNotification()

// Estados generales
const isAddModalOpen = ref(false)
const isLoading = ref(true) 
const productsInventory = ref<any[]>([])
const searchQuery = ref('')
const selectedFormat = ref('todos')

// Alertas API reales
const apiLowStockAlerts = ref<any[]>([])
const apiExpiringBatches = ref<any[]>([])

// Verificador de Stock
const isCheckerOpen = ref(false)
const selectedProductForCheck = ref<number | null>(null)
const testQuantity = ref<number>(10)
const checkResult = ref<any | null>(null)
const isChecking = ref(false)

const filteredInventory = computed(() => {
  let result = productsInventory.value

  if (searchQuery.value.trim()) {
    const query = searchQuery.value.trim().toLowerCase()
    result = result.filter(item => {
      const productName = item.nombre_producto?.toLowerCase() || ''
      return productName.includes(query)
    })
  }

  if (selectedFormat.value !== 'todos') {
    result = result.filter(item => {
      return item.formato?.nombre_formato === selectedFormat.value
    })
  }

  return result
})

const fetchInventoryData = async () => {
  try {
    isLoading.value = true
    const responseInv = await productService.getInventory()
    productsInventory.value = responseInv.data.data || responseInv.data || []
  } catch (error) {
    console.error('Error al cargar inventario:', error)
  } finally {
    isLoading.value = false
  }
}

const fetchAlertsData = async () => {
  try {
    const [resPocoStock, resPorVencer] = await Promise.all([
      productService.getLowStockProducts(10),
      batchService.getExpiringBatches(30)
    ])

    const lowStockList = resPocoStock.data?.data || resPocoStock.data || []
    apiLowStockAlerts.value = lowStockList.map((item: any) => ({
      id: item.id,
      name: item.name || `${item.nombre_producto} - ${item.formato?.nombre_formato || 'N/A'}`,
      statusText: item.statusText,
      pillClass: item.pillClass
    }))

    const expiringList = resPorVencer.data?.data || resPorVencer.data || []
    apiExpiringBatches.value = expiringList.map((b: any) => ({
      id: b.id,
      name: b.name || `${b.nombre_producto} - ${b.formato || 'N/A'}`,
      batchNumber: b.batchNumber || `Lote N°${b.id}`,
      cantidad_producto: b.cantidad_producto,
      statusText: b.statusText || b.batchNumber || `Lote N°${b.id}`,
      pillClass: b.pillClass
    }))
  } catch (e) {
    console.error('Error al cargar alertas del servidor:', e)
  }
}

onMounted(async () => {
  await Promise.all([fetchInventoryData(), fetchAlertsData()])
})

// Computados para Alertas de Stock y Lotes
const deficitAlerts = computed(() => {
  if (apiLowStockAlerts.value.length > 0) {
    return apiLowStockAlerts.value
  }
  
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

const expiringbatchesAlerts = computed(() => {
  return apiExpiringBatches.value
})

const handleSaveProduct = async (datosSimulados: any) => {
  console.log('Datos del nuevo producto:', datosSimulados)
  isAddModalOpen.value = false
  notify('Operación completada', 'success')
  await fetchInventoryData()
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
    return dateString;
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

// LÓGICA DEL VERIFICADOR DE QUIEBRES DE STOCK
const openCheckerModal = () => {
  if (productsInventory.value.length > 0 && !selectedProductForCheck.value) {
    selectedProductForCheck.value = productsInventory.value[0].id
  }
  checkResult.value = null
  isCheckerOpen.value = true
}

const runStockCheck = async () => {
  if (!selectedProductForCheck.value || testQuantity.value <= 0) return

  try {
    isChecking.value = true
    const response = await batchService.verifyStockAvailability([
      { id_producto: selectedProductForCheck.value, cantidad: testQuantity.value }
    ])
    
    checkResult.value = response.data?.data || response.data
  } catch (e) {
    console.error('Error verificando disponibilidad:', e)
    notify('Error al verificar stock del producto', 'error')
  } finally {
    isChecking.value = false
  }
}
</script>

<style scoped>
.inventory-view {
  background-color: #f6f4f6; 
  min-height: 100vh;
  padding: 40px;
  font-family: 'Inter', sans-serif;
  color: #322c44;
}

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

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
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
  transition: transform 0.2s, background-color 0.2s;
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

.main-content-grid {
  display: grid;
  grid-template-columns: 2fr 1.1fr; 
  gap: 40px;
  align-items: start;
}

.toolbar {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
  align-items: center;
}

.filter-wrapper {
  position: relative;
}

.btn-filter-select {
  background-color: white;
  border: none;
  border-radius: 25px;
  padding: 10px 38px 10px 22px;
  font-weight: 700;
  font-size: 0.9rem;
  color: #322c44;
  cursor: pointer;
  outline: none;
  appearance: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23322c44' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><polygon points='22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3'></polygon></svg>");
  background-repeat: no-repeat;
  background-position: right 15px center;
  transition: box-shadow 0.2s;
}

.btn-filter-select:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.search-input-wrapper {
  position: relative;
  flex: 1;
  max-width: 320px;
}

.search-input {
  width: 100%;
  padding: 10px 40px 10px 20px; 
  border-radius: 25px;
  border: none;
  background-color: white;
  font-size: 0.9rem;
  outline: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  color: #322c44;
}

.search-input::placeholder {
  color: #9793a0;
  font-weight: 500;
}

.search-icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #9793a0;
}

.btn-check-toolbar {
  background-color: white;
  border: 1px solid #e4869f;
  color: #e4869f;
  border-radius: 20px;
  padding: 8px 16px;
  font-weight: 700;
  font-size: 0.82rem;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.btn-check-toolbar:hover {
  background-color: #e4869f;
  color: white;
  transform: translateY(-1px);
}

/* CONTENEDOR Y SCROLL DE TABLA DE PRODUCTOS */
.table-container {
  background-color: white;
  border-radius: 25px;
  padding: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.table-scroll {
  max-height: calc(100vh - 280px);
  min-height: 400px;
  overflow-y: auto;
  overflow-x: hidden;
  border-radius: 20px;
  padding-right: 4px;
}

.table-scroll::-webkit-scrollbar {
  width: 5px;
}
.table-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.table-scroll::-webkit-scrollbar-thumb {
  background: rgba(50, 44, 68, 0.15);
  border-radius: 10px;
}
.table-scroll::-webkit-scrollbar-thumb:hover {
  background: #e4869f;
}

.inventory-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  text-align: center;
}

/* CABECERA DE TABLA CON COLOR GRIS REDONDEADO */
.inventory-table thead th {
  position: sticky;
  top: 0;
  background-color: #e9e7e9 !important;
  color: #322c44 !important;
  font-weight: 800;
  font-size: 0.92rem;
  z-index: 10;
  padding: 16px 10px;
  text-transform: none;
  line-height: 1.2;
}

.inventory-table thead tr th:first-child {
  border-top-left-radius: 18px;
  border-bottom-left-radius: 18px;
}

.inventory-table thead tr th:last-child {
  border-top-right-radius: 18px;
  border-bottom-right-radius: 18px;
}

.inventory-table td {
  padding: 18px 10px;
  font-size: 0.92rem;
  color: #322c44;
  border-bottom: 1px solid #f2eff2;
  vertical-align: middle;
}

/* BOTÓN VER DETALLES EN ROSA CORPO */
.btn-details {
  background-color: #e4869f;
  color: white;
  border: none;
  border-radius: 20px;
  padding: 8px 18px;
  font-weight: 800;
  font-size: 0.75rem;
  letter-spacing: 0.3px;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(228, 134, 159, 0.25);
  transition: transform 0.2s, background-color 0.2s;
}

.btn-details:hover {
  transform: translateY(-1px);
  background-color: #d1728c;
}

/* CARDS DE ALERTAS */
.alerts-column {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.alert-card {
  background-color: white;
  border-radius: 25px;
  padding: 24px;
  position: relative;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.card-header-group {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.floating-badge {
  position: absolute;
  top: -12px;
  right: 20px;
  padding: 4px 16px;
  border-radius: 15px;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
}

.badge-pink { background-color: #ffb8c6; color: white; }

.alert-title {
  font-size: 1.15rem;
  font-weight: 800;
  margin: 0 0 15px 0;
  color: #1a1624;
}

.alert-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 300px;
  overflow-y: auto;
  overflow-x: hidden;
  padding-right: 4px;
}

.alert-list::-webkit-scrollbar {
  width: 4px;
}
.alert-list::-webkit-scrollbar-track {
  background: transparent;
}
.alert-list::-webkit-scrollbar-thumb {
  background: rgba(50, 44, 68, 0.15);
  border-radius: 10px;
}
.alert-list::-webkit-scrollbar-thumb:hover {
  background: #e4869f;
}

.empty-alerts {
  font-size: 0.85rem;
  color: #9793a0;
  font-style: italic;
}

.alert-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 16px;
  background: white;
  border: 1px solid #e8e6e8;
  border-radius: 18px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.item-name {
  font-size: 0.95rem;
  font-weight: 800;
  color: #1a1624;
}

.status-pill {
  padding: 8px 20px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 800;
  color: #322c44;
  text-align: center;
  min-width: 130px;
  display: inline-block;
}

.pill-red {
  background-color: #ff8b9a;
  color: #322c44;
}

.pill-orange {
  background-color: #ffb88c;
  color: #322c44;
}

.pill-yellow {
  background-color: #ffe699;
  color: #322c44;
}

/* MODAL VERIFICADOR DE STOCK */
.checker-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.checker-modal-card {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 680px;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 14px 40px rgba(0, 0, 0, 0.18);
  padding: 24px;
}

.checker-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid #f6f4f6;
  padding-bottom: 12px;
  margin-bottom: 16px;
}

.checker-title-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.checker-title-group h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 800;
  color: #322c44;
}

.btn-close-checker {
  background: none;
  border: none;
  font-size: 1.8rem;
  color: #9793a0;
  cursor: pointer;
}

.checker-intro {
  font-size: 0.86rem;
  color: #666;
  margin-bottom: 20px;
  line-height: 1.4;
}

.checker-form-row {
  display: flex;
  gap: 12px;
  align-items: flex-end;
  margin-bottom: 20px;
  background: #f9f8f9;
  padding: 14px;
  border-radius: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.flex-2 { flex: 2; }
.flex-1 { flex: 1; }

.form-group label {
  font-size: 0.78rem;
  font-weight: 800;
  color: #322c44;
  text-transform: uppercase;
}

.checker-select, .checker-input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #e0dde0;
  font-size: 0.88rem;
  outline: none;
}

.btn-run-check {
  background-color: #e4869f;
  color: white;
  border: none;
  border-radius: 10px;
  padding: 10px 18px;
  font-weight: 800;
  font-size: 0.85rem;
  cursor: pointer;
  height: 42px;
}

.btn-run-check:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.checker-result-box {
  border-radius: 15px;
  padding: 18px;
  border: 1px solid #e0dde0;
}

.checker-result-box.is-viable {
  background: #f4fbf7;
  border-color: #a3e2c2;
}

.checker-result-box.is-deficit {
  background: #fff5f5;
  border-color: #ffc9c9;
}

.result-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
}

.result-badge {
  padding: 4px 10px;
  border-radius: 8px;
  font-weight: 800;
  font-size: 0.75rem;
  color: white;
}

.badge-success { background: #12b886; }
.badge-danger { background: #fa5252; }

.result-summary {
  margin: 0;
  font-size: 0.92rem;
  font-weight: 700;
  color: #322c44;
}

.result-alerts {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
}

.alert-msg-item {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #c5221f;
  font-weight: 700;
  font-size: 0.85rem;
  background: #ffe8e6;
  padding: 8px 12px;
  border-radius: 8px;
}

.prod-breakdown-card {
  background: white;
  border-radius: 10px;
  padding: 14px;
  margin-top: 10px;
  border: 1px solid #eee;
}

.prod-breakdown-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.88rem;
  margin-bottom: 10px;
}

.stock-info-tag {
  font-size: 0.78rem;
  color: #666;
  background: #f0f0f0;
  padding: 2px 8px;
  border-radius: 6px;
}

.lots-used-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.lot-used-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.82rem;
  background: #f8f9fa;
  padding: 6px 10px;
  border-radius: 6px;
}

.lot-name { font-weight: 700; color: #322c44; }
.lot-calc { color: #555; }

.empty-lots-warn {
  font-size: 0.82rem;
  color: #d9480f;
  font-weight: 700;
}

.deficit-callout {
  margin-top: 10px;
  padding: 8px 10px;
  background: #fff0f0;
  border-left: 3px solid #fa5252;
  font-size: 0.82rem;
  color: #c5221f;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 992px) {
  .main-content-grid {
    grid-template-columns: 1fr;
  }
}
</style>
