<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Lock, ArrowLeft } from 'lucide-vue-next'
import { authService } from '../services/authService'

const router = useRouter()
const route = useRoute()

const token = ref('')
const password = ref('')
const confirmPassword = ref('')
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

onMounted(() => {
  const t = route.query.token
  token.value = typeof t === 'string' ? t : ''

  if (!token.value) {
    errorMessage.value = 'Este enlace no es válido. Solicita uno nuevo desde "¿Olvidaste tu contraseña?".'
  }
})

const goBack = () => {
  router.push('/login')
}

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (!token.value) {
    errorMessage.value = 'Este enlace no es válido. Solicita uno nuevo.'
    return
  }

  if (!password.value || !confirmPassword.value) {
    errorMessage.value = 'Completa ambos campos.'
    return
  }

  if (password.value !== confirmPassword.value) {
    errorMessage.value = 'Las contraseñas no coinciden.'
    return
  }

  isLoading.value = true

  try {
    const data = await authService.resetPassword(token.value, password.value, confirmPassword.value)
    successMessage.value = data.message || 'Tu contraseña fue restablecida correctamente.'
    setTimeout(() => router.push('/login'), 2500)
  } catch (error: any) {
    // El backend devuelve mensajes como "Token inválido o expirado" sin
    // revelar más detalle que eso.
    errorMessage.value = error.response?.data?.message || 'No se pudo restablecer la contraseña. Intenta nuevamente.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="reset-container">
    <div class="reset-wrapper">
      <div class="back-button" @click="goBack">
        <ArrowLeft :size="24" color="#e4869f" />
        <span>Volver</span>
      </div>

      <div class="reset-card">
        <div class="logo-section">
          <img src="../assets/logo_dicreme.webp" alt="DiCreme Logo" class="logo" />
        </div>

        <div class="divider"></div>

        <div class="text-section">
          <h2>Restablecer contraseña</h2>
          <p>Ingresa tu nueva contraseña. Debe tener al menos 10 caracteres, con mayúsculas, minúsculas y números.</p>
        </div>

        <div class="form-section">
          <div class="input-group">
            <input
              v-model="password"
              type="password"
              placeholder="Nueva contraseña"
              class="custom-input"
              :disabled="!token"
            />
            <Lock class="input-icon" :size="20" color="#322c44" />
          </div>

          <div class="input-group">
            <input
              v-model="confirmPassword"
              type="password"
              placeholder="Confirma la nueva contraseña"
              class="custom-input"
              :disabled="!token"
            />
            <Lock class="input-icon" :size="20" color="#322c44" />
          </div>

          <p v-if="errorMessage" class="feedback-message error">{{ errorMessage }}</p>
          <p v-if="successMessage" class="feedback-message success">{{ successMessage }}</p>

          <button @click="handleSubmit" class="btn btn-primary" :disabled="isLoading || !token">
            {{ isLoading ? 'GUARDANDO...' : 'RESTABLECER CONTRASEÑA' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.reset-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #eeedee;
  font-family: sans-serif;
}

.reset-wrapper {
  position: relative;
  width: 100%;
  max-width: 450px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.back-button {
  position: absolute;
  left: -6.5rem;
  top: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  color: #e4869f;
  font-weight: bold;
  transition: all 0.2s ease;
}

.back-button:hover {
  transform: translateX(-5px);
}

.back-button span {
  margin-top: 0.5rem;
}

.reset-card {
  background-color: white;
  padding: 2.5rem;
  border-radius: 1.5rem;
  width: 100%;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.logo-section {
  width: 100%;
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.logo {
  max-width: 180px;
  height: auto;
}

.divider {
  width: 80%;
  height: 2px;
  background-color: #e4869f;
  margin-bottom: 2rem;
}

.text-section {
  text-align: center;
  margin-bottom: 2rem;
}

.text-section h2 {
  color: #322c44;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.text-section p {
  color: #9793a0;
  font-size: 0.95rem;
  line-height: 1.4;
}

.form-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  align-items: center;
}

.input-group {
  width: 100%;
  position: relative;
  display: flex;
  align-items: center;
}

.custom-input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  background-color: #e6e6e6;
  border: 1px solid #e4869f;
  border-radius: 0.75rem;
  font-size: 1rem;
  outline: none;
  box-sizing: border-box;
}

.custom-input:disabled {
  opacity: 0.6;
}

.input-icon {
  position: absolute;
  right: 1rem;
  pointer-events: none;
}

.btn {
  width: 100%;
  padding: 0.75rem;
  border: none;
  border-radius: 0.75rem;
  font-weight: bold;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.2s ease;
}

.btn:hover {
  transform: translateY(-2px);
  filter: brightness(0.9);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-primary {
  background-color: #e4869f;
  color: white;
}

.feedback-message {
  text-align: center;
  font-size: 0.9rem;
  margin: 0;
  width: 100%;
}

.feedback-message.error {
  color: #c0392b;
}

.feedback-message.success {
  color: #27ae60;
}
</style>