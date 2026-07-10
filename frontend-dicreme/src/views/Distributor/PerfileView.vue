<template>
  <div class="profile-container">
    <div class="header-section">
      <h2>Perfil del Distribuidor</h2>
      <p>Actualiza los datos y la imagen de tu empresa</p>
    </div>

    <div class="avatar-section">
      <div class="avatar-wrapper" @click="triggerFileInput">
        <img v-if="previewUrl || form.avatar_url" :src="previewUrl || getImageUrl(form.avatar_url)" alt="Avatar" class="avatar-img" />
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
          <input v-model="form.rut_empresa" type="text" disabled class="disabled-input">
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
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { Camera } from 'lucide-vue-next'; // Ícono para la foto
import { useNotification } from '@/composables/useNotification';
import { globalLoading } from '@/composables/useLoading';

const { notify } = useNotification();
const loading = globalLoading; 

// --- LÓGICA DE LA IMAGEN ---
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const previewUrl = ref<string | null>(null);

// Función para abrir el selector de archivos oculto
const triggerFileInput = () => {
  if (fileInput.value) fileInput.value.click();
};

const handleFileChange = (event: Event) => {
  const input = event.target as HTMLInputElement;
  if (input.files && input.files[0]) {
    selectedFile.value = input.files[0];
    previewUrl.value = URL.createObjectURL(input.files[0]); 
  }
};

// Formatear la URL de la imagen si viene del backend
const getImageUrl = (path: string) => {
  if (!path) return '';
  return path.startsWith('http') ? path : `http://tu-backend-url.com/storage/${path}`;
  // ⚠️ Cambia 'http://tu-backend-url.com' por la URL real de tu API en Laravel
};

// --- LÓGICA DEL FORMULARIO ---
const form = reactive({
  nombre_empresa: '',
  rut_empresa: '',
  correo_electronico: '',
  direccion: '',
  telefono: '',
  comuna: '',
  nueva_contrasena: '',
  avatar_url: '' // Agregamos este campo para recibir la foto actual del backend
});

onMounted(async () => {
  // 1. Carga Rápida (Caché del LocalStorage)
  const cachedUser = localStorage.getItem('user');
  if (cachedUser) {
    try {
      Object.assign(form, JSON.parse(cachedUser));
    } catch (e) {
      console.warn("Caché corrupto, se limpiará automáticamente.");
      localStorage.removeItem('user');
    }
  }
  
  try {
    const token = localStorage.getItem('token'); 
    
    const { data } = await axios.get('http://localhost:8000/api/distribuidor/perfil', {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    
    Object.assign(form, data);
    localStorage.setItem('user', JSON.stringify(data));
  } catch (error) {
    console.error("Error obteniendo perfil fresco:", error);
    notify('No se pudieron actualizar los datos del servidor', 'error');
  }
});

const updateProfile = async () => {
  loading.value = true;
  
  // 1. Preparamos el contenedor FormData para los textos y la foto
  const formData = new FormData();
  formData.append('nombre_empresa', form.nombre_empresa);
  formData.append('correo_electronico', form.correo_electronico);
  if (form.direccion) formData.append('direccion', form.direccion);
  if (form.comuna) formData.append('comuna', form.comuna);
  if (form.telefono) formData.append('telefono', form.telefono);
  if (form.nueva_contrasena) formData.append('nueva_contrasena', form.nueva_contrasena);
  
  if (selectedFile.value) {
    formData.append('avatar', selectedFile.value);
  }

  // Simulación de PUT para compatibilidad de archivos en Laravel
  formData.append('_method', 'PUT');

  try {
    const token = localStorage.getItem('token');

    // 1. Subimos los datos y la foto
    await axios.post('http://localhost:8000/api/distribuidor/perfil', formData, {
      headers: { 
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${token}`
      }
    });
    
    // 2. Recuperamos tu usuario actual (para no perder el ID ni el Rol)
    const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
    
    // 3. Obtenemos los datos frescos del perfil (usando tu endpoint GET que ya funciona)
    const { data: freshData } = await axios.get('http://localhost:8000/api/distribuidor/perfil', {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    
    // 4. Mezclamos ambos para asegurar que el usuario quede 100% completo
    const finalUser = { ...currentUser, ...freshData };
    localStorage.setItem('user', JSON.stringify(finalUser));
    
    notify('Perfil actualizado exitosamente', 'success');
    
    // Opcional: Actualizamos la vista previa del avatar con la nueva URL
    if (freshData.avatar_url) {
      form.avatar_url = freshData.avatar_url;
    }

  } catch (error) {
    console.error(error);
    notify('Error al guardar cambios', 'error');
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
</style>