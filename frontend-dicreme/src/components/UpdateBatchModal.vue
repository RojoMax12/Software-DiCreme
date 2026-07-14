<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
    <div class="modal-content">
      
      <!-- Cabecera rosada -->
      <div class="modal-header">
        <h2 class="modal-title">LOTE {{ BatchNumber }}</h2>
        <button class="close-btn" @click="closeModal">
          <span class="close-icon">✖</span>
        </button>
      </div>

      <!-- Cuerpo del modal -->
      <div class="modal-body">
        <h3 class="subtitle">Unidades restantes</h3>
        
        <div class="counter-container">
          <button class="counter-btn" @click="decrement" :disabled="localUnits <= 0">
            <span class="sign">−</span>
          </button>
          
          <input 
            type="number" 
            class="counter-display" 
            v-model.number="localUnits" 

            :style="{width: Math.max(80, String(localUnits).length *20 + 40) + 'px'}"
            />
          
          <button class="counter-btn" @click="increment" :disabled="localUnits >= maxUnits">
            <span class="sign">+</span>
          </button>
        </div>

        <div class="error-container">
            <span v-if="errorMessage" class="error-text">{{ errorMessage }}</span> 
        </div>

      </div>

      <!-- Pie del modal (Botones) -->
      <div class="modal-footer">
        <button class="btn-cancel" @click="closeModal">CANCELAR</button>
        <button class="btn-submit" @click="submitUpdate" :disabled="isSubmitDisabled">ACTUALIZAR</button>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';

const props = defineProps<{
  isOpen: boolean;
  BatchNumber: string;
  currentUnits: number;
  maxUnits:number;
}>();

const emit = defineEmits(['close', 'update']);

// Estado local para el contador
const localUnits = ref(props.currentUnits);

// Sincronizar el estado local cada vez que se abre el modal con un lote nuevo
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    localUnits.value = props.currentUnits;
  }
});

const increment = () => {
    if (localUnits.value <= props.maxUnits) {
        localUnits.value++;
    }
};

const decrement = () => {
  if (localUnits.value > 0) {
    localUnits.value--;
  }
};

const errorMessage = computed(() => {
  if (localUnits.value === null ||isNaN(localUnits.value)) {
    return 'Por favor, ingrese una cantidad válida.';
  }
  if (localUnits.value < 0) {
    return 'La cantidad de unidades restantes no puede ser negativa.';
  }
  if (localUnits.value > props.maxUnits) {
    return `La cantidad de unidades restantes no puede superar las unidades producidas (${props.maxUnits}).`;
  }
  return '';
});

const isSubmitDisabled = computed(() => {
  return errorMessage.value !== '' || localUnits.value === props.currentUnits;
});

const closeModal = () => {
  emit('close');
};

const submitUpdate = () => {
  emit('update', localUnits.value);
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.3);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

/* Contenedor principal del modal */
.modal-content {
  background-color: white;
  width: 400px;
  border-radius: 20px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  text-align: center;
  font-family: 'Inter', 'Arial', sans-serif;
}

/* Cabecera */
.modal-header {
  background-color: #df889f; 
  padding: 20px;
  position: relative;
}

.modal-title {
  color: white;
  margin: 0;
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: 1px;
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background-color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.close-icon {
  color: #333;
  font-size: 10px;
  font-weight: bold;
}

/* Cuerpo */
.modal-body {
  padding: 30px 20px;
}

.subtitle {
  color: #000;
  font-size: 1.2rem;
  font-weight: 700;
  margin-top: 0;
  margin-bottom: 20px;
}

/* Contador */
.counter-container {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
}

.counter-btn {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  border: 1px solid #df889f;
  background-color: white;
  color: #333;
  font-size: 1.5rem;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s;
}

.counter-btn:active {
  background-color: #fbe6ec;
}

.counter-btn:disabled {
  border-color: #ccc;
  color: #ccc;
  cursor: not-allowed;
}

.sign {
  margin-top: -2px; 
}

.counter-display {
  width: 60px;
  height: 60px;
  border: 1px solid #df889f;
  border-radius: 12px;
  text-align: center;
  align-items: center;
  font-size: 1.8rem;
  font-weight: 700;
  color: #000;
  background-color: #fff;
  outline: none;
}

/* Pie de modal */
.modal-footer {
  padding: 0 20px 30px 20px;
  display: flex;
  justify-content: center;
  gap: 15px;
}

.btn-cancel, .btn-submit {
  padding: 10px 24px;
  border-radius: 25px;
  font-weight: 800;
  font-size: 0.9rem;
  cursor: pointer;
  border: none;
  transition: opacity 0.2s;
}

.btn-cancel {
  background-color: #2b253b;
  color: white;
}

.btn-submit {
  background-color: #df889f; /* Color rosado */
  color: white;
}

.btn-cancel:hover, .btn-submit:hover {
  opacity: 0.9;
}

.counter-display::-webkit-inner-spin-button {
  -webkit-appearance: none !important;
}

/* Contenedor del error (altura fija para que el modal no salte al aparecer el texto) */
.error-container {
  min-height: 24px;
  margin-top: 15px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.error-text {
  color: #ef4444; /* Rojo de alerta */
  font-size: 0.85rem;
  font-weight: 600;
  background-color: #fef2f2;
  padding: 4px 12px;
  border-radius: 12px;
  border: 1px solid #fca5a5;
}

/* Estilo para el botón de actualizar cuando está bloqueado */
.btn-submit:disabled {
  background-color: #f3f4f6; /* Fondo gris */
  color: #9ca3af; /* Texto gris claro */
  cursor: not-allowed;
  box-shadow: none;
}
</style>>