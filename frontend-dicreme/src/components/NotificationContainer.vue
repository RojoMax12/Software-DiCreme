<template>
  <div class="notification-container">
    <TransitionGroup name="fade-slide">
      <div 
        v-for="n in notifications" 
        :key="n.id" 
        :class="['notification', n.type]"
      >
        {{ n.message }}
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useNotification } from '@/composables/useNotification';
const { notifications } = useNotification();
</script>

<style scoped>
.notification-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999; /* Asegura que esté por encima de todo */
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.notification {
  padding: 15px 25px;
  border-radius: 8px;
  color: white;
  font-weight: bold;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.success { background-color: #4CAF50; }
.error { background-color: #f44336; }

/* Animaciones */
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.4s ease; }
.fade-slide-enter-from { opacity: 0; transform: translateX(30px); }
.fade-slide-leave-to { opacity: 0; transform: translateX(30px); }
</style>