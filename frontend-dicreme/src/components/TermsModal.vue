<script setup lang="ts">
import { X } from 'lucide-vue-next'

const emit = defineEmits(['close', 'accept'])
</script>

<template>
  <div class="modal-overlay" @click.self="emit('close')">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Términos y Condiciones de Uso</h3>
        <button type="button" class="modal-close" @click="emit('close')">
          <X :size="20" />
        </button>
      </div>
      <div class="modal-body">
        <p><strong>1. Registro de Cuenta:</strong> Al registrarte en DiCreme, declaras ser representante autorizado de la empresa ingresada y que los datos proporcionados son fidedignos.</p>
        <p><strong>2. Uso de la Cuenta:</strong> La cuenta es de uso exclusivo para cotizaciones y pedidos de productos DiCreme. Es responsabilidad del usuario mantener la confidencialidad de sus credenciales.</p>
        <p><strong>3. Privacidad de Datos:</strong> Tus datos de contacto e información comercial serán utilizados exclusivamente para la gestión de ventas, despachos y emisión de documentos comerciales.</p>
        <p><strong>4. Cotizaciones y Precios:</strong> Las cotizaciones solicitadas estarán sujetas a confirmación de inventario y factibilidad de despacho por parte del equipo logístico de DiCreme.</p>
      </div>
      <div class="modal-footer">
        <button 
          type="button" 
          class="btn btn-primary" 
          @click="emit('accept')"
        >
          Aceptar Términos
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(50, 44, 68, 0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 1rem;
  animation: fadeIn 0.25s ease-out;
}

.modal-card {
  background-color: #ffffff;
  border-radius: 1.25rem;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: modalPop 0.25s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
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

.modal-header h3 {
  margin: 0;
  font-size: 1.15rem;
  color: #322c44;
  font-weight: 700;
}

.modal-close {
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

.modal-close:hover {
  background-color: #f0f0f0;
  color: #322c44;
}

.modal-body {
  padding: 1.5rem;
  max-height: 320px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
  font-size: 0.92rem;
  color: #495057;
  line-height: 1.5;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #f0f0f0;
  display: flex;
  justify-content: flex-end;
  background-color: #fafafa;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.75rem;
  font-weight: bold;
  cursor: pointer;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

.btn-primary {
  background-color: #e4869f;
  color: white;
}

.btn-primary:hover {
  background-color: #d6758e;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(228, 134, 159, 0.3);
}
</style>
