<template>
  <nav class="navbar">
    <div class="logo-container" @click="goToHome">
      <img src="@/assets/logo_dicreme.png" alt="Di Creme Logo" class="logo-icon" />
      <span class="logo-text">Di Creme</span>
    </div>

    <div class="actions">
      <button 
        v-if="$route.path !== '/'" 
        class="btn-back-catalog" 
        @click="goToHome"
      >
        VOLVER AL CATÁLOGO
      </button>

      <div v-if="isLoggedIn" class="user-logged-zone">
        <div v-if="$route.path !== '/'" class="divider-line"></div>

        <div class="user-info-text">
          <span class="welcome-text">Sesión iniciada: </span>
          <span class="company-name">{{ currentUser?.nombre_empresa || 'Distribuidor' }}</span>
        </div>
        
        <div class="divider-line"></div>
        
        <button class="btn-logout" @click="handleLogout">CERRAR SESIÓN</button>
      </div>

      <router-link v-else to="/login">
        <button class="btn-login">INGRESAR</button>
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

// Checks authentication state by reading from localStorage
const checkAuthStatus = () => {
  const token = localStorage.getItem('token')
  const userParsed = localStorage.getItem('user')
  
  if (token) {
    isLoggedIn.value = true
    currentUser.value = userParsed ? JSON.parse(userParsed) : null
  } else {
    isLoggedIn.value = false
    currentUser.value = null
  }
}

// Global action to destroy active session
const handleLogout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  
  isLoggedIn.value = false
  currentUser.value = null
  
  // Custom elegant alert instead of native browser popup
  alert("Has cerrado sesión exitosamente.")
  
  // Forces clean navigation back to storefront catalog
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
  gap: 16px; /* Holds spacing between the back button and user zones */
}

/* 🚨 CORPORATE LOOK FOR BACK TO CATALOG BUTTON */
.btn-back-catalog {
  background-color: transparent;
  border: 1px solid #e4869f; /* var(--DC-pink) fallback */
  color: #e4869f;
  padding: 8px 20px;
  border-radius: 20px;
  font-weight: bold;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-back-catalog:hover {
  background-color: #e4869f;
  color: white;
}

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

.welcome-text {
  font-size: 0.9rem;
  color: #7c7289; 
  font-weight: 400;
}

.company-name {
  font-size: 0.95rem;
  color: #e4869f; 
  font-weight: 700;
}

.divider-line {
  width: 1px;
  height: 20px;
  background-color: #eeedee;
}

.btn-logout {
  background-color: #322c44; 
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

.btn-login {
  background-color: #e4869f; 
  color: white;
  border: none;
  padding: 8px 25px;
  border-radius: 20px; 
  font-weight: bold;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-login:hover {
  filter: brightness(1.1);
}
</style>