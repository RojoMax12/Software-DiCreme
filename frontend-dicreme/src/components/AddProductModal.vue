<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-box">
      <div class="modal-header">
        <h2 class="modal-title">NUEVO HELADO</h2>
        <button class="btn-close" @click="$emit('close')" title="Cerrar">
          <X :size="16" stroke-width="3" />
        </button>
      </div>

      <div class="modal-body">
        
        <div class="form-group">
          <label>Sabor del nuevo helado:</label>
          <input 
            type="text" 
            placeholder="Sabor" 
            v-model="formData.sabor" 
            class="pink-input"
          />
        </div>

        <div class="form-group">
          <label>Categoría del nuevo helado:</label>
          <select v-model="formData.categoria" class="pink-input select-input">
            <option value="" disabled selected>Categoría</option>
            <option value="1">Crema</option>
            <option value="2">Agua</option>
          </select>
        </div>

        <div class="form-group">
          <label>Formato del nuevo helado:</label>
          <select v-model="formData.formato" class="pink-input select-input">
            <option value="" disabled selected>Formato</option>
            <option value="1L">1L</option>
            <option value="2.5L">2.5L</option>
            <option value="5L">5L</option>
            <option value="10L">10L</option>
          </select>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn-cancel" @click="$emit('close')">CANCELAR</button>
        <button class="btn-save" @click="handleSave">GUARDAR</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { X } from 'lucide-vue-next'

const emit = defineEmits(['close', 'save'])

// Estado local para los campos (cáscara)
const formData = ref({
  sabor: '',
  categoria: '',
  formato: ''
})

const handleSave = () => {
  // Emitimos los datos simulados hacia el componente padre
  emit('save', formData.value)
}
</script>

<style scoped>
/* --- OVERLAY OSCURO --- */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
  backdrop-filter: blur(2px);
}

/* --- CAJA DEL MODAL --- */
.modal-box {
  background-color: white;
  width: 90%;
  max-width: 450px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  font-family: 'Inter', sans-serif;
}

/* --- CABECERA ROSADA --- */
.modal-header {
  background-color: #e4869f;
  padding: 20px;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-title {
  color: white;
  margin: 0;
  font-size: 1.4rem;
  font-weight: 800;
  letter-spacing: 0.5px;
}

.btn-close {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  background-color: white;
  color: #322c44;
  border: none;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  transition: transform 0.2s;
}

.btn-close:hover {
  transform: translateY(-50%) scale(1.1);
}

/* --- CUERPO Y FORMULARIO --- */
.modal-body {
  padding: 30px 40px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.form-group label {
  text-align: center;
  font-weight: 800;
  font-size: 1.05rem;
  color: #1a1624;
}

.pink-input {
  border: 1px solid #e4869f;
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 1rem;
  font-weight: 600;
  color: #322c44;
  outline: none;
  transition: box-shadow 0.2s;
}

.pink-input::placeholder {
  color: #a9a5b1;
  font-weight: 600;
}

.pink-input:focus {
  box-shadow: 0 0 0 3px rgba(228, 134, 159, 0.2);
}

/* Ajustes específicos para los select */
.select-input {
  appearance: none; 
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239793a0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 20px;
  cursor: pointer;
}

/* --- PIE Y BOTONES --- */
.modal-footer {
  padding: 0 40px 30px 40px;
  display: flex;
  justify-content: center;
  gap: 20px;
}

.btn-cancel, .btn-save {
  border: none;
  border-radius: 20px;
  padding: 10px 24px;
  font-weight: 800;
  font-size: 0.9rem;
  cursor: pointer;
  transition: transform 0.2s, background-color 0.2s;
}

.btn-cancel {
  background-color: #3b354d;
  color: white;
}

.btn-cancel:hover {
  background-color: #1a1624;
  transform: translateY(-2px);
}

.btn-save {
  background-color: #e4869f;
  color: white;
}

.btn-save:hover {
  background-color: #d1728c;
  transform: translateY(-2px);
}
</style>
