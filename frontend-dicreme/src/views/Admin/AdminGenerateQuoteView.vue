<template>
  <div class="admin-quote-wizard">
    <div class="wizard-header">
      <h1>Generar Nueva Cotización</h1>
      <div class="steps-indicator">
        <!-- Se añadió @click para permitir volver atrás haciendo clic en el paso -->
        <div :class="['step', { active: currentStep >= 1 }]" @click="goToStep(1)" style="cursor: pointer;">
          1. Cliente
        </div>
        <div class="line"></div>
        <div :class="['step', { active: currentStep >= 2 }]" @click="goToStep(2)" :style="{ cursor: currentStep >= 2 ? 'pointer' : 'default' }">
          2. Productos
        </div>
        <div class="line"></div>
        <div :class="['step', { active: currentStep >= 3 }]">
          3. Resumen
        </div>
      </div>
    </div>

    <!-- Paso 1: Datos del Distribuidor -->
    <div v-if="currentStep === 1" class="step-container client-step">
      <div class="selection-mode">
        <h3>Seleccione o ingrese los datos del distribuidor</h3>
        
        <!-- Se añadió ref="distributorContainer" para detectar clics fuera -->
        <div class="search-box" ref="distributorContainer">
          <Search class="search-icon" :size="20" />
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Buscar distribuidor por nombre o RUT..." 
            class="dc-input pr-10"
            @focus="showDropdown = true"
            @input="handleSearchInput"
            :disabled="isDistributorSelected"
          />
          <!-- Botón para limpiar si ya seleccionó uno -->
          <button v-if="isDistributorSelected" class="btn-clear-search" @click="clearDistributorSelection" title="Limpiar selección">
            <Trash2 :size="18" />
          </button>
          
          <div v-if="showDropdown && filteredDistributors.length > 0" class="search-dropdown">
            <div 
              v-for="d in filteredDistributors" 
              :key="d.id" 
              class="dropdown-item"
              @click="selectDistributor(d)"
            >
              <strong>{{ d.nombre_empresa }}</strong> ({{ d.rut_empresa }})
            </div>
          </div>
        </div>

        <div class="divider">
          <span>O ingresar datos manualmente</span>
        </div>

        <!-- Se añadió :disabled a todos los inputs si isDistributorSelected es true -->
        <form class="distributor-form">
          <div class="input-row">
            <div class="input-group">
              <label>RUT Empresa</label>
              <input 
                v-model="distributorForm.rut_empresa" 
                @input="distributorForm.rut_empresa = formatRutInput(distributorForm.rut_empresa)"
                type="text" 
                placeholder="12345678-9" 
                class="dc-input" 
                :disabled="isDistributorSelected" 
              />
            </div>
            <div class="input-group">
              <label>Nombre Empresa</label>
              <input v-model="distributorForm.nombre_empresa" type="text" class="dc-input" :disabled="isDistributorSelected" />
            </div>
          </div>

          <div class="input-row">
            <div class="input-group">
              <label>Correo Electrónico</label>
              <input v-model="distributorForm.correo_electronico" type="email" placeholder="cliente@correo.com" class="dc-input" :disabled="isDistributorSelected" />
            </div>
            <div class="input-group">
              <label>Teléfono</label>
              <input 
                v-model="distributorForm.telefono" 
                type="text" 
                placeholder="+569..." 
                class="dc-input" 
                @input="handlePhoneInput"
                :disabled="isDistributorSelected"
              />
            </div>
          </div>

          <div class="input-row">
            <div class="input-group">
              <label>Dirección</label>
              <input v-model="distributorForm.direccion" type="text" class="dc-input" :disabled="isDistributorSelected" />
            </div>
            <div class="input-group">
              <label>Comuna</label>
              <!-- Ref para detectar clics fuera del dropdown de comunas -->
              <div class="search-comuna" ref="comunaContainer">
                <input 
                  v-model="comunaSearch" 
                  type="text" 
                  placeholder="Escriba la comuna..." 
                  class="dc-input"
                  @focus="showComunaDropdown = true"
                  :disabled="isDistributorSelected"
                />
                <div v-if="showComunaDropdown && filteredComunas.length > 0" class="comuna-dropdown">
                  <div 
                    v-for="comuna in filteredComunas" 
                    :key="comuna" 
                    class="dropdown-item"
                    @click="selectComuna(comuna)"
                  >
                    {{ comuna }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="input-group">
            <label>Persona que recibe (Opcional)</label>
            <input v-model="distributorForm.persona_recibe" type="text" placeholder="Nombre Apellido" class="dc-input" />
          </div>
        </form>
      </div>

      <div class="actions">
        <button class="btn btn-secondary" @click="router.push('/admin')">Cancelar</button>
        <button class="btn btn-primary" @click="nextStep">
          Continuar a Productos <ArrowRight :size="18" />
        </button>
      </div>
    </div>

    <!-- Paso 2: Selección de Productos -->
    <div v-if="currentStep === 2" class="step-container product-step">
      <div class="product-layout">
        <div class="catalog-section">
          <div class="catalog-header">
            <h3>Selección de Productos</h3>
            <div class="filters">
              <select v-model="selectedCategory" class="dc-select">
                <option value="Todas">Todas las categorías</option>
                <option v-for="cat in categoriesList" :key="cat.id" :value="cat.nombre_categoria">
                  {{ cat.nombre_categoria }}
                </option>
              </select>
              <div class="product-search">
                <Search :size="18" />
                <input v-model="productSearch" type="text" placeholder="Buscar sabor..." />
              </div>
            </div>
          </div>

          <div class="products-grid-admin">
            <div v-for="p in filteredIceCreams" :key="p.name" class="product-card-admin">
              <img :src="p.image" :alt="p.name" />
              <div class="p-info">
                <h4>{{ p.name }}</h4>
                <span class="p-cat">{{ p.category }}</span>
                <div class="formats-list">
                  <div v-for="f in p.formats" :key="f.id" class="format-row">
                    <span>{{ f.size }} - {{ f.formattedPrice }}</span>
                    <button class="btn-add-small" @click="addToCart(p, f)" title="Agregar al carrito">
                      <Plus :size="14" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <aside class="cart-summary-admin">
          <div class="cart-header">
            <ShoppingCart :size="20" />
            <span>Resumen Parcial</span>
          </div>
          <div class="cart-items-list">
            <div v-if="cartItems.length === 0" class="empty-cart">
              No hay productos
            </div>
            <div v-for="(item, idx) in cartItems" :key="item.id" class="cart-item-admin">
              <img :src="item.image" :alt="item.name" class="cart-item-thumb" />
              <div class="item-main">
                <span class="item-name">{{ item.name }} ({{ item.size }})</span>
                <span class="item-price">{{ item.formattedPrice }}</span>
              </div>
              <div class="item-controls">
                <div class="qty-btn" @click="updateQuantity(idx, -1)"><Minus :size="12" /></div>
                <span>{{ item.quantity }}</span>
                <div class="qty-btn" @click="updateQuantity(idx, 1)"><Plus :size="12" /></div>
                <button class="btn-delete" @click="removeFromCart(idx)"><Trash2 :size="14" /></button>
              </div>
            </div>
          </div>
          <div class="cart-total">
            <span>Total:</span>
            <strong>{{ totalQuote }}</strong>
          </div>
        </aside>
      </div>

      <div class="actions">
        <button class="btn btn-secondary" @click="currentStep = 1">
          <ArrowLeft :size="18" /> Datos Cliente
        </button>
        <button class="btn btn-primary" @click="nextStep">
          Revisar Resumen <ArrowRight :size="18" />
        </button>
      </div>
    </div>

    <!-- Paso 3: Revisión y Envío -->
    <div v-if="currentStep === 3" class="step-container summary-step">
      <h3>Resumen Final de la Cotización</h3>
      
      <div class="summary-grid">
        <div class="summary-section">
          <h4>Datos del Distribuidor</h4>
          <div class="summary-card">
            <p><strong>Empresa:</strong> {{ distributorForm.nombre_empresa }}</p>
            <p><strong>RUT:</strong> {{ distributorForm.rut_empresa }}</p>
            <p><strong>Correo:</strong> {{ distributorForm.correo_electronico }}</p>
            <p><strong>Teléfono:</strong> {{ distributorForm.telefono }}</p>
            <p><strong>Dirección:</strong> {{ distributorForm.direccion }}, {{ distributorForm.comuna }}</p>
            <p><strong>Persona Recibe:</strong> {{ distributorForm.persona_recibe || 'N/A' }}</p>
          </div>
        </div>

        <div class="summary-section">
          <h4>Productos Seleccionados</h4>
          <div class="summary-card products-list-final">
            <div v-for="item in cartItems" :key="item.id" class="final-item">
              <div class="final-item-info">
                <img :src="item.image" class="final-thumb" />
                <span>{{ item.quantity }}x {{ item.name }} ({{ item.size }})</span>
              </div>
              <span>{{ item.formattedPrice }} c/u</span>
            </div>
            <div class="final-total">
              <span>Total Estimado</span>
              <strong>{{ totalQuote }}</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="actions">
        <button class="btn btn-secondary" @click="currentStep = 2">
          <ArrowLeft :size="18" /> Editar Productos
        </button>
        <button class="btn btn-primary" @click="confirmQuote" :disabled="isSubmitting">
          {{ isSubmitting ? 'Procesando...' : 'Confirmar y Enviar Cotización' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { Search, ArrowRight, ArrowLeft, ShoppingCart, Trash2, Plus, Minus } from 'lucide-vue-next';
import distributorService from '@/services/distributorService';
import productService from '@/services/productService';
import categoryService from '@/services/productCategoryService';
import quoteService from '@/services/quoteService';
import { authService } from '@/services/authService';
import fotoCaja from '@/assets/caja_dicreme.jpg';
import { useNotification } from '@/composables/useNotification';
const heladoImages = import.meta.glob('@/assets/FotoHelados/*.webp', { eager: true, import: 'default' }) as Record<string, string>;


const { notify } = useNotification();
const router = useRouter();

// --- ESTADOS DE UI Y NAVEGACIÓN ---
const currentStep = ref(1);
const isSubmitting = ref(false);
const isDistributorSelected = ref(false); // Bloquea los inputs si ya existe el distribuidor

// Referencias para el Click Outside
const distributorContainer = ref<HTMLElement | null>(null);
const comunaContainer = ref<HTMLElement | null>(null);

// --- ESTADOS DE DATOS ---
const distributors = ref<any[]>([]);
const searchQuery = ref('');
const showDropdown = ref(false);
const comunaSearch = ref('');
const showComunaDropdown = ref(false);

const distributorForm = ref({
  id: null as null | number,
  nombre_empresa: '',
  rut_empresa: '',
  correo_electronico: '',
  telefono: '+56',
  direccion: '',
  comuna: '',
  persona_recibe: ''
});

const iceCreams = ref<any[]>([]);
const categoriesList = ref<any[]>([]);
const cartItems = ref<any[]>([]);
const productSearch = ref('');
const selectedCategory = ref('Todas');

const comunasSantiago = [
  'Cerrillos', 'Cerro Navia', 'Conchalí', 'El Bosque', 'Estación Central', 'Huechuraba', 
  'Independencia', 'La Cisterna', 'La Florida', 'La Granja', 'La Pintana', 'La Reina', 
  'Las Condes', 'Lo Barnechea', 'Lo Espejo', 'Lo Prado', 'Macul', 'Maipú', 'Ñuñoa', 
  'Pedro Aguirre Cerda', 'Peñalolén', 'Providencia', 'Pudahuel', 'Quilicura', 'Quinta Normal', 
  'Recoleta', 'Renca', 'San Joaquín', 'San Miguel', 'San Ramón', 'Santiago', 'Vitacura',
  'Puente Alto', 'Pirque', 'San José de Maipo', 'San Bernardo', 'Buin', 'Calera de Tango', 
  'Paine', 'Colina', 'Lampa', 'Tiltil', 'Talagante', 'El Monte', 'Isla de Maipo', 
  'Padre Hurtado', 'Peñaflor', 'Melipilla', 'Alhué', 'Curacaví', 'María Pinto', 'San Pedro'
].sort();

// --- CLICK OUTSIDE LOGIC ---
const handleClickOutside = (event: MouseEvent) => {
  if (distributorContainer.value && !distributorContainer.value.contains(event.target as Node)) {
    showDropdown.value = false;
  }
  if (comunaContainer.value && !comunaContainer.value.contains(event.target as Node)) {
    showComunaDropdown.value = false;
  }
};

const getDynamicImage = (flavorName: string) => {
  if (!flavorName) return fotoCaja;
  
  const formattedName = flavorName
    .toLowerCase()
    .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // Quita tildes
    .replace(/\s+/g, '-'); // Cambia espacios por guiones

  const path = `/src/assets/FotoHelados/${formattedName}.webp`;

  if (heladoImages[path]) {
    return heladoImages[path];
  } else {
    return fotoCaja; // Caja por defecto si no hay foto
  }
};


// --- VALIDACIONES CHILENAS ---
const formatRUT = () => {
  distributorForm.value.rut_empresa = formatRutInput(distributorForm.value.rut_empresa);
};

const formatRutInput = (rut: string) => {
  // Limpiamos todo excepto números y la letra K
  let clean = rut.replace(/[^0-9kK]/gi, '');
  if (clean.length > 9) clean = clean.substring(0, 9);
  
  if (clean.length > 1) {
    return clean.slice(0, -1) + '-' + clean.slice(-1).toUpperCase();
  }
  return clean.toUpperCase();
};

const formatRutVisual = (rut: string) => {
  if (!rut) return '';
  let clean = rut.replace(/[^0-9kK]/gi, '');
  if (clean.length > 9) clean = clean.substring(0, 9);
  if (clean.length > 1) {
    return clean.slice(0, -1) + '-' + clean.slice(-1).toUpperCase();
  }
  return clean.toUpperCase();
}

const validateRUT = (rut: string): boolean => {
  const cleanRut = rut.replace(/[^0-9kK]/g, '');
  if (cleanRut.length < 3) return false;
  const body = cleanRut.slice(0, -1);
  const dv = cleanRut.slice(-1).toUpperCase();
  
  let sum = 0;
  let multiplier = 2;
  for (let i = body.length - 1; i >= 0; i--) {
    sum += parseInt(body.charAt(i)) * multiplier;
    multiplier = multiplier === 7 ? 2 : multiplier + 1;
  }
  const expectedDv = 11 - (sum % 11);
  const dvFinal = expectedDv === 11 ? '0' : expectedDv === 10 ? 'K' : expectedDv.toString();
  return dv === dvFinal;
};

const validateEmail = (email: string): boolean => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

const validatePhone = (phone: string): boolean => {
  return /^(\+?56)?9\d{8}$/.test(phone.replace(/\s+/g, ''));
};

// --- NAVEGACIÓN DE PASOS ---
const goToStep = (step: number) => {
  // Solo permite retroceder
  if (step < currentStep.value) {
    currentStep.value = step;
  }
};

const nextStep = () => {
  if (currentStep.value === 1) {
    const form = distributorForm.value;

    if (!form.nombre_empresa || !form.rut_empresa || !form.correo_electronico) {
      notify('Por favor complete los campos obligatorios (Nombre, RUT, Correo).', 'warning');
      return;
    }
    if (!validateRUT(form.rut_empresa)) {
      notify('El RUT ingresado no es válido.', 'error');
      return;
    }
    if (!validateEmail(form.correo_electronico)) {
      notify('El correo electrónico no tiene un formato válido.', 'error');
      return;
    }
    if (!validatePhone(form.telefono)) {
      notify('El teléfono debe ser un celular válido (+569XXXXXXXX).', 'error');
      return;
    }

    notify('Datos del cliente validados correctamente', 'success');
    currentStep.value = 2;
  } else if (currentStep.value === 2) {
    if (cartItems.value.length === 0) {
      notify('Debe añadir al menos un producto a la cotización.', 'warning');
      return;
    }
    currentStep.value = 3;
  }
};

// --- GESTIÓN DEL CLIENTE ---
const handleSearchInput = () => {
  showDropdown.value = true;
};

const clearDistributorSelection = () => {
  isDistributorSelected.value = false;
  searchQuery.value = '';
  distributorForm.value = {
    id: null,
    nombre_empresa: '',
    rut_empresa: '',
    correo_electronico: '',
    telefono: '+56',
    direccion: '',
    comuna: '',
    persona_recibe: ''
  };
  comunaSearch.value = '';
};

const selectDistributor = (d: any) => {
  let phone = d.telefono || '';
  if (phone && !phone.startsWith('+56')) {
    phone = '+56' + phone.replace(/^56/, '');
  } else if (!phone) {
    phone = '+56';
  }

  distributorForm.value = {
    id: d.id,
    nombre_empresa: d.nombre_empresa,
    rut_empresa: d.rut_empresa,
    correo_electronico: d.correo_electronico,
    telefono: phone,
    direccion: d.direccion,
    comuna: d.comuna,
    persona_recibe: d.nombre_empresa || ''
  };
  
  searchQuery.value = d.nombre_empresa;
  comunaSearch.value = d.comuna;
  isDistributorSelected.value = true; // Bloquea los campos
  showDropdown.value = false;
  notify(`Cliente ${d.nombre_empresa} seleccionado.`, 'success');
};

const handlePhoneInput = () => {
  if (!distributorForm.value.telefono.startsWith('+56')) {
    distributorForm.value.telefono = '+56';
  }
};

const selectComuna = (comuna: string) => {
  distributorForm.value.comuna = comuna;
  comunaSearch.value = comuna;
  showComunaDropdown.value = false;
};

// --- GESTIÓN DE PRODUCTOS ---
const addToCart = (product: any, format: any) => {
  const existing = cartItems.value.find(item => item.id === format.id);
  if (existing) {
    existing.quantity++;
  } else {
    cartItems.value.push({
      id: format.id,
      name: product.name,
      size: format.size,
      price: format.price,
      formattedPrice: format.formattedPrice,
      category: product.category,
      quantity: 1,
      image: product.image
    });
  }
};

const removeFromCart = (index: number) => {
  cartItems.value.splice(index, 1);
};

const updateQuantity = (index: number, change: number) => {
  cartItems.value[index].quantity += change;
  if (cartItems.value[index].quantity <= 0) {
    removeFromCart(index);
  }
};

const totalQuote = computed(() => {
  const total = cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  return `$${total.toLocaleString('es-CL')}`;
});


const fetchDistributors = async () => {
  try {
    const response = await distributorService.getDistributors();
    distributors.value = response.data;
  } catch (error) {
    console.error('Error fetching distributors:', error);
  }
};

const fetchIceCreams = async () => {
  try {
    const [productsResponse, categoriesResponse] = await Promise.all([
      productService.getProducts(),
      categoryService.getCategory()
    ]);

    const dbProducts = productsResponse.data;
    const dbCategories = categoriesResponse.data;
    categoriesList.value = dbCategories;

    const categoryMap: Record<number, string> = {};
    dbCategories.forEach((cat: any) => {
      categoryMap[cat.id || cat.ID] = cat.nombre_categoria;
    });

    const grouped: Record<string, any> = {};
    dbProducts.forEach((prod: any) => {
      const flavorName = prod.nombre_producto;
      const catId = prod.id_categoria || prod.ID_categoria;
      const categoryName = categoryMap[catId] || 'Sin categoría';
      const uniqueKey = `${flavorName}_${catId}`;

      if (!grouped[uniqueKey]){
        grouped[uniqueKey] = {
          name: flavorName,
          category: categoryName,
          image: getDynamicImage(flavorName),
          formats: []
        };
      }
      grouped[uniqueKey].formats.push({
        id: prod.id || prod.ID,
        formatId: prod.id_formato || prod.ID_formato,
        size: prod.id_formato === 1 ? '10L' : prod.id_formato === 2 ? '5L' : prod.id_formato === 3 ? '2.5L' : '1L',
        price: prod.precio_producto,
        formattedPrice: `$${Number(prod.precio_producto).toLocaleString('es-CL')}`
      });
    });
    iceCreams.value = Object.values(grouped);
  } catch (error) {
    console.error('Error fetching products:', error);
  }
};

const confirmQuote = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  
  try {
    let distributorId = distributorForm.value.id;

    if (!distributorId) {
      const cleanPhone = distributorForm.value.telefono.replace(/\+56/g, '').trim();
      const rutParaValidar = distributorForm.value.rut_empresa.replace(/[^0-9kK]/gi, '');
      const rutLimpio = distributorForm.value.rut_empresa.replace(/[^0-9kK]/gi, '').toUpperCase();

      if (!validateRUT(rutParaValidar)) {
      notify('El RUT ingresado no es válido.', 'error');
      return;
      }

      const registerData = {
        rut_empresa: rutLimpio,
        nombre_empresa: distributorForm.value.nombre_empresa,
        correo_electronico: distributorForm.value.correo_electronico || `manual_${Date.now()}@dicreme.cl`,
        telefono: cleanPhone || '900000000', 
        comuna: distributorForm.value.comuna || 'Santiago',
        direccion: distributorForm.value.direccion || 'Dirección manual',
        contrasena: 'DiCreme2026', 
      };

      const response = await authService.registerDistribuidor(registerData);
      distributorId = response.id || response.ID || (response.data && (response.data.id || response.data.ID));
      
      if (!distributorId) throw new Error("No se pudo registrar al cliente automáticamente.");
    }

    const payload = {
      id_distribuidor: Number(distributorId),
      id_estado_cotizacion: 1,
      persona_recibe: distributorForm.value.persona_recibe || distributorForm.value.nombre_empresa,
      total_cotizacion: cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0),
      cotizacion_productos: cartItems.value.map(item => ({
        id_producto: item.id,
        cantidad: item.quantity,
        precio_unitario_venta: item.price
      }))
    };

    await quoteService.createQuote(payload);
    notify('Cotización generada exitosamente.', 'success');
    router.push('/admin/quotes');
  } catch (error: any) {
    console.error('Error en el flujo:', error);
    let msg = 'Error al procesar la solicitud.';
    if (error.response?.data?.errors) {
       msg = Object.values(error.response.data.errors).flat().join('\n');
    } else if (error.response?.data?.message) {
       msg = error.response.data.message;
    }
    notify(`Error: ${msg}`, 'error');
  } finally {
    isSubmitting.value = false;
  }
};

const filteredDistributors = computed(() => {
  if (!searchQuery.value) return [];
  const query = searchQuery.value.toLowerCase();
  return distributors.value.filter(d => 
    d.nombre_empresa.toLowerCase().includes(query) || 
    d.rut_empresa.toLowerCase().includes(query)
  ).slice(0, 5);
});

const filteredIceCreams = computed(() => {
  let results = iceCreams.value;
  if (selectedCategory.value !== 'Todas') {
    results = results.filter(item => item.category === selectedCategory.value);
  }
  if (productSearch.value.trim() !== '') {
    const s = productSearch.value.toLowerCase();
    results = results.filter(item => item.name.toLowerCase().includes(s));
  }
  return results;
});

const filteredComunas = computed(() => {
  if (!comunaSearch.value) return comunasSantiago.slice(0, 5); 
  return comunasSantiago.filter(c => 
    c.toLowerCase().includes(comunaSearch.value.toLowerCase())
  ).slice(0, 5); 
});

onMounted(() => {
  fetchDistributors();
  fetchIceCreams();
  window.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* (Mantén el resto de tu CSS actual igual, solo añade o modifica lo siguiente) */

.admin-quote-wizard {
  max-width: 1100px;
  margin: 2rem auto;
  padding: 2rem;
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  font-family: sans-serif;
}

.wizard-header {
  text-align: center;
  margin-bottom: 2rem;
}

.wizard-header h1 {
  color: #1a1624;
  margin-bottom: 1.5rem;
}

.steps-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.step {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  background: #f0f0f0;
  color: #999;
  font-weight: bold;
  transition: all 0.3s ease;
}

.step.active {
  background: var(--DC-pink);
  color: white;
}

.line {
  height: 2px;
  width: 30px;
  background: #eee;
}

.step-container {
  padding: 1rem 0;
}

.search-box {
  padding-top: 1rem;
  position: relative;
  margin-bottom: 1.5rem;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 64%;
  transform: translateY(-50%);
  color: #999;
}

.search-box .dc-input {
  padding-left: 3rem;
}

/* Nuevo: Botón para limpiar la búsqueda */
.btn-clear-search {
  position: absolute;
  right: 1rem;
  top: 64%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #ff4d4f;
  cursor: pointer;
  padding: 5px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s;
}
.btn-clear-search:hover {
  background-color: #fff1f0;
}

/* Inputs deshabilitados */
input:disabled, .dc-input:disabled {
  background-color: #f5f5f5;
  color: #888;
  border-color: #ddd;
  cursor: not-allowed;
}

.search-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  z-index: 10;
  margin-top: 5px;
}

.dropdown-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
}

.dropdown-item:hover {
  background: #f8f8f8;
}

.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 2rem 0;
}

.divider::before, .divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid #eee;
}

.divider span {
  padding: 0 1rem;
  color: #999;
  font-size: 0.9rem;
}

.distributor-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.input-row {
  display: flex;
  gap: 1.5rem;
}

.input-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  position: relative; 
}

.input-group label {
  font-weight: bold;
  font-size: 0.9rem;
  color: #555;
}

.dc-input, .dc-select {
  width: 100%;
  padding: 0.8rem 1.2rem;
  border: 1px solid #e4869f;
  border-radius: 12px;
  font-size: 1rem;
  outline: none;
  background: white;
  appearance: none; 
}

.product-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 2rem;
  min-height: 500px;
}

.catalog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.filters {
  display: flex;
  gap: 1rem;
}

.product-search {
  position: relative;
  display: flex;
  align-items: center;
}

.product-search input {
  padding: 0.5rem 1rem 0.5rem 2.5rem;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.product-search svg {
  position: absolute;
  left: 0.8rem;
  color: #999;
}

.products-grid-admin {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.5rem;
  max-height: 600px;
  overflow-y: auto;
  padding-right: 10px;
}

.product-card-admin {
  background: #fdfdfd;
  border: 1px solid #eee;
  border-radius: 12px;
  overflow: hidden;
}

.product-card-admin img {
  width: 100%;
  height: 120px;
  object-fit: contain; /* Cambia cover por contain */
  background-color: #fff; /* Fondo blanco para que se vea limpio */
  padding: 5px; /* Un pequeño margen */
  box-sizing: border-box;
}

.p-info {
  padding: 1rem;
}

.p-info h4 { margin: 0; font-size: 1rem; }
.p-cat { font-size: 0.8rem; color: var(--DC-pink); font-weight: bold; }

.formats-list {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.format-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
  background: #fff;
  padding: 4px 8px;
  border-radius: 6px;
  border: 1px solid #f0f0f0;
}

.btn-add-small {
  background: var(--DC-pink);
  color: white;
  border: none;
  border-radius: 4px;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.cart-summary-admin {
  background: #f9f9f9;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
}

.cart-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
  color: #333;
}

.cart-items-list {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 1rem;
}

.empty-cart {
  color: #999;
  text-align: center;
  margin-top: 2rem;
}

.cart-item-admin {
  background: white;
  padding: 0.8rem;
  border-radius: 8px;
  margin-bottom: 0.8rem;
  border: 1px solid #eee;
  display: flex;
  align-items: center;
  gap: 10px;
}

.cart-item-thumb {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 6px;
}

.item-main {
  display: flex;
  flex-direction: column;
  margin-bottom: 0.5rem;
}

.item-name { font-weight: bold; font-size: 0.9rem; }
.item-price { font-size: 0.85rem; color: #666; }

.item-controls {
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.qty-btn {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #eee;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.btn-delete {
  margin-left: auto;
  background: none;
  border: none;
  color: #ff4d4f;
  cursor: pointer;
}

.cart-total {
  border-top: 2px solid #eee;
  padding-top: 1rem;
  display: flex;
  justify-content: space-between;
  font-size: 1.1rem;
}

.summary-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.summary-card {
  background: #f9f9f9;
  padding: 1.5rem;
  border-radius: 12px;
  border: 1px solid #eee;
}

.summary-card p { margin: 0.5rem 0; }

.products-list-final {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.final-item {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid #eee;
  padding-bottom: 0.5rem;
  font-size: 0.95rem;
}

.final-item-info {
  display: flex;
  align-items: center;
  gap: 10px;
}
.final-thumb {
  width: 30px;
  height: 30px;
  object-fit: cover;
  border-radius: 4px;
}

.final-total {
  margin-top: 1rem;
  display: flex;
  justify-content: space-between;
  font-size: 1.2rem;
  color: var(--DC-pink);
}

.actions {
  display: flex;
  justify-content: space-between;
  margin-top: 3rem;
}

.btn {
  padding: 0.8rem 2rem;
  border-radius: 12px;
  font-weight: bold;
  cursor: pointer;
  border: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.btn-primary {
  background: var(--DC-pink);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  filter: brightness(0.9);
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f0f0f0;
  color: #666;
}

.btn-secondary:hover {
  background: #e5e5e5;
}

.search-comuna {
  position: relative; /* Clave para que el dropdown se ancle a este div */
}


.comuna-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e4869f;
  border-radius: 12px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 1000; /* Siempre encima de todo */
  box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  margin-top: 5px;
}
</style>