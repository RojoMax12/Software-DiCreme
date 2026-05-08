<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Mail, Lock, User, UserPlus, Phone, ArrowLeft, Eye, EyeOff } from 'lucide-vue-next'

const router = useRouter()

const form = ref({
  email: '',
  password: '',
  confirmPassword: '',
  firstName: '',
  lastName: '',
  phone: ''
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const goBack = () => {
  router.back()
}

const handleRegister = () => {
  console.log('Register attempt:', form.value)
}
</script>

<template>
  <div class="register-container">
    <div class="register-wrapper">
      <div class="back-button" @click="goBack">
        <ArrowLeft :size="24" color="#e4869f" />
        <span>Volver</span>
      </div>

      <div class="register-card">
        <div class="logo-section">
          <img src="../assets/logo_dicreme.png" alt="DiCreme Logo" class="logo" />
        </div>
        
        <div class="divider"></div>

        <div class="form-section">
          <!-- Nombre y Apellido -->
          <div class="input-row">
            <div class="input-group half">
              <input 
                v-model="form.firstName" 
                type="text" 
                placeholder="Nombre" 
                class="custom-input"
              />
              <User class="input-icon" :size="20" color="#322c44" />
            </div>

            <div class="input-group half">
              <input 
                v-model="form.lastName" 
                type="text" 
                placeholder="Apellido" 
                class="custom-input"
              />
              <UserPlus class="input-icon" :size="20" color="#322c44" />
            </div>
          </div>

          <!-- Correo -->
          <div class="input-group">
            <input 
              v-model="form.email" 
              type="email" 
              placeholder="Correo" 
              class="custom-input"
            />
            <Mail class="input-icon" :size="20" color="#322c44" />
          </div>

          <!-- Teléfono -->
          <div class="input-group">
            <input 
              v-model="form.phone" 
              type="tel" 
              placeholder="Teléfono" 
              class="custom-input"
            />
            <Phone class="input-icon" :size="20" color="#322c44" />
          </div>
          
          <!-- Contraseña -->
          <div class="input-group">
            <input 
              v-model="form.password" 
              type="password" 
              placeholder="Contraseña" 
              class="custom-input"
            />
            <Lock class="input-icon" :size="20" color="#322c44" />
          </div>

          <!-- Confirmar Contraseña -->
          <div class="input-group">
            <input 
              v-model="form.confirmPassword" 
              :type="showConfirmPassword ? 'text' : 'password'" 
              placeholder="Confirmar contraseña" 
              class="custom-input"
            />
            <div class="icon-wrapper" @click="showConfirmPassword = !showConfirmPassword">
              <Eye v-if="!showConfirmPassword" class="input-icon clickable" :size="20" color="#322c44" />
              <EyeOff v-else class="input-icon clickable" :size="20" color="#322c44" />
            </div>
          </div>

          <button @click="handleRegister" class="btn btn-primary">CREAR CUENTA</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #eeedee;
  font-family: sans-serif;
  padding: 2rem 0;
}

.register-wrapper {
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

.register-card {
  background-color: white;
  padding: 2rem;
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

.form-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  align-items: center;
}

.input-row {
  width: 100%;
  display: flex;
  gap: 1rem;
}

.input-group {
  width: 100%;
  position: relative;
  display: flex;
  align-items: center;
}

.input-group.half {
  flex: 1;
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

.custom-input::placeholder {
  color: #9793a0;
}

.input-icon {
  position: absolute;
  right: 1rem;
  pointer-events: none;
}

.icon-wrapper {
  position: absolute;
  right: 0;
  height: 100%;
  width: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.clickable {
  pointer-events: auto;
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
  margin-top: 0.5rem;
}

.btn:hover {
  transform: translateY(-2px);
  filter: brightness(0.9);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn:active {
  transform: translateY(0);
  filter: brightness(0.8);
}

.btn-primary {
  background-color: #e4869f;
  color: white;
}
</style>
