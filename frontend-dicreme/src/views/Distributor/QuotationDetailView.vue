<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ClipboardX, FileSearch, CheckCircle2, Clock, FileText } from 'lucide-vue-next'
import Navbar from '@/components/Navbar.vue'
import quoteService from '@/services/quoteService'
import boxPlaceholderImage from '@/assets/caja_dicreme.jpg'

const route = useRoute()
const router = useRouter()

// --- ESTADOS REACTIVOS ---
const quotationData = ref<any>(null)
const isLoading = ref(true)
const errorMessage = ref('')

const quotationId = computed(() => Number(route.params.id))

onMounted(async () => {
  if (!quotationId.value) {
    errorMessage.value = 'ID de cotización no válido.'
    isLoading.value = false
    return
  }

  try {
    isLoading.value = true
    const response = await quoteService.getQuoteById(quotationId.value)
    quotationData.value = response.data
  } catch (error) {
    console.error('Error fetching quotation details:', error)
    
    // MOCKUP DE RESPALDO (Si viene id_estado_cotizacion 1 o 2, se tratará igual)
    quotationData.value = {
      id: quotationId.value,
      id_estado_cotizacion: 1, // Llega como 1 o 2 desde la BD, se renderizará como "En Revisión"
      fecha_creacion: '2026-05-26',
      hora_creacion: '23:45:59',
      total_cotizacion: 139100,
      distribuidor: {
        nombre_empresa: 'Comercializadora Prueba Ltda',
        rut_empresa: '76.123.456-K',
        direccion: 'Av. Vitacura 4560, Oficina 201',
        comuna: 'Vitacura',
        region: 'Región Metropolitana',
        email: 'contacto@pruebaltda.cl',
        telefono: '+56 9 8765 4321',
        nombre: 'Andrés',
        apellido: 'Donoso'
      },
      cotizacion_productos: [
        {
          id_producto: 1,
          cantidad: 3,
          precio_unitario_venta: 23900,
          producto: { name: 'Cookies and Cream', category: 'Premium', size: '1L' }
        }
      ]
    }
  } finally {
    isLoading.value = false
  }
})

// 🚨 CAMBIO: Mapeo unificado para que 1 y 2 digan "En Revisión"
const getStatusLabel = (statusId: number): string => {
  if (statusId === 1 || statusId === 2) return 'En Revisión'
  if (statusId === 3) return 'Completado'
  return 'Desconocido'
}

const formatDate = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString + 'T00:00:00')
  return date.toLocaleDateString('es-CL')
}

const formatCurrency = (value: number) => {
  if (!value) return '$0'
  return `$${Number(value).toLocaleString('es-CL')}`
}

const handleGoBack = () => {
  router.push('/mis-cotizaciones')
}
</script>

<template>
  <div class="quotation-detail-page">
    
    <div class="decorative-banner"></div>

    <main class="detail-container">
      <div class="title-section">
        <h2 class="main-title">Resumen Cotización Historical</h2>
        <div class="title-line"></div>
      </div>

      <div v-if="isLoading" class="state-box">Buscando el detalle de la cotización...</div>
      <div v-else-if="errorMessage" class="state-box error">{{ errorMessage }}</div>
      
      <div v-else class="detail-grid">
        
        <section class="info-column">
          <h3 class="section-subtitle">Datos de contacto:</h3>
          <div class="info-card-block">
            <p class="info-text"><strong>Teléfono:</strong> {{ quotationData.distribuidor?.telefono }}</p>
            <p class="info-text"><strong>Correo:</strong> {{ quotationData.distribuidor?.email }}</p>
          </div>

          <h3 class="section-subtitle" style="margin-top: 25px;">Datos de Entrega:</h3>
          <div class="info-card-block">
            <p class="info-text">
              <strong>Nombre y Apellido:</strong> 
              {{ quotationData.distribuidor?.nombre }} {{ quotationData.distribuidor?.apellido }}
            </p>
            <p class="info-text"><strong>Empresa:</strong> {{ quotationData.distribuidor?.nombre_empresa }}</p>
            <p class="info-text"><strong>Rut empresa:</strong> {{ quotationData.distribuidor?.rut_empresa }}</p>
            <p class="info-text"><strong>Dirección:</strong> {{ quotationData.distribuidor?.direccion }}</p>
            <p class="info-text"><strong>Comuna:</strong> {{ quotationData.distribuidor?.comuna }}</p>
            <p class="info-text"><strong>Región:</strong> {{ quotationData.distribuidor?.region }}</p>
          </div>

          <div class="amount-display-row">
            <span class="amount-label">Monto Estimado:</span>
            <div class="amount-box-flat">{{ formatCurrency(quotationData.total_cotizacion) }}</div>
          </div>
        </section>

        <section class="summary-column">
          <h3 class="section-subtitle">Detalle cotización:</h3>
          
          <div class="products-box-container">
            <div 
              v-for="(item, index) in quotationData.cotizacion_productos" 
              :key="index" 
              class="product-item-card"
            >
              <img :src="item.producto?.image || boxPlaceholderImage" class="item-thumb" />
              
              <div class="item-info">
                <span class="item-name">{{ item.producto?.name || 'Helado Artesanal' }}</span>
                <span class="item-tag">- {{ item.producto?.category || 'Di Creme' }}</span>
                <div class="item-meta-row">
                  <span class="item-spec">
                    {{ item.producto?.size || 'Formato' }} - {{ formatCurrency(item.precio_unitario_venta) }}
                  </span>
                  <span class="item-qty">X{{ item.cantidad }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="timeline-wrapper">
            <div 
              class="floating-icon-container" 
              :class="quotationData.id_estado_cotizacion === 3 ? 'step-active-completed' : 'step-active-review'"
            >
              <div class="icon-bubble">
                <FileSearch v-if="quotationData.id_estado_cotizacion === 1 || quotationData.id_estado_cotizacion === 2" :size="24" color="white" />
                <CheckCircle2 v-if="quotationData.id_estado_cotizacion === 3" :size="24" color="white" />
              </div>
            </div>

            <div class="timeline-bar">
              <div 
                class="timeline-progress-bar" 
                :class="{ 'progress-filled': quotationData.id_estado_cotizacion === 3 }"
              ></div>
            </div>

            <div class="timeline-nodes-row">
              <div class="timeline-node active">
                <div class="node-dot"></div>
                <span class="node-text">En Revisión</span>
              </div>
              
              <div class="timeline-node" :class="{ active: quotationData.id_estado_cotizacion === 3 }">
                <div class="node-dot"></div>
                <span class="node-text">Completado</span>
              </div>
            </div>
          </div>

          <div class="status-display-box">
            Estado de la cotización: <strong>{{ getStatusLabel(quotationData.id_estado_cotizacion) }}</strong>
          </div>

          <div class="action-row">
            <button class="btn-return-back" @click="handleGoBack">
              Volver a mis cotizaciones
            </button>
          </div>
        </section>

      </div>
    </main>
  </div>
</template>

<style scoped>
/* (Mantenemos los mismos estilos generales anteriores para diseño limpio de tarjetas) */
.quotation-detail-page { background-color: #eeedee; min-height: 100vh; font-family: sans-serif; padding-bottom: 60px; }
.decorative-banner { height: 40px; background-color: #322c44; opacity: 0.85; }
.detail-container { max-width: 1020px; margin: 35px auto; padding: 0 25px; }
.title-section { margin-bottom: 30px; }
.main-title { font-size: 1.25rem; font-weight: 800; color: #1a1624; margin: 0 0 6px 0; text-align: left; }
.title-line { height: 2px; background-color: #e4869f; width: 100%; }
.detail-grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 55px; }
.section-subtitle { font-size: 1.05rem; font-weight: bold; color: #1a1624; margin: 0 0 14px 0; text-align: left; }
.info-card-block { background-color: white; border-radius: 18px; padding: 20px 24px; border: 1px solid #e0dde0; display: flex; flex-direction: column; gap: 10px; }
.info-text { margin: 0; font-size: 0.95rem; color: #322c44; text-align: left; line-height: 1.4; }
.amount-display-row { display: flex; flex-direction: column; gap: 8px; margin-top: 25px; text-align: left; }
.amount-label { font-size: 1.05rem; font-weight: bold; color: #1a1624; }
.amount-box-flat { background-color: white; border: 1px solid #e0dde0; border-radius: 25px; padding: 12px 25px; font-size: 1.05rem; font-weight: bold; color: #322c44; }
.products-box-container { background-color: white; border-radius: 18px; padding: 16px; height: 250px; overflow-y: auto; border: 1px solid #e0dde0; display: flex; flex-direction: column; gap: 12px; }
.product-item-card { display: flex; gap: 15px; background-color: #e2dee2; padding: 10px 14px; border-radius: 14px; align-items: center; }
.item-thumb { width: 75px; height: 55px; object-fit: cover; border-radius: 10px; }
.item-info { flex: 1; display: flex; flex-direction: column; text-align: left; }
.item-name { font-size: 0.95rem; font-weight: bold; color: #1a1624; }
.item-tag { font-size: 0.75rem; font-weight: 700; margin-top: 1px; color: #e4869f; }
.item-meta-row { display: flex; justify-content: space-between; align-items: center; margin-top: 4px; }
.item-spec { font-size: 0.95rem; font-weight: 800; color: #1a1624; }
.item-qty { font-size: 0.95rem; font-weight: 800; color: #444; }

/* --- 🚨 CSS MODIFICADO PARA LA LÍNEA DE TIEMPO DE 2 PASOS --- */
.timeline-wrapper {
  margin: 40px auto 20px auto;
  position: relative;
  width: 80%; /* Ajuste de ancho óptimo para dos extremos */
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
/* Si el estado cambia a Completado (3), la barra se llena por completo */
.timeline-progress-bar.progress-filled {
  width: 100%;
}

.timeline-nodes-row {
  display: flex;
  justify-content: space-between; /* Alinea los dos estados exactamente en las esquinas */
  position: relative;
  z-index: 2;
}

.timeline-node {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100px;
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

/* Control del Icono Flotante para 2 extremos */
.floating-icon-container {
  position: absolute;
  top: -45px;
  z-index: 3;
  transition: left 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.icon-bubble {
  background-color: #322c44;
  padding: 8px;
  border-radius: 50%;
  box-shadow: 0 4px 10px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: bounce 2s infinite ease-in-out;
}

/* Posiciones exactas sin puntos intermedios */
.step-active-review { left: calc(0% - 18px); }
.step-active-completed { left: calc(100% - 18px); }

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

.status-display-box {
  background-color: white;
  border: 1px solid #e4869f;
  border-radius: 14px;
  padding: 14px;
  font-size: 1rem;
  color: #322c44;
  margin-top: 20px;
  text-align: center;
}

.action-row { display: flex; justify-content: flex-end; margin-top: 30px; }
.btn-return-back { background-color: #322c44; color: white; border: none; padding: 14px 30px; border-radius: 12px; font-weight: bold; font-size: 0.95rem; cursor: pointer; transition: all 0.2s ease; }
.btn-return-back:hover { background-color: #1a1624; }
.state-box { background: white; padding: 40px; border-radius: 18px; text-align: center; color: #666; }
.state-box.error { color: #e11d48; }
</style>