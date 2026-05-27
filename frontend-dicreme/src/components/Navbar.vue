<template>
  <nav class="navbar">
    <div class="logo-container" @click="goToHome">
      <img src="@/assets/logo_dicreme.png" alt="Di Creme Logo" class="logo-icon" />
      <span class="logo-text">Di Creme</span>
    </div>

    <div class="actions">
      <div v-if="isLoggedIn" class="user-logged-zone">
        <div class="user-info-text">
          <span class="welcome-text">Sesión iniciada: </span>
          <span class="company-name">
            {{ currentUser?.id_rol === 3 ? currentUser?.nombre_empresa : currentUser?.nombre_usuario }}
          </span>
        </div>
        
        <div class="divider-line"></div>
        
        <button class="btn-logout" @click="handleLogout">CERRAR SESIÓN</button>
      </div>

      <router-link v-else to="/login">
        <button class="btn-ingresar">INGRESAR</button>
      </router-link>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isLoggedIn = ref(false)
const currentUser = ref<any>(null)

// Revisa el estado de la autenticación leyendo el localStorage
const checkAuthStatus = () => {
  const token = localStorage.getItem('token')
  const userParsed = localStorage.getItem('user')
  
  if (token) {
    isLoggedIn.value = true
    currentUser.value = userParsed ? JSON.parse(userParsed) : null
    console.log("Este es el usuario de ahroa",currentUser.value)
  } else {
    isLoggedIn.value = false
    currentUser.value = null
  }
}

// Acción global para destruir la sesión
const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  
  isLoggedIn.value = false
  currentUser.value = null
  
  alert("Has cerrado sesión exitosamente.")
  
  // Forzamos un viaje al catálogo limpio para refrescar la vista global
  router.push('/').then(() => {
    window.location.reload()
  })
}

onMounted(() => {
  checkAuthStatus()
})

const goToHome = () => {
  router.push('/')
}
</script>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 8%; 
  background-color: #ffffff;
  height: 80px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.04);
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
}

.logo-icon {
  height: 75px; 
  width: auto;
}

.logo-text {
  font-family: 'Brush Script MT', 'Pacifico', cursive; 
  font-size: 2rem;
  font-weight: 600;
  color: #000;
}

.actions {
  display: flex;
  align-items: center;
}

/* Contenedor de la zona activa */
.user-logged-zone {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-info-text {
  display: flex;
  align-items: center;
  gap: 6px;
}

/* Texto de introducción limpio y sutil */
.welcome-text {
  font-size: 0.9rem;
  color: #7c7289; /* Gris intermedio */
  font-weight: 400;
}

/* El nombre destacado sutilmente en el rosa de la marca */
.company-name {
  font-size: 0.95rem;
  color: var(--DC-pink); 
  font-weight: 700;
}

/* Línea vertical divisoria elegante */
.divider-line {
  width: 1px;
  height: 20px;
  background-color: #eeedee;
}

/* Botón cerrar sesión estilizado sin sombras pesadas */
.btn-logout {
  background-color: #322c44; /* Tu gris oscuro corporativo */
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 20px; 
  font-weight: bold;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background-color 0.2s ease, transform 0.2s ease;
}

.btn-logout:hover {
  background-color: #1a1624;
  transform: translateY(-1px);
}

/* Botón ingresar original */
.btn-ingresar {
  background-color: var(--DC-pink); 
  color: white;
  border: none;
  padding: 8px 25px;
  border-radius: 20px; 
  font-weight: bold;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-ingresar:hover {
  filter: brightness(1.1);
}
</style>