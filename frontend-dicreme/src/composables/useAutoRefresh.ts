import { ref, onMounted, onUnmounted } from 'vue';

export interface AutoRefreshOptions {
  /**
   * Intervalo en milisegundos para el auto-refresco (por defecto: 15000 ms = 15s)
   */
  intervalMs?: number;
  /**
   * Función opcional que devuelve false para pausar el refresco (ej: si hay un modal abierto)
   */
  enabled?: () => boolean;
}

export function useAutoRefresh(
  refreshCallback: (silent?: boolean) => Promise<void> | void,
  options: AutoRefreshOptions = {}
) {
  const { intervalMs = 15000, enabled } = options;
  const isRefreshing = ref(false);
  const lastRefreshed = ref<Date | null>(null);
  let timer: ReturnType<typeof setInterval> | null = null;

  const executeRefresh = async (silent = true) => {
    if (isRefreshing.value) return;
    if (enabled && !enabled()) return;
    if (document.hidden) return; // No realizar peticiones si la pestaña está en segundo plano

    isRefreshing.value = true;
    try {
      await refreshCallback(silent);
      lastRefreshed.value = new Date();
    } catch (err) {
      console.error('[AutoRefresh] Error durante refresco silencioso:', err);
    } finally {
      isRefreshing.value = false;
    }
  };

  const startPolling = () => {
    stopPolling();
    timer = setInterval(() => {
      executeRefresh(true);
    }, intervalMs);
  };

  const stopPolling = () => {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  };

  const handleVisibilityChange = () => {
    if (!document.hidden) {
      // Al volver a la pestaña, refresca inmediatamente y reinicia el timer
      executeRefresh(true);
    }
  };

  onMounted(() => {
    lastRefreshed.value = new Date();
    startPolling();
    document.addEventListener('visibilitychange', handleVisibilityChange);
  });

  onUnmounted(() => {
    stopPolling();
    document.removeEventListener('visibilitychange', handleVisibilityChange);
  });

  return {
    isRefreshing,
    lastRefreshed,
    manualRefresh: () => executeRefresh(false),
    startPolling,
    stopPolling
  };
}
