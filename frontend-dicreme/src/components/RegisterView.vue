<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { Mail, Phone, ArrowLeft, Eye, EyeOff, Building2, CreditCard, Hash, ChevronDown } from 'lucide-vue-next'

const router = useRouter()

const form = ref({
  rut: '',
  username: '',
  email: '',
  phone: '',
  comuna: '',
  direccion: '',
  password: '',
  confirmPassword: ''
})

const comunas = [
  'Cerrillos', 'Cerro Navia', 'Conchalí', 'El Bosque', 'Estación Central',
  'Huechuraba', 'Independencia', 'La Cisterna', 'La Florida', 'La Granja',
  'La Pintana', 'La Reina', 'Las Condes', 'Lo Barnechea', 'Lo Espejo',
  'Lo Prado', 'Macul', 'Maipú', 'Ñuñoa', 'Pedro Aguirre Cerda',
  'Peñalolén', 'Providencia', 'Pudahuel', 'Quilicura', 'Quinta Normal',
  'Recoleta', 'Renca', 'San Joaquín', 'San Miguel', 'San Ramón',
  'Santiago centro', 'Vitacura'
]

const showComunaDropdown = ref(false)
const comunaSearch = ref('')

const filteredComunas = computed(() => {
  return comunas.filter(comuna => 
    comuna.toLowerCase().includes(comunaSearch.value.toLowerCase())
  )
})

const selectComuna = (comuna: string) => {
  form.value.comuna = comuna
  comunaSearch.value = comuna
  showComunaDropdown.value = false
}

const toggleComunaDropdown = () => {
  showComunaDropdown.value = !showComunaDropdown.value
}

// Close dropdown when clicking outside
const dropdownRef = ref<HTMLElement | null>(null)
const handleClickOutside = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    showComunaDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside)
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const goBack = () => {
  router.back()
}

const handleRegister = () => {
  const fullPhone = '+56' + form.value.phone
  console.log('Register attempt:', { ...form.value, phone: fullPhone })
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
          <!-- Rut Empresa -->
          <div class="input-group">
            <input 
              v-model="form.rut" 
              type="text" 
              placeholder="Rut Empresa" 
              class="custom-input"
            />
            <CreditCard class="input-icon" :size="20" color="#322c44" />
          </div>

          <!-- Nombre de Usuario -->
          <div class="input-group">
            <input 
              v-model="form.username" 
              type="text" 
              placeholder="Nombre de Usuario (Empresa)" 
              class="custom-input"
            />
            <Building2 class="input-icon" :size="20" color="#322c44" />
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
          <div class="input-group phone-group">
            <div class="phone-prefix">+56</div>
            <div class="input-wrapper">
              <input 
                v-model="form.phone" 
                type="tel" 
                placeholder="Teléfono" 
                class="custom-input phone-input"
              />
              <Phone class="input-icon" :size="20" color="#322c44" />
            </div>
          </div>

          <!-- Comuna y Dirección -->
          <div class="input-row">
            <div class="input-group half" ref="dropdownRef">
              <div class="custom-select-wrapper">
                <input 
                  v-model="comunaSearch" 
                  type="text" 
                  placeholder="Comuna" 
                  class="custom-input select-input"
                  @focus="showComunaDropdown = true"
                />
                <ChevronDown 
                  class="input-icon select-arrow" 
                  :size="20" 
                  color="#322c44"
                  :class="{ 'rotate': showComunaDropdown }"
                  @click="toggleComunaDropdown"
                />
                
                <div v-if="showComunaDropdown" class="dropdown-menu">
                  <div 
                    v-for="comuna in filteredComunas" 
                    :key="comuna"
                    class="dropdown-item"
                    @click.stop="selectComuna(comuna)"
                  >
                    {{ comuna }}
                  </div>
                  <div v-if="filteredComunas.length === 0" class="dropdown-no-results">
                    No resultados
                  </div>
                </div>
              </div>
            </div>

            <div class="input-group half">
              <input 
                v-model="form.direccion" 
                type="text" 
                placeholder="Dirección" 
                class="custom-input"
              />
              <Hash class="input-icon" :size="20" color="#322c44" />
            </div>
          </div>
          
          <!-- Contraseña -->
          <div class="input-group">
            <input 
              v-model="form.password" 
              :type="showPassword ? 'text' : 'password'" 
              placeholder="Contraseña" 
              class="custom-input"
            />
            <div class="icon-wrapper" @click="showPassword = !showPassword">
              <Eye v-if="!showPassword" class="input-icon clickable" :size="20" color="#322c44" />
              <EyeOff v-else class="input-icon clickable" :size="20" color="#322c44" />
            </div>
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

.phone-group {
  gap: 0.75rem;
}

.phone-prefix {
  background-color: #e6e6e6;
  border: 1px solid #e4869f;
  border-radius: 0.75rem;
  padding: 0.75rem 1rem;
  font-weight: bold;
  color: #322c44;
  min-width: 50px;
  text-align: center;
}

.input-wrapper {
  flex: 1;
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

.custom-select-wrapper {
  width: 100%;
  position: relative;
}

.select-arrow {
  top: 50%;
  transform: translateY(-50%);
  transition: transform 0.3s ease;
  cursor: pointer;
  pointer-events: auto;
}

.select-arrow.rotate {
  transform: translateY(-50%) rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: 110%;
  left: 0;
  width: 100%;
  background-color: white;
  border: 1px solid #e4869f;
  border-radius: 0.75rem;
  max-height: 200px;
  overflow-y: auto;
  z-index: 1000;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dropdown-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
  color: #322c44;
}

.dropdown-item:hover {
  background-color: #f0f0f0;
}

.dropdown-no-results {
  padding: 0.75rem 1rem;
  color: #9793a0;
  text-align: center;
}

.dropdown-menu::-webkit-scrollbar {
  width: 6px;
}

.dropdown-menu::-webkit-scrollbar-track {
  background: transparent;
}

.dropdown-menu::-webkit-scrollbar-thumb {
  background: #e4869f;
  border-radius: 10px;
}
</style>