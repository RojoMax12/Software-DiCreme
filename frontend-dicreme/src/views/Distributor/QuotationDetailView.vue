<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { FileSearch, CheckCircle2, IceCream } from 'lucide-vue-next'
import quoteService from '@/services/quoteService'
import userService from '@/services/userService'
import distributorService from '@/services/distributorService'
import quoteProductService from '@/services/quoteProductService'
import productService from '@/services/productService'
import productCategoryService from '@/services/productCategoryService' 
import boxPlaceholderImage from '@/assets/caja_dicreme.jpg'

const route = useRoute()
const router = useRouter()

// --- ESTADOS REACTIVOS ---
const quotationData = ref<any>(null)
const distributorData = ref<any>(null)
const usuarioDicremeData = ref<any>(null)
const productsData = ref<any[]>([]) 
const isLoading = ref(true)
const errorMessage = ref('')

// Fallbacks de seguridad basados en la sesión activa por si algún campo viene null
const fallbackCompany = ref('Distribuidor Di Creme')
const fallbackAddress = ref('Dirección registrada')
const fallbackPhone = ref('No registrado')
const fallbackEmail = ref('No registrado')
const fallbackName = ref('Recibidor')
const fallbackComuna = ref('Comuna Registrada')

// Captura el ID de la cotización directo desde los parámetros de la URL
const quotationId = computed(() => Number(route.params.id))

// CONTROL DE ESTADOS DE LA LÍNEA DE TIEMPO (Paso 1 o Paso 2)
const currentStep = computed(() => {
  if (!quotationData.value) return 1
  
  // Intenta obtener el ID desde distintas propiedades comunes por seguridad
  const rawStatus = quotationData.value.id_estado_cotizacion 
                 || quotationData.value.estado_id 
                 || quotationData.value.id_estado;
                 
  const statusId = Number(rawStatus);
  
  // Imprime en consola para depurar si sigue fallando
  console.log("Estado de cotización detectado:", statusId, rawStatus);

  // Si el estado es 3 (Completado), vamos al paso 2. Si no, nos quedamos en el 1.
  return statusId === 3 ? 2 : 1
})

// --- CARGA DE DATOS EN CADENA Y PARALELO ---
onMounted(async () => {
  if (!quotationId.value) {
    errorMessage.value = 'ID de cotización no válido.'
    isLoading.value = false
    return
  }

  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      fallbackCompany.value = userObj.nombre_empresa || 'Distribuidor Di Creme'
      fallbackAddress.value = userObj.direccion || 'Dirección registrada'
      fallbackPhone.value = userObj.telefono || 'No registrado'
      fallbackEmail.value = userObj.email || userObj.correo_electronico || 'No registrado'
      fallbackName.value = userObj.nombre ? `${userObj.nombre} ${userObj.apellido || ''}` : 'No registrado'
      fallbackComuna.value = userObj.comuna || userObj.nombre_comuna || 'Comuna Registrada'
    } catch (e) {
      console.error('Error parsing user session storage fallback:', e)
    }
  }

  try {
    isLoading.value = true

    // Obtener el objeto plano de la cotización
    const response = await quoteService.getQuoteById(quotationId.value)
    quotationData.value = response.data

    if (quotationData.value) {
      const idDist = quotationData.value.id_distribuidor
      const idDicreme = quotationData.value.id_usuario_dicreme

      const llamadasSecundarias = []

      // Consulta a la pivot + Resolución de productos + Resolución de categorías en tiempo real
      llamadasSecundarias.push(
        quoteProductService.getByQuotationId(quotationId.value)
          .then(async (res: any) => {
            const pivotItems = res.data || []
            
            const itemsConDetalle = await Promise.all(
              pivotItems.map(async (pivotItem: any) => {
                if (pivotItem.id_producto) {
                  try {
                    const prodRes = await productService.getProductById(pivotItem.id_producto)
                    pivotItem.producto = prodRes.data

                    if (pivotItem.producto && pivotItem.producto.id_categoria) {
                      try {
                        const catRes = await productCategoryService.getCategoryById(pivotItem.producto.id_categoria)
                        pivotItem.producto.categoria_objeto = catRes.data
                      } catch (catErr) {
                        console.error(`Error resolviendo categoría ID ${pivotItem.producto.id_categoria}:`, catErr)
                      }
                    }

                  } catch (err) {
                    console.error(`Error resolviendo maestro para producto ID ${pivotItem.id_producto}:`, err)
                  }
                }
                return pivotItem
              })
            )
            
            productsData.value = itemsConDetalle
          })
          .catch((err: any) => console.error("Error cargando productos desde la tabla pivot:", err))
      )

      // Carga del distribuidor (Cliente) desde distributorService
      if (idDist) {
        llamadasSecundarias.push(
          distributorService.getDistributorById(idDist)
            .then((res: any) => { 
              distributorData.value = res.data 
            })
            .catch((err: any) => console.error("Error en distributorService:", err))
        )
      }

      // Carga del usuario interno (Trabajador) desde userService usando getUserById
      if (idDicreme) {
        llamadasSecundarias.push(
          userService.getUserById(idDicreme)
            .then((res: any) => { 
              usuarioDicremeData.value = res.data 
            })
            .catch((err: any) => console.error("Error en userService:", err))
        )
      }

      await Promise.all(llamadasSecundarias)
    }
  } catch (error) {
    console.error('Error fetching quotation details:', error)
    errorMessage.value = 'Hubo un problema al conectar con el servidor.'
  } finally {
    isLoading.value = false
  }
})

const getQuoteStatusLabel = (statusId: number): string => {
  const safeId = Number(statusId)
  if (safeId === 1) return 'En revisión'
  if (safeId === 3) return 'Completado'
  return 'En proceso'
}

const formatCurrency = (value: number) => {
  const safeValue = value ? Number(value) : 0
  return `$${safeValue.toLocaleString('es-CL')}`
}

const handleGoBack = () => {
  router.push('/mis-cotizaciones')
}
</script>

<template>
  <div class="quotation-detail-page">

    <main class="detail-container">
      <div class="title-section">
        <h2 class="main-title">Resumen Cotización N° {{ String(quotationId).padStart(6, '0') }}</h2>
        <div class="title-line"></div>
      </div>

      <div class="state-box" v-if="isLoading">
        <IceCream class="spinner" :size="40" color="#e4869f" />
        <p>Cargando el estado de tu cotización...</p>
      </div>
      
      <div class="state-box error" v-else-if="errorMessage">{{ errorMessage }}</div>
      
      <div class="detail-grid" v-else-if="quotationData">
        
        <section class="info-column">
          <h3 class="section-subtitle">Datos de contacto:</h3>
          <div class="info-card-block">
            <p class="info-text"><strong>Teléfono:</strong> {{ distributorData?.telefono || fallbackPhone }}</p>
            <p class="info-text"><strong>Correo:</strong> {{ distributorData?.email || distributorData?.correo_electronico || fallbackEmail }}</p>
          </div>

          <h3 class="section-subtitle" style="margin-top: 25px;">Datos de Entrega:</h3>
          <div class="info-card-block">
            <p class="info-text"><strong>Nombre y Apellido:</strong> {{ quotationData?.persona_recibe || fallbackName }}</p>
            <p class="info-text"><strong>Empresa:</strong> {{ distributorData?.nombre_empresa || fallbackCompany }}</p>
            <p class="info-text"><strong>Rut empresa:</strong> {{ distributorData?.rut_empresa || distributorData?.rut || 'N/A' }}</p>
            <p class="info-text"><strong>Dirección:</strong> {{ distributorData?.direccion || fallbackAddress }}</p>
            <p class="info-text"><strong>Comuna:</strong> {{ usuarioDicremeData?.comuna || usuarioDicremeData?.nombre_comuna || fallbackComuna }}</p>
          </div>

          <div class="amount-group">
            <div class="amount-row highlighted">
              <span class="amount-label">Monto Estimado:</span>
              <div class="amount-box-pink">
                {{ formatCurrency(quotationData.total_cotizacion ?? quotationData.total) }}
              </div>
            </div>
          </div>
        </section>

        <section class="summary-column">
          <h3 class="section-subtitle">Detalle productos:</h3>
          
          <div class="products-box-container">
            <div class="empty-products-state" v-if="productsData.length === 0">
              Cargando el detalle de los productos...
            </div>

            <div 
              class="product-item-card"
              v-else
              v-for="(item, index) in productsData" 
              :key="index" 
            >
              <img :src="item.producto?.image ?? item.producto?.imagen ?? boxPlaceholderImage" class="item-thumb" />
              
              <div class="item-info">
                <span class="item-name">
                  {{ item.producto?.name ?? item.producto?.nombre ?? item.producto?.nombre_producto ?? 'Helado Artesanal' }}
                </span>
                
                <span class="item-tag">
                  - {{ item.producto?.id_categoria === 1 ? 'Al agua' : item.producto?.id_categoria === 2 ? 'Crema' : item.producto?.categoria_objeto?.nombre_categoria ?? item.producto?.categoria_nombre ?? 'Categoría' }}
                </span>
                
                <div class="item-meta-row">
                  <span class="item-spec">
                    {{ item.producto?.size ?? item.producto?.formato ?? '10L' }} - 
                    {{ formatCurrency((item.precio_unitario_venta ?? item.precio) ?? (item.producto?.precio || 0)) }}
                  </span>
                  
                  <span class="item-qty">X{{ item.cantidad }}</span>
                </div>
              </div>
            </div>
          </div> 

          <div class="timeline-wrapper">
            
            <div class="floating-icon-container" :class="'step-active-' + currentStep">
              <div class="icon-bubble">
                <FileSearch v-if="currentStep === 1" :size="24" color="white" />
                <CheckCircle2 v-else :size="24" color="white" />
              </div>
            </div>

            <div class="timeline-bar">
              <div class="timeline-progress-bar" :class="'progress-fill-' + currentStep"></div>
            </div>

            <div class="timeline-nodes-row">
              <div class="timeline-node" :class="{ active: currentStep >= 1 }">
                <div class="node-dot"></div>
                <span class="node-text">En revisión</span>
              </div>
              <div class="timeline-node" :class="{ active: currentStep === 2 }">
                <div class="node-dot"></div>
                <span class="node-text">Completado</span>
              </div>
            </div>
          </div>

          <div class="status-display-box">
            Estado de la cotización: <span class="capitalize-text">{{ quotationData.estado_nombre ?? getQuoteStatusLabel(quotationData.id_estado_cotizacion) }}</span>
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
.quotation-detail-page {
  background-color: var(--DC-bg-light);
  min-height: 100vh;
  font-family: sans-serif;
  padding-bottom: 60px;
}

.detail-container {
  max-width: 1020px;
  margin: 35px auto;
  padding: 0 25px;
}

.title-section { margin-bottom: 30px; }
.main-title { font-size: 1.25rem; font-weight: 800; color: #1a1624; margin: 0 0 6px 0; text-align: left; }
.title-line { height: 2px; background-color: var(--DC-pink); width: 100%; }

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
  color: var(--DC-gray);
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

.amount-row.highlighted .amount-box-pink {
  background-color: white;
  border: 2px solid var(--DC-pink);
  border-radius: 25px;
  padding: 12px 25px;
  font-size: 1.1rem;
  font-weight: 800;
  color: #1a1624;
  text-align: left;
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
.item-tag { font-size: 0.75rem; font-weight: 700; margin-top: 1px; color: var(--DC-pink); }
.item-meta-row { display: flex; justify-content: space-between; align-items: center; margin-top: 4px; }
.item-spec { font-size: 0.95rem; font-weight: 800; color: #1a1624; }
.item-qty { font-size: 0.95rem; font-weight: 800; color: #444; }

.timeline-wrapper {
  margin: 60px auto 30px auto;
  position: relative;
  width: 75%; 
}

.timeline-bar {
  position: absolute;
  top: 6px;
  left: 0;
  width: 100%;
  height: 4px;
  background-color: #e0dde0;
  z-index: 1;
}


.progress-fill-1 { width: 0%; }
.progress-fill-2 { width: 100%; }

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
  border: 2px solid var(--DC-bg-light);
  transition: background-color 0.3s ease;
}

.timeline-node.active .node-dot {
  background-color: var(--DC-pink);
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
  top: -46px;
  z-index: 3;
  transform: translateX(-50%); 
  transition: left 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.step-active-1 { 
  left: 35px; 
}
.step-active-2 { 
  left: calc(100% - 35px); 
}

.icon-bubble {
  background-color: var(--DC-pink);
  padding: 8px;
  border-radius: 50%;
  box-shadow: 0 4px 10px rgba(228, 134, 159, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: bounce 2s infinite ease-in-out;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

.status-display-box {
  background-color: white;
  border: 1px solid var(--DC-pink);
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