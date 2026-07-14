<template>
  <Transition name="fade">
    <div v-if="globalLoading" class="loading-overlay">
      <div class="loading-content">
        <IceCream class="spinner" :size="100" color="#e4869f" />
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { globalLoading } from '@/composables/useLoading';
import { IceCream } from 'lucide-vue-next'
</script>

<style scoped>
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: var(--DC-bg-gray, #fcf8f2); 
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
}

.spinner {
  animation: spin 1s linear infinite;
}


@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Puedes dejar este si luego quieres usarlo para el palpitado */
@keyframes pulse {
  0% { transform: scale(1) translateY(0); }
  100% { transform: scale(1.15) translateY(-10px); }
}

/* Transición suave al entrar y salir */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>