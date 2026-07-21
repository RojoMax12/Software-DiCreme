<template>
  <div class="notification-container" role="region" aria-label="Notificaciones del sistema">
    <TransitionGroup name="toast-slide">
      <div
        v-for="n in notifications"
        :key="n.id"
        :class="['toast-card', n.type, { 'is-clickable': !!n.route || !!n.onAction }]"
        @click="handleToastClick(n)"
        @mouseenter="pauseTimer(n.id)"
        @mouseleave="resumeTimer(n)"
      >
        <div class="toast-icon-col">
          <CheckCircle2 v-if="n.type === 'success'" :size="22" class="toast-icon icon-success" />
          <AlertCircle v-else-if="n.type === 'error'" :size="22" class="toast-icon icon-error" />
          <AlertTriangle v-else-if="n.type === 'warning'" :size="22" class="toast-icon icon-warning" />
          <Info v-else :size="22" class="toast-icon icon-info" />
        </div>

        <div class="toast-content-col">
          <h4 v-if="n.title" class="toast-title">{{ n.title }}</h4>
          <p class="toast-message">{{ n.message }}</p>
          
          <div v-if="n.route || n.actionText" class="toast-action-hint">
            <span class="hint-text">{{ n.actionText || 'Haz clic para ver más detalles' }}</span>
            <ExternalLink :size="14" class="hint-icon" />
          </div>
        </div>

        <button class="toast-close-btn" @click.stop="removeNotification(n.id)" title="Cerrar notificación">
          <X :size="16" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { 
  CheckCircle2, AlertCircle, AlertTriangle, 
  Info, ExternalLink, X 
} from 'lucide-vue-next';
import { useNotification, type Notification } from '@/composables/useNotification';

const router = useRouter();
const { notifications, removeNotification } = useNotification();
const activeTimers = new Map<number, { timerId: any; remaining: number; start: number }>();

const startTimer = (n: Notification) => {
  if (activeTimers.has(n.id)) return;
  const duration = n.duration || 4500;
  const start = Date.now();

  const timerId = setTimeout(() => {
    activeTimers.delete(n.id);
    removeNotification(n.id);
  }, duration);

  activeTimers.set(n.id, { timerId, remaining: duration, start });
};

const pauseTimer = (id: number) => {
  const item = activeTimers.get(id);
  if (item) {
    clearTimeout(item.timerId);
    const elapsed = Date.now() - item.start;
    item.remaining = Math.max(1000, item.remaining - elapsed);
  }
};

const resumeTimer = (n: Notification) => {
  const item = activeTimers.get(n.id);
  if (item) {
    item.start = Date.now();
    item.timerId = setTimeout(() => {
      activeTimers.delete(n.id);
      removeNotification(n.id);
    }, item.remaining);
  } else {
    startTimer(n);
  }
};

const handleToastClick = (n: Notification) => {
  if (n.onAction) {
    n.onAction();
  }
  if (n.route) {
    router.push(n.route);
    removeNotification(n.id);
  }
};

const checkTimers = () => {
  notifications.value.forEach(n => {
    if (!activeTimers.has(n.id)) {
      startTimer(n);
    }
  });
};

let intervalId: any;
onMounted(() => {
  intervalId = setInterval(checkTimers, 200);
});

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId);
  activeTimers.forEach(item => clearTimeout(item.timerId));
  activeTimers.clear();
});
</script>

<style scoped>
.notification-container {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 999999; /* Asegura que esté por encima de modals, navbars y contenido */
  display: flex;
  flex-direction: column-reverse; /* Las notificaciones más recientes entran por abajo */
  gap: 12px;
  max-width: 420px;
  width: calc(100vw - 48px);
  pointer-events: none;
}

.toast-card {
  pointer-events: auto;
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 14px 16px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(229, 231, 235, 0.9);
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease, box-shadow 0.25s ease;
}

.toast-card.is-clickable {
  cursor: pointer;
}

.toast-card.is-clickable:hover {
  transform: translateY(-3px) scale(1.01);
  box-shadow: 0 14px 36px rgba(0, 0, 0, 0.16);
}

/* Temas por tipo */
.toast-card.success {
  border-left: 4px solid #10b981;
}

.toast-card.error {
  border-left: 4px solid #ef4444;
}

.toast-card.warning {
  border-left: 4px solid #f59e0b;
}

.toast-card.info {
  border-left: 4px solid #3b82f6;
}

/* Columna Ícono */
.toast-icon-col {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 2px;
  flex-shrink: 0;
}

.icon-success { color: #10b981; }
.icon-error { color: #ef4444; }
.icon-warning { color: #f59e0b; }
.icon-info { color: #3b82f6; }

/* Columna Contenido */
.toast-content-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 3px;
  padding-right: 18px;
}

.toast-title {
  margin: 0;
  font-size: 0.92rem;
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
}

.toast-message {
  margin: 0;
  font-size: 0.86rem;
  color: #4b5563;
  line-height: 1.35;
  font-weight: 500;
}

.toast-action-hint {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  margin-top: 6px;
  font-size: 0.78rem;
  font-weight: 700;
  color: #e4869f;
}

.toast-action-hint:hover {
  color: #d6758e;
}

.hint-icon {
  transition: transform 0.2s ease;
}

.toast-card:hover .hint-icon {
  transform: translateX(2px);
}

/* Botón de Cierre */
.toast-close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: transparent;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s ease, color 0.2s ease;
}

.toast-close-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

/* Animaciones Toast */
.toast-slide-enter-active {
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-slide-leave-active {
  transition: all 0.25s cubic-bezier(0.7, 0, 0.84, 0);
}
.toast-slide-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}
.toast-slide-leave-to {
  opacity: 0;
  transform: translateX(40px) scale(0.9);
}

@media (max-width: 576px) {
  .notification-container {
    bottom: 16px;
    right: 16px;
    left: 16px;
    width: auto;
    max-width: none;
  }
}
</style>