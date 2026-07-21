<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  Search,
  MapPin,
  Package,
  DollarSign,
  Calendar,
  Clock,
  CheckCircle2,
  Truck,
  Eye,
  X,
  Camera,
  Bell,
  LogOut,
  AlertCircle,
  FileText
} from 'lucide-vue-next';
import dispatchService from '@/services/dispatchService';
import userService from '@/services/userService';
import type { AvailableDispatch, MyDispatch } from '@/services/dispatchService';
import { useNotification } from '@/composables/useNotification';

const router = useRouter();
const { notify } = useNotification();

// Auth and User
const currentUser = ref<any>(null);

// Profile Sidebar State
const isProfileSidebarOpen = ref(false);
const profileForm = ref({
  nombre_usuario: '',
  correo_electronico: '',
  contrasena: '',
  contrasena_confirm: ''
});
const profileAvatarFile = ref<File | null>(null);
const profileAvatarPreview = ref<string | null>(null);
const isSavingProfile = ref(false);

// Active Navigation Tab: 'disponibles' | 'mis-despachos'
const activeTab = ref<'disponibles' | 'mis-despachos'>('disponibles');

// Filters for Available Orders
const searchAvailable = ref('');
const selectedComunaAvailable = ref('');
const sortAvailable = ref<'oldest' | 'newest'>('oldest');

// Filters for My Dispatches
const searchMy = ref('');
const selectedComunaMy = ref('');
const statusFilterMy = ref<'todos' | 'asignados' | 'en_ruta' | 'entregados'>('todos');

// Data state
const availableDispatches = ref<AvailableDispatch[]>([]);
const myDispatches = ref<MyDispatch[]>([]);
const isLoading = ref(false);
const errorMsg = ref('');
const successMsg = ref('');

// Modals State
const showDetailModal = ref(false);
const selectedOrder = ref<any>(null);

const showCompleteModal = ref(false);
const selectedDispatchToComplete = ref<MyDispatch | null>(null);
const deliveryNotes = ref('');
const selectedPhotoFile = ref<File | null>(null);
const photoPreviewUrl = ref<string | null>(null);
const isSubmittingDelivery = ref(false);

onMounted(() => {
  const userRaw = localStorage.getItem('user');
  if (userRaw) {
    try {
      currentUser.value = JSON.parse(userRaw);
      profileForm.value.nombre_usuario = currentUser.value.nombre_usuario || '';
      profileForm.value.correo_electronico = currentUser.value.correo_electronico || '';
    } catch (e) {
      console.error('Error parseando usuario:', e);
    }
  }
  loadData();
});

const loadData = async () => {
  isLoading.value = true;
  errorMsg.value = '';
  try {
    const resAvailable: any = await dispatchService.getAvailableDispatches();
    availableDispatches.value = Array.isArray(resAvailable) ? resAvailable : (resAvailable.data || []);

    if (currentUser.value && currentUser.value.id) {
      const resMy: any = await dispatchService.getMyDispatches(currentUser.value.id);
      myDispatches.value = Array.isArray(resMy) ? resMy : (resMy.data || []);
    }
  } catch (err: any) {
    console.error('Error al cargar despachos:', err);
    errorMsg.value = 'No se pudieron cargar los datos de despacho.';
  } finally {
    isLoading.value = false;
  }
};

// Comunas dropdown options
const comunasAvailableOptions = computed(() => {
  const list = availableDispatches.value.map(d => d.comuna).filter(Boolean);
  return Array.from(new Set(list));
});

const comunasMyOptions = computed(() => {
  const list = myDispatches.value.map(d => d.comuna).filter(Boolean);
  return Array.from(new Set(list));
});

// Helper for waiting time in days
const getDaysWaiting = (dateStr: string | null) => {
  if (!dateStr) return 0;
  const created = new Date(dateStr);
  const now = new Date();
  const diffTime = Math.abs(now.getTime() - created.getTime());
  return Math.floor(diffTime / (1000 * 60 * 60 * 24));
};

const getBadgeInfo = (dateStr: string | null) => {
  const days = getDaysWaiting(dateStr);
  if (days === 0) return { label: 'Hoy', colorClass: 'badge-green', iconColor: '#22c55e' };
  if (days <= 5) return { label: `${days} días`, colorClass: 'badge-yellow', iconColor: '#eab308' };
  return { label: 'Más de 5 días', colorClass: 'badge-pink', iconColor: '#e4869f' };
};

const getInitials = (name: string) => {
  if (!name) return 'D';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
};

const getAvatarUrl = (url: string | undefined | null) => {
  if (!url) return '';
  if (url.startsWith('http')) return url;
  return `http://localhost:8000${url.startsWith('/') ? '' : '/'}${url}`;
};

const triggerProfileAvatarInput = () => {
  const el = document.getElementById('despachador-avatar-input');
  if (el) el.click();
};

const handleProfileAvatarSelect = async (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files && input.files[0]) {
    const file = input.files[0];
    try {
      const { webpFile, dataUrl } = await processAndCompressImage(file);
      profileAvatarFile.value = webpFile;
      profileAvatarPreview.value = dataUrl;
    } catch (e) {
      profileAvatarFile.value = file;
      const reader = new FileReader();
      reader.onload = (ev) => {
        profileAvatarPreview.value = ev.target?.result as string;
      };
      reader.readAsDataURL(file);
    }
  }
};

const handleSaveProfile = async () => {
  if (!currentUser.value?.id) return;
  
  if (profileForm.value.contrasena && profileForm.value.contrasena !== profileForm.value.contrasena_confirm) {
    notify('Las contraseñas no coinciden', 'error');
    return;
  }

  try {
    isSavingProfile.value = true;
    const formData = new FormData();
    formData.append('nombre_usuario', profileForm.value.nombre_usuario);
    formData.append('correo_electronico', profileForm.value.correo_electronico);
    
    if (profileForm.value.contrasena) {
      formData.append('contrasena', profileForm.value.contrasena);
    }
    
    if (profileAvatarFile.value) {
      formData.append('foto_perfil', profileAvatarFile.value);
    }

    const res = await userService.updateUserProfile(currentUser.value.id, formData);
    const updatedUser = res.data?.data || res.data;

    if (updatedUser) {
      currentUser.value = { ...currentUser.value, ...updatedUser };
      localStorage.setItem('user', JSON.stringify(currentUser.value));
      notify('Perfil de despachador actualizado con éxito', 'success');
      showSuccessNotification('¡Perfil actualizado con éxito!');
      isProfileSidebarOpen.value = false;
      profileForm.value.contrasena = '';
      profileForm.value.contrasena_confirm = '';
    }
  } catch (err: any) {
    console.error('Error al actualizar perfil:', err);
    notify(err.response?.data?.message || 'Error al actualizar el perfil', 'error');
  } finally {
    isSavingProfile.value = false;
  }
};

const formatDate = (dateStr: string | null | undefined) => {
  if (!dateStr) return 'Sin fecha';
  
  try {
    const raw = dateStr.trim();
    let isoString = raw.includes('T') ? raw : raw.replace(' ', 'T');
    
    if (!isoString.endsWith('Z') && !isoString.includes('+') && !isoString.includes('-')) {
      isoString = `${isoString}Z`;
    }

    const d = new Date(isoString);
    
    if (isNaN(d.getTime())) {
      return dateStr;
    }

    const dateFormatted = d.toLocaleDateString('es-CL', {
      timeZone: 'America/Santiago',
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });

    const timeFormatted = d.toLocaleTimeString('es-CL', {
      timeZone: 'America/Santiago',
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    });

    return `${dateFormatted} a las ${timeFormatted} hrs`;
  } catch (e) {
    return dateStr;
  }
};

// Filtered Available Dispatches
const filteredAvailable = computed(() => {
  let list = [...availableDispatches.value];

  if (searchAvailable.value.trim()) {
    const q = searchAvailable.value.toLowerCase();
    list = list.filter(
      item =>
        item.id_pedido.toString().includes(q) ||
        item.nombre_distribuidor.toLowerCase().includes(q) ||
        item.comuna.toLowerCase().includes(q)
    );
  }

  if (selectedComunaAvailable.value) {
    list = list.filter(item => item.comuna === selectedComunaAvailable.value);
  }

  list.sort((a, b) => {
    const tA = new Date(a.created_at || a.fecha_creacion || 0).getTime();
    const tB = new Date(b.created_at || b.fecha_creacion || 0).getTime();
    return sortAvailable.value === 'oldest' ? tA - tB : tB - tA;
  });

  return list;
});

// Filtered My Dispatches
const filteredMy = computed(() => {
  let list = [...myDispatches.value];

  if (statusFilterMy.value === 'asignados') {
    list = list.filter(d => d.id_estado_despacho === 2);
  } else if (statusFilterMy.value === 'en_ruta') {
    list = list.filter(d => d.id_estado_despacho === 3);
  } else if (statusFilterMy.value === 'entregados') {
    list = list.filter(d => d.id_estado_despacho === 4);
  }

  if (searchMy.value.trim()) {
    const q = searchMy.value.toLowerCase();
    list = list.filter(
      item =>
        item.id_pedido.toString().includes(q) ||
        item.nombre_distribuidor.toLowerCase().includes(q) ||
        item.comuna.toLowerCase().includes(q)
    );
  }

  if (selectedComunaMy.value) {
    list = list.filter(item => item.comuna === selectedComunaMy.value);
  }

  return list;
});

// Counter badges for My Dispatches
const countAllMy = computed(() => myDispatches.value.length);
const countAsignadosMy = computed(() => myDispatches.value.filter(d => d.id_estado_despacho === 2).length);
const countEnRutaMy = computed(() => myDispatches.value.filter(d => d.id_estado_despacho === 3).length);
const countEntregadosMy = computed(() => myDispatches.value.filter(d => d.id_estado_despacho === 4).length);

// Actions
const handleTakeOrder = async (item: AvailableDispatch) => {
  if (!currentUser.value || !currentUser.value.id) return;
  isLoading.value = true;
  try {
    await dispatchService.takeDispatch(item.id_despacho, currentUser.value.id);
    showDetailModal.value = false;
    showSuccessNotification('¡Pedido tomado exitosamente!');
    await loadData();
    activeTab.value = 'mis-despachos';
  } catch (err: any) {
    console.error('Error al tomar pedido:', err);
    errorMsg.value = err.response?.data?.message || 'Error al tomar el pedido.';
  } finally {
    isLoading.value = false;
  }
};

const handleStartRoute = async (item: MyDispatch) => {
  isLoading.value = true;
  try {
    await dispatchService.startRoute(item.id_despacho);
    showDetailModal.value = false;
    showSuccessNotification('¡Ruta iniciada correctamente!');
    await loadData();
  } catch (err: any) {
    console.error('Error al iniciar ruta:', err);
    errorMsg.value = err.response?.data?.message || 'Error al iniciar la ruta.';
  } finally {
    isLoading.value = false;
  }
};

const handleReleaseDispatch = async (item: MyDispatch) => {
  if (item.id_estado_despacho !== 1 && item.id_estado_despacho !== 2) {
    notify('Solo se pueden liberar despachos que no hayan iniciado ruta (Estado 1 o 2).', 'error');
    return;
  }
  if (!confirm(`¿Estás seguro de liberar el despacho #${item.id_despacho}? Volverá a la lista de pedidos disponibles.`)) return;

  try {
    isLoading.value = true;
    await dispatchService.releaseDispatch(item.id_despacho);
    showDetailModal.value = false;
    notify('¡Despacho liberado correctamente!', 'success');
    await loadData();
  } catch (err: any) {
    console.error('Error al liberar despacho:', err);
    notify(err.response?.data?.message || 'Error al liberar el despacho.', 'error');
  } finally {
    isLoading.value = false;
  }
};

const openCompleteModal = (item: MyDispatch) => {
  selectedDispatchToComplete.value = item;
  deliveryNotes.value = '';
  selectedPhotoFile.value = null;
  photoPreviewUrl.value = null;
  showCompleteModal.value = true;
};

const isCompressingPhoto = ref(false);

const processAndCompressImage = (file: File): Promise<{ webpFile: File; dataUrl: string }> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const MAX_WIDTH = 1200;
        const MAX_HEIGHT = 1200;
        let width = img.width;
        let height = img.height;

        if (width > height) {
          if (width > MAX_WIDTH) {
            height = Math.round((height * MAX_WIDTH) / width);
            width = MAX_WIDTH;
          }
        } else {
          if (height > MAX_HEIGHT) {
            width = Math.round((width * MAX_HEIGHT) / height);
            height = MAX_HEIGHT;
          }
        }

        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        if (!ctx) {
          reject(new Error('No canvas context'));
          return;
        }

        ctx.drawImage(img, 0, 0, width, height);

        // Generamos Data URL en formato WebP para preview directo (compatible con CSP 'self' data:)
        const dataUrl = canvas.toDataURL('image/webp', 0.82);

        // Convertimos a Blob / File WebP para envío liviano al servidor (~150-250KB)
        canvas.toBlob((blob) => {
          if (blob) {
            const webpFile = new File([blob], `comprobante_${Date.now()}.webp`, {
              type: 'image/webp',
              lastModified: Date.now()
            });
            resolve({ webpFile, dataUrl });
          } else {
            reject(new Error('Fallo al crear Blob WebP'));
          }
        }, 'image/webp', 0.82);
      };
      img.onerror = (err) => reject(err);
      img.src = e.target?.result as string;
    };
    reader.onerror = (err) => reject(err);
    reader.readAsDataURL(file);
  });
};

const handlePhotoSelect = async (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files && input.files[0]) {
    const file = input.files[0];
    isCompressingPhoto.value = true;
    try {
      const { webpFile, dataUrl } = await processAndCompressImage(file);
      selectedPhotoFile.value = webpFile;
      photoPreviewUrl.value = dataUrl;
    } catch (err) {
      console.error('Error al optimizar imagen:', err);
      selectedPhotoFile.value = file;
      const reader = new FileReader();
      reader.onload = (e) => {
        photoPreviewUrl.value = e.target?.result as string;
      };
      reader.readAsDataURL(file);
    } finally {
      isCompressingPhoto.value = false;
    }
  }
};

const triggerFileInput = () => {
  const inputEl = document.getElementById('photo-upload-input');
  if (inputEl) inputEl.click();
};

const getProofUrl = (url: string | undefined) => {
  if (!url) return '';
  if (url.startsWith('http')) return url;
  return `http://localhost:8000${url}`;
};

const handleFinalizeDelivery = async () => {
  if (!selectedDispatchToComplete.value) return;
  if (!selectedPhotoFile.value) {
    alert('Es necesario adjuntar una foto del comprobante de entrega.');
    return;
  }

  isSubmittingDelivery.value = true;
  try {
    const formData = new FormData();
    formData.append('foto_comprobante', selectedPhotoFile.value);
    if (deliveryNotes.value) {
      formData.append('notas_entrega', deliveryNotes.value);
    }

    await dispatchService.completeDelivery(selectedDispatchToComplete.value.id_despacho, formData);
    showCompleteModal.value = false;
    showSuccessNotification('¡Entrega finalizada con éxito!');
    await loadData();
  } catch (err: any) {
    console.error('Error al finalizar entrega:', err);
    alert(err.response?.data?.message || 'Error al finalizar la entrega.');
  } finally {
    isSubmittingDelivery.value = false;
  }
};

const openDetailModal = (item: any) => {
  selectedOrder.value = item;
  showDetailModal.value = true;
};

const showSuccessNotification = (msg: string) => {
  successMsg.value = msg;
  setTimeout(() => {
    successMsg.value = '';
  }, 4000);
};

const handleLogout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  router.push('/login');
};

const formatPrice = (val: number) => {
  return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(val);
};
</script>

<template>
  <div class="dispatcher-layout">
    <!-- Header -->
    <header class="top-bar">
      <div class="header-center">
        <img src="@/assets/logo_dicreme.png" alt="DiCreme Logo" class="brand-logo" />
      </div>

      <div class="header-right">
        <!-- Despachador Profile Badge in Header -->
        <button class="despachador-profile-btn" @click="isProfileSidebarOpen = true" title="Ver perfil de despachador">
          <div class="despachador-avatar-header">
            <img v-if="currentUser?.foto_perfil" :src="getAvatarUrl(currentUser.foto_perfil)" alt="Avatar" class="avatar-img-header" />
            <span v-else class="avatar-initials-header">{{ getInitials(currentUser?.nombre_usuario || 'Despachador') }}</span>
          </div>
          <div class="despachador-info-header">
            <span class="despachador-name">{{ currentUser?.nombre_usuario || 'Despachador' }}</span>
            <span class="despachador-role">Despachador</span>
          </div>
        </button>

        <button class="icon-btn logout-btn" @click="handleLogout" title="Cerrar sesión">
          <LogOut :size="20" color="#666" />
        </button>
      </div>
    </header>

    <!-- SIDEBAR DE PERFIL DE DESPACHADOR -->
    <Transition name="slide">
      <div v-if="isProfileSidebarOpen" class="profile-sidebar-overlay" @click.self="isProfileSidebarOpen = false">
        <aside class="profile-sidebar">
          <header class="sidebar-header">
            <h3>Perfil de Despachador</h3>
            <button class="btn-close-sidebar" @click="isProfileSidebarOpen = false">&times;</button>
          </header>

          <div class="sidebar-body">
            <!-- Sección Subida de Foto de Perfil -->
            <div class="avatar-upload-section">
              <div class="avatar-circle-wrapper" @click="triggerProfileAvatarInput" title="Cambiar foto de perfil">
                <img v-if="profileAvatarPreview || currentUser?.foto_perfil" :src="profileAvatarPreview || getAvatarUrl(currentUser?.foto_perfil)" class="avatar-circle-img" />
                <div v-else class="avatar-circle-placeholder">
                  {{ getInitials(currentUser?.nombre_usuario || 'Despachador') }}
                </div>
                <div class="avatar-camera-overlay">
                  <Camera :size="18" color="white" />
                </div>
              </div>
              <input type="file" id="despachador-avatar-input" accept="image/*" class="hidden-input" @change="handleProfileAvatarSelect" />
              <p class="avatar-help-text">Haz clic en la foto para subir una nueva imagen</p>
            </div>

            <!-- Formulario de Datos Personales -->
            <form @submit.prevent="handleSaveProfile" class="profile-form">
              <div class="form-group">
                <label>Nombre de Usuario</label>
                <input type="text" v-model="profileForm.nombre_usuario" required class="form-input" />
              </div>

              <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" v-model="profileForm.correo_electronico" required class="form-input" />
              </div>

              <div class="form-group">
                <label>Rol en Sistema</label>
                <input type="text" value="Despachador (Rol 4)" disabled class="form-input disabled-input" />
              </div>

              <div class="form-group">
                <label>Nueva Contraseña (Opcional)</label>
                <input type="password" v-model="profileForm.contrasena" placeholder="Mínimo 8 caracteres" class="form-input" />
              </div>

              <div class="form-group" v-if="profileForm.contrasena">
                <label>Confirmar Contraseña</label>
                <input type="password" v-model="profileForm.contrasena_confirm" placeholder="Repite la contraseña" class="form-input" />
              </div>

              <button type="submit" class="btn-save-profile" :disabled="isSavingProfile">
                {{ isSavingProfile ? 'Guardando...' : 'GUARDAR CAMBIOS' }}
              </button>
            </form>

          </div>
        </aside>
      </div>
    </Transition>

    <!-- Success / Error Banners -->
    <div v-if="successMsg" class="banner banner-success">
      {{ successMsg }}
    </div>
    <div v-if="errorMsg" class="banner banner-error">
      {{ errorMsg }}
    </div>

    <!-- Main Container -->
    <main class="main-content">
      <!-- TAB 1: PEDIDOS DISPONIBLES -->
      <section v-if="activeTab === 'disponibles'" class="tab-section">
        <div class="section-title-box">
          <h1 class="page-title">Pedidos disponibles</h1>
          <p class="page-subtitle">Estos pedidos están listos para despacho y aún no han sido asignados.</p>
        </div>

        <!-- Filters Bar -->
        <div class="filters-container">
          <div class="search-input-wrapper">
            <input
              v-model="searchAvailable"
              type="text"
              placeholder="Buscar por ID o distribuidor"
              class="search-input"
            />
            <Search class="search-icon" :size="18" color="#999" />
          </div>

          <div class="select-row">
            <select v-model="selectedComunaAvailable" class="custom-select">
              <option value="">Todas las comunas</option>
              <option v-for="comuna in comunasAvailableOptions" :key="comuna" :value="comuna">
                {{ comuna }}
              </option>
            </select>

            <select v-model="sortAvailable" class="custom-select">
              <option value="oldest">Ordenar por: Más antiguos</option>
              <option value="newest">Ordenar por: Más recientes</option>
            </select>
          </div>
        </div>

        <!-- List of Available Orders -->
        <div v-if="isLoading" class="loading-box">
          <div class="spinner"></div>
          <span>Cargando pedidos disponibles...</span>
        </div>

        <div v-else-if="filteredAvailable.length === 0" class="empty-state">
          <Package :size="48" color="#d1d5db" />
          <p>No hay pedidos disponibles por el momento.</p>
        </div>

        <div v-else class="cards-list">
          <div
            v-for="item in filteredAvailable"
            :key="item.id_despacho"
            class="order-card card-pink-border"
          >
            <div class="card-header">
              <div class="card-title-group">
                <div class="status-icon-box box-pink">
                  <FileText :size="20" color="#e4869f" />
                </div>
                <div>
                  <h3 class="order-id">#Pedido {{ item.id_pedido }}</h3>
                  <span class="distributor-name">Distribuidor: {{ item.nombre_distribuidor }}</span>
                </div>
              </div>
              <span class="badge" :class="getBadgeInfo(item.created_at || item.fecha_creacion).colorClass">
                {{ getBadgeInfo(item.created_at || item.fecha_creacion).label }}
              </span>
            </div>

            <div class="card-location">
              <MapPin :size="16" color="#888" />
              <span>{{ item.direccion_entrega }}, {{ item.comuna }}</span>
            </div>

            <div class="card-info-grid">
              <div class="info-item">
                <Package :size="16" color="#888" />
                <span><strong>{{ item.cantidad_productos }}</strong> productos</span>
              </div>
              <div class="info-item">
                <DollarSign :size="16" color="#888" />
                <span><strong>{{ formatPrice(item.monto_total) }}</strong> total</span>
              </div>
              <div class="info-item">
                <Calendar :size="16" color="#888" />
                <span>{{ item.fecha_creacion }} {{ item.hora_creacion || '' }} Ingresado</span>
              </div>
            </div>

            <div class="card-actions">
              <button class="btn btn-outline" @click="openDetailModal(item)">
                <Eye :size="16" /> Ver detalle
              </button>
              <button class="btn btn-pink" @click="handleTakeOrder(item)">
                Tomar pedido
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- TAB 2: MIS DESPACHOS -->
      <section v-else class="tab-section">
        <div class="section-title-box">
          <h1 class="page-title">Mis Despachos</h1>
          <p class="page-subtitle">Estos son los pedidos que tienes asignados.</p>
        </div>

        <!-- Status Pills Filters -->
        <div class="status-pills-row">
          <button
            class="status-pill"
            :class="{ active: statusFilterMy === 'todos' }"
            @click="statusFilterMy = 'todos'"
          >
            <div class="pill-icon-box gray-bg">
              <FileText :size="16" color="#666" />
            </div>
            <span>Todos <strong>{{ countAllMy }}</strong></span>
          </button>

          <button
            class="status-pill"
            :class="{ active: statusFilterMy === 'asignados' }"
            @click="statusFilterMy = 'asignados'"
          >
            <div class="pill-icon-box orange-bg">
              <Clock :size="16" color="#f97316" />
            </div>
            <span>Asignados <strong>{{ countAsignadosMy }}</strong></span>
          </button>

          <button
            class="status-pill"
            :class="{ active: statusFilterMy === 'en_ruta' }"
            @click="statusFilterMy = 'en_ruta'"
          >
            <div class="pill-icon-box blue-bg">
              <Truck :size="16" color="#3b82f6" />
            </div>
            <span>En ruta <strong>{{ countEnRutaMy }}</strong></span>
          </button>

          <button
            class="status-pill"
            :class="{ active: statusFilterMy === 'entregados' }"
            @click="statusFilterMy = 'entregados'"
          >
            <div class="pill-icon-box green-bg">
              <CheckCircle2 :size="16" color="#22c55e" />
            </div>
            <span>Entregado <strong>{{ countEntregadosMy }}</strong></span>
          </button>
        </div>

        <!-- Filters Bar -->
        <div class="filters-container">
          <div class="search-input-wrapper">
            <input
              v-model="searchMy"
              type="text"
              placeholder="Buscar por ID o distribuidor"
              class="search-input"
            />
            <Search class="search-icon" :size="18" color="#999" />
          </div>

          <div class="select-row">
            <select v-model="selectedComunaMy" class="custom-select">
              <option value="">Todas las comunas</option>
              <option v-for="comuna in comunasMyOptions" :key="comuna" :value="comuna">
                {{ comuna }}
              </option>
            </select>
          </div>
        </div>

        <!-- List of My Dispatches -->
        <div v-if="isLoading" class="loading-box">
          <div class="spinner"></div>
          <span>Cargando mis despachos...</span>
        </div>

        <div v-else-if="filteredMy.length === 0" class="empty-state">
          <Truck :size="48" color="#d1d5db" />
          <p>No tienes despachos en este estado.</p>
        </div>

        <div v-else class="cards-list">
          <div
            v-for="item in filteredMy"
            :key="item.id_despacho"
            class="order-card"
            :class="{
              'card-orange-border': item.id_estado_despacho === 2,
              'card-blue-border': item.id_estado_despacho === 3,
              'card-green-border': item.id_estado_despacho === 4
            }"
          >
            <div class="card-header">
              <div class="card-title-group">
                <!-- Icon based on status -->
                <div v-if="item.id_estado_despacho === 2" class="status-icon-box orange-bg">
                  <Clock :size="20" color="#f97316" />
                </div>
                <div v-else-if="item.id_estado_despacho === 3" class="status-icon-box blue-bg">
                  <Truck :size="20" color="#3b82f6" />
                </div>
                <div v-else class="status-icon-box green-bg">
                  <CheckCircle2 :size="20" color="#22c55e" />
                </div>

                <div>
                  <h3 class="order-id">#Pedido {{ item.id_pedido }}</h3>
                  <span class="distributor-name">Distribuidor: {{ item.nombre_distribuidor }}</span>
                </div>
              </div>

              <!-- Badge based on status -->
              <span
                class="badge"
                :class="{
                  'badge-orange': item.id_estado_despacho === 2,
                  'badge-blue': item.id_estado_despacho === 3,
                  'badge-green': item.id_estado_despacho === 4
                }"
              >
                {{ item.nombre_estado_despacho || (item.id_estado_despacho === 2 ? 'Asignado' : item.id_estado_despacho === 3 ? 'En ruta' : 'Entregado') }}
              </span>
            </div>

            <div class="card-location">
              <MapPin :size="16" color="#888" />
              <span>{{ item.direccion_entrega }}, {{ item.comuna }}</span>
            </div>

            <div class="card-info-grid">
              <div class="info-item">
                <Package :size="16" color="#888" />
                <span><strong>{{ item.cantidad_productos }}</strong> productos</span>
              </div>
              <div class="info-item">
                <DollarSign :size="16" color="#888" />
                <span><strong>{{ formatPrice(item.monto_total) }}</strong> total</span>
              </div>
              <div class="info-item">
                <Calendar :size="16" color="#888" />
                <span>{{ item.fecha_creacion }} {{ item.hora_creacion || '' }} Ingresado</span>
              </div>
            </div>

            <div class="card-actions">
              <button class="btn btn-outline" @click="openDetailModal(item)">
                <Eye :size="16" /> Ver detalle
              </button>

              <button
                v-if="item.id_estado_despacho === 1 || item.id_estado_despacho === 2"
                class="btn btn-outline"
                style="border-color: #f43f5e; color: #f43f5e;"
                @click="handleReleaseDispatch(item)"
                title="Liberar despacho"
              >
                Liberar
              </button>

              <button
                v-if="item.id_estado_despacho === 2"
                class="btn btn-orange"
                @click="handleStartRoute(item)"
              >
                Iniciar ruta
              </button>

              <button
                v-else-if="item.id_estado_despacho === 3"
                class="btn btn-blue"
                @click="openCompleteModal(item)"
              >
                Finalizar entrega
              </button>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="bottom-nav">
      <button
        class="nav-tab"
        :class="{ active: activeTab === 'disponibles' }"
        @click="activeTab = 'disponibles'"
      >
        <FileText :size="20" />
        <span>Pedidos disponibles</span>
      </button>

      <button
        class="nav-tab"
        :class="{ active: activeTab === 'mis-despachos' }"
        @click="activeTab = 'mis-despachos'"
      >
        <Truck :size="20" />
        <span>Mis despachos</span>
      </button>
    </nav>

    <!-- MODAL 1: DETALLE DE PEDIDO (Bottom Sheet) -->
    <div v-if="showDetailModal" class="modal-backdrop" @click.self="showDetailModal = false">
      <div class="bottom-sheet modal-content">
        <div class="drag-handle"></div>
        <button class="close-btn" @click="showDetailModal = false">
          <X :size="22" color="#666" />
        </button>

        <div v-if="selectedOrder" class="detail-wrapper">
          <!-- Detail Header -->
          <div class="detail-header">
            <div class="status-icon-box green-bg">
              <CheckCircle2 :size="24" color="#22c55e" />
            </div>
            <div class="header-text">
              <h2>#Pedido {{ selectedOrder.id_pedido }}</h2>
              <p class="distributor-sub">Distribuidor: {{ selectedOrder.nombre_distribuidor }}</p>
              <div class="location-sub">
                <MapPin :size="16" color="#666" />
                <span>{{ selectedOrder.direccion_entrega }}, {{ selectedOrder.comuna }}</span>
              </div>
            </div>
          </div>

          <!-- Summary 4 Cards Grid -->
          <div class="summary-grid">
            <div class="summary-box">
              <span class="box-label">Fecha de Ingreso</span>
              <strong class="box-val">{{ selectedOrder.fecha_creacion }}</strong>
              <small v-if="selectedOrder.hora_creacion" class="box-sub">{{ selectedOrder.hora_creacion }} hrs</small>
            </div>

            <div class="summary-box">
              <span class="box-label">Productos del pedido</span>
              <strong class="box-val large-val">{{ selectedOrder.cantidad_productos }}</strong>
            </div>

            <div class="summary-box">
              <span class="box-label">Total del pedido</span>
              <strong class="box-val price-val">{{ formatPrice(selectedOrder.monto_total) }}</strong>
            </div>

            <div class="summary-box">
              <span class="box-label">Días en espera / Entrega</span>
              <span v-if="selectedOrder.fecha_entrega" class="box-val small-val">{{ formatDate(selectedOrder.fecha_entrega) }}</span>
              <span v-else class="badge badge-green">
                {{ getDaysWaiting(selectedOrder.created_at || selectedOrder.fecha_creacion) }} días
              </span>
            </div>
          </div>

          <!-- Section: Productos del pedido -->
          <div class="detail-section">
            <h3 class="section-heading"><Package :size="18" /> Productos del pedido</h3>
            <div class="products-list">
              <div
                v-for="prod in selectedOrder.productos || []"
                :key="prod.id"
                class="product-item-card"
              >
                <div class="prod-img-placeholder">
                  <Package :size="24" color="#e4869f" />
                </div>
                <div class="prod-details">
                  <h4>
                    {{ prod.nombre_producto }}
                    <span v-if="prod.categoria" class="tag-category">- {{ prod.categoria }}</span>
                  </h4>
                  <span v-if="prod.formato" class="prod-format">{{ prod.formato }}</span>
                </div>
                <div class="prod-qty">
                  <strong>{{ prod.cantidad }}</strong>
                  <small>unidades</small>
                </div>
              </div>
            </div>
          </div>

          <!-- Section: Información de Entrega -->
          <div class="detail-section">
            <h3 class="section-heading"><MapPin :size="18" /> Información de Entrega</h3>
            <div class="delivery-info-card">
              <div class="info-group">
                <label>Dirección</label>
                <p>{{ selectedOrder.direccion_entrega }}, {{ selectedOrder.comuna }}</p>
              </div>
              <div class="info-group">
                <label>Referencia</label>
                <p>Sin referencias adicionales</p>
              </div>
              <div class="info-group">
                <label>Contacto</label>
                <p>{{ selectedOrder.persona_recibe }} {{ selectedOrder.telefono_contacto ? '• ' + selectedOrder.telefono_contacto : '' }}</p>
              </div>
            </div>

            <div v-if="selectedOrder.foto_comprobante" style="margin-top: 15px; background: #f8f9fa; padding: 12px; border-radius: 12px; border: 1px solid #eeedee;">
              <p style="font-size: 0.85rem; font-weight: 700; color: #1a1624; margin-bottom: 8px;">Comprobante de entrega registrado:</p>
              <img :src="getProofUrl(selectedOrder.foto_comprobante)" style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; border: 1px solid #e4869f;" />
              <p v-if="selectedOrder.notas_entrega" style="font-size: 0.82rem; color: #666; margin-top: 6px; font-style: italic;">"{{ selectedOrder.notas_entrega }}"</p>
            </div>
          </div>

          <!-- Action Button inside detail modal -->
          <div class="modal-footer-action">
            <button
              v-if="activeTab === 'disponibles'"
              class="btn btn-pink full-btn"
              @click="handleTakeOrder(selectedOrder)"
            >
              Tomar pedido
            </button>
            <button
              v-else-if="selectedOrder.id_estado_despacho === 2"
              class="btn btn-orange full-btn"
              @click="handleStartRoute(selectedOrder)"
            >
              Iniciar ruta
            </button>
            <button
              v-else-if="selectedOrder.id_estado_despacho === 3"
              class="btn btn-blue full-btn"
              @click="showDetailModal = false; openCompleteModal(selectedOrder)"
            >
              Finalizar entrega
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 2: FINALIZAR ENTREGA (Con foto) -->
    <div v-if="showCompleteModal" class="modal-backdrop" @click.self="showCompleteModal = false">
      <div class="bottom-sheet modal-content">
        <div class="drag-handle"></div>
        <button class="close-btn" @click="showCompleteModal = false">
          <X :size="22" color="#666" />
        </button>

        <div class="complete-wrapper">
          <div class="camera-circle-icon" @click="triggerFileInput">
            <Camera :size="36" color="#e4869f" />
          </div>

          <div class="upload-trigger-box" @click="triggerFileInput">
            <p class="upload-title">Adjuntar imágen desde la galería</p>
            <span class="upload-or">o</span>
            <p class="upload-action">Tomar fotografía</p>
          </div>

          <!-- Hidden File Input -->
          <input
            id="photo-upload-input"
            type="file"
            accept="image/*"
            capture="environment"
            class="hidden-file-input"
            @change="handlePhotoSelect"
          />

          <!-- Loading state when processing image -->
          <div v-if="isCompressingPhoto" style="padding: 10px; text-align: center; color: #e4869f; font-size: 0.9rem; font-weight: 600;">
            <span>Procesando y comprimiendo imagen a WebP...</span>
          </div>

          <!-- Preview image if selected -->
          <div v-else-if="photoPreviewUrl" class="photo-preview-box">
            <img :src="photoPreviewUrl" alt="Vista previa de entrega" class="preview-img" />
            <button class="btn-remove-photo" @click="photoPreviewUrl = null; selectedPhotoFile = null">
              <X :size="14" /> Quitar imagen
            </button>
          </div>

          <div class="warning-hint">
            <AlertCircle :size="16" color="#e4869f" />
            <span>Es necesario adjuntar imagen del pedido entregado para finalizar.</span>
          </div>

          <div class="notes-input-wrapper">
            <textarea
              v-model="deliveryNotes"
              placeholder="Notas de la entrega..."
              rows="3"
              class="notes-textarea"
            ></textarea>
          </div>

          <button
            class="btn btn-submit-delivery full-btn"
            :disabled="!selectedPhotoFile || isSubmittingDelivery"
            @click="handleFinalizeDelivery"
          >
            {{ isSubmittingDelivery ? 'Guardando...' : 'Finalizar entrega' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dispatcher-layout {
  min-height: 100vh;
  background-color: #f8fafc;
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  padding-bottom: 80px;
}

/* Header */
.top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.25rem;
  background-color: #ffffff;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 100;
}

.brand-logo {
  height: 40px;
  width: auto;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.icon-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.4rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.icon-btn:hover {
  background-color: #f1f5f9;
}

.hamburger-line {
  display: block;
  width: 18px;
  height: 2px;
  background-color: #e4869f;
  margin: 3px 0;
}

/* Banners */
.banner {
  padding: 0.75rem 1rem;
  margin: 0.5rem 1rem 0;
  border-radius: 0.5rem;
  font-size: 0.9rem;
  font-weight: 600;
  text-align: center;
}

.banner-success {
  background-color: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}

.banner-error {
  background-color: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
}

/* Main Content */
.main-content {
  padding: 1rem;
  max-width: 600px;
  margin: 0 auto;
}

.section-title-box {
  margin-bottom: 1.25rem;
}

.page-title {
  font-size: 1.35rem;
  font-weight: 800;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.page-subtitle {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0;
}

/* Filters */
.filters-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.search-input-wrapper {
  position: relative;
  width: 100%;
}

.search-input {
  width: 100%;
  padding: 0.65rem 1rem 0.65rem 2.4rem;
  border: 1px solid #cbd5e1;
  border-radius: 0.6rem;
  font-size: 0.9rem;
  background-color: #fff;
  box-sizing: border-box;
  outline: none;
}

.search-input:focus {
  border-color: #e4869f;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
}

.select-row {
  display: flex;
  gap: 0.5rem;
}

.custom-select {
  flex: 1;
  padding: 0.55rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 0.6rem;
  background-color: #fff;
  font-size: 0.85rem;
  color: #334155;
  outline: none;
}

/* Status Pills Bar (Mis Despachos) */
.status-pills-row {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
  margin-bottom: 1rem;
}

.status-pill {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  padding: 0.4rem 0.75rem;
  border-radius: 0.75rem;
  font-size: 0.8rem;
  color: #475569;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
}

.status-pill.active {
  border-color: #e4869f;
  background-color: #fff0f5;
  color: #e4869f;
  font-weight: 700;
}

.pill-icon-box {
  width: 24px;
  height: 24px;
  border-radius: 0.4rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gray-bg { background-color: #f1f5f9; }
.orange-bg { background-color: #fff7ed; }
.blue-bg { background-color: #eff6ff; }
.green-bg { background-color: #f0fdf4; }
.box-pink { background-color: #fff0f5; }

/* Cards List */
.cards-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.order-card {
  background: #ffffff;
  border-radius: 1rem;
  padding: 1rem;
  border-left: 5px solid #cbd5e1;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.card-pink-border { border-left-color: #e4869f; }
.card-orange-border { border-left-color: #f97316; }
.card-blue-border { border-left-color: #3b82f6; }
.card-green-border { border-left-color: #22c55e; }

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.card-title-group {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.status-icon-box {
  width: 38px;
  height: 38px;
  border-radius: 0.6rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.order-id {
  font-size: 1rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.distributor-name {
  font-size: 0.82rem;
  color: #64748b;
}

.badge {
  padding: 0.25rem 0.6rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 700;
}

.badge-pink { background-color: #ffe4e6; color: #e11d48; }
.badge-yellow { background-color: #fef9c3; color: #ca8a04; }
.badge-green { background-color: #dcfce7; color: #16a34a; }
.badge-orange { background-color: #ffedd5; color: #ea580c; }
.badge-blue { background-color: #dbeafe; color: #2563eb; }

.card-location {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.85rem;
  color: #334155;
  margin-bottom: 0.75rem;
}

.card-info-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
  background-color: #f8fafc;
  padding: 0.6rem;
  border-radius: 0.6rem;
  margin-bottom: 0.85rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  font-size: 0.75rem;
  color: #64748b;
  text-align: center;
}

.info-item strong {
  color: #0f172a;
}

.card-actions {
  display: flex;
  gap: 0.5rem;
}

.btn {
  flex: 1;
  padding: 0.6rem 0.75rem;
  border-radius: 0.6rem;
  font-size: 0.85rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  transition: opacity 0.2s;
}

.btn:hover {
  opacity: 0.9;
}

.btn-pink { background-color: #e4869f; color: #fff; }
.btn-orange { background-color: #f97316; color: #fff; }
.btn-blue { background-color: #3b82f6; color: #fff; }

.btn-outline {
  background-color: transparent;
  border: 1px solid #cbd5e1;
  color: #475569;
}

/* Bottom Navigation Bar */
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 65px;
  background-color: #ffffff;
  display: flex;
  border-top: 1px solid #e2e8f0;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
  z-index: 90;
}

.nav-tab {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
}

.nav-tab.active {
  color: #e4869f;
  font-weight: 800;
  background-color: #fff0f5;
}

/* Modals (Bottom Sheet) */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 200;
  display: flex;
  justify-content: center;
  align-items: flex-end;
}

.bottom-sheet {
  background: #ffffff;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  border-top-left-radius: 1.5rem;
  border-top-right-radius: 1.5rem;
  padding: 1.25rem;
  overflow-y: auto;
  position: relative;
  animation: slideUp 0.25s ease-out;
}

@keyframes slideUp {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}

.drag-handle {
  width: 40px;
  height: 4px;
  background-color: #cbd5e1;
  border-radius: 2px;
  margin: 0 auto 1rem auto;
}

.close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: transparent;
  border: none;
  cursor: pointer;
}

/* Detail Modal Styling */
.detail-header {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.header-text h2 {
  font-size: 1.2rem;
  font-weight: 800;
  margin: 0 0 0.2rem 0;
}

.distributor-sub {
  font-size: 0.9rem;
  color: #475569;
  margin: 0 0 0.3rem 0;
  font-weight: 600;
}

.location-sub {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.85rem;
  color: #64748b;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.5rem;
  margin-bottom: 1.25rem;
}

.summary-box {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.6rem;
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.box-label {
  font-size: 0.7rem;
  color: #64748b;
  margin-bottom: 0.2rem;
}

.box-val {
  font-size: 0.85rem;
  font-weight: 800;
  color: #0f172a;
}

.large-val { font-size: 1.1rem; }
.price-val { color: #16a34a; }

.detail-section {
  margin-bottom: 1.25rem;
}

.section-heading {
  font-size: 0.95rem;
  font-weight: 700;
  color: #334155;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 0.6rem;
}

.products-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.product-item-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.6rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.6rem;
  background-color: #fff;
}

.prod-img-placeholder {
  width: 42px;
  height: 42px;
  background-color: #fff0f5;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.75rem;
}

.prod-details {
  flex: 1;
}

.prod-details h4 {
  font-size: 0.85rem;
  font-weight: 700;
  margin: 0 0 0.2rem 0;
}

.tag-category {
  color: #e4869f;
}

.prod-format {
  font-size: 0.75rem;
  color: #64748b;
}

.prod-qty {
  text-align: right;
}

.prod-qty strong {
  display: block;
  font-size: 1rem;
  color: #0f172a;
}

.prod-qty small {
  font-size: 0.7rem;
  color: #64748b;
}

.delivery-info-card {
  background-color: #f8fafc;
  border-radius: 0.6rem;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.info-group label {
  display: block;
  font-size: 0.75rem;
  color: #94a3b8;
  margin-bottom: 0.1rem;
}

.info-group p {
  margin: 0;
  font-size: 0.85rem;
  font-weight: 600;
  color: #334155;
}

.full-btn {
  width: 100%;
  padding: 0.75rem;
  font-size: 0.95rem;
  border-radius: 0.75rem;
}

/* Complete Delivery Modal Styling */
.complete-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.5rem 0;
}

.camera-circle-icon {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background-color: #fff0f5;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
  cursor: pointer;
}

.upload-trigger-box {
  border: 2px dashed #e4869f;
  border-radius: 1rem;
  padding: 1.5rem;
  width: 100%;
  box-sizing: border-box;
  text-align: center;
  cursor: pointer;
  background-color: #fafafa;
  margin-bottom: 1rem;
}

.upload-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: #334155;
  margin: 0 0 0.3rem 0;
}

.upload-or {
  display: block;
  font-size: 0.8rem;
  color: #94a3b8;
  margin: 0.2rem 0;
}

.upload-action {
  font-size: 0.9rem;
  font-weight: 700;
  color: #e4869f;
  margin: 0;
}

.hidden-file-input {
  display: none;
}

.photo-preview-box {
  width: 100%;
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.preview-img {
  max-width: 100%;
  max-height: 180px;
  border-radius: 0.75rem;
  border: 1px solid #cbd5e1;
}

.btn-remove-photo {
  background: #fee2e2;
  color: #b91c1c;
  border: none;
  padding: 0.3rem 0.6rem;
  border-radius: 0.4rem;
  font-size: 0.75rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.warning-hint {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.78rem;
  color: #e4869f;
  margin-bottom: 1rem;
  text-align: center;
}

.notes-input-wrapper {
  width: 100%;
  margin-bottom: 1.25rem;
}

.notes-textarea {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 0.75rem;
  padding: 0.75rem;
  font-size: 0.85rem;
  box-sizing: border-box;
  outline: none;
}

.notes-textarea:focus {
  border-color: #e4869f;
}

.btn-submit-delivery {
  background-color: #e4869f;
  color: #fff;
}

.btn-submit-delivery:disabled {
  background-color: #cbd5e1;
  cursor: not-allowed;
}

.loading-box, .empty-state {
  padding: 3rem 1rem;
  text-align: center;
  color: #64748b;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #e4869f;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* ESTILOS DE PERFIL Y SIDEBAR DESPACHADOR */
.despachador-profile-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  background: white;
  border: 1px solid #e5e7eb;
  padding: 5px 14px 5px 6px;
  border-radius: 30px;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
  transition: all 0.2s ease;
}

.despachador-profile-btn:hover {
  border-color: #e4869f;
  transform: translateY(-1px);
}

.despachador-avatar-header {
  width: 36px;
  height: 36px;
}

.avatar-img-header {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e4869f;
}

.avatar-initials-header {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #e4869f;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.85rem;
}

.despachador-info-header {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.despachador-name {
  font-size: 0.88rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1.1;
}

.despachador-role {
  font-size: 0.72rem;
  color: #6b7280;
}

/* SIDEBAR SLIDE OVER */
.profile-sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(3px);
  z-index: 9999;
  display: flex;
  justify-content: flex-end;
}

.profile-sidebar {
  width: 360px;
  max-width: 90vw;
  height: 100vh;
  background: white;
  box-shadow: -4px 0 25px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  padding: 24px;
  overflow-y: auto;
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid #f3f4f6;
  padding-bottom: 14px;
  margin-bottom: 20px;
}

.sidebar-header h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 800;
  color: #1f2937;
}

.btn-close-sidebar {
  background: none;
  border: none;
  font-size: 1.8rem;
  color: #9ca3af;
  cursor: pointer;
}

.avatar-upload-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 24px;
}

.avatar-circle-wrapper {
  position: relative;
  width: 96px;
  height: 96px;
  border-radius: 50%;
  cursor: pointer;
  margin-bottom: 10px;
  box-shadow: 0 4px 12px rgba(228, 134, 159, 0.25);
}

.avatar-circle-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #e4869f;
}

.avatar-circle-placeholder {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: #e4869f;
  color: white;
  font-size: 2rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-camera-overlay {
  position: absolute;
  bottom: 0;
  right: 0;
  background: #322c44;
  border-radius: 50%;
  padding: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
}

.avatar-help-text {
  font-size: 0.78rem;
  color: #6b7280;
  text-align: center;
}

.hidden-input {
  display: none;
}

.profile-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #374151;
}

.form-input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #d1d5db;
  font-size: 0.88rem;
  outline: none;
}

.form-input:focus {
  border-color: #e4869f;
}

.disabled-input {
  background-color: #f3f4f6;
  color: #6b7280;
  cursor: not-allowed;
}

.btn-save-profile {
  background: #e4869f;
  color: white;
  border: none;
  border-radius: 12px;
  padding: 12px;
  font-weight: 800;
  font-size: 0.9rem;
  cursor: pointer;
  margin-top: 10px;
  box-shadow: 0 4px 10px rgba(228, 134, 159, 0.3);
  transition: transform 0.2s, background-color 0.2s;
}

.btn-save-profile:hover {
  transform: translateY(-1px);
  background: #d1728c;
}

.btn-save-profile:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
}

.btn-logout-sidebar {
  width: 100%;
  background: #f3f4f6;
  color: #4b5563;
  border: none;
  border-radius: 12px;
  padding: 12px;
  font-weight: 700;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  margin-top: auto;
}

.btn-logout-sidebar:hover {
  background: #fee2e2;
  color: #dc2626;
}

.slide-enter-active, .slide-leave-active { transition: opacity 0.25s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; }
</style>
