<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-container">
      <header class="modal-header">
        <h2 class="modal-title">Detalle de pedido #{{ orderId }}</h2>
        <div class="header-actions">
          <button class="btn-whatsapp-header" @click="abrirWhatsapPedido(orderId, distributor, distributorPhone)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.477-1.761-1.65-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.346.446-.52.149-.174.199-.298.298-.497.1-.198.05-.372-.025-.521-.075-.148-.675-1.628-.925-2.228-.243-.588-.495-.508-.675-.515-.174-.007-.374-.008-.573-.008-.199 0-.521.074-.794.372-.273.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.174-1.413-.074-.124-.273-.198-.57-.347z"/>
                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.113.548 4.16 1.574 5.96L0 24l6.198-1.576A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22.119c-1.805 0-3.57-.484-5.116-1.405l-.367-.217-3.8.968.995-3.674-.24-.38a9.92 9.92 0 0 1-1.52-5.323c0-5.518 4.485-10.003 10.003-10.003 5.518 0 10.002 4.485 10.002 10.003 0 5.517-4.484 10.002-10.002 10.002z"/>
            </svg>
            <span>WhatsApp</span>
          </button>
          <button class="btn-export" @click="exportarPDF">
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
                    <td>{{ formato(product.format) }}</td>
                    <td>
                      <span class="category-badge">{{ category(product.category) }}</span>
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
                  <Calendar :size="20" />
                </div>
                <div class="meta-text">
                  <span class="meta-label">Fecha de ingreso</span>
                  <span class="meta-value">{{ date }}</span>
                </div>
              </div>

              <div class="meta-item">
                <div class="meta-icon-box">
                  <LayoutGrid :size="20" />
                </div>
                <div class="meta-text">
                  <span class="meta-label">Estado</span>
                  <span class="status-badge" :class="getStatusClass(localStatus, localStatusId)">
                    {{ localStatus }}
                  </span>
                </div>
              </div>

              <div class="meta-item">
                  <div class="meta-icon-box"><CircleDollarSign :size="20" /></div>
                  <div class="meta-text">
                      <span class="meta-label">Estado de Pago</span>
                      <label class="switch">
                          <input type="checkbox" :checked="localPago === 'Pagada'" @change="togglePago">
                          <span class="slider round"></span>
                          <span class="status-label">{{ localPago }}</span>
                      </label>
                  </div>
              </div>
            </div>

            <div class="totals-summary-card">
              <div class="summary-header">
                <h4 class="summary-title">Resumen de pago</h4>
              </div>
              <div class="summary-divider"></div>
              <div class="summary-content">
                <div class="summary-line">
                  <span class="summary-label">Subtotal</span>
                  <span class="summary-value">${{ formatNumber(subtotalAmount) }}</span>
                </div>
                <div class="summary-line">
                  <span class="summary-label">Descuento total aplicado</span>
                  <span class="summary-value text-pink">- ${{ formatNumber(currentDiscount || 0) }}</span>
                </div>
                <div class="summary-line highlight">
                  <span class="summary-label">Total Final</span>
                  <span class="summary-total">${{ formatNumber(totalAmount) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="status-timeline-section" v-if="localStatusId !== 8">
          <div class="section-title-box">
            <LayoutGrid :size="22" class="text-pink" />
            <h3 class="section-title">Estado del pedido</h3>
          </div>

          <div class="timeline-container">
            <div class="timeline-track">
              <div 
                v-for="(step, index) in timelineSteps" 
                :key="step.id" 
                class="timeline-step"
                :class="{ 
                  'completed': index < currentStepIndex, 
                  'current': step.id === localStatusId,
                  'selected': step.id === selectedTimelineId,
                  'pending': index > currentStepIndex && step.id !== selectedTimelineId
                }"
              >
                <div class="step-dot">
                  <Check v-if="index < currentStepIndex" :size="18" :stroke-width="3" />
                  <template v-else>
                    <Banknote v-if="step.id === 6" :size="18" :stroke-width="2" />
                    <Receipt v-else-if="step.id === 7" :size="18" :stroke-width="2" />
                    <Package v-else-if="step.id === 2" :size="18" :stroke-width="2" />
                    <Truck v-else-if="step.id === 3" :size="18" :stroke-width="2" />
                    <Home v-else-if="step.id === 4" :size="18" :stroke-width="2" />
                  </template>
                </div>
                <span class="step-label">{{ step.label }}</span>
                <div v-if="index < timelineSteps.length - 1" class="step-connector" :class="{'connected': index < currentStepIndex}"></div>
              </div>
            </div>

            <div class="timeline-controls">
              <button 
                class="btn-timeline-nav" 
                @click="moveTimeline('prev')" 
                :disabled="!canGoBack"
              >
                <ChevronLeft :size="20" />
                <span>Anterior</span>
              </button>
              
              <button 
                class="btn-update-status" 
                @click="updateStatus"
                :disabled="selectedTimelineId === localStatusId || isUpdatingStatus"
              >
                {{ isUpdatingStatus ? 'Actualizando...' : 'Actualizar estado' }}
              </button>

              <button 
                class="btn-timeline-nav" 
                @click="moveTimeline('next')" 
                :disabled="!canGoForward"
              >
                <span>Siguiente</span>
                <ChevronRight :size="20" />
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">

import jsPDF from 'jspdf'; //npm install jspdf jspdf-autotable
import autoTable from 'jspdf-autotable'; // Importa la función directamente
import { ref, computed, onMounted } from 'vue';
import { 
  X, Upload, Building2, Calendar, 
  ClipboardList, LayoutGrid,
  ChevronLeft, ChevronRight, Check,
  Banknote, Receipt, Package, Truck, Home, CircleDollarSign
} from 'lucide-vue-next';
import orderService from '@/services/orderService';
import { useNotification } from '@/composables/useNotification';


const { notify } = useNotification();
const backendSubtotal = ref(0);
const backendDiscount = ref(0);
const backendTotal = ref(0);


const props = defineProps<{
  orderId: number | string;
  distributor?: string;
  distributorPhone?: string;
  status?: string;
  statusId?: number;
  date?: string;
  time?: string;
  total?: number;
  discount?: number;
}>();

const emit = defineEmits(['close', 'statusChanged']);
const localStatus = ref(props.status);
const localStatusId = ref(props.statusId ? Number(props.statusId) : 1);
const localPago = ref(props.pago || 'Pendiente'); // Ajusta según el prop que recibas

// Lógica de la línea de tiempo
const timelineSteps = [
  { id: 6, label: 'Por pagar' },
  { id: 7, label: 'Pagado' },
  { id: 2, label: 'Preparación' },
  { id: 3, label: 'En despacho' },
  { id: 4, label: 'Entregado' }
];

const currentStepIndex = computed(() => {
  return timelineSteps.findIndex(s => s.id === localStatusId.value);
});

// El estado que el usuario está SELECCIONANDO en la línea de tiempo (por defecto el actual)
const selectedTimelineId = ref(localStatusId.value);

const currentUser = ref({ id: 0, name: '', role: 0 });

const canGoBack = computed(() => {
  const selectedIndex = timelineSteps.findIndex(s => s.id === selectedTimelineId.value);
  // Los administradores (rol 1) pueden retroceder a cualquier estado anterior.
  if (currentUser.value.role === 1) {
    return selectedIndex > 0;
  }
  // Los no administradores solo pueden retroceder si avanzaron visualmente más allá de su estado real actual.
  return selectedIndex > currentStepIndex.value;
});

const canGoForward = computed(() => {
  const selectedIndex = timelineSteps.findIndex(s => s.id === selectedTimelineId.value);
  // Nadie puede saltarse un estado. El máximo estado seleccionable es el inmediatamente siguiente al estado actual (currentStepIndex + 1).
  const maxAllowedIndex = currentStepIndex.value + 1;
  return selectedIndex < maxAllowedIndex && selectedIndex < timelineSteps.length - 1;
});

const moveTimeline = (direction: 'next' | 'prev') => {
  const currentIndex = timelineSteps.findIndex(s => s.id === selectedTimelineId.value);
  if (direction === 'next' && canGoForward.value) {
    selectedTimelineId.value = timelineSteps[currentIndex + 1].id;
  } else if (direction === 'prev' && canGoBack.value) {
    selectedTimelineId.value = timelineSteps[currentIndex - 1].id;
  }
};

const isUpdatingStatus = ref(false);

const updateStatus = async () => {
  if (selectedTimelineId.value === localStatusId.value) return;
  
  isUpdatingStatus.value = true;
  try {
    const result = await orderService.changeOrderStatus(Number(props.orderId), selectedTimelineId.value);
    console.log("estado",result)
    notify(result.data.message, 'success');

    localStatusId.value = selectedTimelineId.value;
    localStatus.value = nombresEstados[localStatusId.value];
    
    emit('statusChanged');
  } catch (error: any) {
    console.error('Error al actualizar el estado:', error);
    notify(error.response?.data?.message || 'Error al actualizar el estado', 'error');
  } finally {
    isUpdatingStatus.value = false;
  }
};

const products = ref<any[]>([]);


const nombresEstados: Record<number, string> = {
  1: 'En validación',
  2: 'En preparación',
  3: 'En despacho',
  4: 'Entregado',
  5: 'Pendiente',
  6: 'Por pagar',
  7: 'Pagada',
  8: 'Cancelado'
};

const abrirWhatsapPedido = (pedido, nombreDistribuidor, telefonoDistribuidor) => {

  console.log('Datos para WhatsApp:', { pedido, nombreDistribuidor, telefonoDistribuidor });
  // 1. Estructuramos un mensaje claro y profesional con salto de línea (\n)
  const mensaje = `¡Hola ${nombreDistribuidor}!
Te contactamos de DiCreme respecto a tu Pedido #${pedido}

¿Tienes alguna duda sobre lo que solicitaste?`;

  // 2. Codificamos el mensaje para que sea seguro viajar en la URL de WhatsApp
  const url = `https://wa.me/${telefonoDistribuidor}?text=${encodeURIComponent(mensaje)}`;
  
  // 3. Abrimos la pestaña independiente
  window.open(url, '_blank');
};

const subtotalAmount = computed(() => {
  if (products.value.length > 0) {
    return products.value.reduce((acc, curr) => acc + curr.subtotal, 0);
  }
  return backendSubtotal.value; // Fallback al valor crudo del backend
});

// Capturamos el descuento global que necesitas pintar
const currentDiscount = computed(() => {
  return props.discount || backendDiscount.value || 0;
});

// El total final neto de la operación
const totalAmount = computed(() => {
  if (props.total) return props.total;
  if (backendTotal.value) return backendTotal.value;
  return subtotalAmount.value - currentDiscount.value;
});

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('es-CL').format(Math.round(num));
};

const formato = (CategoryId: number) => {
  switch (CategoryId) {
    case 1: return '10L';
    case 2: return '5L';
    case 3: return '2.5L';
    case 4: return '1L';
  }
};

const category = (CategoryId: number) =>
{
  switch (CategoryId) {
    case 1: return 'Al agua';
    case 2: return 'Leche de avena';
    case 3: return 'Tradicional ';
    case 4: return 'Sin azúcar';
  }
};

const getproducts = async () => {
  try {
    const response = await orderService.getOrderDetails(Number(props.orderId));
    console.log("datos",response.data)
    const orderData = response.data; 

    console.log('Objeto interno de la cotización/pedido:', orderData);

    if (orderData) {
      backendSubtotal.value = Number(orderData.subtotal_cotizacion || 0);
      backendDiscount.value = Number(orderData.descuento_total || 0);
      backendTotal.value = Number(orderData.total_cotizacion || 0);

      if (orderData.productos) {
        products.value = orderData.productos.map((p: any) => {
          const cantidadItem = Number(p.cantidad || p.pivot?.cantidad || p.quantity || 0);
          const precioItem = Number(p.precio_unitario_venta || p.pivot?.precio_unitario_venta || p.price || 0);
          
          return {
            id: p.id_producto || p.id,
            name: p.nombre_producto || 'Producto desconocido',
            format: p.nombre_formato || p.format || p.id_formato || '-',
            category: p.nombre_categoria || p.category || p.id_categoria || '-',
            quantity: cantidadItem,
            price: precioItem,
            subtotal: cantidadItem * precioItem,
            
            discountType: 'none',
            discountValue: 0,
          };
        });
      }
    } else {
      console.warn('No se encontraron datos dentro de response.data.data:', orderData);
    }

    console.log('Resultado final del Proxy de Vue ya mapeado:', products.value);

  } catch (error) {
    console.error('Error al obtener los productos del pedido/cotización:', error);
    notify('No se pudieron cargar los productos del detalle.', 'error');
  }
};

const getStatusClass = (status: string | undefined) => {
  switch (status) {
    case 'Por pagar': return 'status-unpaid';
    case 'Pagada': return 'status-paid';
    case 'En preparación': return 'status-preparation';
    case 'En despacho': return 'status-shipping';
    case 'Entregado': return 'status-completed';
    case 'En validación': return 'status-validation';
    case 'Pendiente': return 'status-pending';
    case 'Cancelado': return 'status-cancelled';
    default: return 'status-generic';
  }
};

onMounted(() => {
  const userStored = localStorage.getItem('user');
  if (userStored) {
    const userObj = JSON.parse(userStored);
    currentUser.value = {
      id: userObj.id,
      name: userObj.nombre_usuario || userObj.name,
      role: userObj.id_rol || 0
    };
  }
  getproducts();
});

const getBase64FromUrl = async (url: string): Promise<string> => {
  const data = await fetch(url);
  const blob = await data.blob();
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.readAsDataURL(blob);
    reader.onloadend = () => resolve(reader.result as string);
  });
};

const exportarPDF = async () => {
  const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
  
  // 1. Logo ajustado
  const logoBase64 = await getBase64FromUrl('/src/assets/logo_dicreme.png');
  // Ajuste: 35 ancho, 12 alto (más rectangular para logos)
  doc.addImage(logoBase64, 'PNG', 15, 10, 19, 19); 

  // 2. Encabezado mejor posicionado
  doc.setFont("helvetica", "bold");
  doc.setFontSize(20);
  doc.setTextColor(50, 44, 68); // Color #322c44
  doc.text(`Pedido N° ${String(props.orderId).padStart(6, '0')}`, 195, 20, { align: 'right' });
  
  doc.setFontSize(10);
  doc.setFont("helvetica", "normal");
  doc.setTextColor(80); // Un gris oscuro para texto secundario
  doc.text(`Distribuidor: ${props.distributor || 'N/A'}`, 15, 32);
  doc.text(`Teléfono: ${props.distributorPhone || 'N/A'}`, 15, 37);
  doc.text(`Estado de Pago: ${localPago.value}`, 15, 42);
  
  doc.setDrawColor(228, 134, 159); // Color #e4869f
  doc.setLineWidth(0.5);
  doc.line(15, 47, 195, 47); // Línea con color de marca

  // 3. Tabla con mejor padding
  autoTable(doc, {
    startY: 55, // Bajamos la tabla para que no toque el encabezado
    head: [['Producto', 'Formato', 'Cantidad', 'Precio Unit.', 'Subtotal']],
    body: products.value.map(p => [
      p.name, 
      formato(p.format), 
      p.quantity, 
      `$${formatNumber(p.price)}`, 
      `$${formatNumber(p.subtotal)}`
    ]),
    theme: 'striped',
    headStyles: { fillColor: [50, 44, 68], textColor: 255, fontStyle: 'bold' },
    styles: { cellPadding: 3, fontSize: 10 },
    columnStyles: { 
        2: { halign: 'center' },
        3: { halign: 'right' },
        4: { halign: 'right' }
    }
  });

  // 4. Resumen (Cálculo dinámico de posición Y)
  const finalY = (doc as any).lastAutoTable.finalY + 15;
  
  doc.setFont("helvetica", "bold");
  doc.setFontSize(11);
  doc.text(`Descuento:`, 150, finalY);
  doc.text(`-$${formatNumber(currentDiscount.value)}`, 195, finalY, { align: 'right' });
  
  doc.setFontSize(14);
  doc.text(`TOTAL FINAL:`, 145, finalY + 10);
  doc.text(`$${formatNumber(totalAmount.value)}`, 195, finalY + 10, { align: 'right' });

  // 5. Pie de página (con borde superior opcional)
  doc.setLineWidth(0.2);
  doc.line(15, 280, 195, 280);
  doc.setFontSize(8);
  doc.setFont("helvetica", "italic");
  doc.text("Documento generado automáticamente por DiCreme - Sistema de Gestión de Pedidos", 105, 285, { align: 'center' });

  doc.save(`Pedido_${props.orderId}.pdf`);
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

.btn-whatsapp-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background-color: #e8fbf1;
  border: 1px solid #a3ebd0;
  border-radius: 12px;
  color: #1ea952;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-whatsapp-header:hover {
  background-color: #d2f7e4;
  border-color: #61cf9f;
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
  padding: 24px 32px 32px 32px;
  overflow-y: auto;
}

.section-header {
  margin-bottom: 20px;
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
}

.products-column {
  flex: 1.8;
}

.info-column {
  flex: 1.2;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.order-meta {
  background-color: #f8f9fa;
  padding: 24px;
  border-radius: 16px;
  border: 1px solid #eee;
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

.status-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  display: inline-block;
  margin-top: 4px;
}

.status-unpaid { background-color: #fff0f3; color: #e4869f; border: 1px solid #e4869f; }
.status-paid { background-color: #ebfbee; color: #37b24d; border: 1px solid #37b24d; }
.status-preparation { background-color: #e7f5ff; color: #1c7ed6; border: 1px solid #1c7ed6; }
.status-shipping { background-color: #dcd5ff; color: #6741d9; border: 1px solid #6741d9; }
.status-completed { background-color: #e6fffa; color: #087f5b; border: 1px solid #087f5b; }
.status-validation { background-color: #fff4e6; color: #f76707; border: 1px solid #f76707; }


.table-wrapper {
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  overflow-y: auto;
  height: 450px;
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

.totals-summary-card {
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.summary-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 800;
  color: #322c44;
}

.summary-divider {
  height: 1px;
  background-color: #eee;
  margin: 16px 0;
}

.summary-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.summary-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.summary-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #777;
}

.summary-value {
  font-size: 1rem;
  font-weight: 700;
  color: #322c44;
}

.highlight {
  margin-top: 8px;
  padding-top: 16px;
  border-top: 2px solid #fce4ec;
}

.highlight .summary-label {
  font-size: 1.1rem;
  font-weight: 800;
  color: #322c44;
}

.summary-total {
  font-size: 1.5rem;
  font-weight: 900;
  color: #322c44;
}

.modal-actions-footer {
  display: flex;
  justify-content: flex-end; 
  padding: 0 32px 24px 32px; 
}

.btn-change-status {
  padding: 12px 20px;
  background-color: #e4869f;
  border: 1px solid #ddd;
  border-radius: 12px;
  color: #ffffff;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-change-status:hover {
  background-color: #928f8f;
  border-color: #ccc;
}

.btn-whatsapp {
  background-color: #e8fbf1;
  color: #1ea952; 
  border: 1px solid #a3ebd0;
  transition: all 0.2s ease;
}

.btn-whatsapp:hover {
  background-color: #d2f7e4;
  border-color: #61cf9f;
}


.btn-whatsapp svg {
  fill: currentColor;
  display: inline-block;
  flex-shrink: 0;
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
  margin-left: 16px;
}


.status-timeline-section {
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  margin-top: 32px;
}

.timeline-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
  margin-top: 16px;
}

.timeline-track {
  display: flex;
  justify-content: space-between;
  position: relative;
  width: 100%;
  padding: 0 20px;
}

.timeline-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  z-index: 2;
  gap: 8px;
  flex: 1;
}

.step-dot {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: white;
  border: 2px solid #ddd;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.9rem;
  color: #adb5bd;
  transition: all 0.3s ease;
}

.step-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #adb5bd;
  text-align: center;
  transition: all 0.3s ease;
}

.step-connector {
  position: absolute;
  top: 16px;
  left: 50%;
  width: 100%;
  height: 2px;
  background-color: #ddd;
  z-index: -1;
  transition: background-color 0.3s ease;
}


.timeline-step.completed .step-dot {
  background-color: #e4869f;
  border-color: #e4869f;
  color: white;
}
.timeline-step.completed .step-label {
  color: #322c44;
}
.step-connector.connected {
  background-color: #e4869f;
}

.timeline-step.current .step-dot {
  border-color: #e4869f;
  color: #e4869f;
  box-shadow: 0 0 0 4px rgba(228, 134, 159, 0.15);
}
.timeline-step.current .step-label {
  color: #e4869f;
  font-weight: 700;
}

.timeline-step.selected .step-dot {
  transform: scale(1.2);
  border-color: #322c44;
  color: #322c44;
}
.timeline-step.selected .step-label {
  color: #322c44;
  font-weight: 800;
}


.timeline-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 16px;
}

.btn-timeline-nav {
  display: flex;
  align-items: center;
  gap: 4px;
  background: none;
  border: 1px solid #ddd;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  color: #495057;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-timeline-nav:hover:not(:disabled) {
  background-color: #f8f9fa;
  border-color: #ced4da;
}

.btn-timeline-nav:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-update-status {
  background-color: #e4869f;
  color: white;
  border: none;
  padding: 10px 32px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 180px;
}

.btn-update-status:hover:not(:disabled) {
  background-color: #d6758e;
  transform: translateY(-1px);
}

.btn-update-status:disabled {
  background-color: #e9ecef;
  color: #adb5bd;
  cursor: not-allowed;
}


.switch { position: relative; display: inline-block; width: 50px; height: 24px; margin-top: 5px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider {
    position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
    background-color: #ccc; transition: .4s; border-radius: 24px;
}
.slider:before {
    position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px;
    background-color: white; transition: .4s; border-radius: 50%;
}
input:checked + .slider { background-color: #37b24d; }
input:checked + .slider:before { transform: translateX(26px); }

.status-label { font-size: 0.8rem; font-weight: 600; margin-left: 10px; vertical-align: middle; }

/* Botón Exportar */
.btn-export {
    display: flex; align-items: center; gap: 8px; padding: 10px 20px;
    background-color: #322c44; color: white; border: none; border-radius: 12px;
    font-weight: 700; cursor: pointer; transition: 0.2s;
}
.btn-export:hover { background-color: #4a445c; }

</style>