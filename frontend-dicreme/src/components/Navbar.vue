<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { FileText, ShoppingBag, ChevronDown, LogOut } from 'lucide-vue-next'

const router = useRouter()

// --- ESTADOS REACTIVOS ---
const username = ref('prueba')
const isDropdownOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

// Carga el nombre real del usuario logueado al montar la barra
onMounted(() => {
  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      username.value = userObj.nombre_empresa || userObj.nombre || 'prueba'
    } catch (e) {
      console.error('Error parsing user session inside Navbar:', e)
    }
  }
  
  // Agrega el escuchador global para cerrar el menú flotante al hacer clic afuera
  window.addEventListener('click', handleOutsideClick)
})

// Limpia el evento global al destruir el componente para evitar fugas de memoria
onUnmounted(() => {
  window.removeEventListener('click', handleOutsideClick)
})

// Alterna la visibilidad del menú flotante evitando la propagación inmediata
const toggleDropdown = (event: Event) => {
  event.stopPropagation()
  isDropdownOpen.value = !isDropdownOpen.value
}

// Cierra el menú si el clic del usuario ocurre fuera del contenedor del dropdown
const handleOutsideClick = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    isDropdownOpen.value = false
  }
}

// Navegación fluida hacia las respectivas vistas históricas
const navigateTo = (path: string) => {
  isDropdownOpen.value = false
  router.push(path)
}

// Destruye la sesión activa y retorna al login principal
const handleLogout = () => {
  localStorage.removeItem('user')
  router.push('/')
}
</script>

<template>
  <nav class="dc-navbar">
    <!-- Contenedor Izquierdo: Logotipo Corporativo -->
    <div class="nav-left" @click="router.push('/')">
      <img src="@/assets/logo_dicreme.png" alt="Di Creme Logo" class="brand-logo" />
      <span class="brand-text">Di Creme</span>
    </div>

    <!-- Contenedor Derecho: Sesión, Dropdown de Avance e Historial y Cierre -->
    <div class="nav-right">
      
      <!-- --- BOTÓN DE MENÚ FLOTANTE INTERACTIVO --- -->
      <div class="dropdown-wrapper" ref="dropdownRef">
        <button class="btn-history-dropdown" @click="toggleDropdown" :class="{ 'btn-active': isDropdownOpen }">
          <span>Mi Historial</span>
          <ChevronDown :size="16" class="arrow-icon" :class="{ 'rotate-arrow': isDropdownOpen }" />
        </button>

        <!-- Contenedor Flotante del Menú (Aparece dinámicamente con transiciones CSS) -->
        <Transition name="dropdown-fade">
          <div v-if="isDropdownOpen" class="floating-menu">
            <div class="menu-arrow-pointer"></div>
            
            <!-- Opción 1: Redirección al historial de Cotizaciones -->
            <button class="menu-item" @click="navigateTo('/mis-cotizaciones')">
              <FileText :size="18" color="#e4869f" />
              <div class="item-text-group">
                <span class="item-title">Mis Cotizaciones</span>
                <span class="item-desc">Revisa propuestas y estados</span>
              </div>
            </button>

            <!-- Opción 2: Redirección al historial de Pedidos con el camioncito -->
            <button class="menu-item" @click="navigateTo('/mis-pedidos')">
              <ShoppingBag :size="18" color="#322c44" />
              <div class="item-text-group">
                <span class="item-title">Mis Pedidos</span>
                <span class="item-desc">Seguimiento de compras</span>
              </div>
            </button>
          </div>
        </Transition>
      </div>

      <div class="divider-line"></div>

      <!-- Indicador de Sesión Activa (Estilo image_071ac9.jpg) -->
      <div class="session-info">
        <span class="session-label">Sesión iniciada:</span>
        <span class="session-username">{{ username }}</span>
      </div>

      <!-- Botón de Salida -->
      <button class="btn-logout-dark" @click="handleLogout">
        <LogOut :size="14" class="logout-icon" />
        <span>CERRAR SESIÓN</span>
      </button>
    </div>
  </nav>
</template>

<style scoped>
/* --- ESTILOS ESTRUCTURALES DEL NAVBAR --- */
.dc-navbar {
  background-color: white;
  height: 70px;
  padding: 0 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 999;
  font-family: sans-serif;
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
}

.brand-logo {
  height: 50px;
  object-fit: contain;
}

.brand-text {
  font-size: 1.4rem;
  font-weight: 800;
  color: #1a1624;
  font-style: italic;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

.divider-line {
  width: 1px;
  height: 24px;
  background-color: #e0dde0;
}

.session-info {
  font-size: 0.9rem;
  color: #7c7289;
}

.session-username {
  color: #e4869f;
  font-weight: bold;
  margin-left: 5px;
}

/* --- ESTILOS DEL BOTÓN DEL DROPDOWN --- */
.dropdown-wrapper {
  position: relative;
}

.btn-history-dropdown {
  background-color: #f6f4f6;
  border: 1px solid #e0dde0;
  color: #322c44;
  padding: 8px 18px;
  border-radius: 20px;
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.btn-history-dropdown:hover, .btn-active {
  background-color: #fff0f3;
  border-color: #fad2dc;
  color: #e4869f;
}

.arrow-icon {
  transition: transform 0.2s ease;
}

.rotate-arrow {
  transform: rotate(180deg);
}

/* --- MENÚ FLOTANTE DROPDOWN DESPLEGABLE --- */
.floating-menu {
  position: absolute;
  top: 45px;
  right: 0;
  background-color: white;
  border: 1px solid #fad2dc;
  border-radius: 14px;
  box-shadow: 0 10px 25px rgba(228, 134, 159, 0.15);
  padding: 8px;
  width: 240px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  z-index: 1000;
}

/* Flecha apuntadora superior del menú flotante */
.menu-arrow-pointer {
  position: absolute;
  top: -6px;
  right: 20px;
  width: 10px;
  height: 10px;
  background-color: white;
  border-left: 1px solid #fad2dc;
  border-top: 1px solid #fad2dc;
  transform: rotate(45deg);
}

.menu-item {
  background: none;
  border: none;
  width: 100%;
  padding: 10px 14px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  text-align: left;
}

.menu-item:hover {
  background-color: #fff0f3;
}

.item-text-group {
  display: flex;
  flex-direction: column;
}

.item-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1a1624;
}

.item-desc {
  font-size: 0.72rem;
  color: #888;
  margin-top: 1px;
}

/* --- BOTÓN CERRAR SESIÓN (Estilo exacto de tu imagen) --- */
.btn-logout-dark {
  background-color: #3b354d;
  color: white;
  border: none;
  padding: 9px 18px;
  border-radius: 20px;
  font-weight: bold;
  font-size: 0.8rem;
  letter-spacing: 0.5px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: background-color 0.2s ease;
}

.btn-logout-dark:hover {
  background-color: #1a1624;
}

.logout-icon {
  margin-top: -1px;
}

/* --- TRANSICIONES ANIMADAS (Vue Transition) --- */
.dropdown-fade-enter-active, .dropdown-fade-leave-active {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-fade-enter-from, .dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>