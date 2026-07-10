<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { IceCream } from 'lucide-vue-next' 
import orderService from '@/services/orderService'
import {ShoppingBag} from 'lucide-vue-next'

const router = useRouter()

// --- ESTADOS REACTIVOS ---
const ordersList = ref<any[]>([])
const isLoading = ref(true)
const errorMessage = ref('')

// Almacena los datos del distribuidor para usarlos como respaldo en la vista
const fallbackUserCompany = ref('Distribuidor Di Creme')
const fallbackUserAddress = ref('Dirección Registrada')

// Rescata los pedidos procesados del distribuidor activo
const fetchDistributorOrders = async () => {
  try {
    isLoading.value = true
    const userParsed = localStorage.getItem('user')
    
    if (!userParsed) {
      errorMessage.value = 'No se encontró una sesión activa.'
      return
    }
    
    const userObj = JSON.parse(userParsed)
    const distributorId = userObj.id
    
    // Guardamos los datos de sesión para usarlos si las columnas del backend vienen vacías
    fallbackUserCompany.value = userObj.nombre_empresa || userObj.nombre || 'Distribuidor Di Creme'
    fallbackUserAddress.value = userObj.direccion || 'Dirección Registrada'

    // Llamada en vivo usando tu servicio de Axios
    const response = await orderService.getOrdersByDistributor(distributorId)
    ordersList.value = response.data || []
    
    console.log('Orders logs fetched successfully:', ordersList.value)

  } catch (error) {
    console.error('Error fetching orders logs:', error)
    
  } finally {
    isLoading.value = false
  }
}

// Mapea el id_estado_pedido a un string legible en español
const getOrderStatusLabel = (statusId: number): string => {
  if (statusId === 1) return 'Validación'
  if (statusId === 2) return 'En preparación'
  if (statusId === 3) return 'En despacho'
  if (statusId === 4) return 'Entregado'
  return 'Pendiente'
}

onMounted(() => {
  fetchDistributorOrders()
})

// Formatea las fechas al estándar chileno (DD/MM/AAAA)
const formatDate = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('es-CL')
}

// Formatea los valores numéricos a dinero con separador de miles
const formatCurrency = (value: any) => {
  const safeValue = (value !== undefined && value !== null) ? Number(value) : 0
  return `$${safeValue.toLocaleString('es-CL')}`
}
</script>

<template>
  <div class="my-orders-page">
    <main class="history-container">
      <div class="title-section">
        <h2 class="main-title">Mis Pedidos Históricos</h2>
        <p class="page-subtitle">Revisa el estado y detalle de tus compras procesadas.</p>
        <div class="title-line"></div>
      </div>

      <div v-if="isLoading" class="loading-state">
        <IceCream class="spinner" :size="40" color="#e4869f" />
        <p>Buscando tus pedidos...</p>
      </div>

      <div v-else-if="ordersList.length === 0" class="empty-state">
        <ShoppingBag :size="48" color="#b5b2bc" />
        <p class="empty-text">Aún no tienes pedidos registrados.</p>
      </div>

      <div v-else class="orders-list">
        <div 
          v-for="order in ordersList" 
          :key="order.id" 
          class="order-item-card"
          @click="router.push(`/pedido/${order.id}`)"
        >
          <div class="card-left">
            <div class="file-icon-box">
              <ShoppingBag :size="24" color="#322c44" />
            </div>
            <div class="order-meta">
              <h4 class="order-number">Pedido N° {{ String(order.id).padStart(6, '0') }}</h4>
              <div class="time-group">
                <span class="time-item"><Calendar :size="14" /> {{ formatDate(order.fecha_creacion ?? order.created_at) }}</span>
              </div>
            </div>
          </div>

          <div class="card-right">
            <span class="status-badge" :class="'status-' + (order.id_estado_pedido ?? 1)">
              {{ order.estado_nombre ?? getOrderStatusLabel(order.id_estado_pedido) }}
            </span>
            <span class="order-total">
              {{ formatCurrency(order.monto_final ?? order.total ?? 0) }}
            </span>
            <ChevronRight :size="20" color="#b5b2bc" />
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.my-orders-page { background-color: #eeedee; min-height: 100vh; padding-bottom: 60px; }
.history-container { max-width: 850px; margin: 35px auto; padding: 0 20px; }

.title-section { margin-bottom: 30px; }
.main-title { font-size: 1.25rem; font-weight: 800; color: #1a1624; margin: 0 0 6px 0; text-align: left; }
.title-line { height: 2px; background-color: #e4869f; width: 100%; margin-top: 10px; }

.orders-list { display: flex; flex-direction: column; gap: 15px; }

.order-item-card {
  background: white; border-radius: 18px; padding: 16px 24px;
  display: flex; justify-content: space-between; align-items: center;
  cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.02);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.order-item-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(228, 134, 159, 0.12); }

.card-left, .card-right { display: flex; align-items: center; gap: 20px; }

.file-icon-box { background-color: #f3eff2; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.order-number { margin: 0 0 4px 0; font-weight: 700; color: #1a1624; }
.time-group { display: flex; gap: 10px; color: #888; font-size: 0.8rem; }
.time-item { display: flex; align-items: center; gap: 4px; }

.status-badge { padding: 6px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; min-width: 100px; text-align: center; }
.order-total { font-weight: 800; color: #1a1624; font-size: 1.05rem; min-width: 80px; text-align: right; }

/* Estados */
.status-1 { background-color: #fffbeb; color: #d97706; border: 1px solid #fef3c7; }
.status-2 { background-color: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
.status-3 { background-color: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
.status-4 { background-color: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }

/* Estados vacíos */
.loading-state, .empty-state { background-color: white; border-radius: 20px; padding: 50px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
.empty-text { color: #666; margin-top: 15px; }
.spinner { animation: rotate 2s linear infinite; }
@keyframes rotate { 100% { transform: rotate(360deg); } }
</style>