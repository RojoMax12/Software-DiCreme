<script setup lang="ts">
import { X, ShoppingCart, Trash2, Plus, Minus } from 'lucide-vue-next';

defineProps<{
  isOpen: boolean;
}>();

const emit = defineEmits(['close']);
</script>

<template>
  <Transition name="fade">
    <div v-if="isOpen" class="modal-overlay" @click="emit('close')">
      
      <Transition name="slide">
        <div v-if="isOpen" class="modal-content" @click.stop>
          
          <div class="cart-header">
            <button class="close-btn" @click="emit('close')">
              <X :size="24" color="white" />
            </button>
            
            <div class="header-info">
              <ShoppingCart :size="48" color="white" stroke-width="1.5" />
              <h2 class="cart-title">Mi Carrito</h2>
            </div>
          </div>

          <div class="cart-body">
            <div class="empty-state" style="text-align: center; margin-top: 50px; color: #999;">
               <p>Tu carrito está listo para recibir helados</p>
            </div>
          </div>

          <div class="cart-footer">
            <button class="btn-checkout">
              Finalizar Cotización
            </button>
          </div>

        </div>
      </Transition>
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
  background-color: rgba(0, 0, 0, 0.4);
  z-index: 2000;
  display: flex;
}

.modal-content {
  width: 380px; /* Un poco más ancho según el mockup */
  height: 100%;
  background-color: white;
  display: flex;
  flex-direction: column;
  position: relative;
}

/* --- ESTILO DE LA CABECERA ROSA --- */
.cart-header {
  background-color: var(--DC-pink);
  padding: 30px 20px;
  border-bottom-left-radius: 40px; /* El borde redondeado del mockup */
  border-bottom-right-radius: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: none;
  border: none;
  cursor: pointer;
  transition: transform 0.2s;
}

.close-btn:hover {
  transform: scale(1.1);
}

.header-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.cart-title {
  color: white;
  font-size: 1.8rem;
  font-weight: 800;
  margin: 0;
}

/* --- ESTILO DEL CUERPO --- */
.cart-body {
  flex: 1; /* Esto hace que el cuerpo ocupe el espacio sobrante */
  padding: 20px;
  overflow-y: auto; /* Por si hay muchos productos */
}

/* --- ESTILO DEL BOTÓN FINAL --- */
.cart-footer {
  padding: 20px;
  border-top: 1px solid #eee;
}

.btn-checkout {
  width: 100%;
  background-color: var(--DC-gray);
  color: white;
  border: none;
  padding: 15px;
  border-radius: 12px;
  font-weight: bold;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-checkout:hover {
  background-color: #1a1624; /* Un tono más oscuro del gris */
}

/* ANIMACIONES */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-enter-active, .slide-leave-active { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
</style>