<script setup lang="ts">
import { ref, watch } from 'vue'
import { X, Power, CheckCircle2, XCircle } from 'lucide-vue-next'

const props = defineProps<{
  isOpen: boolean
  product: any
  isLoading?: boolean
}>()

const emit = defineEmits(['close', 'save'])

const formatsState = ref<Array<{ id_producto: number; id_formato: number; nombre_formato: string; estado_producto: boolean }>>([])

watch(
  () => props.product,
  (newProd) => {
    if (newProd && newProd.formats) {
      const getRank = (idFmt: number) => {
        switch (idFmt) {
          case 4: return 1 // 1L
          case 3: return 2 // 2.5L
          case 2: return 3 // 5L
          case 1: return 4 // 10L
          default: return idFmt
        }
      }
      formatsState.value = newProd.formats
        .map((f: any) => ({
          id_producto: f.id_producto,
          id_formato: f.id_formato,
          nombre_formato: f.nombre_formato || getFormatName(f.id_formato),
          estado_producto: f.estado_producto !== undefined ? Boolean(f.estado_producto) : true
        }))
        .sort((a: any, b: any) => getRank(a.id_formato) - getRank(b.id_formato))
    } else {
      formatsState.value = []
    }
  },
  { immediate: true }
)

const getFormatName = (idFormato: number): string => {
  switch (idFormato) {
    case 1: return '10L'
    case 2: return '5L'
    case 3: return '2.5L'
    case 4: return '1L'
    default: return `Formato #${idFormato}`
  }
}

const toggleFormat = (index: number) => {
  const item = formatsState.value[index]
  if (item) {
    item.estado_producto = !item.estado_producto
  }
}

const setAllFormatsState = (status: boolean) => {
  formatsState.value.forEach((fmt) => {
    fmt.estado_producto = status
  })
}

const handleSave = () => {
  emit('save', formatsState.value)
}
</script>

<template>
  <Transition name="fade">
    <div v-if="isOpen && product" class="modal-overlay" @click.self="emit('close')">
      <div class="modal-card">
        <header class="modal-header">
          <div class="header-title-box">
            <Power :size="22" color="#e4869f" />
            <h3>Gestionar Disponibilidad de Formatos</h3>
          </div>
          <button class="close-btn" @click="emit('close')">
            <X :size="20" />
          </button>
        </header>

        <div class="modal-body">
          <div class="product-banner">
            <span class="product-name">{{ product.nombre_producto }}</span>
            <p class="help-text">
              Selecciona cuáles formatos estarán disponibles para los clientes en la tienda. Desactivar un formato impedirá su cotización.
            </p>
          </div>

          <!-- Acciones rápidas -->
          <div class="quick-actions-bar">
            <span class="quick-actions-label">Acciones rápidas:</span>
            <div class="quick-actions-buttons">
              <button type="button" class="quick-btn btn-enable-all" @click="setAllFormatsState(true)">
                <CheckCircle2 :size="14" />
                <span>Activar todo</span>
              </button>
              <button type="button" class="quick-btn btn-disable-all" @click="setAllFormatsState(false)">
                <XCircle :size="14" />
                <span>Desactivar todo</span>
              </button>
            </div>
          </div>

          <div class="formats-list">
            <div 
              v-for="(fmt, idx) in formatsState" 
              :key="fmt.id_producto || idx" 
              class="format-row"
              :class="{ 'is-inactive': !fmt.estado_producto }"
              @click="toggleFormat(idx)"
            >
              <div class="format-left">
                <span class="format-badge">{{ fmt.nombre_formato }}</span>
                <span class="status-indicator" :class="fmt.estado_producto ? 'text-active' : 'text-inactive'">
                  {{ fmt.estado_producto ? 'Disponible' : 'Desactivado' }}
                </span>
              </div>

              <div class="switch-container" @click.stop>
                <input 
                  type="checkbox" 
                  :id="'switch-' + idx" 
                  :checked="fmt.estado_producto" 
                  @change="toggleFormat(idx)"
                  class="toggle-checkbox"
                />
                <label :for="'switch-' + idx" class="toggle-switch-label"></label>
              </div>
            </div>
          </div>
        </div>

        <footer class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="emit('close')" :disabled="isLoading">
            Cancelar
          </button>
          <button type="button" class="btn btn-primary" @click="handleSave" :disabled="isLoading">
            {{ isLoading ? 'Guardando...' : 'Guardar Cambios' }}
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
  max-width: 480px;
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

.product-banner {
  background-color: #f8fafc;
  border-left: 4px solid #e4869f;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
}

.product-name {
  font-weight: 800;
  color: #322c44;
  font-size: 1.05rem;
  display: block;
  margin-bottom: 4px;
}

.help-text {
  margin: 0;
  font-size: 0.85rem;
  color: #64748b;
  line-height: 1.4;
}

/* Acciones rápidas */
.quick-actions-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: #f8fafc;
  padding: 8px 12px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}

.quick-actions-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #64748b;
}

.quick-actions-buttons {
  display: flex;
  gap: 8px;
}

.quick-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 5px 10px;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.2s ease;
}

.btn-enable-all {
  background-color: #ecfdf5;
  color: #059669;
  border-color: #a7f3d0;
}

.btn-enable-all:hover {
  background-color: #d1fae5;
}

.btn-disable-all {
  background-color: #fff1f2;
  color: #e11d48;
  border-color: #fecdd3;
}

.btn-disable-all:hover {
  background-color: #ffe4e6;
}

.formats-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.format-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1.1rem;
  background-color: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  cursor: pointer;
  user-select: none;
  transition: all 0.2s ease;
}

.format-row:hover {
  border-color: #cbd5e1;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.format-row.is-inactive {
  background-color: #f8fafc;
  border-color: #cbd5e1;
}

.format-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.format-badge {
  background-color: #322c44;
  color: white;
  font-weight: 800;
  font-size: 0.85rem;
  padding: 4px 12px;
  border-radius: 6px;
  min-width: 55px;
  text-align: center;
}

.status-indicator {
  font-size: 0.9rem;
  font-weight: 700;
  min-width: 100px;
}

.text-active { color: #10b981; }
.text-inactive { color: #ef4444; }

/* Switch style */
.switch-container {
  display: flex;
  align-items: center;
}

.toggle-checkbox {
  display: none;
}

.toggle-switch-label {
  width: 48px;
  height: 26px;
  background-color: #cbd5e1;
  border-radius: 20px;
  position: relative;
  cursor: pointer;
  transition: background-color 0.25s ease;
  display: block;
}

.toggle-checkbox:checked + .toggle-switch-label {
  background-color: #10b981;
}

.toggle-switch-label::after {
  content: '';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  background-color: #ffffff;
  border-radius: 50%;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.toggle-checkbox:checked + .toggle-switch-label::after {
  transform: translateX(22px);
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
  background-color: #e4869f;
  color: white;
  box-shadow: 0 4px 12px rgba(228, 134, 159, 0.3);
}

.btn-primary:hover {
  background-color: #d1728c;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
