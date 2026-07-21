<template>
  <div class="orders-container">
    <header class="orders-header">
      <h1 class="orders-title">Pedidos</h1>
      <p class="orders-description">Gestiona y Monitorea todos los pedidos ingresados.</p>
    </header>

    <div class="status-cards">
      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-unpaid">
            <ClipboardCheck :size="24" />
          </div>
          <span class="card-label">Por pagar</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.unpaid }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-paid">
            <CheckCircle :size="24" />
          </div>
          <span class="card-label">Pagada</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.paid }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-preparation">
            <Package :size="24" />
          </div>
          <span class="card-label">En preparación</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.preparation }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-shipping">
            <Truck :size="24" />
          </div>
          <span class="card-label">En despacho</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.shipping }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-delivered">
            <CheckCircle :size="24" />
          </div>
          <span class="card-label">Entregado</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.delivered }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>
    </div>

    <div class="browser-tabs">
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'pagos' }"
        @click="activeTab = 'pagos'"
      >
        <span>Pagos</span>
        <span class="tab-count">{{ counts.pagos }}</span>
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'pedidos' }"
        @click="activeTab = 'pedidos'"
      >
        <span>Pedidos</span>
        <span class="tab-count">{{ counts.pedidos }}</span>
      </button>
    </div>

    <div class="main-table-card">
      <div class="table-actions">
        <div class="actions-left">
          <div class="search-box">
            <Search :size="18" class="search-icon" />
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Busca por ID de pedido o distribuidor..."
            />
          </div>

          <div class="dropdown-container">
            <button class="btn-secondary" @click.stop="toggleStatusDropdown">
              <Filter :size="18" />
              <span>{{ statusFilter === 'all' ? 'Todos los estados' : statusFilter }}</span>
              <ChevronDown :size="16" />
            </button>
            
            <div class="dropdown-menu" v-if="isStatusDropdownOpen">
              <div class="dropdown-item" @click="selectStatus('all')">Todos los estados</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item" @click="selectStatus('Validación')">Validación</div>
              <div class="dropdown-item" @click="selectStatus('En preparación')">En preparación</div>
              <div class="dropdown-item" @click="selectStatus('Listo para despacho')">Listo para despacho</div>
              <div class="dropdown-item" @click="selectStatus('En despacho')">En despacho</div>
              <div class="dropdown-item" @click="selectStatus('Entregado')">Entregado</div>
              <div class="dropdown-item" @click="selectStatus('Pendiente')">Pendiente</div>
              <div class="dropdown-item" @click="selectStatus('Cancelado')">Cancelado</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item" @click="selectStatus('Por pagar')">Por pagar</div>
              <div class="dropdown-item" @click="selectStatus('Pagada')">Pagada</div>
            </div>
          </div>

          <div class="dropdown-container">
            <button class="btn-secondary" @click.stop="toggleDateDropdown">
              <Calendar :size="18" />
              <span>{{ dateFilterLabel }}</span>
              <ChevronDown :size="16" />
            </button>
            
            <div class="dropdown-menu" v-if="isDateDropdownOpen">
              <div class="dropdown-item" @click="selectDateFilter('all', 'Todas las fechas')">Todas las fechas</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item" @click="selectDateFilter('last30', 'Últimos 30 días')">Últimos 30 días</div>
              <div class="dropdown-item" @click="selectDateFilter('last3months', 'Últimos 3 meses')">Últimos 3 meses</div>
            </div>
          </div>
        </div>

        <div class="actions-right">
          <button class="btn-export" @click="exportarExcel">
            <Download :size="18" />
            <span>Exportar</span>
          </button>
        </div>
      </div>

      <table class="orders-table">
        <thead>
          <tr>
            <th @click="sortBy('id')">
              <div class="header-content">
                ID pedido <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'id' }" />
              </div>
            </th>
            <th @click="sortBy('distributor')">
              <div class="header-content">
                Distribuidor <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'distributor' }" />
              </div>
            </th>
            <th @click="sortBy('status')">
              <div class="header-content">
                Estado <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'status' }" />
              </div>
            </th>
            <th @click="sortBy('date')">
              <div class="header-content">
                Fecha de ingreso <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'date' }" />
              </div>
            </th>
            <th @click="sortBy('total')">
              <div class="header-content">
                Total <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'total' }" />
              </div>
            </th>
            <th v-if="activeTab === 'pagos'" @click="sortBy('pagoStatus')">
              <div class="header-content">
                Estado Pago <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'pagoStatus' }" />
              </div>
            </th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="7">
              <div class="loading-container">
                <IceCream class="spinner" :size="80" color="#e4869f" />
                <p>Cargando Pedidos...</p>
              </div>
            </td>
          </tr>
          <tr v-else-if="sortedOrders.length === 0">
            <td colspan="6" class="text-center padding-large">
              <div class="empty-state">
                <Package :size="48" class="empty-icon" />
                <p>No se encontraron pedidos en esta sección.</p>
                <button @click="fetchOrders" class="btn-retry">Reintentar carga</button>
              </div>
            </td>
          </tr>
          <tr v-else v-for="order in paginatedOrders" :key="order.id">
            <td class="bold-text">#{{ order.id }}</td>
            <td class="bold-text">{{ order.distributor }}</td>
            <td>
              <span class="status-badge" :class="getStatusClass(order.status, order.rawStatusId)">
                {{ order.status }}
              </span>
            </td>
            <td>
              <div class="date-content">
                <CalendarIcon :size="18" class="date-icon" />
                <div class="date-time">
                  <span class="date">{{ order.date }}</span>
                  <span class="time">{{ order.time }}</span>
                </div>
              </div>
            </td>
            <td class="bold-text">${{ formatPrice(order.total) }}</td>
            <td v-if="activeTab === 'pagos'">
              <span class="status-badge" :class="getStatusClass(order.pagoStatus)">
                {{ order.pagoStatus }}
              </span>
            </td>
            <td>
              <div class="actions-content">
                <button 
                  class="btn-action btn-detail" 
                  :disabled="loadingOrderId === order.id"
                  @click="openModal(order.id)"
                >
                  <component 
                    :is="loadingOrderId === order.id ? Loader2 : Eye" 
                    :size="18" 
                    :class="{ 'spinner': loadingOrderId === order.id }" 
                  />
                  <span>{{ loadingOrderId === order.id ? 'Cargando...' : 'Detalle' }}</span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="7">
              <div class="pagination-container">
                <button 
                  class="btn-page" 
                  :disabled="currentPage === 1" 
                  @click="changePage(currentPage - 1)"
                >
                  Anterior
                </button>

                <span class="page-info">
                  Página {{ currentPage }} de {{ totalPages }}
                </span>

                <button 
                  class="btn-page" 
                  :disabled="currentPage >= totalPages" 
                  @click="changePage(currentPage + 1)"
                >
                  Siguiente
                </button>
              </div>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <OrdersDetailModal 
      v-if="selectedOrderId !== ''" 
      v-show="isModalOpen" 
      :order-id="selectedOrderId" 
      :distributor="selectedOrder?.distributor"
      :distributor-phone="selectedOrder?.distributorPhone"
      :status="selectedOrder?.status"
      :status-id="selectedOrder?.rawStatusId"
      :pago="selectedOrder?.pagoStatus"
      :pago-id="selectedOrder?.pagoId"
      :date="selectedOrder?.date"
      :time="selectedOrder?.time"
      :total="selectedOrder?.total"
      @loaded="handleModalLoaded"
      @close="closeModal" 
      @status-changed="fetchOrders"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import OrdersDetailModal from './OrdersDetailModal.vue';
import orderService from '@/services/orderService';
import distributorService from '@/services/distributorService';
import orderStatusService from '@/services/orderStatusService';
import { 
  ClipboardCheck, 
  Package, 
  Truck, 
  CheckCircle,
  Search,
  Filter,
  ChevronDown,
  Calendar,
  Calendar as CalendarIcon,
  Download,
  Eye,
  ChevronsUpDown,
  Loader2
} from 'lucide-vue-next';
import { IceCream } from 'lucide-vue-next'
import * as XLSX from 'xlsx';

const orders = ref<any[]>([]);
const isLoading = ref(true);
const activeTab = ref('pedidos');
const isExporting = ref(false);
import { useNotification } from '@/composables/useNotification';

const { notify } = useNotification();


// Mapa reactivo que se llenará EXCLUSIVAMENTE desde la BDD
const statusMap = ref<Map<number, string>>(new Map());

const extractTime = (timeString: string) => {
  if (!timeString) return '';
  
  // Si viene con formato completo "2024-05-12 14:26:00" o con la T de ISO "2024-05-12T14:26:00.000Z"
  if (timeString.length > 10) {
    const timePart = timeString.includes('T') ? timeString.split('T')[1] : timeString.split(' ')[1];
    return timePart ? timePart.substring(0, 5) : '';
  }
  
  // Si el backend envía solamente "14:26:00"
  return timeString.substring(0, 5);
};

const itemsPerPage = ref(10);
const currentPage = ref(1);

// Paginación: Cálculos
const totalPages = computed(() => Math.max(1, Math.ceil(filteredOrders.value.length / itemsPerPage.value)));

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return sortedOrders.value.slice(start, end);
});

// Paginación: Acción
const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};


const fetchOrders = async () => {
  isLoading.value = true;
  console.log('--- [DEBUG] INICIANDO CARGA COMPLETA ---');
  
  try {
    let ordersRes, distsRes, statsRes;
    
    try { ordersRes = await orderService.getOrders(); } catch (e) { console.error(e); }
    try { distsRes = await distributorService.getDistributors(); } catch (e) { console.error(e); }
    try { statsRes = await orderStatusService.getOrderStatuses(); } catch (e) { console.error(e); }

    const rawOrders = Array.isArray(ordersRes?.data) ? ordersRes.data : (ordersRes?.data?.data || []);
    const rawDists = Array.isArray(distsRes?.data) ? distsRes.data : (distsRes?.data?.data || []);
    const rawStats = Array.isArray(statsRes?.data) ? statsRes.data : (statsRes?.data?.data || []);

    statusMap.value = new Map(rawStats.map((s: any) => [Number(s.id), s.nombre_estado || s.nombre_estado_pedido]));
    const distMap = new Map(rawDists.map((d: any) => [Number(d.id), d.nombre_empresa]));
    const distPhoneMap = new Map(rawDists.map((d: any) => [Number(d.id), d.telefono || '']));


    const DEFAULT_NAMES: Record<number, string> = {
      1: 'Validación', 
      2: 'En preparación', 
      3: 'Listo para despacho', 
      4: 'En despacho', 
      5: 'Entregado', 
      6: 'Pendiente', 
      7: 'Cancelado'
    };

    orders.value = rawOrders.map((o: any) => {

      const statusId = Number(o.id_estado_pedido || o.id_estado || 1);
      const distId = Number(o.id_usuario_distribuidor || o.id_distribuidor || 0);

      const pagoId = Number(o.id_estado_pago || 1); 
      const pagoStatus = pagoId === 2 ? 'Pagada' : 'Por pagar'; 
      
      
      return {
        id: o.id,
        distributor: distMap.get(distId) || `Distribuidor #${distId}`,
        distributorPhone: distPhoneMap.get(distId) || o.telefono || o.distributorPhone || '',
        // Sincronización perfecta de strings:
        status: statusMap.value.get(statusId) || DEFAULT_NAMES[statusId] || `Estado #${statusId}`,
        pagoId: pagoId,         
        pagoStatus: pagoStatus, 
        total: Number(o.monto_final || o.total_pedido || o.total_cotizacion || 0),
        date: formatDate(o.fecha_creacion || o.fecha_pedido || o.created_at),
        time: extractTime(o.hora_creacion || o.created_at),
        rawStatusId: statusId
      };
    });

    console.log('Mapeo de grilla general refrescado con éxito:', orders.value);

  } catch (error) {
    console.error('Error crítico al refrescar la grilla:', error);
  } finally {
    isLoading.value = false;
  }
};

const exportarExcel = () => {
  if (orders.value.length === 0) {
    notify('No hay datos para exportar', 'error');
    return;
  }

  isExporting.value = true;
  try {
    // Función auxiliar para dar formato ordenado a las columnas en Excel
    const formatForExcel = (dataList: any[]) => {
      return dataList.map(o => ({
        'ID Pedido': o.id,
        'Distribuidor': o.distributor,
        'Teléfono': o.distributorPhone || 'N/A',
        'Estado Logístico': o.status,
        'Estado de Pago': o.pagoStatus,
        // Convertimos el string formateado "15.000" a un número puro para que Excel permita sumar la columna
        'Total ($)': parseFloat(o.total.toString().replace(/\./g, '')) || 0,
        'Fecha de Ingreso': o.date,
        'Hora': o.time
      }));
    };

    const workbook = XLSX.utils.book_new();

    // 1. HOJA: PEDIDOS
    // Usamos la misma lógica de tu pestaña "pedidos"
    const pedidos = orders.value.filter((o: any) => [1, 2, 3, 4, 5, 6].includes(o.rawStatusId));
    const sheetPedidos = XLSX.utils.json_to_sheet(formatForExcel(pedidos));
    XLSX.utils.book_append_sheet(workbook, sheetPedidos, "Pedidos");

    // 2. HOJA: PAGOS
    // Usamos la misma lógica de tu pestaña "pagos"
    const pagos = orders.value.filter((o: any) => [1, 2].includes(o.pagoId));
    const sheetPagos = XLSX.utils.json_to_sheet(formatForExcel(pagos));
    XLSX.utils.book_append_sheet(workbook, sheetPagos, "Pagos");

    // Generar archivo con nombre y fecha
    const dateStr = new Date().toLocaleDateString('es-CL').replace(/\//g, '-');
    XLSX.writeFile(workbook, `Reporte_General_DiCreme_${dateStr}.xlsx`);
    
    notify('Excel generado correctamente con ambas hojas', 'success');
  } catch (error) {
    console.error('Error al exportar a Excel:', error);
    notify('Hubo un error al exportar los datos', 'error');
  } finally {
    isExporting.value = false;
  }
};


const formatDate = (dateString: string) => {
  if (!dateString) return 'Sin fecha';
  const cleanDate = dateString.includes('T') ? dateString.split('T')[0] : dateString;
  if (!cleanDate) return 'Sin fecha';
  const parts = cleanDate.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return cleanDate;
};

const counts = computed(() => {
  // Pagos: Por pagar (6) y Pagada (7)
  const pagos = orders.value.filter((o: any) => [1, 2].includes(o.pagoId)).length;
  // Pedidos: Todo lo demás + Pagada (7) para que no se pierda el rastro
  const pedidos = orders.value.filter((o: any) => [1, 2, 3, 4, 6].includes(o.rawStatusId)).length;
  return { pagos, pedidos };
});

const stats = computed(() => {
  return {
    unpaid: orders.value.filter((o: any) => o.pagoId === 1).length,
    paid: orders.value.filter((o: any) => o.pagoId === 2).length,
    preparation: orders.value.filter((o: any) => o.rawStatusId === 2).length,
    shipping: orders.value.filter((o: any) => [3, 4].includes(o.rawStatusId)).length,
    delivered: orders.value.filter((o: any) => o.rawStatusId === 5).length
  };
});

const formatPrice = (price: number) => {
  return price.toLocaleString('es-CL');
};

const getStatusClass = (status: string, statusId?: number) => {
  // Si viene el ID numérico directo de PostgreSQL, es mucho más rápido y seguro validar por número:
  if (statusId) {
    switch (Number(statusId)) {
      case 1: return 'status-validation';  // Validacion
      case 2: return 'status-preparation'; // Preparacion
      case 3: return 'status-ready';       // Listo para Despacho
      case 4: return 'status-shipping';    // En Despacho
      case 5: return 'status-completed';   // Entregado
      case 6: return 'status-pending';     // Pendiente
      case 7: return 'status-cancelled';   // Cancelado
    }
  }

  // Respaldo por si evalúa mediante el string de texto:
  switch (status) {
    case 'Por pagar': return 'status-unpaid';
    case 'Pagada': return 'status-paid';
    case 'Validación': case 'En validación': return 'status-validation';
    case 'Preparación': case 'En preparación': return 'status-preparation';
    case 'Listo para despacho': case 'Listo para Despacho': return 'status-ready';
    case 'En despacho': case 'En Despacho': return 'status-shipping';
    case 'Entregado': return 'status-completed';
    case 'Pendiente': return 'status-pending';
    case 'Cancelado': return 'status-cancelled';
    default: return 'status-generic';
  }
};

const searchQuery = ref('');
const statusFilter = ref('all');
const dateFilterLabel = ref('Fecha: Últimos 30 días');

const isStatusDropdownOpen = ref(false);
const isDateDropdownOpen = ref(false);

const isModalOpen = ref(false);
const selectedOrderId = ref<number | string>('');
  const loadingOrderId = ref<number | string>(''); // Spinner del botón

const selectedOrder = computed(() => {
  return orders.value.find((o: any) => o.id === selectedOrderId.value);
});

const openModal = (id: number | string) => {
  loadingOrderId.value = id; // Activa el spinner en el botón de la tabla
  selectedOrderId.value = id; // Define cuál es el pedido, pero NO abre el modal aún
};

const handleModalLoaded = () => {
  loadingOrderId.value = ''; // Detiene el spinner
  isModalOpen.value = true;  // Abre el modal (v-show)
};

const closeModal = () => {
  isModalOpen.value = false;
  // Pequeño timeout para limpiar el ID después de la animación de cierre
  setTimeout(() => { selectedOrderId.value = ''; }, 300);
};
const toggleStatusDropdown = () => {
  isStatusDropdownOpen.value = !isStatusDropdownOpen.value;
  isDateDropdownOpen.value = false;
};

const toggleDateDropdown = () => {
  isDateDropdownOpen.value = !isDateDropdownOpen.value;
  isStatusDropdownOpen.value = false;
};

const normalizeStr = (str: string) => {
  if (!str) return '';
  return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
};

const selectStatus = (status: string) => {
  statusFilter.value = status;
  isStatusDropdownOpen.value = false;

  if (status === 'all') {
    activeTab.value = 'pedidos';
  }
};

const dateFilterType = ref('all');

const selectDateFilter = (type: string, label: string) => {
  dateFilterType.value = type;
  dateFilterLabel.value = label;
  isDateDropdownOpen.value = false;
};

const closeDropdowns = () => {
  isStatusDropdownOpen.value = false;
  isDateDropdownOpen.value = false;
};

// Filtering logic
const filteredOrders = computed(() => {
  let result = orders.value;

  // 1. Tab Filter
  if (activeTab.value === 'pagos') {
    result = result.filter((o: any) => [1, 2].includes(o.pagoId));
  } else {
    // Pedidos incluye todos los pedidos (1-7)
    result = result.filter((o: any) => [1, 2, 3, 4, 5, 6, 7].includes(o.rawStatusId));
  }

  // 2. Search Query (ID or Distributor)
  if (searchQuery.value) {
    const query = normalizeStr(searchQuery.value);
    result = result.filter((o: any) => 
      o.id.toString().includes(query) || 
      normalizeStr(o.distributor).includes(query)
    );
  }

  // 3. Status Filter (accent-insensitive)
  if (statusFilter.value !== 'all') {
    const targetStatus = normalizeStr(statusFilter.value);
    result = result.filter((o: any) => {
      const s = normalizeStr(o.status);
      const p = normalizeStr(o.pagoStatus);
      return s === targetStatus || p === targetStatus || s.includes(targetStatus) || targetStatus.includes(s);
    });
  }

  // 4. Date Filter
  if (dateFilterType.value !== 'all') {
    const now = new Date();
    result = result.filter((o: any) => {
      if (!o.date) return true;
      const parts = o.date.split('/');
      if (parts.length === 3) {
        const oDate = new Date(Number(parts[2]), Number(parts[1]) - 1, Number(parts[0]));
        const diffDays = (now.getTime() - oDate.getTime()) / (1000 * 3600 * 24);
        if (dateFilterType.value === 'last30') return diffDays <= 30;
        if (dateFilterType.value === 'last3months') return diffDays <= 90;
      }
      return true;
    });
  }

  return result;
});

const sortConfig = ref({
  key: '',
  direction: 'asc'
});

const sortBy = (key: string) => {
  if (sortConfig.value.key === key) {
    sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
  } else {
    sortConfig.value.key = key;
    sortConfig.value.direction = 'asc';
  }
};

const sortedOrders = computed(() => {
  const dataToSort = filteredOrders.value;
  if (!sortConfig.value.key) return dataToSort;

  return [...dataToSort].sort((a: any, b: any) => {
    let aValue = a[sortConfig.value.key];
    let bValue = b[sortConfig.value.key];

    if (sortConfig.value.key === 'date') {
      const parseDate = (d: string, t: string) => {
        if (!d) return 0;
        const parts = d.split('/').map(Number);
        const day = parts[0] || 1;
        const month = parts[1] || 1;
        const year = parts[2] || 1970;
        const timeParts = (t || '00:00').split(':').map(Number);
        const hours = timeParts[0] || 0;
        const minutes = timeParts[1] || 0;
        return new Date(year, month - 1, day, hours, minutes).getTime();
      };
      aValue = parseDate(a.date, a.time);
      bValue = parseDate(b.date, b.time);
    }

    if (aValue < bValue) return sortConfig.value.direction === 'asc' ? -1 : 1;
    if (aValue > bValue) return sortConfig.value.direction === 'asc' ? 1 : -1;
    return 0;
  });
});

onMounted(async () => {
  window.addEventListener('click', closeDropdowns);
  await fetchOrders();
});

onUnmounted(() => {
  window.removeEventListener('click', closeDropdowns);
});


watch([searchQuery, statusFilter, activeTab], () => {
  currentPage.value = 1;
});

</script>

<style scoped>
.orders-container {
  padding: 40px 20px;
  font-family: 'Inter', sans-serif;
  background-color: #f8f9fa;
  min-height: calc(100vh - 80px);
}

.orders-header {
  max-width: 1200px;
  margin: 0 auto 30px auto;
}

.orders-title {
  font-size: 1.8rem;
  font-weight: 800;
  color: #322c44;
  margin: 0;
}

.orders-description {
  font-size: 0.95rem;
  color: #666;
  margin-top: 4px;
}

.status-cards {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 15px;
  margin: 0 auto 40px auto;
  max-width: 1200px;
}

.status-card {
  background-color: white;
  border-radius: 16px;
  padding: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  border: 1px solid #e9ecef;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.status-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.card-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.icon-box {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-unpaid { background-color: #fff0f3; color: #e4869f; }
.bg-paid { background-color: #ebfbee; color: #37b24d; }
.bg-preparation { background-color: #e7f5ff; color: #1c7ed6; }
.bg-shipping { background-color: #dcd5ff; color: #6741d9; }
.bg-delivered { background-color: #e6fffa; color: #087f5b; }

.card-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #322c44;
}

.card-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.card-count {
  font-size: 1.6rem;
  font-weight: 800;
  color: #322c44;
  line-height: 1;
}

.card-subtext {
  font-size: 0.7rem;
  font-weight: 500;
  color: #868e96;
  margin-top: 2px;
}

.browser-tabs {
  display: flex;
  gap: 4px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 24px;
  background-color: #ddd;
  border: 1px solid #ddd;
  border-bottom: none;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
  cursor: pointer;
  font-family: 'Inter', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  color: #777;
  transition: all 0.2s ease;
  position: relative;
  top: 1px;
  z-index: 1;
}

.tab-btn:hover:not(.active) {
  background-color: #e8e8e8;
  color: #555;
}

.tab-btn.active {
  background-color: white;
  color: #e4869f;
  border-color: #ddd;
  padding-bottom: 13px;
  top: 2px;
}

.tab-count {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  background-color: #000;
  color: white;
  border-radius: 6px;
  font-size: 0.75rem;
}

.tab-btn.active .tab-count {
  background-color: #e4869f;
}

.main-table-card {
  background-color: white;
  border-radius: 0 16px 16px 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border: 1px solid #e9ecef;
  overflow: hidden;
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
  z-index: 2;
}

.table-actions {
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.actions-left {
  display: flex;
  gap: 12px;
  flex: 1;
}

.search-box {
  position: relative;
  width: 100%;
  max-width: 400px;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #adb5bd;
}

.search-box input {
  width: 100%;
  padding: 12px 12px 12px 42px;
  border-radius: 10px;
  border: 1px solid #dee2e6;
  font-size: 0.9rem;
  color: #495057;
  outline: none;
  transition: border-color 0.2s;
}

.search-box input:focus {
  border-color: #e4869f;
}

.dropdown-container {
  position: relative;
}

.btn-secondary {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background-color: white;
  border: 1px solid #dee2e6;
  border-radius: 10px;
  color: #495057;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background-color: #f8f9fa;
  border-color: #ced4da;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border: 1px solid #e9ecef;
  min-width: 220px;
  z-index: 100;
  padding: 8px;
}

.dropdown-item {
  padding: 10px 16px;
  font-size: 0.85rem;
  font-weight: 500;
  color: #495057;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.2s;
}

.dropdown-item:hover {
  background-color: #fff0f3;
  color: #e4869f;
}

.dropdown-divider {
  height: 1px;
  background-color: #e9ecef;
  margin: 6px 0;
}

.btn-export {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 20px;
  background-color: white;
  border: 1.5px solid #e4869f;
  border-radius: 10px;
  color: #e4869f;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-export:hover {
  background-color: #fff0f3;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.orders-table th {
  padding: 16px 20px;
  text-align: left;
  border-top: 1px solid #dee2e6;
  border-bottom: 2px solid #ddd;
  background-color: #e5e5e5 !important;
  color: #777777 !important;
  font-weight: 700 !important;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  user-select: none;
}

.orders-table th.text-center {
  text-align: center;
}

.orders-table td {
  padding: 20px;
  text-align: left;
  border-bottom: 1px solid #f1f3f5;
  font-size: 0.9rem;
  color: #495057;
}

.bold-text {
  font-weight: 700;
  color: #322c44;
}

.status-badge {
  padding: 5px 14px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  line-height: 1.2;
}

.status-unpaid { background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }
.status-paid { background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.status-validation { background-color: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.status-preparation { background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.status-ready { background-color: #f3e8ff; color: #7c3aed; border: 1px solid #ddd6fe; }
.status-shipping { background-color: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
.status-completed { background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.status-pending { background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
.status-cancelled { background-color: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
.status-generic { background-color: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }

.date-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.date-time {
  display: flex;
  flex-direction: column;
}

.date {
  font-weight: 600;
  color: #495057;
}

.time {
  font-size: 0.75rem;
  color: #9793a0;
}

.date-icon {
  color: #adb5bd;
}

.actions-content {
  display: flex;
  justify-content: center;
}

.btn-action {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid #dee2e6;
  background-color: white;
  color: #495057;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action:hover {
  background-color: #f8f9fa;
  border-color: #ced4da;
}

.padding-large {
  padding: 60px !important;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
  color: #9793a0;
  font-weight: 600;
}



@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
  color: #9793a0;
}

.empty-icon {
  color: #eeedee;
}

.btn-retry {
  padding: 8px 20px;
  background-color: #e4869f;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-retry:hover {
  background-color: #d6758e;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
  cursor: pointer;
}

.sort-icon {
  color: #999;
  transition: color 0.2s;
}

.active-sort {
  color: #322c44 !important;
}

.orders-table th:hover .sort-icon {
  color: #666;
}


.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 0;
  gap: 15px;
  font-weight: 600;
  color: #e4869f;
}

.spinner {
  animation: spin 1.5s linear infinite;
  margin-bottom: 10px;
}

.pagination-container {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  padding: 20px;
  background-color: #fff;
  border-top: 1px solid #dee2e6;
}

.page-info {
  font-size: 0.85rem;
  font-weight: 600;
  color: #495057;
}

.btn-page {
  padding: 8px 16px;
  background-color: white;
  border: 1px solid #e4869f;
  color: #e4869f;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-page:hover:not(:disabled) {
  background-color: #e4869f;
  color: white;
}

.btn-page:disabled {
  border-color: #dee2e6;
  color: #adb5bd;
  cursor: not-allowed;
  background-color: #f8f9fa;
}
</style>