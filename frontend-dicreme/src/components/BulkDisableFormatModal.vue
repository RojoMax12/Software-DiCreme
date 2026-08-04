<script setup lang="ts">
import { ref, watch } from 'vue'
import { X, CheckCircle2, XCircle, CheckSquare, Square, Power } from 'lucide-vue-next'

const props = defineProps<{
  isOpen: boolean
  selectedProducts: any[]
  initialAction?: 'activar' | 'desactivar'
  isLoading?: boolean
}>()

const emit = defineEmits(['close', 'save'])

const actionType = ref<'activar' | 'desactivar'>('activar')
const selectedFormats = ref<number[]>([1, 2, 3, 4]) // Default all formats (10L, 5L, 2.5L, 1L)

const availableFormats = [
  { id: 4, name: '1L' },
  { id: 3, name: '2.5L' },
  { id: 2, name: '5L' },
  { id: 1, name: '10L' }
]

watch(
  () => props.isOpen,
  (open) => {
    if (open) {
      actionType.value = props.initialAction || 'activar'
      selectedFormats.value = [1, 2, 3, 4]
    }
  },
  { immediate: true }
)

const toggleFormatSelection = (formatId: number) => {
  if (selectedFormats.value.includes(formatId)) {
    selectedFormats.value = selectedFormats.value.filter(id => id !== formatId)
  } else {
    selectedFormats.value.push(formatId)
  }
}

const selectAllFormats = () => {
  selectedFormats.value = [1, 2, 3, 4]
}

const deselectAllFormats = () => {
  selectedFormats.value = []
}

const handleApply = () => {
  if (selectedFormats.value.length === 0) return
  emit('save', {
    selectedFormatIds: [...selectedFormats.value],
    actionType: actionType.value
  })
}
</script>

<template>
  <Transition name="fade">
    <div v-if="isOpen" class="modal-overlay" @click.self="emit('close')">
      <div class="modal-card">
        <header class="modal-header">
          <div class="header-title-box">
            <Power :size="22" color="#e4869f" />
            <h3>Gestión Masiva por Formatos</h3>
          </div>
          <button class="close-btn" @click="emit('close')">
            <X :size="20" />
          </button>
        </header>

        <div class="modal-body">
          <div class="summary-banner">
            <span class="summary-count">
              Aplicando a <strong>{{ selectedProducts.length }}</strong> {{ selectedProducts.length === 1 ? 'helado seleccionado' : 'helados seleccionados' }}
            </span>
            <div class="action-selector">
              <button 
                type="button" 
                class="action-tab" 
                :class="{ active: actionType === 'activar' }"
                @click="actionType = 'activar'"
              >
                <CheckCircle2 :size="16" /> Activar Formatos
              </button>
              <button 
                type="button" 
                class="action-tab tab-danger" 
                :class="{ active: actionType === 'desactivar' }"
                @click="actionType = 'desactivar'"
              >
                <XCircle :size="16" /> Desactivar Formatos
              </button>
            </div>
          </div>

          <div class="quick-toggle-row">
            <span class="section-title">Seleccionar formatos a modificar:</span>
            <div class="quick-toggle-btns">
              <button type="button" class="btn-text" @click="selectAllFormats">
                <CheckSquare :size="14" /> Marcar todos
              </button>
              <button type="button" class="btn-text" @click="deselectAllFormats">
                <Square :size="14" /> Desmarcar todos
              </button>
            </div>
          </div>

          <div class="formats-grid">
            <div 
              v-for="fmt in availableFormats" 
              :key="fmt.id"
              class="format-card-toggle"
              :class="{ selected: selectedFormats.includes(fmt.id) }"
              @click="toggleFormatSelection(fmt.id)"
            >
              <div class="checkbox-box">
                <CheckSquare v-if="selectedFormats.includes(fmt.id)" :size="18" color="#e4869f" />
                <Square v-else :size="18" color="#cbd5e1" />
              </div>
              <span class="fmt-name">{{ fmt.name }}</span>
            </div>
          </div>

          <div v-if="selectedFormats.length === 0" class="warning-text">
            * Debe seleccionar al menos un formato para aplicar la acción masiva.
          </div>
        </div>

        <footer class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="emit('close')" :disabled="isLoading">
            Cancelar
          </button>
          <button 
            type="button" 
            class="btn btn-primary"
            :class="{ 'btn-danger-action': actionType === 'desactivar' }"
            @click="handleApply" 
            :disabled="isLoading || selectedFormats.length === 0"
          >
            {{ isLoading ? 'Aplicando...' : (actionType === 'activar' ? 'Activar Formatos Seleccionados' : 'Desactivar Formatos Seleccionados') }}
          </button>
        </footer>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(50, 44, 68, 0.45);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 1rem;
}

.modal-card {
  background-color: #ffffff;
  border-radius: 1.25rem;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: modalPop 0.25s ease-out;
}

@keyframes modalPop {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f0f0f0;
}

.header-title-box {
  display: flex;
  align-items: center;
  gap: 10px;
}

.header-title-box h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #322c44;
  font-weight: 800;
}

.close-btn {
  background: none;
  border: none;
  color: #9793a0;
  cursor: pointer;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4px;
  transition: all 0.2s;
}

.close-btn:hover {
  background-color: #f0f0f0;
  color: #322c44;
}

.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.summary-banner {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 1rem;
  border-radius: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.summary-count {
  font-size: 0.9rem;
  color: #334155;
}

.action-selector {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.action-tab {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background: white;
  font-size: 0.85rem;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-tab.active {
  background-color: #ecfdf5;
  color: #059669;
  border-color: #10b981;
}

.action-tab.tab-danger.active {
  background-color: #fff1f2;
  color: #e11d48;
  border-color: #ef4444;
}

.quick-toggle-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.section-title {
  font-size: 0.85rem;
  font-weight: 700;
  color: #475569;
}

.quick-toggle-btns {
  display: flex;
  gap: 10px;
}

.btn-text {
  background: none;
  border: none;
  color: #e4869f;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
}

.btn-text:hover {
  text-decoration: underline;
}

.formats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.format-card-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  background-color: white;
  cursor: pointer;
  user-select: none;
  transition: all 0.2s ease;
}

.format-card-toggle:hover {
  border-color: #cbd5e1;
  background-color: #f8fafc;
}

.format-card-toggle.selected {
  border-color: #e4869f;
  background-color: #fff5f7;
}

.fmt-name {
  font-weight: 800;
  color: #322c44;
  font-size: 0.95rem;
}

.warning-text {
  font-size: 0.8rem;
  color: #ef4444;
  font-weight: 600;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 1rem 1.5rem;
  border-top: 1px solid #f0f0f0;
  background-color: #fafafa;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.9rem;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
}

.btn-secondary:hover {
  background: #e2e8f0;
}

.btn-primary {
  background-color: #10b981;
  color: white;
}

.btn-primary:hover {
  background-color: #059669;
}

.btn-primary.btn-danger-action {
  background-color: #ef4444;
}

.btn-primary.btn-danger-action:hover {
  background-color: #dc2626;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
