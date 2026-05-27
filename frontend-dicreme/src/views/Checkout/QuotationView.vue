<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import fotoCaja from '@/assets/caja_dicreme.jpg'
// 🚨 Importamos el icono de advertencia vectorizado
import { AlertTriangle } from 'lucide-vue-next'

const router = useRouter()

// --- ESTADOS REACTIVOS PARA LOS INPUTS DEL MOCKUP ---
const email = ref('')
const phone = ref('')
const firstName = ref('')
const lastName = ref('')
const company = ref('')
const companyRut = ref('')
const address = ref('')
const commune = ref('')
const region = ref('')

// ID dinámico extraído desde la autenticación activa
const userId = ref<number | null>(null)

// --- CONTENEDOR DEL CARRITO REAL ---
const quotationItems = ref<any[]>([])

// --- ESTADOS PARA LA NOTIFICACIÓN PREMIUM ---
const errorMessage = ref('')
const showToast = ref(false)

// Función para gatillar la alerta integrada
const triggerAlert = (message: string) => {
  errorMessage.value = message
  showToast.value = true
  
  // Se esconde automáticamente después de 4 segundos
  setTimeout(() => {
    showToast.value = false
  }, 4000)
}

// Rescatamos los helados y los datos del distribuidor al montar la pantalla
onMounted(() => {
  // 1. Cargar productos del carrito
  const savedCart = localStorage.getItem('dicreme_temp_cart')
  if (savedCart) {
    quotationItems.value = JSON.parse(savedCart)
  }

  // 2. AUTORRELLENO CON LAS COLUMNAS REALES DE POSTGRESQL
  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      
      userId.value = userObj.id || null

      // Mapeo exacto basado en la estructura real de tu tabla de distribuidores
      company.value = userObj.nombre_empresa || ''
      companyRut.value = userObj.rut_empresa || ''
      address.value = userObj.direccion || ''
      email.value = userObj.email || userObj.correo_electronico || ''
      phone.value = userObj.telefono || ''
      commune.value = userObj.comuna || ''
      region.value = userObj.region || ''

      // El nombre del receptor
      firstName.value = userObj.nombre || ''
      lastName.value = '' 
      
    } catch (error) {
      console.error('Error al autorrellenar los datos de sesión:', error)
    }
  }
})

// LÓGICA: Calcula el Total Estimado sumando los productos reales del carrito
const totalEstimated = computed(() => {
  const totalRaw = quotationItems.value.reduce((sum, item) => {
    const cleanPrice = typeof item.price === 'string'
      ? Number(item.price.replace(/[^0-9]/g, ''))
      : Number(item.price)
    return sum + (cleanPrice * item.quantity)
  }, 0)
  
  return `$${totalRaw.toLocaleString('es-CL')}`
})

// Acción del botón principal "Confirmar cotización"
const handleConfirmQuotation = async () => {
  
  // --- VALIDACIÓN ESTRICTA CON NUESTRA ALERTA INTEGRADA ---
  if (!email.value.trim()) { triggerAlert('Por favor, ingresa el correo electrónico.'); return; }
  if (!phone.value.trim()) { triggerAlert('Por favor, ingresa el teléfono.'); return; }
  if (!firstName.value.trim()) { triggerAlert('Por favor, ingresa el nombre de quien recibe.'); return; }
  if (!lastName.value.trim()) { triggerAlert('Por favor, ingresa el apellido.'); return; }
  if (!company.value.trim()) { triggerAlert('Por favor, ingresa el nombre de la empresa.'); return; }
  if (!companyRut.value.trim()) { triggerAlert('Por favor, ingresa el RUT de la empresa.'); return; }
  if (!address.value.trim()) { triggerAlert('Por favor, ingresa la dirección de despacho.'); return; }
  if (!commune.value.trim()) { triggerAlert('Por favor, ingresa la comuna.'); return; }
  if (!region.value.trim()) { triggerAlert('Por favor, ingresa la región.'); return; }

  // Empaquetamos la data
  const quotationData = {
    id_usuario_distribuidores: userId.value || 1, 
    productos: quotationItems.value.map(item => ({
      id_producto: item.id,
      cantidad: item.quantity,
      precio_unitario: typeof item.price === 'string' ? Number(item.price.replace(/[^0-9]/g, '')) : Number(item.price)
    })),
    datos_contacto: {
      correo: email.value.trim(),
      telefono: phone.value.trim()
    },
    datos_entrega: {
      nombre_receptor: `${firstName.value.trim()} ${lastName.value.trim()}`,
      empresa: company.value.trim(),
      rut_empresa: companyRut.value.trim(),
      direccion: address.value.trim(),
      comuna: commune.value.trim(),
      region: region.value.trim()
    }
  }

  try {
    const response = await fetch('http://localhost:8000/api/cotizacion', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(quotationData)
    })

    if (!response.ok) throw new Error('Error al procesar la cotización en el servidor')

    const result = await response.json()
    alert(`¡Cotización confirmada exitosamente! Pedido N°: ${result.id || result.ID || 'Generado'}`)
    
    localStorage.removeItem('dicreme_temp_cart')
    router.push('/')

  } catch (error) {
    console.error('Error enviando la cotización:', error)
    triggerAlert('Hubo un problema al conectar con el servidor.')
  }
}
</script>

<template>
  <div class="quotation-page">
    <div class="banner-dicreme"></div>

    <Transition name="toast-fade">
      <div v-if="showToast" class="dc-toast-alert">
        <div class="toast-content">
          <AlertTriangle class="toast-icon-vec" color="#e11d48" :size="22" />
          <span class="toast-text">{{ errorMessage }}</span>
        </div>
      </div>
    </Transition>

    <main class="quotation-container">
      <div class="title-section">
        <h2 class="main-title">Resumen Cotización</h2>
        <div class="title-line"></div>
      </div>

      <div class="quotation-grid">
        <section class="forms-column">
          <h3 class="section-subtitle">Datos de contacto:</h3>
          <div class="input-group-full">
            <input v-model="email" type="email" placeholder="Correo" class="dc-input" />
          </div>
          <div class="input-group-full">
            <input v-model="phone" type="text" placeholder="Teléfono" class="dc-input" />
          </div>

          <h3 class="section-subtitle" style="margin-top: 25px;">Datos de Entrega:</h3>
          <div class="input-row">
            <input v-model="firstName" type="text" placeholder="Nombre" class="dc-input half-width" />
            <input v-model="lastName" type="text" placeholder="Apellido" class="dc-input half-width" />
          </div>
          <div class="input-group-full">
            <input v-model="company" type="text" placeholder="Empresa" class="dc-input" />
          </div>
          <div class="input-group-full">
            <input v-model="companyRut" type="text" placeholder="Rut Empresa" class="dc-input" />
          </div>
          <div class="input-group-full">
            <input v-model="address" type="text" placeholder="Dirección" class="dc-input" />
          </div>
          <div class="input-row">
            <input v-model="commune" type="text" placeholder="Comuna" class="dc-input half-width" />
            <input v-model="region" type="text" placeholder="Región" class="dc-input half-width" />
          </div>
        </section>

        <section class="summary-column">
          <h3 class="section-subtitle">Detalle cotización:</h3>
          <div class="cart-box-container">
            <div v-if="quotationItems.length === 0" class="empty-box-state">
              No hay productos cargados en la cotización.
            </div>
            
            <div 
              v-else 
              v-for="item in quotationItems" 
              :key="item.id + '-' + item.size" 
              class="checkout-item-card"
            >
              <img :src="item.image || fotoCaja" :alt="item.name" class="item-thumb" />
              
              <div class="item-info">
                <div class="item-name-row">
                  <span class="item-name">{{ item.name }}</span>
                </div>
                <span class="item-tag" :style="{ color: item.color || 'var(--DC-pink)' }">
                  - {{ item.category }}
                </span>
                <div class="item-meta-row">
                  <span class="item-spec">{{ item.size }} - {{ item.price }}</span>
                  <span class="item-qty">X{{ item.quantity }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="total-display-box">
            <span class="total-label">Total Estimado:</span>
            <span class="total-value">{{ totalEstimated }}</span>
          </div>

          <div class="action-row">
            <button class="btn-confirm-cotizacion" @click="handleConfirmQuotation">
              Confirmar cotización
            </button>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* --- ESTILOS DE LA NOTIFICACIÓN INTEGRADA CON ICONO --- */
.dc-toast-alert {
  position: fixed;
  top: 30px;
  right: 30px;
  background-color: #fff0f3; /* Rosa pastel de la marca */
  border: 1px solid #fad2dc;   /* Borde fino */
  padding: 14px 24px;
  border-radius: 25px;       /* Forma de píldora idéntica a tus inputs */
  box-shadow: 0 4px 15px rgba(228, 134, 159, 0.15);
  z-index: 1000;
  max-width: 350px;
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Alineación fina del icono vectorial */
.toast-icon-vec {
  margin-top: -2px; 
}

.toast-text {
  color: #322c44; /* Tu gris oscuro corporativo */
  font-size: 0.9rem;
  font-weight: 600;
  text-align: left;
}

/* Transición animada (Fade + Desplazamiento sutil) */
.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: all 0.3s ease;
}

.toast-fade-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.toast-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* --- ESTILOS PREEXISTENTES (MANTENIDOS INTACTOS ABAJO) --- */
.quotation-page {
  background-color: #eeedee;
  min-height: 100vh;
  font-family: sans-serif;
  padding-bottom: 60px;
}
.banner-dicreme {
  height: 60px;
  background-image: url('@/assets/banner_dicreme.png');
  background-size: cover;
  background-position: center;
  filter: brightness(0.75);
}
.quotation-container {
  max-width: 1020px;
  margin: 35px auto;
  padding: 0 25px;
}
.title-section { margin-bottom: 30px; }
.main-title { font-size: 1.25rem; font-weight: 800; color: #1a1624; margin: 0 0 6px 0; text-align: left; }
.title-line { height: 2px; background-color: #e4869f; width: 100%; }
.quotation-grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 55px; }
.section-subtitle { font-size: 1.05rem; font-weight: bold; color: #1a1624; margin: 0 0 14px 0; text-align: left; }
.forms-column { display: flex; flex-direction: column; gap: 12px; }
.input-group-full { width: 100%; }
.input-row { display: flex; gap: 15px; width: 100%; }
.half-width { flex: 1; }
.dc-input {
  width: 100%;
  padding: 11px 22px;
  background-color: white;
  border: 1px solid #e4869f;
  border-radius: 25px;
  font-size: 0.95rem;
  outline: none;
  box-sizing: border-box;
  color: #333;
}
.dc-input::placeholder { color: #b5b2bc; font-weight: 600; }
.summary-column { display: flex; flex-direction: column; }
.cart-box-container {
  background-color: white;
  border-radius: 18px;
  padding: 16px;
  height: 250px;
  overflow-y: auto;
  border: 1px solid #e0dde0;
  display: flex;
  flex-direction: column;
  gap: 12px;
  box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);
}
.empty-box-state { color: #999; font-size: 0.9rem; margin: auto; font-style: italic; }
.checkout-item-card { display: flex; gap: 15px; background-color: #e2dee2; padding: 10px 14px; border-radius: 14px; align-items: center; }
.item-thumb { width: 75px; height: 55px; object-fit: cover; border-radius: 10px; }
.item-info { flex: 1; display: flex; flex-direction: column; text-align: left; }
.item-name { font-size: 0.95rem; font-weight: bold; color: #1a1624; }
.item-tag { font-size: 0.75rem; font-weight: 700; margin-top: 1px; }
.item-meta-row { display: flex; justify-content: space-between; align-items: center; margin-top: 4px; }
.item-spec { font-size: 0.95rem; font-weight: 800; color: #1a1624; }
.item-qty { font-size: 0.95rem; font-weight: 800; color: #444; }
.cart-box-container::-webkit-scrollbar { width: 6px; }
.cart-box-container::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 4px; }
.total-display-box {
  background-color: white;
  border: 1px solid #e4869f;
  border-radius: 25px;
  padding: 12px 25px;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 25px;
  width: 100%;
  box-sizing: border-box;
}
.total-label { font-size: 1.05rem; font-weight: bold; color: #1a1624; }
.total-value { font-size: 1.05rem; font-weight: bold; color: #1a1624; }
.action-row { display: flex; justify-content: flex-end; margin-top: 30px; }
.btn-confirm-cotizacion {
  background-color: #322c44;
  color: white;
  border: none;
  padding: 14px 38px;
  border-radius: 12px;
  font-weight: bold;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 10px rgba(50, 44, 68, 0.2);
}
.btn-confirm-cotizacion:hover { background-color: #1a1624; }
</style>