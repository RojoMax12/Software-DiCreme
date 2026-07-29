<template>
  <div class="admin-storefront-container">
    <div class="header-titles">
      <h1 class="page-title">Apariencia de la Tienda</h1>
      <p class="page-subtitle">Gestiona el carrusel de imágenes y los avisos de la página principal.</p>
    </div>

    <!-- Navegación por pestañas -->
    <div class="tab-navigation">
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'carousel' }" 
        @click="activeTab = 'carousel'"
      >
        <Image :size="18" />
        <span>Carrusel de Imágenes</span>
      </button>
      
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'ticker' }" 
        @click="activeTab = 'ticker'"
      >
        <MessageCircle :size="18" />
        <span>Barra de Avisos</span>
      </button>
    </div>

    <!-- ==========================================
         PESTAÑA 1: CARRUSEL DE IMÁGENES
    =========================================== -->
    <div v-if="activeTab === 'carousel'" class="editor-card tab-pane">
      
      <div class="upload-zone" @click="triggerUpload">
        <input 
          type="file" 
          id="carousel-file-input" 
          multiple 
          accept="image/png, image/jpeg, image/webp" 
          class="hidden-input"
          @change="handleFiles"
        />
        <div class="upload-content">
          <Upload :size="32" color="#e4869f" class="upload-icon" />
          <h3>Haz clic para subir imágenes</h3>
          <p>Formatos recomendados: JPG, PNG, WebP (Proporción 1920x600)</p>
        </div>
      </div>

      <div v-if="carouselImages.length === 0" class="empty-state">
        <ImageOff :size="48" color="#cbd5e1" />
        <p>No hay imágenes configuradas en el carrusel actualmente.</p>
      </div>

      <div v-else class="images-grid">
        <div v-for="(image, index) in carouselImages" :key="index" class="image-card">
          <img :src="image.url" alt="Banner preview" class="preview-img" />
          <div class="image-overlay">
            <span class="order-badge">{{ index + 1 }}</span>
            <button class="btn-delete" @click="removeImage(index)" title="Eliminar imagen">
              <Trash2 :size="18" />
            </button>
          </div>
        </div>
      </div>

      <div class="editor-footer">
        <button class="btn-save" @click="saveCarousel" :disabled="isSavingCarousel">
          <Save :size="18" />
          <span>{{ isSavingCarousel ? 'Guardando...' : 'Guardar Carrusel' }}</span>
        </button>
      </div>
    </div>

    <!-- ==========================================
         PESTAÑA 2: BARRA DE AVISOS (TICKER)
    =========================================== -->
    <div v-if="activeTab === 'ticker'" class="editor-card tab-pane">
      
      <div class="preview-section">
        <h3 class="section-subtitle">Vista Previa en Vivo</h3>
        <div class="ticker-wrapper">
          <div v-if="messages.length === 0" class="ticker-empty">
            No hay mensajes para mostrar en la barra.
          </div>
          <div v-else class="ticker-track">
            <div class="ticker-content">
              <span v-for="(msg, index) in messages" :key="index" class="ticker-msg">{{ msg }}</span>
            </div>
            <div class="ticker-content" aria-hidden="true">
              <span v-for="(msg, index) in messages" :key="'dup-'+index" class="ticker-msg">{{ msg }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="add-section">
        <h3 class="section-subtitle">Agregar Mensaje</h3>
        <form @submit.prevent="addMessage" class="add-form">
          <div class="input-with-icon">
            <Megaphone :size="18" color="#888" />
            <input 
              v-model="newMessage" 
              type="text" 
              placeholder="Ej: 📢 Oferta especial..." 
              class="message-input"
              maxlength="150"
            />
          </div>
          <button type="submit" class="btn-add" :disabled="!newMessage.trim()">
            <Plus :size="18" />
            <span>Agregar</span>
          </button>
        </form>
      </div>

      <div class="list-section">
        <h3 class="section-subtitle">Mensajes Activos ({{ messages.length }})</h3>
        <div v-if="messages.length === 0" class="empty-state">
          <MessageSquareOff :size="40" color="#cbd5e1" />
          <p>No has agregado ningún mensaje aún.</p>
        </div>
        <ul v-else class="message-list">
          <li v-for="(msg, index) in messages" :key="index" class="message-item">
            <div class="message-text">
              <span class="message-number">{{ index + 1 }}</span>
              <p>{{ msg }}</p>
            </div>
            <button class="btn-delete" @click="removeMessage(index)" title="Eliminar mensaje">
              <Trash2 :size="16" />
            </button>
          </li>
        </ul>
      </div>

      <div class="editor-footer">
        <button class="btn-save" @click="saveTicker" :disabled="isSavingTicker">
          <Save :size="18" />
          <span>{{ isSavingTicker ? 'Guardando...' : 'Guardar Avisos' }}</span>
        </button>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { 
  Upload, Trash2, ImageOff, Save, 
  Megaphone, Plus, MessageSquareOff, 
  Image, MessageCircle 
} from 'lucide-vue-next';

// --- ESTADO GENERAL ---
const activeTab = ref<'carousel' | 'ticker'>('carousel');

// --- ESTADO CARRUSEL ---
interface CarouselImage {
  id?: number | string;
  url: string;
  file?: File;
}
const carouselImages = ref<CarouselImage[]>([]);
const isSavingCarousel = ref(false);

// --- ESTADO TICKER ---
const messages = ref<string[]>([]);
const newMessage = ref('');
const isSavingTicker = ref(false);

onMounted(() => {
  // Cargar datos simulados (AQUÍ REEMPLAZAS POR TUS LLAMADAS A LA API)
  carouselImages.value = [
    { url: 'https://via.placeholder.com/1920x600/fdf2f8/e4869f?text=Banner+1' }
  ];
  messages.value = [
    "📢 Aviso: Horario de atención hasta las 17:00 hrs.",
    "🚛 Envíos gratuitos a toda la Región Metropolitana."
  ];
});

// --- FUNCIONES CARRUSEL ---
const triggerUpload = () => {
  document.getElementById('carousel-file-input')?.click();
};

const handleFiles = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (!target.files) return;
  Array.from(target.files).forEach(file => {
    carouselImages.value.push({ url: URL.createObjectURL(file), file });
  });
  target.value = '';
};

const removeImage = (index: number) => {
  carouselImages.value.splice(index, 1);
};

const saveCarousel = async () => {
  isSavingCarousel.value = true;
  try {
    const formData = new FormData();

    // Recorremos las imágenes actuales y las metemos al FormData
    carouselImages.value.forEach((img, index) => {
      if (img.file) {
        // Es una imagen NUEVA recién subida
        formData.append(`nuevas_imagenes[${index}]`, img.file);
      } else {
        // Es una imagen que YA existía en la BD (solo enviamos la URL o ID)
        formData.append(`imagenes_existentes[${index}]`, img.url);
      }
    });

    // Ajusta la ruta '/api/carousel' a la que corresponda en tu Laravel
    await axios.post('http://localhost:8000/api/carousel', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    alert('Carrusel guardado con éxito');

  } catch (error) {
    console.error('Error al guardar el carrusel:', error);
    alert('Error al guardar el carrusel');
  } finally {
    isSavingCarousel.value = false;
  }
};

// --- FUNCIONES TICKER ---
const addMessage = () => {
  if (newMessage.value.trim()) {
    messages.value.push(newMessage.value.trim());
    newMessage.value = '';
  }
};

const removeMessage = (index: number) => {
  messages.value.splice(index, 1);
};

// IMPORTANTE: Asegúrate de importar axios o tu servicio de API arriba
// import api from '@/services/api' o import axios from 'axios'

const saveTicker = async () => {
  isSavingTicker.value = true;
  try {
    // 1. Aquí haces la petición REAL a tu backend
    // Ajusta la ruta '/api/ticker' a la que hayas creado en las rutas de Laravel
    await axios.post('http://localhost:8000/api/ticker', { 
      messages: messages.value 
    });

    // 2. Si usas tu composable de notificaciones, reemplaza el alert:
    // notify('Avisos guardados en la base de datos', 'success');
    alert('Avisos guardados con éxito');

  } catch (error) {
    console.error('Error al guardar avisos:', error);
    // notify('Error al guardar los avisos', 'error');
    alert('Error al guardar los avisos');
  } finally {
    isSavingTicker.value = false;
  }
};
</script>

<style scoped>
/* --- ESTILOS GENERALES Y PESTAÑAS --- */
.admin-storefront-container {
  padding: 24px;
  max-width: 1000px;
  margin: 0 auto;
}

.header-titles {
  margin-bottom: 24px;
}

.page-title {
  font-size: 1.8rem;
  font-weight: 800;
  color: #1e293b;
}

.page-subtitle {
  color: #64748b;
  font-size: 0.95rem;
  margin-top: 4px;
}

.tab-navigation {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 8px;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 12px;
  border: none;
  background: transparent;
  color: #64748b;
  font-weight: 700;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn.active {
  background: #2b253b;
  color: white;
  box-shadow: 0 4px 12px rgba(43, 37, 59, 0.2);
}

.editor-card {
  background: white;
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
}

/* --- ESTILOS DEL CARRUSEL --- */
.upload-zone {
  border: 2px dashed #cbd5e1;
  border-radius: 16px;
  background: #f8fafc;
  padding: 40px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: 24px;
}

.upload-zone:hover {
  border-color: #e4869f;
  background: #fdf2f8;
}

.hidden-input { display: none; }
.upload-icon { margin-bottom: 12px; }

.upload-content h3 { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
.upload-content p { font-size: 0.85rem; color: #64748b; }

.images-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.image-card {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  aspect-ratio: 16 / 7;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
}

.preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.image-card:hover .preview-img { transform: scale(1.05); }

.image-overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.4);
  opacity: 0;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 12px;
  transition: opacity 0.2s ease;
}

.image-card:hover .image-overlay { opacity: 1; }

.order-badge {
  background: #1e293b; color: white; width: 28px; height: 28px;
  display: flex; justify-content: center; align-items: center;
  border-radius: 50%; font-weight: 800; font-size: 0.85rem;
}

/* --- ESTILOS DEL TICKER --- */
.section-subtitle {
  font-size: 1.1rem; font-weight: 800; color: #334155;
  margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #f1f5f9;
}

.preview-section { margin-bottom: 32px; }

.ticker-wrapper {
  background-color: #e4869f; color: white; height: 36px; width: 100%;
  border-radius: 8px; overflow: hidden; position: relative;
  display: flex; align-items: center; font-size: 0.85rem; font-weight: 600;
}

.ticker-empty { width: 100%; text-align: center; font-style: italic; opacity: 0.8; }
.ticker-track { display: flex; width: max-content; animation: ticker-move 20s linear infinite; }
.ticker-content { display: flex; align-items: center; gap: 40px; padding-right: 40px; white-space: nowrap; flex-shrink: 0; }

@keyframes ticker-move { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }

.add-section { margin-bottom: 32px; }
.add-form { display: flex; gap: 12px; }

.input-with-icon {
  flex: 1; display: flex; align-items: center; gap: 10px;
  background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9999px;
  padding: 10px 18px; transition: border-color 0.2s;
}

.input-with-icon:focus-within { border-color: #e4869f; }
.message-input { border: none; background: transparent; outline: none; width: 100%; font-size: 0.95rem; color: #1e293b; }

.btn-add {
  display: flex; align-items: center; gap: 6px;
  background: #f3f4f6; color: #1e293b; padding: 0 24px;
  border: none; border-radius: 9999px; font-weight: 700; cursor: pointer;
}
.btn-add:hover:not(:disabled) { background: #e2e8f0; }
.btn-add:disabled { opacity: 0.5; cursor: not-allowed; }

.list-section { margin-bottom: 16px; }
.message-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; }

.message-item {
  display: flex; justify-content: space-between; align-items: center;
  background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 12px;
}
.message-text { display: flex; align-items: center; gap: 12px; flex: 1; }
.message-number {
  background: #e2e8f0; color: #475569; width: 24px; height: 24px;
  display: flex; justify-content: center; align-items: center;
  border-radius: 50%; font-size: 0.8rem; font-weight: 800;
}
.message-text p { margin: 0; color: #334155; font-size: 0.95rem; font-weight: 500; }

/* --- ESTILOS COMPARTIDOS (Botón Guardar / Eliminar) --- */
.empty-state {
  text-align: center; padding: 40px 20px; color: #94a3b8; display: flex;
  flex-direction: column; align-items: center; gap: 8px; background: #f8fafc;
  border-radius: 12px; border: 1px dashed #cbd5e1; margin-bottom: 24px;
}

.btn-delete {
  background: #fff1f2; color: #e11d48; border: none; width: 32px; height: 32px;
  border-radius: 50%; display: flex; justify-content: center; align-items: center;
  cursor: pointer; transition: all 0.2s;
}
.btn-delete:hover { background: #e11d48; color: white; }

.editor-footer {
  display: flex; justify-content: flex-end; padding-top: 24px; border-top: 1px solid #f1f5f9;
}

.btn-save {
  display: flex; align-items: center; gap: 8px; background: #2b253b; color: white;
  padding: 12px 28px; border: none; border-radius: 9999px; font-weight: 700; cursor: pointer;
}
.btn-save:hover:not(:disabled) { background: #1e1b2e; }
.btn-save:disabled { background: #cbd5e1; cursor: not-allowed; }
</style>