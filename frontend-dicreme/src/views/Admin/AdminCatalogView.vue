<template>
  <div class="admin-catalog-container">
    <!-- Header banner -->
    <div class="catalog-header">
      <div class="header-titles">
        <h1 class="page-title">Gestión de Catálogo e Historial</h1>
        <p class="page-subtitle">Administración de helados, precios, formatos, categorías y movimientos auditados.</p>
      </div>

      <div class="tab-navigation">
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'productos' }"
          @click="activeTab = 'productos'"
        >
          <Package :size="18" />
          <span>Helados y Precios</span>
        </button>

        <button
          class="tab-btn"
          :class="{ active: activeTab === 'categorias' }"
          @click="activeTab = 'categorias'"
        >
          <Layers :size="18" />
          <span>Categorías</span>
        </button>

        <button
          class="tab-btn"
          :class="{ active: activeTab === 'formatos' }"
          @click="activeTab = 'formatos'"
        >
          <Box :size="18" />
          <span>Formatos</span>
        </button>

        <button
          class="tab-btn"
          :class="{ active: activeTab === 'historial' }"
          @click="activeTab = 'historial'"
        >
          <History :size="18" />
          <span>Historial de Movimientos</span>
        </button>
      </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div class="catalog-content">

      <!-- ==================== PESTAÑA 1: HELADOS Y PRECIOS ==================== -->
      <div v-if="activeTab === 'productos'" class="tab-pane">
        <div class="pane-toolbar">
          <div class="search-filters-bar">
            <div class="input-with-icon">
              <Search :size="18" color="#888" />
              <input
                v-model="searchProduct"
                type="text"
                placeholder="Buscar helado por nombre..."
                class="search-input"
              />
            </div>

            <select v-model="filterCategory" class="filter-select">
              <option value="">Todas las categorías</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.nombre_categoria }}
              </option>
            </select>

            <select v-model="filterFormat" class="filter-select">
              <option value="">Todos los formatos</option>
              <option v-for="fmt in formats" :key="fmt.id" :value="fmt.id">
                {{ fmt.nombre_formato }}
              </option>
            </select>
          </div>

          <button class="btn btn-primary" @click="openProductModal()">
            <Plus :size="18" />
            <span>Nuevo Helado</span>
          </button>
        </div>

        <!-- Products Grid -->
        <div v-if="isLoadingProducts" class="loading-box">
          <Loader2 class="spinner" :size="32" />
          <p>Cargando productos del catálogo...</p>
        </div>

        <div v-else-if="groupedFlavorProducts.length === 0" class="empty-state">
          <PackageX :size="48" color="#94a3b8" />
          <p>No se encontraron productos que coincidan con la búsqueda.</p>
        </div>

        <div v-else class="products-grid">
          <div
            v-for="prod in groupedFlavorProducts"
            :key="prod.nombre_producto"
            class="product-card"
            :class="{ inactive: !prod.estado_producto }"
          >
            <div class="card-image-box">
              <img
                v-if="prod.foto_producto"
                :src="getImageUrl(prod.foto_producto)"
                :alt="prod.nombre_producto"
                class="prod-img"
              />
              <div v-else class="img-placeholder">
                <IceCream :size="40" color="#e4869f" />
              </div>

              <span
                class="status-badge"
                :class="prod.estado_producto ? 'badge-active' : 'badge-inactive'"
              >
                {{ prod.estado_producto ? 'Activo' : 'Inactivo' }}
              </span>
            </div>

            <div class="card-body">
              <h3 class="prod-title">{{ prod.nombre_producto }}</h3>

              <div class="tags-row">
                <span class="tag-badge cat-tag">
                  {{ getCategoryName(prod.id_categoria) }}
                </span>
              </div>

              <div class="formats-list" style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 8px;">
                <span v-for="fmt in prod.formats" :key="fmt.id_formato" class="tag-badge fmt-tag" style="font-size: 0.75rem;">
                  {{ getFormatName(fmt.id_formato) }}: ${{ formatPrice(fmt.precio_producto) }}
                </span>
              </div>
            </div>

            <div class="card-actions">
              <button
                class="btn-icon btn-edit"
                title="Editar producto"
                @click="openProductModal(prod)"
              >
                <Edit :size="16" />
                <span>Editar</span>
              </button>

              <button
                class="btn-icon"
                :class="prod.estado_producto ? 'btn-toggle-off' : 'btn-toggle-on'"
                :title="prod.estado_producto ? 'Desactivar producto' : 'Activar producto'"
                @click="toggleProductStatus(prod)"
              >
                <Power :size="16" />
                <span>{{ prod.estado_producto ? 'Desactivar' : 'Activar' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== PESTAÑA 2: CATEGORÍAS ==================== -->
      <div v-if="activeTab === 'categorias'" class="tab-pane">
        <div class="pane-toolbar">
          <div class="search-filters-bar">
            <div class="input-with-icon">
              <Search :size="18" color="#888" />
              <input
                v-model="searchCategory"
                type="text"
                placeholder="Buscar categoría..."
                class="search-input"
              />
            </div>
          </div>

          <button class="btn btn-primary" @click="openCategoryModal()">
            <Plus :size="18" />
            <span>Nueva Categoría</span>
          </button>
        </div>

        <div class="table-container">
          <table class="custom-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre de Categoría</th>
                <th>Descripción</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoadingCategories">
                <td colspan="4" class="text-center">Cargando categorías...</td>
              </tr>
              <tr v-else-if="filteredCategories.length === 0">
                <td colspan="4" class="text-center">No hay categorías registradas.</td>
              </tr>
              <tr v-for="cat in filteredCategories" :key="cat.id">
                <td>#{{ cat.id }}</td>
                <td class="font-bold">{{ cat.nombre_categoria }}</td>
                <td>{{ cat.descripcion_categoria || 'Sin descripción' }}</td>
                <td>
                  <div class="table-actions">
                    <button class="btn-table btn-edit-sm" @click="openCategoryModal(cat)">
                      <Edit :size="14" /> Editar
                    </button>
                    <button class="btn-table btn-delete-sm" @click="deleteCategory(cat)">
                      <Trash2 :size="14" /> Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ==================== PESTAÑA 3: FORMATOS ==================== -->
      <div v-if="activeTab === 'formatos'" class="tab-pane">
        <div class="pane-toolbar">
          <div class="search-filters-bar">
            <div class="input-with-icon">
              <Search :size="18" color="#888" />
              <input
                v-model="searchFormat"
                type="text"
                placeholder="Buscar formato..."
                class="search-input"
              />
            </div>
          </div>

          <button class="btn btn-primary" @click="openFormatModal()">
            <Plus :size="18" />
            <span>Nuevo Formato</span>
          </button>
        </div>

        <div class="table-container">
          <table class="custom-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre del Formato</th>
                <th>Precio Base (CLP)</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoadingFormats">
                <td colspan="4" class="text-center">Cargando formatos...</td>
              </tr>
              <tr v-else-if="filteredFormats.length === 0">
                <td colspan="4" class="text-center">No hay formatos registrados.</td>
              </tr>
              <tr v-for="fmt in filteredFormats" :key="fmt.id">
                <td>#{{ fmt.id }}</td>
                <td class="font-bold">{{ fmt.nombre_formato }}</td>
                <td class="font-bold text-success">${{ formatPrice(fmt.precio_formato) }} CLP</td>
                <td>
                  <div class="table-actions">
                    <button class="btn-table btn-edit-sm" @click="openFormatModal(fmt)">
                      <Edit :size="14" /> Editar
                    </button>
                    <button class="btn-table btn-delete-sm" @click="deleteFormat(fmt)">
                      <Trash2 :size="14" /> Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ==================== PESTAÑA 4: HISTORIAL DE MOVIMIENTOS ==================== -->
      <div v-if="activeTab === 'historial'" class="tab-pane">
        <div class="pane-toolbar history-toolbar">
          <div class="entity-tabs">
            <button
              class="entity-btn"
              :class="{ active: historyEntityFilter === '' }"
              @click="historyEntityFilter = ''"
            >
              <ListFilter :size="16" />
              <span>Todos los movimientos</span>
            </button>
            <button
              class="entity-btn"
              :class="{ active: historyEntityFilter === 'lote' }"
              @click="historyEntityFilter = 'lote'"
            >
              <Box :size="16" />
              <span>Lotes</span>
            </button>
            <button
              class="entity-btn"
              :class="{ active: historyEntityFilter === 'usuario' }"
              @click="historyEntityFilter = 'usuario'"
            >
              <Users :size="16" />
              <span>Usuarios</span>
            </button>
            <button
              class="entity-btn"
              :class="{ active: historyEntityFilter === 'producto' }"
              @click="historyEntityFilter = 'producto'"
            >
              <IceCream :size="16" />
              <span>Productos</span>
            </button>
          </div>

          <div class="input-with-icon search-history">
            <Search :size="18" color="#888" />
            <input
              v-model="searchHistory"
              type="text"
              placeholder="Buscar en historial por descripción o usuario..."
              class="search-input"
            />
          </div>
        </div>

        <div class="table-container">
          <table class="custom-table history-table">
            <thead>
              <tr>
                <th>Fecha y Hora</th>
                <th>Entidad</th>
                <th>Acción</th>
                <th>Descripción del Movimiento</th>
                <th>Usuario Responsable</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoadingHistory">
                <td colspan="5" class="text-center">Cargando historial de movimientos...</td>
              </tr>
              <tr v-else-if="filteredHistory.length === 0">
                <td colspan="5" class="text-center">No se registraron movimientos en el historial.</td>
              </tr>
              <tr v-for="item in filteredHistory" :key="item.id">
                <td class="time-col">{{ formatDate(item.created_at) }}</td>
                <td>
                  <span class="entity-badge" :class="`entity-${item.tipo_entidad}`">
                    {{ item.tipo_entidad.toUpperCase() }}
                  </span>
                </td>
                <td>
                  <span class="action-tag" :class="`action-${item.accion}`">
                    {{ formatActionLabel(item.accion) }}
                  </span>
                </td>
                <td class="desc-col">{{ item.descripcion }}</td>
                <td class="user-col">
                  <strong>{{ item.usuario_responsable || 'Sistema' }}</strong>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- ==================== MODAL: CREAR / EDITAR HELADO ==================== -->
    <div v-if="showProductModal" class="modal-backdrop" @click.self="showProductModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h2>{{ editingProduct ? 'Editar Helado' : 'Nuevo Helado' }}</h2>
          <button class="close-btn" @click="showProductModal = false">
            <X :size="20" />
          </button>
        </div>

        <form @submit.prevent="saveProduct" class="modal-body">
          <!-- Image Picker -->
          <div class="photo-upload-container">
            <div class="image-preview" @click="triggerProductPhotoSelect">
              <img v-if="productPhotoPreview" :src="productPhotoPreview" alt="Preview" />
              <div v-else class="upload-placeholder">
                <Camera :size="32" color="#e4869f" />
                <span>Adjuntar imagen</span>
              </div>
            </div>
            <input
              id="product-photo-file-input"
              type="file"
              accept="image/*"
              class="hidden-input"
              @change="handleProductPhotoChange"
            />
            <span class="photo-hint">Formatos soportados: WebP, PNG, JPG (Se comprime automáticamente)</span>
          </div>

          <div class="form-group">
            <label>Nombre del Helado *</label>
            <input
              v-model="productForm.nombre_producto"
              type="text"
              required
              placeholder="Ej: Helado de Mango Maracuyá"
              class="form-control"
            />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Categoría *</label>
              <select v-model="productForm.id_categoria" required class="form-control">
                <option value="" disabled>Seleccione categoría</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.nombre_categoria }}
                </option>
              </select>
            </div>

            <div class="form-group flex-center">
              <label class="switch-label">Estado del Producto</label>
              <div class="checkbox-toggle">
                <input
                  id="prod-status-toggle"
                  v-model="productForm.estado_producto"
                  type="checkbox"
                />
                <label for="prod-status-toggle" class="toggle-slider"></label>
                <span class="toggle-text">{{ productForm.estado_producto ? 'Activo' : 'Inactivo' }}</span>
              </div>
            </div>
          </div>

          <div class="auto-formats-info" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 8px; margin-top: 12px; font-size: 0.85rem; color: #475569;">
            <p style="margin: 0; font-weight: 600;">✨ Formatos vinculados automáticamente:</p>
            <p style="margin: 4px 0 0 0; color: #64748b;">Al guardar, este helado se creará automáticamente en todos los formatos (10L, 5L, 2.5L y 1L) aplicando sus precios base correspondientes.</p>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showProductModal = false">
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="isSaving">
              {{ isSaving ? 'Guardando...' : (editingProduct ? 'Actualizar Helado' : 'Crear Helado') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ==================== MODAL: CREAR / EDITAR CATEGORÍA ==================== -->
    <div v-if="showCategoryModal" class="modal-backdrop" @click.self="showCategoryModal = false">
      <div class="modal-card modal-sm">
        <div class="modal-header">
          <h2>{{ editingCategory ? 'Editar Categoría' : 'Nueva Categoría' }}</h2>
          <button class="close-btn" @click="showCategoryModal = false">
            <X :size="20" />
          </button>
        </div>

        <form @submit.prevent="saveCategory" class="modal-body">
          <div class="form-group">
            <label>Nombre de Categoría *</label>
            <input
              v-model="categoryForm.nombre_categoria"
              type="text"
              required
              placeholder="Ej: Sabores Frutales"
              class="form-control"
            />
          </div>

          <div class="form-group">
            <label>Descripción</label>
            <textarea
              v-model="categoryForm.descripcion_categoria"
              placeholder="Descripción opcional de la categoría..."
              rows="3"
              class="form-control"
            ></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showCategoryModal = false">
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="isSaving">
              {{ isSaving ? 'Guardando...' : 'Guardar Categoría' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ==================== MODAL: CREAR / EDITAR FORMATO ==================== -->
    <div v-if="showFormatModal" class="modal-backdrop" @click.self="showFormatModal = false">
      <div class="modal-card modal-sm">
        <div class="modal-header">
          <h2>{{ editingFormat ? 'Editar Formato' : 'Nuevo Formato' }}</h2>
          <button class="close-btn" @click="showFormatModal = false">
            <X :size="20" />
          </button>
        </div>

        <form @submit.prevent="saveFormat" class="modal-body">
          <div class="form-group">
            <label>Nombre del Formato *</label>
            <input
              v-model="formatForm.nombre_formato"
              type="text"
              required
              placeholder="Ej: Balde 5 Litros"
              class="form-control"
            />
          </div>

          <div class="form-group">
            <label>Precio Base del Formato (CLP) *</label>
            <input
              v-model.number="formatForm.precio_formato"
              type="number"
              min="0"
              required
              placeholder="Ej: 16900"
              class="form-control"
            />
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showFormatModal = false">
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="isSaving">
              {{ isSaving ? 'Guardando...' : 'Guardar Formato' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import {
  Package,
  Layers,
  Box,
  History,
  Search,
  Plus,
  Edit,
  Trash2,
  Power,
  Camera,
  X,
  IceCream,
  PackageX,
  Loader2, 
  Users,
  ListFilter
} from 'lucide-vue-next';

import productService from '@/services/productService';
import historyService, { type MovementHistoryItem } from '@/services/historyService';
import { useNotification } from '@/composables/useNotification';

const { notify } = useNotification();

const activeTab = ref<'productos' | 'categorias' | 'formatos' | 'historial'>('productos');

// State
const products = ref<any[]>([]);
const categories = ref<any[]>([]);
const formats = ref<any[]>([]);
const historyLogs = ref<MovementHistoryItem[]>([]);

const isLoadingProducts = ref(false);
const isLoadingCategories = ref(false);
const isLoadingFormats = ref(false);
const isLoadingHistory = ref(false);
const isSaving = ref(false);

// Filters
const searchProduct = ref('');
const filterCategory = ref<number | ''>('');
const filterFormat = ref<number | ''>('');

const searchCategory = ref('');
const searchFormat = ref('');

const searchHistory = ref('');
const historyEntityFilter = ref<string>('');

// Modals
const showProductModal = ref(false);
const editingProduct = ref<any>(null);
const productPhotoPreview = ref<string | null>(null);
const productPhotoFile = ref<File | null>(null);

const productForm = ref({
  nombre_producto: '',
  id_categoria: '' as number | '',
  estado_producto: true,
});

const showCategoryModal = ref(false);
const editingCategory = ref<any>(null);
const categoryForm = ref({
  nombre_categoria: '',
  descripcion_categoria: '',
});

const showFormatModal = ref(false);
const editingFormat = ref<any>(null);
const formatForm = ref({
  nombre_formato: '',
  precio_formato: 0,
});

// Load Data
const loadProducts = async () => {
  isLoadingProducts.value = true;
  try {
    const res = await productService.getProducts();
    products.value = res.data?.data || res.data || [];
  } catch (e) {
    console.error('Error al cargar productos:', e);
  } finally {
    isLoadingProducts.value = false;
  }
};

const loadCategories = async () => {
  isLoadingCategories.value = true;
  try {
    const res = await productService.getCategories();
    categories.value = res.data?.data || res.data || [];
  } catch (e) {
    console.error('Error al cargar categorías:', e);
  } finally {
    isLoadingCategories.value = false;
  }
};

const loadFormats = async () => {
  isLoadingFormats.value = true;
  try {
    const res = await productService.getFormats();
    formats.value = res.data?.data || res.data || [];
  } catch (e) {
    console.error('Error al cargar formatos:', e);
  } finally {
    isLoadingFormats.value = false;
  }
};

const loadHistory = async () => {
  isLoadingHistory.value = true;
  try {
    const res = await historyService.getHistory();
    historyLogs.value = res.data?.data || res.data || [];
  } catch (e) {
    console.error('Error al cargar historial:', e);
  } finally {
    isLoadingHistory.value = false;
  }
};

onMounted(() => {
  loadProducts();
  loadCategories();
  loadFormats();
  loadHistory();
});

// Computed Filters
const filteredProducts = computed(() => {
  return products.value.filter((p) => {
    const matchSearch = !searchProduct.value.trim() || p.nombre_producto.toLowerCase().includes(searchProduct.value.toLowerCase());
    const matchCat = filterCategory.value === '' || p.id_categoria == filterCategory.value;
    const matchFmt = filterFormat.value === '' || p.id_formato == filterFormat.value;
    return matchSearch && matchCat && matchFmt;
  });
});

const groupedFlavorProducts = computed(() => {
  const map = new Map<string, any>();

  filteredProducts.value.forEach((p) => {
    const flavorName = p.nombre_producto;
    if (!map.has(flavorName)) {
      map.set(flavorName, {
        id: p.id,
        nombre_producto: p.nombre_producto,
        id_categoria: p.id_categoria,
        foto_producto: p.foto_producto,
        estado_producto: Boolean(p.estado_producto),
        formats: [] as any[]
      });
    }
    const item = map.get(flavorName);
    if (!item.formats.some((f: any) => f.id_formato === p.id_formato)) {
      item.formats.push({
        id_producto: p.id,
        id_formato: p.id_formato,
        precio_producto: p.precio_formato || p.precio_producto
      });
    }
    if (p.estado_producto) {
      item.estado_producto = true;
    }
  });

  return Array.from(map.values());
});

const filteredCategories = computed(() => {
  return categories.value.filter((c) => {
    return !searchCategory.value.trim() || c.nombre_categoria.toLowerCase().includes(searchCategory.value.toLowerCase());
  });
});

const filteredFormats = computed(() => {
  return formats.value.filter((f) => {
    return !searchFormat.value.trim() || f.nombre_formato.toLowerCase().includes(searchFormat.value.toLowerCase());
  });
});

const filteredHistory = computed(() => {
  return historyLogs.value.filter((item) => {
    const matchEntity = !historyEntityFilter.value || item.tipo_entidad === historyEntityFilter.value;
    const q = searchHistory.value.toLowerCase();
    const matchSearch = !q || item.descripcion.toLowerCase().includes(q) || (item.usuario_responsable && item.usuario_responsable.toLowerCase().includes(q));
    return matchEntity && matchSearch;
  });
});

// Helpers
const getCategoryName = (id: number) => {
  const c = categories.value.find(cat => cat.id == id);
  return c ? c.nombre_categoria : `Cat #${id}`;
};

const getFormatName = (id: number) => {
  const f = formats.value.find(fmt => fmt.id == id);
  return f ? f.nombre_formato : `Fmt #${id}`;
};

const formatPrice = (price: number) => {
  return Number(price || 0).toLocaleString('es-CL');
};

const getImageUrl = (url: string) => {
  if (!url) return '';
  if (url.startsWith('http') || url.startsWith('data:')) return url;
  return `http://localhost:8000${url}`;
};

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('es-CL', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (e) {
    return dateStr;
  }
};

const formatActionLabel = (action: string) => {
  switch (action) {
    case 'creacion': return 'CREACIÓN';
    case 'modificacion': return 'MODIFICACIÓN';
    case 'cambio_precio': return 'CAMBIO PRECIO';
    case 'activacion': return 'ACTIVACIÓN';
    case 'desactivacion': return 'DESACTIVACIÓN';
    case 'liberacion': return 'LIBERACIÓN';
    case 'eliminacion': return 'ELIMINACIÓN';
    default: return action.toUpperCase();
  }
};

// Modal Actions - Products
const openProductModal = (prod?: any) => {
  editingProduct.value = prod || null;
  productPhotoFile.value = null;
  if (prod) {
    productForm.value = {
      nombre_producto: prod.nombre_producto,
      id_categoria: prod.id_categoria,
      estado_producto: Boolean(prod.estado_producto),
    };
    productPhotoPreview.value = prod.foto_producto ? getImageUrl(prod.foto_producto) : null;
  } else {
    productForm.value = {
      nombre_producto: '',
      id_categoria: categories.value[0]?.id || '',
      estado_producto: true,
    };
    productPhotoPreview.value = null;
  }
  showProductModal.value = true;
};

const triggerProductPhotoSelect = () => {
  document.getElementById('product-photo-file-input')?.click();
};

const handleProductPhotoChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    productPhotoFile.value = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
      productPhotoPreview.value = ev.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

const saveProduct = async () => {
  isSaving.value = true;
  try {
    const formData = new FormData();
    formData.append('nombre_producto', productForm.value.nombre_producto);
    formData.append('id_categoria', String(productForm.value.id_categoria));
    formData.append('estado_producto', productForm.value.estado_producto ? '1' : '0');

    if (productPhotoFile.value) {
      formData.append('foto_producto', productPhotoFile.value);
    } else if (productPhotoPreview.value && productPhotoPreview.value.startsWith('data:image')) {
      formData.append('foto_producto', productPhotoPreview.value);
    } else if (editingProduct.value && editingProduct.value.foto_producto) {
      formData.append('foto_producto', editingProduct.value.foto_producto);
    }

    if (editingProduct.value) {
      await productService.updateProduct(editingProduct.value.id, formData);
      notify('Producto actualizado correctamente', 'success');
    } else {
      await productService.createProduct(formData);
      notify('Producto creado correctamente', 'success');
    }

    showProductModal.value = false;
    await loadProducts();
    await loadHistory();
  } catch (err: any) {
    console.error('Error guardando producto:', err);
    notify(err.response?.data?.message || 'Error al guardar el producto.', 'error');
  } finally {
    isSaving.value = false;
  }
};

const toggleProductStatus = async (prod: any) => {
  try {
    const res = await productService.toggleProductState(prod.nombre_producto || prod.id);
    const nuevoEstado = !prod.estado_producto;
    prod.estado_producto = nuevoEstado;

    // Actualizar de forma reactiva instantánea todas las variaciones de este sabor en la lista local
    products.value.forEach((p) => {
      if (p.nombre_producto === prod.nombre_producto) {
        p.estado_producto = nuevoEstado;
      }
    });

    if (nuevoEstado) {
      notify(`Se activó el producto "${prod.nombre_producto}".`, 'success');
    } else {
      notify(`Se desactivó el producto "${prod.nombre_producto}".`, 'warning');
    }

    await loadHistory();
  } catch (err: any) {
    console.error('Error al cambiar estado:', err);
    notify('Error al cambiar el estado del producto.', 'error');
  }
};

// Modal Actions - Categories
const openCategoryModal = (cat?: any) => {
  editingCategory.value = cat || null;
  if (cat) {
    categoryForm.value = {
      nombre_categoria: cat.nombre_categoria,
      descripcion_categoria: cat.descripcion_categoria || ''
    };
  } else {
    categoryForm.value = { nombre_categoria: '', descripcion_categoria: '' };
  }
  showCategoryModal.value = true;
};

const saveCategory = async () => {
  isSaving.value = true;
  try {
    if (editingCategory.value) {
      await productService.updateCategory(editingCategory.value.id, categoryForm.value);
      notify('Categoría actualizada', 'success');
    } else {
      await productService.createCategory(categoryForm.value);
      notify('Categoría creada', 'success');
    }
    showCategoryModal.value = false;
    await loadCategories();
    await loadHistory();
  } catch (err: any) {
    notify('Error al guardar la categoría', 'error');
  } finally {
    isSaving.value = false;
  }
};

const deleteCategory = async (cat: any) => {
  if (!confirm(`¿Eliminar la categoría "${cat.nombre_categoria}"?`)) return;
  try {
    await productService.deleteCategory(cat.id);
    notify('Categoría eliminada', 'success');
    await loadCategories();
    await loadHistory();
  } catch (err: any) {
    notify('Error al eliminar categoría', 'error');
  }
};

// Modal Actions - Formats
const openFormatModal = (fmt?: any) => {
  editingFormat.value = fmt || null;
  if (fmt) {
    formatForm.value = {
      nombre_formato: fmt.nombre_formato,
      precio_formato: Number(fmt.precio_formato) || 0
    };
  } else {
    formatForm.value = {
      nombre_formato: '',
      precio_formato: 0
    };
  }
  showFormatModal.value = true;
};

const saveFormat = async () => {
  isSaving.value = true;
  try {
    if (editingFormat.value) {
      await productService.updateFormat(editingFormat.value.id, formatForm.value);
      notify('Formato actualizado correctamente', 'success');
    } else {
      await productService.createFormat(formatForm.value);
      notify('Formato creado correctamente', 'success');
    }
    showFormatModal.value = false;
    await loadFormats();
    await loadProducts();
    await loadHistory();
  } catch (err: any) {
    notify('Error al guardar el formato', 'error');
  } finally {
    isSaving.value = false;
  }
};

const deleteFormat = async (fmt: any) => {
  if (!confirm(`¿Eliminar el formato "${fmt.nombre_formato}"?`)) return;
  try {
    await productService.deleteFormat(fmt.id);
    notify('Formato eliminado', 'success');
    await loadFormats();
    await loadHistory();
  } catch (err: any) {
    notify('Error al eliminar formato', 'error');
  }
};
</script>

<style scoped>
.admin-catalog-container {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.catalog-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 1.8rem;
  font-weight: 800;
  color: #1e1b4b;
}

.page-subtitle {
  color: #64748b;
  font-size: 0.95rem;
  margin-top: 4px;
}

.tab-navigation {
  display: flex;
  gap: 12px;
  margin-top: 20px;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 8px;
  overflow-x: auto;
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
  background: #e4869f;
  color: white;
  box-shadow: 0 4px 12px rgba(228, 134, 159, 0.3);
}

.tab-pane {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
}

.pane-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.search-filters-bar {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  flex: 1;
}

.input-with-icon {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8fafc;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  padding: 8px 14px;
  min-width: 260px;
}

.search-input {
  border: none;
  background: transparent;
  outline: none;
  width: 100%;
  font-size: 0.9rem;
}

.filter-select {
  padding: 8px 14px;
  border-radius: 12px;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  font-size: 0.9rem;
  outline: none;
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.product-card {
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  background: white;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

.product-card.inactive {
  opacity: 0.65;
  filter: grayscale(40%);
}

.card-image-box {
  height: 160px;
  background: #f8fafc;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.prod-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.status-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 800;
}

.badge-active { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.badge-inactive { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }

.card-body {
  padding: 16px;
  flex: 1;
}

.prod-title {
  font-size: 1.1rem;
  font-weight: 800;
  color: #1e293b;
  margin-bottom: 8px;
}

.tags-row {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

.tag-badge {
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
}

.cat-tag { background: #f7e9f9; color: var(--DC-pink); }
.fmt-tag { background: #f3e8ff; color: #7c3aedba; }

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 8px;
  border-top: 1px dashed #e2e8f0;
}

.price-label { font-size: 0.8rem; color: #64748b; font-weight: 600; }
.price-amount { font-size: 1.2rem; font-weight: 900; color: #e4869f; }

.card-actions {
  display: flex;
  gap: 8px;
  padding: 12px 16px;
  background: #f8fafc;
  border-top: 1px solid #f1f5f9;
}

.btn-icon {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background: white;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
}

.btn-edit { color: var(--DC-gray); border-color: var(--DC-gray); }
.btn-toggle-off { color: #ef4444; border-color: #fca5a5; }
.btn-toggle-on { color: #10b981; border-color: #6ee7b7; }

/* Custom Tables */
.table-container {
  overflow-x: auto;
}

.custom-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 8px;
}

.custom-table th {
  background: #f8fafc;
  color: #475569;
  font-size: 0.8rem;
  font-weight: 800;
  text-transform: uppercase;
  padding: 12px 16px;
  text-align: left;
  border-bottom: 2px solid #e2e8f0;
}

.custom-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.9rem;
  color: #334155;
}

.table-actions {
  display: flex;
  gap: 8px;
}

.btn-table {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  border: none;
}

.btn-edit-sm { background: var(--DC-bg-gray); color: var(--DC-gray); }
.btn-delete-sm { background: rgb(253, 240, 251); color: var(--DC-pink); }

/* History View */
.history-toolbar {
  flex-direction: column;
  align-items: stretch;
}

.entity-tabs {
  display: flex;
  gap: 8px;
  overflow-x: auto;
}

.entity-btn {
  display: flex;
  align-items: center;
  gap: 8px; 
  padding: 8px 18px;
  border-radius: 20px; 
  border: 1px solid transparent; 
  background: #f1f5f9; 
  font-size: 0.85rem;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
}

.entity-btn:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.entity-btn.active {
  background: #2b253b; 
  color: white;
  box-shadow: 0 4px 12px rgba(43, 37, 59, 0.2); 
}

.search-history {
  width: 100%;
}

/* --- Mejoras visuales para la tabla de Historial --- */
.history-table {
  border-collapse: separate; 
  border-spacing: 0;
  width: 100%;
}

.history-table th {
  background: #f8fafc;
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  padding: 12px 16px;
  text-align: left;
  border-bottom: 2px solid #e2e8f0;
}

.history-table .time-col { 
  font-weight: 400; /* Texto normal, sin negrita */
  color: #64748b; 
  font-size: 0.85rem; 
}

.history-table .desc-col { 
  font-weight: 400; /* Texto normal para que respire */
  color: #475569; 
}

.history-table .user-col strong {
  font-weight: 500; 
  color: #475569;
}


.entity-badge {
  padding: 4px 12px;
  border-radius: 9999px; 
  font-size: 0.75rem;
  font-weight: 600; 
  display: inline-block;
  text-align: center;
}


.entity-lote { background: #fef3c7; color: #b45309; } /* Naranja suave */
.entity-usuario { background: #e0e7ff; color: #4f46e5; } /* Azul índigo suave */
.entity-producto { background: #fce7f3; color: #be185d; } /* Rosado Di Creme suave */

.action-tag {
  padding: 4px 12px;
  border-radius: 9999px; /* Forma de píldora perfecta */
  font-size: 0.75rem;
  font-weight: 600; /* Letra más delgada */
  display: inline-block;
  text-align: center;
}

/* Colores pasteles súper suaves para las acciones */
.action-creacion, .action-creacion_categoria, .action-creacion_formato { 
  background: #f0fdf4; color: #16a34a; /* Verde menta claro */
}
.action-modificacion, .action-modificacion_categoria, .action-modificacion_formato { 
  background: #eff6ff; color: #2563eb; /* Azul cielo */
}
.action-cambio_precio { 
  background: #fffbeb; color: #d97706; /* Amarillo pastel */
}
.action-activacion { 
  background: #f0fdf4; color: #16a34a; /* Verde menta claro */
}
.action-desactivacion { 
  background: #fef2f2; color: #dc2626; /* Rojo/Rosado pálido */
}
.action-liberacion { 
  background: #fce7f3; color: #be185d; /* Rosado Di Creme suave */
}

/* Modals */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  padding: 20px;
}

.modal-card {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 540px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

.modal-sm { max-width: 440px; }

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 { font-size: 1.2rem; font-weight: 800; color: #1e293b; }

.modal-body { padding: 24px; }

.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 6px; }

.form-control {
  width: 100%;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  font-size: 0.9rem;
  outline: none;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.photo-upload-container {
  text-align: center;
  margin-bottom: 20px;
}

.image-preview {
  width: 120px;
  height: 120px;
  border-radius: 16px;
  border: 2px dashed #cbd5e1;
  margin: 0 auto 8px auto;
  overflow: hidden;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
}

.image-preview img { width: 100%; height: 100%; object-fit: cover; }
.upload-placeholder { display: flex; flex-direction: column; align-items: center; gap: 4px; font-size: 0.75rem; color: #e4869f; font-weight: 700; }

.hidden-input { display: none; }
.photo-hint { font-size: 0.75rem; color: #94a3b8; }

.checkbox-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 6px;
}

.toggle-slider {
  width: 44px;
  height: 24px;
  background: #cbd5e1;
  border-radius: 20px;
  position: relative;
  cursor: pointer;
  transition: 0.3s;
}

#prod-status-toggle { display: none; }
#prod-status-toggle:checked + .toggle-slider { background: #10b981; }
#prod-status-toggle:checked + .toggle-slider::after { transform: translateX(20px); }

.toggle-slider::after {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  transition: 0.3s;
}

.toggle-text { font-size: 0.9rem; font-weight: 700; color: #334155; }

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
  padding: 10px 18px;
  border-radius: 10px;
  border: none;
  font-weight: 700;
  cursor: pointer;
}

.loading-box, .empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

.spinner {
  animation: spin 1s linear infinite;
  margin-bottom: 12px;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
