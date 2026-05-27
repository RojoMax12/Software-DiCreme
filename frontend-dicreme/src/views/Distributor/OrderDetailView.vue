<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ShieldCheck, PackageOpen, Truck, CheckCircle2, IceCream } from 'lucide-vue-next'
import orderService from '@/services/orderService'
import boxPlaceholderImage from '@/assets/caja_dicreme.jpg'

const route = useRoute()
const router = useRouter()

// --- ESTADOS REACTIVOS ---
const orderData = ref<any>(null)
const isLoading = ref(true)
const errorMessage = ref('')

// Fallbacks locales basados en la sesión activa del localStorage
const fallbackCompany = ref('Distribuidor Di Creme')
const fallbackAddress = ref('Dirección registrada')
const fallbackPhone = ref('No registrado')
const fallbackEmail = ref('No registrado')
const fallbackName = ref('Representante Di Creme')

// Captura el ID del pedido directo desde los parámetros de la URL
const orderId = computed(() => Number(route.params.id))

// --- CARGA DE DATOS DESDE LA API ---
onMounted(async () => {
  if (!orderId.value) {
    errorMessage.value = 'ID de pedido no válido.'
    isLoading.value = false
    return
  }

  // Cargamos los datos preventivos de sesión para los bloques vacíos
  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      fallbackCompany.value = userObj.nombre_empresa || 'Distribuidor Di Creme'
      fallbackAddress.value = userObj.direccion || 'Dirección registrada'
      fallbackPhone.value = userObj.telefono || 'No registrado'
      fallbackEmail.value = userObj.email || userObj.correo_electronico || 'No registrado'
      fallbackName.value = userObj.nombre ? `${userObj.nombre} ${userObj.apellido || ''}` : 'Representante Di Creme'
    } catch (e) {
      console.error('Error parsing user session storage fallback:', e)
    }
  }

  try {
    isLoading.value = true
    // Llama al endpoint de Laravel: GET api/pedidos/{id}
    const response = await orderService.getOrderById(orderId.value)
    orderData.value = response.data
  } catch (error) {
    console.error('Error fetching order details:', error)
    errorMessage.value = 'Hubo un problema al conectar con el servidor.'
  } finally {
    isLoading.value = false
  }
})

// Mapea el identificador relacional a un nombre legible en español
const getOrderStatusLabel = (statusId: number): string => {
  if (statusId === 1) return 'Validación'
  if (statusId === 2) return 'En preparación'
  if (statusId === 3) return 'En despacho'
  if (statusId === 4) return 'Entregado'
  return 'En proceso'
}

// Formatea los montos numéricos a pesos chilenos de forma segura
const formatCurrency = (value: number) => {
  const safeValue = value ? Number(value) : 0
  return `$${safeValue.toLocaleString('es-CL')}`
}

const handleGoBack = () => {
  router.push('/mis-pedidos')
}
</script>

<template>
  <div class="order-detail-page">
    
    <main class="detail-container">
      <div class="title-section">
        <h2 class="main-title">Resumen Pedido N° {{ String(orderId).padStart(6, '0') }}</h2>
        <div class="title-line"></div>
      </div>

      <div v-if="isLoading" class="state-box">
        <IceCream class="spinner" :size="40" color="#e4869f" />
        <p>Cargando el estado de tu pedido...</p>
      </div>
      
      <div v-else-if="errorMessage" class="state-box error">{{ errorMessage }}</div>
      
      <div v-else class="detail-grid">
        
        <section class="info-column">
          <h3 class="section-subtitle">Datos de contacto:</h3>
          <div class="info-card-block">
            <p class="info-text"><strong>Teléfono:</strong> {{ orderData.telefono || orderData.id_usuario_distribuidor?.telefono || fallbackPhone }}</p>
            <p class="info-text"><strong>Correo:</strong> {{ orderData.email || orderData.id_usuario_distribuidor?.email || fallbackEmail }}</p>
          </div>

          <h3 class="section-subtitle" style="margin-top: 25px;">Datos de Entrega:</h3>
          <div class="info-card-block">
            <p class="info-text"><strong>Nombre y Apellido:</strong> {{ orderData.nombre_receptor || fallbackName }}</p>
            <p class="info-text"><strong>Empresa:</strong> {{ orderData.empresa || fallbackCompany }}</p>
            <p class="info-text"><strong>Rut empresa:</strong> {{ orderData.rut_empresa || 'N/A' }}</p>
            <p class="info-text"><strong>Dirección:</strong> {{ orderData.direccion_despacho || fallbackAddress }}</p>
            <p class="info-text"><strong>Comuna:</strong> {{ orderData.comuna || 'Comuna Registrada' }}</p>
          </div>

          <div class="amount-group">
            <div class="amount-row">
              <span class="amount-label">Monto Estimado:</span>
              <div class="amount-box-flat">
                {{ formatCurrency(orderData.monto_estimado) }}
              </div>
            </div>
            
            <div class="amount-row highlighted">
              <span class="amount-label">Monto Final:</span>
              <div class="amount-box-pink">
                {{ formatCurrency(orderData.monto_final) }}
              </div>
            </div>
          </div>
        </section>

        <section class="summary-column">
          <h3 class="section-subtitle">Detalle productos:</h3>
          
          <div class="products-box-container">
            <div v-if="!orderData.pedido_productos && !orderData.pedido_producto" class="empty-products-state">
              Cargando el detalle de los productos...
            </div>

            <div 
              v-else
              v-for="(item, index) in (orderData.pedido_productos ?? orderData.pedido_producto ?? [])" 
              :key="index" 
              class="product-item-card"
            >
              <img :src="item.producto?.image ?? item.producto?.imagen ?? boxPlaceholderImage" class="item-thumb" />
              
              <div class="item-info">
                <span class="item-name">
                  {{ item.producto?.name ?? item.producto?.nombre ?? 'Helado Artesanal' }}
                </span>
                
                <span class="item-tag">
                  - {{ item.producto?.category ?? item.producto?.categoria ?? 'Di Creme' }}
                </span>
                
                <div class="item-meta-row">
                  <span class="item-spec">
                    {{ item.producto?.size ?? item.producto?.formato ?? 'Formato' }} - 
                    {{ formatCurrency(item.precio_unitario_venta) }}
                  </span>
                  
                  <span class="item-qty">X{{ item.cantidad }}</span>
                </div>
              </div>
            </div>
          </div> 
        

          <div class="timeline-wrapper">
            <div class="floating-icon-container" 
                 :class="'step-active-' + (orderData.id_estado_pedido ?? orderData.estado_pedido ?? 1)"
            >
              <div class="icon-bubble">
                <ShieldCheck v-if="(orderData.id_estado_pedido ?? orderData.estado_pedido ?? 1) === 1" :size="24" color="white" />
                <PackageOpen v-if="(orderData.id_estado_pedido ?? orderData.estado_pedido ?? 1) === 2" :size="24" color="white" />
                <Truck v-if="(orderData.id_estado_pedido ?? orderData.estado_pedido ?? 1) === 3" :size="24" color="white" />
                <CheckCircle2 v-if="(orderData.id_estado_pedido ?? orderData.estado_pedido ?? 1) === 4" :size="24" color="white" />
              </div>
            </div>

            <div class="timeline-bar">
              <div class="timeline-progress-bar" 
                   :class="'progress-fill-' + (orderData.id_estado_pedido ?? orderData.estado_pedido ?? 1)"
              ></div>
            </div>

            <div class="timeline-nodes-row">
              <div class="timeline-node" :class="{ active: (orderData.id_estado_pedido ?? orderData.estado_pedido ?? 1) >= 1 }">
                <div class="node-dot"></div>
                <span class="node-text">Validación</span>
              </div>
              <div class="timeline-node" :class="{ active: (orderData.id_estado_pedido ?? orderData.estado_pedido ?? 1) >= 2 }">
                <div class="node-dot"></div>
                <span class="node-text">Preparación</span>
              </div>
              <div class="timeline-node" :class="{ active: (orderData.id_estado_pedido ?? orderData.estado_pedido ?? 1) >= 3 }">
                <div class="node-dot"></div>
                <span class="node-text">Despachado</span>
              </div>
              <div class="timeline-node" :class="{ active: (orderData.id_estado_pedido ?? orderData.estado_pedido ?? 1) >= 4 }">
                <div class="node-dot"></div>
                <span class="node-text">Entregado</span>
              </div>
            </div>
          </div>

          <div class="status-display-box">
            Estado del pedido: <span class="capitalize-text">{{ orderData.estado_nombre ?? getOrderStatusLabel(orderData.id_estado_pedido) }}</span>
          </div>

          <div class="action-row">
            <button class="btn-return-back" @click="handleGoBack">
              Volver a página principal
            </button>
          </div>
        </section>

      </div>
    </main>
  </div>
</template>

<style scoped>
.order-detail-page {
  background-color: #eeedee;
  min-height: 100vh;
  font-family: sans-serif;
  padding-bottom: 60px;
}

.decorative-banner {
  height: 40px;
  background-color: #322c44;
  opacity: 0.85;
}

.detail-container {
  max-width: 1020px;
  margin: 35px auto;
  padding: 0 25px;
}

.title-section { margin-bottom: 30px; }
.main-title { font-size: 1.25rem; font-weight: 800; color: #1a1624; margin: 0 0 6px 0; text-align: left; }
.title-line { height: 2px; background-color: #e4869f; width: 100%; }

.detail-grid {
  display: grid;
  grid-template-columns: 1.15fr 1fr;
  gap: 55px;
}

.section-subtitle { font-size: 1.05rem; font-weight: bold; color: #1a1624; margin: 0 0 14px 0; text-align: left; }

.info-card-block {
  background-color: white;
  border-radius: 18px;
  padding: 20px 24px;
  border: 1px solid #e0dde0;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.01);
}

.info-text {
  margin: 0;
  font-size: 0.95rem;
  color: #322c44;
  text-align: left;
  line-height: 1.4;
}

.amount-group {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-top: 25px;
}

.amount-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
  text-align: left;
}

.amount-label {
  font-size: 1.05rem;
  font-weight: bold;
  color: #1a1624;
}

.amount-box-flat {
  background-color: white;
  border: 1px solid #e0dde0;
  border-radius: 25px;
  padding: 12px 25px;
  font-size: 1.05rem;
  font-weight: bold;
  color: #322c44;
}

.amount-row.highlighted .amount-box-pink {
  background-color: white;
  border: 2px solid #e4869f;
  border-radius: 25px;
  padding: 12px 25px;
  font-size: 1.1rem;
  font-weight: 800;
  color: #1a1624;
}

.products-box-container {
  background-color: white;
  border-radius: 18px;
  padding: 16px;
  height: 250px;
  overflow-y: auto;
  border: 1px solid #e0dde0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.empty-products-state {
  color: #999;
  font-size: 0.9rem;
  margin: auto;
  font-style: italic;
}

.product-item-card {
  display: flex;
  gap: 15px;
  background-color: #e2dee2;
  padding: 10px 14px;
  border-radius: 14px;
  align-items: center;
}

.item-thumb { width: 75px; height: 55px; object-fit: cover; border-radius: 10px; }
.item-info { flex: 1; display: flex; flex-direction: column; text-align: left; }
.item-name { font-size: 0.95rem; font-weight: bold; color: #1a1624; }
.item-tag { font-size: 0.75rem; font-weight: 700; margin-top: 1px; color: #e4869f; }
.item-meta-row { display: flex; justify-content: space-between; align-items: center; margin-top: 4px; }
.item-spec { font-size: 0.95rem; font-weight: 800; color: #1a1624; }
.item-qty { font-size: 0.95rem; font-weight: 800; color: #444; }

.timeline-wrapper {
  margin: 50px auto 25px auto;
  position: relative;
  width: 90%;
}

.timeline-bar {
  position: absolute;
  top: 6px;
  left: 0;
  width: 100%;
  height: 4px;
  background-color: #e0dde0;
  z-index: 1;
  border-radius: 2px;
}

.timeline-progress-bar {
  height: 100%;
  background-color: #e4869f;
  width: 0%;
  transition: width 0.4s ease;
}

.progress-fill-1 { width: 0%; }
.progress-fill-2 { width: 33.33%; }
.progress-fill-3 { width: 66.66%; }
.progress-fill-4 { width: 100%; }

.timeline-nodes-row {
  display: flex;
  justify-content: space-between;
  position: relative;
  z-index: 2;
}

.timeline-node {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 70px;
}

.node-dot {
  width: 14px;
  height: 14px;
  background-color: #b5b2bc;
  border-radius: 50%;
  border: 2px solid #eeedee;
  transition: background-color 0.3s ease;
}

.timeline-node.active .node-dot {
  background-color: #e4869f;
}

.node-text {
  font-size: 0.75rem;
  font-weight: 700;
  color: #7c7289;
  margin-top: 8px;
  white-space: nowrap;
}

.timeline-node.active .node-text {
  color: #1a1624;
}

.floating-icon-container {
  position: absolute;
  top: -45px;
  z-index: 3;
  transition: left 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.icon-bubble {
  background-color: #e4869f;
  padding: 8px;
  border-radius: 50%;
  box-shadow: 0 4px 10px rgba(228, 134, 159, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: bounce 2s infinite ease-in-out;
}

.step-active-1 { left: calc(0% - 18px); }
.step-active-2 { left: calc(33.33% - 18px); }
.step-active-3 { left: calc(66.66% - 18px); }
.step-active-4 { left: calc(100% - 18px); }

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

.status-display-box {
  background-color: white;
  border: 1px solid #e4869f;
  border-radius: 14px;
  padding: 14px 20px;
  font-size: 1.05rem;
  font-weight: bold;
  color: #1a1624;
  margin-top: 25px;
  text-align: left;
  box-shadow: 0 2px 8px rgba(228, 134, 159, 0.04);
}

.capitalize-text {
  text-transform: lowercase;
}
.capitalize-text::first-letter {
  text-transform: uppercase;
}

.action-row {
  display: flex;
  justify-content: flex-end;
  margin-top: 30px;
}

.btn-return-back {
  background-color: #322c44;
  color: white;
  border: none;
  padding: 14px 30px;
  border-radius: 12px;
  font-weight: bold;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 10px rgba(50, 44, 68, 0.15);
}

.btn-return-back:hover { background-color: #1a1624; }

.state-box { background: white; padding: 50px; border-radius: 18px; text-align: center; color: #7c7289; font-weight: 600; }
.state-box.error { color: #e11d48; }
.spinner { animation: rotate 2s linear infinite; margin-bottom: 10px; }
@keyframes rotate { 100% { transform: rotate(360deg); } }
</style>