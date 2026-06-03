<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-container">
      <header class="modal-header">
        <h2 class="modal-title">Detalle de cotización #{{ orderId }}</h2>
        <div class="header-actions">
          <button class="btn-export">
            <Upload :size="18" />
            <span>Exportar</span>
          </button>
          <button class="btn-close" @click="$emit('close')">
            <X :size="24" />
          </button>
        </div>
      </header>
      
      <div class="modal-content">
        <div class="section-header">
          <div class="section-title-box">
            <ClipboardList :size="22" class="text-pink" />
            <h3 class="section-title">Detalle de productos ({{ products.length }})</h3>
          </div>
        </div>

        <div class="modal-body-layout">
          <div class="products-column">
            <div class="table-wrapper">
              <table class="products-table">
                <thead>
                  <tr>
                    <th>PRODUCTO</th>
                    <th>FORMATO</th>
                    <th>CATEGORÍA</th>
                    <th>CANTIDAD</th>
                    <th>PRECIO UNIT.</th>
                    <th class="text-right">SUBTOTAL</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="product in products" :key="product.id">
                    <td class="bold-text">{{ product.name }}</td>
                    <td>{{ product.format }}</td>
                    <td>
                      <span class="category-badge">{{ product.category }}</span>
                    </td>
                    <td>{{ product.quantity }}</td>
                    <td>${{ formatNumber(product.price) }}</td>
                    <td class="text-right bold-text">${{ formatNumber(product.subtotal) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="info-column">
            <div class="order-meta">
              <div class="meta-item">
                <div class="meta-icon-box">
                  <Building2 :size="20" />
                </div>
                <div class="meta-text">
                  <span class="meta-label">Distribuidor</span>
                  <span class="meta-value">{{ distributor || 'Cargando...' }}</span>
                </div>
              </div>

              <div class="meta-item">
                <div class="meta-icon-box">
                  <User :size="20" />
                </div>
                <div class="meta-text">
                  <span class="meta-label">Gestionado por</span>
                  <span class="meta-value">{{ managedBy || 'Sin asignar' }}</span>
                </div>
              </div>

              <div class="meta-item">
                <div class="meta-icon-box">
                  <Calendar :size="20" />
                </div>
                <div class="meta-text">
                  <span class="meta-label">Fecha</span>
                  <span class="meta-value">{{ date }} {{ time ? '- ' + time : '' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer-layout">
          <div class="discount-card">
            <div class="card-header">
              <h4 class="card-title">Descuento general</h4>
              <p class="card-description">Aplica un descuento sobre el total de la cotización</p>
            </div>
            
            <div class="card-divider"></div>
            
            <div class="discount-columns">
              <div class="discount-col">
                <span class="col-label">Tipo de descuento</span>
                <div class="type-selector-vertical">
                  <button 
                    class="type-option" 
                    :class="{ active: discountType === 'percentage' }"
                    @click="discountType = 'percentage'"
                  >
                    <div class="radio-circle"></div>
                    <span>Porcentaje (%)</span>
                  </button>
                  <button 
                    class="type-option" 
                    :class="{ active: discountType === 'fixed' }"
                    @click="discountType = 'fixed'"
                  >
                    <div class="radio-circle"></div>
                    <span>Monto fijo ($)</span>
                  </button>
                </div>
              </div>

              <div class="discount-col">
                <span class="col-label">Valor del descuento</span>
                <div class="input-container">
                  <span class="input-prefix">{{ discountType === 'percentage' ? '%' : '$' }}</span>
                  <input 
                    type="number" 
                    v-model="discountInput"
                    @input="validateGeneralDiscount"
                    class="discount-input"
                  />
                </div>
              </div>

              <div class="discount-col">
                <span class="col-label">Descuento aplicado</span>
                <div class="applied-value text-pink">
                  - ${{ formatNumber(appliedGeneralDiscount) }}
                </div>
              </div>
            </div>

            <div class="advanced-discount-toggle" @click="isAdvancedOpen = !isAdvancedOpen">
              <div class="toggle-left">
                <div class="toggle-icon-box">
                  <Settings2 :size="20" class="text-pink" />
                </div>
                <div class="toggle-text">
                  <span class="toggle-title">Descuento avanzado por producto</span>
                  <span class="toggle-description">Aplica descuentos de forma individual a cada producto</span>
                </div>
              </div>
              <ChevronDown :size="20" class="text-gray transition-icon" :class="{ rotated: isAdvancedOpen }" />
            </div>

            <div v-if="isAdvancedOpen" class="advanced-list">
              <div v-for="product in products" :key="product.id" class="advanced-item">
                <div class="adv-col">
                  <span class="adv-product-name">{{ product.name }}</span>
                  <span class="adv-subtotal-val">${{ formatNumber(product.subtotal) }}</span>
                </div>
                <div class="adv-col">
                  <select v-model="product.discountType" class="adv-select">
                    <option value="percentage">Porcentaje (%)</option>
                    <option value="fixed">Monto fijo ($)</option>
                  </select>
                </div>
                <div class="adv-col">
                  <div class="adv-input-box">
                    <input 
                      type="number" 
                      v-model="product.discountValue"
                      @input="validateProductDiscount(product)"
                      class="adv-input"
                      placeholder="0"
                    />
                  </div>
                </div>
                <div class="adv-col">
                  <span class="adv-applied-val text-pink">
                    - ${{ formatNumber(calculateProductDiscount(product)) }}
                  </span>
                </div>
                <div class="adv-col-action">
                  <button class="btn-erase" @click="product.discountValue = 0">
                    <Eraser :size="18" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="totals-card">
            <div class="card-header">
              <h4 class="card-title">Resumen de montos</h4>
            </div>
            
            <div class="card-divider"></div>
            
            <div class="totals-info">
              <div class="total-line">
                <span class="total-label">Subtotal</span>
                <span class="total-val">${{ formatNumber(subtotalSum) }}</span>
              </div>
              <div class="total-line">
                <span class="total-label">Descuentos por producto</span>
                <span class="total-val text-pink">- ${{ formatNumber(totalProductDiscounts) }}</span>
              </div>
              <div class="total-line">
                <span class="total-label">Descuento general</span>
                <span class="total-val text-pink">- ${{ formatNumber(appliedGeneralDiscount) }}</span>
              </div>
              
              <div class="total-final-container">
                <div class="final-row discount-final">
                  <span class="total-final-label">Descuento Total Final</span>
                  <span class="total-final-val text-pink">- ${{ formatNumber(totalDiscountSum) }}</span>
                </div>
                <div class="final-row">
                  <span class="total-final-label">Total Final</span>
                  <span class="total-final-val">${{ formatNumber(finalTotal) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-final-actions">
          <button class="btn-modal btn-cancel" @click="showCancelConfirm = true">
            <XCircle :size="18" />
            <span>Cancelar Cotización</span>
          </button>
          <button class="btn-modal btn-complete" @click="handleComplete">
            <CheckCircle2 :size="18" />
            <span>Completar Cotización</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Cancel Confirmation Overlay -->
    <ConfirmModal
      v-if="showCancelConfirm"
      type="cancel"
      :order-id="orderId"
      :is-success="isCancelSuccess"
      @confirm="handleCancel"
      @cancel="showCancelConfirm = false"
      @accept="handleAcceptCancel"
    />
  </div>

</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
import quoteService from '@/services/quoteService';
import productService from '@/services/productService';
import productCategoryService from '@/services/productCategoryService';
import productFormatService from '@/services/productFormatService';
import { 
  X, Upload, Building2, User, Calendar, 
  ClipboardList, Settings2, ChevronDown, Eraser,
  XCircle, CheckCircle2, ClockAlert
} from 'lucide-vue-next';
import { useNotification } from '@/composables/useNotification';

interface ProductItem {
  id: number;
  name: string;
  format: string;
  category: string;
  quantity: number;
  price: number;       // Mantenido como número
  subtotal: number;    // Mantenido como número
  discountType: 'percentage' | 'fixed' | 'none';
  discountValue: number;
}

const props = defineProps<{
  orderId: number | string;
  distributor?: string;
  managedBy?: string;
  date?: string;
  time?: string;
}>();

const emit = defineEmits(['close', 'cancel', 'complete', 'notificar']);

const showCancelConfirm = ref(false);
const isCancelSuccess = ref(false);
const { notify } = useNotification();



const obtenerUsuarioInicial = () => {
  const userStored = localStorage.getItem('user');
  if (userStored) {
    const userObj = JSON.parse(userStored);
    return {
      id: userObj.id,
      name: userObj.nombre_usuario || userObj.name
    };
  }
  return { id: 0, name: '' };
};


const currentUser = ref(obtenerUsuarioInicial());


const handleCancel = async () => {
  try {
    const response = await quoteService.cancelQuote(Number(props.orderId), currentUser.value.id);
    notify(response.data.message, 'success');

    isCancelSuccess.value = true;
  } catch (error) {
    console.error('Error cancelling quote:', error);
    notify(error.response?.data?.message || 'Error', 'error');
  }
};

const handleAcceptCancel = () => {
  showCancelConfirm.value = false;
  isCancelSuccess.value = false;
  emit('cancel');
};

const handleComplete = async () => {
  try {
    const discountPayload = {
      discountType: discountType.value,              // 'percentage', 'fixed' o 'none'
      discountInput: Number(discountInput.value) || 0, // Valor del descuento general
      productos: products.value.map((p) => ({
        id_cotizacion_producto: p.id,                 // ID de la tabla intermedia
        discountType: p.discountType,                 // 'percentage', 'fixed' o 'none'
        discountValue: Number(p.discountValue) || 0   // Valor del descuento por producto
      }))
    };


    const responseValidacion = await quoteService.validateQuote(
      Number(props.orderId), 
      currentUser.value.id, 
      discountPayload 
    );

    console.log('Respuesta de validación:', responseValidacion);

    notify(responseValidacion.data.message, 'success');


    const responseTransformacion = await quoteService.transformQuoteToOrder(Number(props.orderId));
    notify(responseTransformacion.data.message, 'success');

    emit('complete');

  } catch (error: any) {
    console.error('Error al completar la cotización:', error);
    const errorMsg = error.response?.data?.message || 'Hubo un error al procesar la cotización.';
    notify(errorMsg, 'error');
  }
};

const products = ref<ProductItem[]>([]);

onMounted(async () => {

  try {
    const [quoteProdsRes, allProdsRes, catsRes, formatsRes] = await Promise.all([
      quoteService.getQuoteProducts(props.orderId),
      productService.getProducts(),
      productCategoryService.getCategory(),
      productFormatService.getFormats()
    ]);

    const prodMap = new Map(allProdsRes.data.map((p: any) => [p.id, p]));
    const catMap = new Map(catsRes.data.map((c: any) => [c.id, c.nombre_categoria]));
    const formatMap = new Map(formatsRes.data.map((f: any) => [f.id, f.nombre_formato]));

    products.value = quoteProdsRes.data.map((qp: any) => {
      const p = prodMap.get(qp.id_producto);
      const subtotal = qp.cantidad * qp.precio_unitario_venta;
      
      return {
        id: qp.id,
        name: p ? p.nombre_producto : 'Producto desconocido',
        format: p ? formatMap.get(p.id_formato) : '-',
        category: p ? catMap.get(p.id_categoria) : '-',
        quantity: qp.cantidad,
        price: Number(qp.precio_unitario_venta), // Guardado puro como número
        subtotal: subtotal,                     // Guardado puro como número
        discountType: 'none',
        discountValue: 0
      };
    });
  } catch (error) {
    console.error('Error fetching quote details:', error);
  }
});

const discountType = ref<'percentage' | 'fixed'>('percentage');
const discountInput = ref(0);
const isAdvancedOpen = ref(false);

// Ahora las operaciones aritméticas son limpias y directas
const subtotalSum = computed(() => {
  return products.value.reduce((acc, curr) => acc + curr.subtotal, 0);
});

const appliedGeneralDiscount = computed(() => {
  const input = Number(discountInput.value) || 0;
  if (discountType.value === 'percentage') {
    return (subtotalSum.value * input) / 100;
  }
  return input;
});

const totalProductDiscounts = computed(() => {
  return products.value.reduce((acc, prod) => acc + calculateProductDiscount(prod), 0);
});

const totalDiscountSum = computed(() => {
  return Number(appliedGeneralDiscount.value) + Number(totalProductDiscounts.value);
});

const finalTotal = computed(() => {
  return Math.max(0, Number(subtotalSum.value) - Number(totalDiscountSum.value));
});

const validateGeneralDiscount = () => {
  let input = Number(discountInput.value);
  if (discountType.value === 'percentage') {
    if (input > 100) discountInput.value = 100;
    if (input < 0) discountInput.value = 0;
  } else {
    if (input > subtotalSum.value) discountInput.value = subtotalSum.value;
    if (input < 0) discountInput.value = 0;
  }
};

const calculateProductDiscount = (product: ProductItem) => {
  const val = Number(product.discountValue) || 0;
  if (product.discountType === 'percentage') {
    return (product.subtotal * val) / 100;
  }
  return val;
};

const validateProductDiscount = (product: ProductItem) => {
  let val = Number(product.discountValue);
  if (product.discountType === 'percentage') {
    if (val > 100) product.discountValue = 100;
    if (val < 0) product.discountValue = 0;
  } else {
    if (val > product.subtotal) product.discountValue = product.subtotal;
    if (val < 0) product.discountValue = 0;
  }
};

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('es-CL').format(Math.round(num));
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-container {
  background-color: white;
  width: 95%;
  max-width: 1100px;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  max-height: 95vh;
}

.modal-header {
  padding: 12px 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eee;
}

.modal-title {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 800;
  color: #322c44;
  font-family: 'Inter', sans-serif;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.btn-export {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background-color: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 12px;
  color: #322c44;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-export:hover {
  background-color: #eee;
  border-color: #ccc;
}

.btn-close {
  background: none;
  border: none;
  color: #9793a0;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
}

.btn-close:hover {
  color: #322c44;
}

.modal-content {
  padding: 12px 32px 32px 32px;
  overflow-y: auto;
}

.section-header {
  margin-bottom: 12px;
}

.section-title-box {
  display: flex;
  align-items: center;
  gap: 10px;
}

.text-pink {
  color: #e4869f !important;
}

.section-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 800;
  color: #322c44;
}

.modal-body-layout {
  display: flex;
  gap: 32px;
  align-items: flex-start;
  margin-bottom: 32px;
}

.products-column {
  flex: 3;
}

.info-column {
  flex: 1;
  background-color: #f8f9fa;
  padding: 20px;
  border-radius: 16px;
  border: 1px solid #eee;
}

.order-meta {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.meta-icon-box {
  width: 40px;
  height: 40px;
  background-color: white;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #777777;
  border: 1px solid #eee;
}

.meta-text {
  display: flex;
  flex-direction: column;
}

.meta-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #9793a0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.meta-value {
  font-size: 0.95rem;
  font-weight: 700;
  color: #322c44;
}

.table-wrapper {
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  overflow-y: auto;
  height: 340px;
}

.products-table {
  width: 100%;
  border-collapse: collapse;
}

.products-table th {
  background-color: #e5e5e5;
  padding: 16px;
  text-align: left;
  font-size: 0.7rem;
  font-weight: 700;
  color: #777777;
  text-transform: uppercase;
  border-bottom: 1px solid #ddd;
  position: sticky;
  top: 0;
  z-index: 10;
}

.products-table td {
  padding: 24px 16px;
  font-size: 0.85rem;
  color: #322c44;
  border-bottom: 1px solid #ddd;
}

.bold-text {
  font-weight: 700;
}

.text-right {
  text-align: right;
}

.category-badge {
  background-color: #f3f0ff;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #7950f2;
}

.modal-footer-layout {
  display: flex;
  gap: 32px;
}

.discount-card {
  flex: 1.8;
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.totals-card {
  flex: 1;
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
}

.card-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 800;
  color: #322c44;
}

.card-description {
  margin: 4px 0 0 0;
  font-size: 0.85rem;
  color: #777;
}

.card-divider {
  height: 1px;
  background-color: #eee;
  margin: 16px 0;
}

.discount-columns {
  display: flex;
  gap: 24px;
  margin-bottom: 24px;
}

.discount-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.col-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #9793a0;
  text-transform: uppercase;
}

.type-selector-vertical {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.type-option {
  display: flex;
  align-items: center;
  gap: 10px;
  background: none;
  border: none;
  padding: 6px 0;
  cursor: pointer;
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  font-weight: 600;
  color: #777;
  text-align: left;
}

.radio-circle {
  width: 18px;
  height: 18px;
  border: 2px solid #ddd;
  border-radius: 50%;
  position: relative;
}

.type-option.active {
  color: #322c44;
}

.type-option.active .radio-circle {
  border-color: #e4869f;
}

.type-option.active .radio-circle::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 8px;
  height: 8px;
  background-color: #e4869f;
  border-radius: 50%;
}

.input-container {
  display: flex;
  align-items: center;
  background-color: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 0 12px;
}

.input-prefix {
  font-weight: 700;
  color: #9793a0;
  margin-right: 8px;
}

.discount-input {
  border: none;
  background: transparent;
  padding: 10px 0;
  width: 100%;
  font-family: 'Inter', sans-serif;
  font-weight: 700;
  color: #322c44;
  outline: none;
}

.applied-value {
  font-size: 1.1rem;
  font-weight: 800;
  height: 42px;
  display: flex;
  align-items: center;
}

.advanced-discount-toggle {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 16px 20px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.advanced-discount-toggle:hover {
  background-color: #f1f3f4;
}

.toggle-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.toggle-icon-box {
  width: 40px;
  height: 40px;
  background-color: #fce4ec;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-text {
  display: flex;
  flex-direction: column;
}

.toggle-title {
  font-size: 0.95rem;
  font-weight: 750;
  color: #322c44;
}

.toggle-description {
  font-size: 0.8rem;
  color: #9793a0;
  font-weight: 500;
}

.text-gray {
  color: #9793a0;
}

.transition-icon {
  transition: transform 0.3s ease;
}

.transition-icon.rotated {
  transform: rotate(180deg);
}

.advanced-list {
  background-color: white;
  border: 1px solid #ddd;
  border-top: none;
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
  padding: 8px 0;
  max-height: 200px;
  overflow-y: auto;
}

.advanced-item {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  border-bottom: 1px solid #eee;
  gap: 16px;
}

.advanced-item:last-child {
  border-bottom: none;
}

.adv-col {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.adv-col-action {
  width: 40px;
  display: flex;
  justify-content: flex-end;
}

.adv-product-name {
  font-size: 0.85rem;
  font-weight: 750;
  color: #322c44;
}

.adv-subtotal-val {
  font-size: 0.75rem;
  color: #9793a0;
}

.adv-select {
  padding: 6px 8px;
  border-radius: 8px;
  border: 1px solid #ddd;
  background-color: #f8f9fa;
  font-family: 'Inter', sans-serif;
  font-size: 0.75rem;
  font-weight: 600;
}

.adv-input-box {
  background-color: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 0 10px;
}

.adv-input {
  border: none;
  background: transparent;
  padding: 6px 0;
  width: 100%;
  font-family: 'Inter', sans-serif;
  font-size: 0.8rem;
  font-weight: 700;
}

.adv-applied-val {
  font-size: 0.9rem;
  font-weight: 800;
}

.btn-erase {
  background-color: #fce4ec;
  color: #e4869f;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.totals-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.total-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #777;
}

.total-val {
  font-size: 1rem;
  font-weight: 700;
  color: #322c44;
}

.total-final-container {
  margin-top: 12px;
  padding-top: 16px;
  border-top: 2px solid #fce4ec;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.final-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.discount-final .total-final-label {
  font-size: 0.9rem;
  font-weight: 700;
  color: #e4869f;
}

.discount-final .total-final-val {
  font-size: 1.1rem;
  font-weight: 800;
}

.total-final-label {
  font-size: 1.1rem;
  font-weight: 800;
  color: #322c44;
}

.total-final-val {
  font-size: 1.5rem;
  font-weight: 900;
  color: #322c44;
}

.modal-final-actions {
  margin-top: 32px;
  display: flex;
  justify-content: flex-end;
  gap: 16px;
}

.btn-modal {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 12px 24px;
  border-radius: 12px;
  border: none;
  font-family: 'Inter', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 200px;
}

.btn-cancel {
  background-color: #fff5f5;
  color: #fa5252;
  border: 1px solid #ffc9c9;
}

.btn-cancel:hover {
  background-color: #ffe3e3;
  border-color: #ffa8a8;
}

.btn-complete {
  background-color: #ebfbee;
  color: #2b8a3e;
  border: 1px solid #8ce99a;
}

.btn-complete:hover {
  background-color: #d3f9d8;
  border-color: #51cf66;
}

</style>
