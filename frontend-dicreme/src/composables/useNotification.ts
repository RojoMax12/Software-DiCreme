import { ref } from 'vue';

export interface Notification {
  id: number;
  message: string;
  title?: string;
  type: 'success' | 'error' | 'warning' | 'info';
  route?: string;
  actionText?: string;
  onAction?: () => void;
  duration?: number;
}

export interface NotifyOptions {
  title?: string;
  type?: 'success' | 'error' | 'warning' | 'info';
  route?: string;
  actionText?: string;
  onAction?: () => void;
  duration?: number;
}

const DEFAULT_TITLES: Record<string, string> = {
  success: 'Éxito',
  error: 'Atención',
  warning: 'Advertencia',
  info: 'Información'
};

const notifications = ref<Notification[]>([]);

export function useNotification() {
  const notify = (
    message: string,
    typeOrOptions: 'success' | 'error' | 'warning' | 'info' | NotifyOptions = 'success',
    options?: NotifyOptions
  ) => {
    const id = Date.now() + Math.floor(Math.random() * 1000);

    let type: 'success' | 'error' | 'warning' | 'info' = 'success';
    let title: string | undefined = undefined;
    let route: string | undefined = undefined;
    let actionText: string | undefined = undefined;
    let onAction: (() => void) | undefined = undefined;
    let duration = 4500;

    if (typeof typeOrOptions === 'string') {
      type = typeOrOptions;
      title = DEFAULT_TITLES[type] || 'Notificación';
      if (options) {
        if (options.title) title = options.title;
        if (options.route) route = options.route;
        if (options.actionText) actionText = options.actionText;
        if (options.onAction) onAction = options.onAction;
        if (options.duration) duration = options.duration;
      }
    } else if (typeof typeOrOptions === 'object' && typeOrOptions !== null) {
      type = typeOrOptions.type || 'success';
      title = typeOrOptions.title || DEFAULT_TITLES[type] || 'Notificación';
      route = typeOrOptions.route;
      actionText = typeOrOptions.actionText;
      onAction = typeOrOptions.onAction;
      if (typeOrOptions.duration) duration = typeOrOptions.duration;
    }

    const newNotification: Notification = {
      id,
      message,
      title,
      type,
      route,
      actionText,
      onAction,
      duration
    };

    notifications.value.push(newNotification);
  };

  const removeNotification = (id: number) => {
    notifications.value = notifications.value.filter(n => n.id !== id);
  };

  return {
    notifications,
    notify,
    removeNotification
  };
}