<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
    <div class="modal-content">
      
      <div class="modal-header">
        <h2 class="modal-title">AGREGAR LOTE</h2>
        <button class="close-btn" @click="closeModal">
          <span class="close-icon">✖</span>
        </button>
      </div>

      <div class="modal-body">
        
        <h3 class="subtitle">Unidades producidas:</h3>
        <div class="counter-container">
          <button class="counter-btn" @click="decrement" :disabled="localUnits <= 1">
            <span class="sign">−</span>
          </button>
          
          <input 
            type="number" 
            class="counter-display" 
            v-model.number="localUnits" 
            :style="{ width: Math.max(80, String(localUnits).length * 20 + 40) + 'px' }"
          />
          
          <button class="counter-btn" @click="increment">
            <span class="sign">+</span>
          </button>
        </div>

        <h3 class="subtitle mt-4">Bodega:</h3>
        <div class="select-container">
          <select 
            v-model="selectedBodega" 
            class="bodega-select" 
            :disabled="isLoadingBodegas"
          >
            <option value="" disabled selected>
              {{ isLoadingBodegas ? 'Cargando...' : 'Bodega' }}
            </option>
            <option 
              v-for="bodega in bodegasList" 
              :key="bodega.id" 
              :value="bodega.id"
            >
              {{ bodega.nombre_bodega || bodega.nombre || `Bodega ${bodega.id}` }}
            </option>
          </select>
        </div>

        <div class="error-container">
          <span v-if="errorMessage" class="error-text">{{ errorMessage }}</span>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn-cancel" @click="closeModal">CANCELAR</button>
        <button 
          class="btn-submit" 
          @click="submitAdd" 
          :disabled="isSubmitDisabled"
        >
          AGREGAR
        </button>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
// IMPORTANTE: Asegúrate de importar tu servicio para las bodegas
// import bodegaService from '@/services/bodegaService';

const props = defineProps<{
  isOpen: boolean;
}>();

const emit = defineEmits(['close', 'add']);

// Estados reactivos
const localUnits = ref(1); // Por defecto creamos 1 unidad
const selectedBodega = ref('');
const bodegasList = ref<any[]>([]);
const isLoadingBodegas = ref(false);

// Cargar las bodegas cuando el componente se monta
onMounted(async () => {
  try {
    isLoadingBodegas.value = true;
    
    // AQUÍ VA LA LLAMADA A TU API REAL. Ejemplo:
    // const response = await bodegaService.getBodegas();
    // bodegasList.value = response.data.data || response.data;

    // DATOS DE PRUEBA MIENTRAS CONECTAS LA API:
    bodegasList.value = [
      { id: 1, nombre_bodega: 'Bodega Central' },
      { id: 2, nombre_bodega: 'Bodega Norte' },
      { id: 3, nombre_bodega: 'Bodega Sur' }
    ];

  } catch (error) {
    console.error('Error al cargar las bodegas:', error);
  } finally {
    isLoadingBodegas.value = false;
  }
});

// Reseteamos el modal cada vez que se abre
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    localUnits.value = 1;
    selectedBodega.value = '';
  }
});

// Funciones del contador
const increment = () => {
  localUnits.value++;
};

const decrement = () => {
  if (localUnits.value > 1) { // No dejamos que creen un lote con 0 unidades
    localUnits.value--;
  }
};

const closeModal = () => {
  emit('close');
};

// Validaciones en tiempo real
const errorMessage = computed(() => {
  if (isNaN(Number(localUnits.value)) || Number(localUnits.value) < 1) {
    return 'Debes ingresar al menos 1 unidad.';
  }
  return '';
});

const isSubmitDisabled = computed(() => {
  return errorMessage.value !== '' || selectedBodega.value === '';
});

// Enviar los datos
const submitAdd = () => {
  if (!isSubmitDisabled.value) {
    emit('add', {
      cantidad_producida: localUnits.value,
      id_bodega: selectedBodega.value
    });
  }
};
</script>

<style scoped>
/* Fondo oscuro translúcido */
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
  background-color: #f8f9fb; /* Fondo ligeramene grisáceo como en tu imagen */
  width: 380px;
  border-radius: 20px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  text-align: center;
  font-family: 'Inter', 'Arial', sans-serif;
}

/* Cabecera */
.modal-header {
  background-color: #df889f;
  padding: 15px 20px;
  position: relative;
}

.modal-title {
  color: white;
  margin: 0;
  font-size: 1.3rem;
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
  font-size: 12px;
  font-weight: bold;
}

/* Cuerpo */
.modal-body {
  padding: 25px 20px 10px 20px;
  background-color: white; /* Para que resalte la sección central */
}

.subtitle {
  color: #000;
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0 0 15px 0;
}

.mt-4 {
  margin-top: 20px;
}

/* Contador (Mismos estilos que el anterior) */
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

.counter-btn:disabled {
  border-color: #e0dde0;
  color: #e0dde0;
  cursor: not-allowed;
}

.sign {
  margin-top: -2px;
}

.counter-display {
  min-width: 80px;
  max-width: 150px;
  height: 60px;
  padding: 0 10px;
  border: 1px solid #df889f;
  border-radius: 12px;
  text-align: center;
  font-size: 1.8rem;
  font-weight: 700;
  color: #000;
  outline: none;
  transition: width 0.2s ease;
  -moz-appearance: textfield;
}

.counter-display::-webkit-outer-spin-button,
.counter-display::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Estilos del Select de Bodega */
.select-container {
  margin-top: 10px;
  padding: 0 20px;
}

.bodega-select {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #df889f;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  color: #666;
  background-color: white;
  outline: none;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'></polyline></svg>");
  background-repeat: no-repeat;
  background-position: right 15px center;
}

.bodega-select:focus {
  border-color: #d77f98;
  box-shadow: 0 0 5px rgba(223, 136, 159, 0.3);
}

/* Contenedor de Error */
.error-container {
  min-height: 24px;
  margin-top: 15px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.error-text {
  color: #ef4444;
  font-size: 0.85rem;
  font-weight: 600;
}

/* Pie de modal */
.modal-footer {
  padding: 20px;
  display: flex;
  justify-content: center;
  gap: 15px;
  background-color: white; /* Consistente con el body */
  border-bottom-left-radius: 20px;
  border-bottom-right-radius: 20px;
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
  background-color: #df889f;
  color: white;
}

.btn-submit:disabled {
  background-color: #e5e7eb; /* Gris deshabilitado */
  color: #9ca3af;
  cursor: not-allowed;
}

.btn-cancel:hover:not(:disabled), .btn-submit:hover:not(:disabled) {
  opacity: 0.9;
}
</style>