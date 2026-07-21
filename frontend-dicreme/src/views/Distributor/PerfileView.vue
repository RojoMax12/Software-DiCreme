<template>
  <div class="profile-container">
    <div class="header-section">
      <h2>Perfil del Distribuidor</h2>
      <p>Actualiza los datos y la imagen de tu empresa</p>
    </div>

    <div class="avatar-section">
      <div class="avatar-wrapper" @click="triggerFileInput">
        <img v-if="previewUrl || hasAvatar" :src="previewUrl || getImageUrl(form.avatar_url)" alt="Avatar" class="avatar-img" />
        <div v-else class="avatar-placeholder">
          {{ form.nombre_empresa ? form.nombre_empresa.charAt(0).toUpperCase() : 'U' }}
        </div>
        
        <div class="avatar-overlay">
          <Camera :size="24" color="white" />
          <span>Cambiar</span>
        </div>
      </div>
      <input type="file" ref="fileInput" @change="handleFileChange" accept="image/*" class="hidden-input">
    </div>

    <form @submit.prevent="updateProfile" class="profile-form">
      <div class="grid-form">
        <div class="form-group">
          <label>Nombre de Empresa</label>
          <input v-model="form.nombre_empresa" type="text" required>
        </div>

        <div class="form-group">
          <label>RUT Empresa</label>
          <input :value="formattedRut" type="text" disabled class="disabled-input rut-styled">
        </div>

        <div class="form-group full-width">
          <label>Correo Electrónico</label>
          <input v-model="form.correo_electronico" type="email" required>
        </div>

        <div class="form-group">
          <label>Dirección</label>
          <input v-model="form.direccion" type="text">
        </div>

        <div class="form-group">
          <label>Comuna</label>
          <input v-model="form.comuna" type="text">
        </div>

        <div class="form-group">
          <label>Teléfono</label>
          <input v-model="form.telefono" type="text">
        </div>

        <div class="form-group full-width">
          <label>Nueva Contraseña (opcional)</label>
          <input v-model="form.nueva_contrasena" type="password" placeholder="••••••••">
        </div>
      </div>

      <button type="submit" :disabled="loading.value" class="submit-btn">
        {{ loading.value ? 'Guardando...' : 'Guardar Cambios' }}
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue';
import { Camera } from 'lucide-vue-next';
import { useNotification } from '@/composables/useNotification';
import { globalLoading } from '@/composables/useLoading';
import distributorService from '@/services/distributorService';
import api from '@/services/api';

const { notify } = useNotification();
const loading = globalLoading; 

// --- LÓGICA DE LA IMAGEN ---
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const previewUrl = ref<string | null>(null);

const triggerFileInput = () => {
  if (fileInput.value) fileInput.value.click();
};

const formatRUT = (rut: string) => {
  if (!rut) return '';
  const cleanRut = rut.replace(/[^0-9kK]/g, '');
  if (cleanRut.length > 1) {
    return cleanRut.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "-" + cleanRut.slice(-1).toUpperCase();
  }
  return cleanRut;
};

const isCompressingPhoto = ref(false);

const processAndCompressAvatar = (file: File): Promise<{ file: File; dataUrl: string }> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const MAX_SIZE = 800;
        let width = img.width;
        let height = img.height;

        if (width > height) {
          if (width > MAX_SIZE) {
            height = Math.round((height * MAX_SIZE) / width);
            width = MAX_SIZE;
          }
        } else {
          if (height > MAX_SIZE) {
            width = Math.round((width * MAX_SIZE) / height);
            height = MAX_SIZE;
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

        const dataUrl = canvas.toDataURL('image/webp', 0.85);

        canvas.toBlob((blob) => {
          if (blob) {
            const webpFile = new File([blob], `avatar_${Date.now()}.webp`, {
              type: 'image/webp'
            });
            resolve({ file: webpFile, dataUrl });
          } else {
            reject(new Error('Error al generar blob'));
          }
        }, 'image/webp', 0.85);
      };
      img.onerror = () => reject(new Error('Error al cargar la imagen'));
      img.src = e.target?.result as string;
    };
    reader.onerror = () => reject(new Error('Error al leer el archivo'));
    reader.readAsDataURL(file);
  });
};

const handleFileChange = async (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (!input.files || !input.files[0]) return;

  const file = input.files[0];

  // 1. Validar tipo
  if (!file.type.startsWith('image/')) {
    notify('El archivo seleccionado debe ser una imagen válida (JPG, PNG, WebP).', 'error');
    input.value = '';
    return;
  }

  // 2. Validar tamaño máximo previo (15MB max input)
  if (file.size > 15 * 1024 * 1024) {
    notify('La foto seleccionada es demasiado pesada (máximo 15 MB).', 'error');
    input.value = '';
    return;
  }

  isCompressingPhoto.value = true;
  try {
    const { file: compressedFile, dataUrl } = await processAndCompressAvatar(file);
    selectedFile.value = compressedFile;
    previewUrl.value = dataUrl;
    notify('Foto de perfil optimizada y lista para guardar.', 'success');
  } catch (err) {
    console.error('Error al comprimir foto:', err);
    notify('No se pudo procesar la foto seleccionada. Intenta con otra imagen.', 'error');
    input.value = '';
  } finally {
    isCompressingPhoto.value = false;
  }
};

const getImageUrl = (path: string | null | undefined) => {
  if (!path || path === 'undefined' || path === 'null') return '';
  if (path.startsWith('http')) return path;
  if (path.startsWith('/storage/')) return `http://localhost:8000${path}`;
  if (path.startsWith('storage/')) return `http://localhost:8000/${path}`;
  return `http://localhost:8000/storage/${path}`;
};

const hasAvatar = computed(() => {
  return !!form.avatar_url && form.avatar_url !== 'null' && form.avatar_url !== 'undefined';
});

const formattedRut = computed(() => formatRUT(form.rut_empresa));

// --- LÓGICA DEL FORMULARIO ---
const userId = ref<number | null>(null);
const form = reactive({
  nombre_empresa: '',
  rut_empresa: '',
  correo_electronico: '',
  direccion: '',
  telefono: '',
  comuna: '',
  nueva_contrasena: '',
  avatar_url: ''
});

onMounted(async () => {
  const cachedUser = localStorage.getItem('user');
  if (cachedUser) {
    try {
      const parsed = JSON.parse(cachedUser);
      Object.assign(form, parsed);
      if (parsed.id) userId.value = parsed.id;
    } catch (e) {
      console.warn("Caché corrupto, se limpiará automáticamente.");
      localStorage.removeItem('user');
    }
  }

  // Intentar sincronizar con el backend
  try {
    let res: any;
    if (userId.value) {
      res = await distributorService.getDistributorById(userId.value);
    } else {
      res = await api.get('/usuarios_distribuidores/me');
    }
    const freshData = res.data?.data || res.data;
    if (freshData) {
      Object.assign(form, freshData);
      form.avatar_url = freshData.foto_perfil || freshData.avatar_url || '';
      if (freshData.id) userId.value = freshData.id;
      const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
      localStorage.setItem('user', JSON.stringify({ ...currentUser, ...freshData }));
    }
  } catch (error) {
    console.log("Cargado con datos del almacenamiento local.");
  }
});

const updateProfile = async () => {
  loading.value = true;

  try {
    let updatedUser: any = null;

    if (selectedFile.value && userId.value) {
      const formData = new FormData();
      formData.append('nombre_empresa', form.nombre_empresa);
      formData.append('correo_electronico', form.correo_electronico);
      if (form.direccion) formData.append('direccion', form.direccion);
      if (form.comuna) formData.append('comuna', form.comuna);
      if (form.telefono) formData.append('telefono', form.telefono);
      if (form.rut_empresa) formData.append('rut_empresa', form.rut_empresa);
      if (form.nueva_contrasena) formData.append('contrasena', form.nueva_contrasena);
      formData.append('avatar', selectedFile.value);
      formData.append('_method', 'PUT');

      const res = await api.post(`/usuarios_distribuidores/${userId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      updatedUser = res.data?.data || res.data;
    } else if (userId.value) {
      const payload: any = {
        nombre_empresa: form.nombre_empresa,
        correo_electronico: form.correo_electronico,
        direccion: form.direccion,
        comuna: form.comuna,
        telefono: form.telefono,
        rut_empresa: form.rut_empresa
      };
      if (form.nueva_contrasena) payload.contrasena = form.nueva_contrasena;

      const res = await distributorService.updateDistributor(userId.value, payload);
      updatedUser = res.data?.data || res.data;
    }
    
    if (updatedUser?.foto_perfil) {
      form.avatar_url = updatedUser.foto_perfil;
    }

    const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
    const finalUser = { ...currentUser, ...form, ...(updatedUser || {}) };
    localStorage.setItem('user', JSON.stringify(finalUser));

    window.dispatchEvent(new Event('perfil-actualizado'));
    notify('Perfil actualizado exitosamente', 'success');
  } catch (error: any) {
    console.error(error);
    const errRes = error.response?.data;
    let errorMessage = errRes?.message || 'Error al guardar cambios del perfil';

    if (errRes?.errors && typeof errRes.errors === 'object') {
      const firstField = Object.keys(errRes.errors)[0];
      if (firstField && Array.isArray(errRes.errors[firstField]) && errRes.errors[firstField][0]) {
        errorMessage = errRes.errors[firstField][0];
      }
    }

    notify(errorMessage, 'error');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.profile-container { 
  max-width: 650px; margin: 3rem auto; padding: 2rem; 
  background: #fff; border-radius: 20px; 
  box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
}

.header-section { text-align: center; margin-bottom: 1.5rem; }
.header-section h2 { color: #1a1624; font-size: 1.8rem; margin-bottom: 5px; }
.header-section p { color: #9793a0; font-size: 0.95rem; }

/* --- DISEÑO DEL AVATAR PROFESIONAL --- */
.avatar-section {
  display: flex;
  justify-content: center;
  margin-bottom: 2rem;
}

.avatar-wrapper {
  position: relative;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  border: 3px solid var(--DC-pink);
  background-color: #fcf8f2;
  overflow: hidden;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(228, 134, 159, 0.3);
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  font-weight: 800;
  color: var(--DC-pink);
}

.avatar-overlay {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.avatar-wrapper:hover .avatar-overlay {
  opacity: 1;
}

.avatar-overlay span {
  font-size: 0.8rem;
  font-weight: 600;
  margin-top: 5px;
}

.hidden-input {
  display: none;
}

/* --- RESTO DEL FORMULARIO --- */
.grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.full-width { grid-column: span 2; }

.form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: #555; }
input { 
  width: 100%; padding: 12px; border: 1px solid #ddd; 
  border-radius: 10px; transition: border 0.3s;
}
input:focus { border-color: var(--DC-pink); outline: none; }
.disabled-input { background: #f9f9f9; color: #999; cursor: not-allowed; }

.submit-btn { 
  margin-top: 1.5rem; width: 100%; padding: 14px; 
  background: var(--DC-pink); color: white; border: none; 
  border-radius: 12px; font-weight: bold; cursor: pointer;
  transition: opacity 0.3s;
}
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.rut-styled {
  background: #f4f6f8;
  color: #322c44;
  font-weight: 700;
  letter-spacing: 0.5px;
  border-color: #eee;
  opacity: 0.8;
}
</style>