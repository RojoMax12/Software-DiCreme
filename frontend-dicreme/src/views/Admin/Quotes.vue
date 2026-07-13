<template>
  <div class="orders-container">
    <div class="filter-card">
      <div class="filter-row filter-row-actions">
        <div class="filter-actions">
          <div class="search-box">
            <Search :size="18" class="search-icon" />
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Buscar por Distribuidor o ID..."
            />
          </div>

          <div class="dropdown-container">
            <button class="btn-filter" @click.stop="toggleStatusDropdown">
              <Filter :size="18" />
              <span>{{ statusFilter === 'all' ? 'Todos los estados' : statusFilter }}</span>
              <ChevronDown :size="16" />
            </button>
            
            <div class="dropdown-menu" v-if="isStatusDropdownOpen">
              <div class="dropdown-item" @click="selectStatus('all')">Todos los estados</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item" @click="selectStatus('Por Tomar')">Por Tomar</div>
              <div class="dropdown-item" @click="selectStatus('En Revision')">En Revision</div>
              <div class="dropdown-item" @click="selectStatus('Completado')">Completado</div>
              <div class="dropdown-item" @click="selectStatus('Cancelado')">Cancelado</div>
            </div>
          </div>
          <button class="btn-export" @click="exportarExcel" :disabled="isExporting">
            <Download :size="18" />
            <span>{{ isExporting ? 'Exportando...' : 'Exportar' }}</span>
          </button>
        </div>
      </div>

      <div class="summary-cards">
        <div class="summary-card">
          <div class="summary-left">
            <div class="summary-icon-box bg-pending">
              <ClockAlert :size="20" />
            </div>
            <div class="summary-text">
              <div class="summary-label">Pendientes de revisión</div>
              <div class="summary-description">Cotizaciones sin asignar</div>
            </div>
          </div>
          <span class="summary-value">{{ stats.pending }}</span>
        </div>
        <div class="summary-card">
          <div class="summary-left">
            <div class="summary-icon-box bg-review">
              <FileSearch :size="20" />
            </div>
            <div class="summary-text">
              <div class="summary-label">En revisión</div>
              <div class="summary-description">Cotizaciones asignadas</div>
            </div>
          </div>
          <span class="summary-value">{{ stats.inReview }}</span>
        </div>
        <div class="summary-card">
          <div class="summary-left">
            <div class="summary-icon-box bg-total">
              <LayoutGrid :size="20" />
            </div>
            <div class="summary-text">
              <div class="summary-label">Total actuales</div>
              <div class="summary-description">Cotizaciones activas</div>
            </div>
          </div>
          <span class="summary-value">{{ stats.totalActual }}</span>
        </div>
      </div>
    </div>

    <div class="browser-tabs">
      <button 
        class="tab-btn" 
        :class="{ active: activeFilter === 'actual' }"
        @click="activeFilter = 'actual'"
      >
        <span>Cotizaciones actuales</span>
        <span class="tab-count">{{ counts.actual }}</span>
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: activeFilter === 'completed' }"
        @click="activeFilter = 'completed'"
      >
        <span>Cotizaciones completadas</span>
        <span class="tab-count">{{ counts.completed }}</span>
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: activeFilter === 'cancelled' }"
        @click="activeFilter = 'cancelled'"
      >
        <span>Cotizaciones canceladas</span>
        <span class="tab-count">{{ counts.cancelled }}</span>
      </button>
    </div>
    
    <div class="table-card">
      <table class="orders-table">
        <thead>
          <tr>
            <th @click="sortBy('id')">
              <div class="header-content">
                ID <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'id' }" />
              </div>
            </th>
            <th @click="sortBy('distributor')">
              <div class="header-content">
                DISTRIBUIDOR <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'distributor' }" />
              </div>
            </th>
            <th @click="sortBy('managedBy')">
              <div class="header-content">
                GESTIONADO POR <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'managedBy' }" />
              </div>
            </th>
            <th @click="sortBy('status')">
              <div class="header-content">
                ESTADO <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'status' }" />
              </div>
            </th>
            <th @click="sortBy('total')">
              <div class="header-content">
                TOTAL <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'total' }" />
              </div>
            </th>
            <th @click="sortBy('date')">
              <div class="header-content">
                FECHA <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'date' }" />
              </div>
            </th>
            <th class="text-center">ACCIONES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="7">
              <div class="loading-container">
                <IceCream class="spinner" :size="80" color="#e4869f" />
                <p>Cargando cotizaciones...</p>
              </div>
            </td>
          </tr>
          <tr v-for="order in paginatedOrders" :key="order.id">
            <td class="bold-text">#{{ order.id }}</td>
            <td class="bold-text">{{ order.distributor }}</td>
            <td>
              <div class="user-content">
                <div 
                  class="user-icon-circle"
                  :class="{
                    'case-unassigned': !order.managedBy,
                    'case-others': order.managedBy && order.managedBy.id !== currentUser.id,
                    'case-me': order.managedBy && order.managedBy.id === currentUser.id
                  }"
                >
                  <User :size="14" />
                </div>
                <span>{{ order.managedBy ? order.managedBy.name : 'Sin asignar' }}</span>
              </div>
            </td>
            <td>
              <span class="status-badge" :class="getStatusClass(order.status)">
                {{ order.status }}
              </span>
            </td>
            <td class="bold-text">${{ order.total }}</td>
            <td>
              <div class="date-content">
                <Calendar :size="18" class="date-icon" />
                <div class="date-time">
                  <span class="date">{{ order.date }}</span>
                  <span class="time">{{ order.time }}</span>
                </div>
              </div>
            </td>
            <td>
              <div class="actions-content">
                <!-- Solo mostramos Tomar/Dejar si la cotización está activa -->
                <template v-if="order.status !== 'Completado' && order.status !== 'Cancelado'">
                  <button 
                    v-if="order.managedBy?.id === currentUser.id"
                    class="btn-action btn-leave" 
                    @click="leaveQuote(order.id)"
                  >
                    <UserMinus :size="18" />
                    <span>Dejar</span>
                  </button>
                  <button 
                    v-else
                    class="btn-action btn-take" 
                    :disabled="order.status !== 'Por Tomar'"
                    @click="takeQuote(order.id)"
                  >
                    <UserPlus :size="18" />
                    <span>Tomar</span>
                  </button>
                </template>

                <!-- El botón de detalle siempre se muestra, pero el ícono cambia a "Ojo" si está completado -->
                <button 
                  class="btn-action btn-detail" 
                  :class="{ 'btn-detail-managed': order.managedBy }"
                  :disabled="loadingOrderId === order.id" 
                  @click="openModal(order.id)"
                >
                  <component 
                    :is="loadingOrderId === order.id ? Loader2 : (order.managedBy?.id === currentUser.id && order.status !== 'Completado' && order.status !== 'Cancelado') ? Pencil : Eye" 
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
                <div class="pagination">
                  <button 
                    class="btn-pagination" 
                    :disabled="currentPage === 1"
                    @click="changePage(currentPage - 1)"
                  >
                    Anterior
                  </button>

                  <span class="page-info">
                  Página {{ currentPage }} de {{ totalPages }}
                  </span>
                
                  <button 
                  class="btn-pagination" 
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

    <QuotesDetailModal 
      v-if="selectedOrderId !== ''" 
      v-show="isModalOpen"
      :order-id="selectedOrderId" 
      :status="selectedOrder?.status"
      :distributor="selectedOrder?.distributor"
      :distributor-phone="selectedOrder?.distributorPhone" 
      :managed-by="selectedOrder?.managedBy?.name"
      :managed-by-id="selectedOrder?.managedBy?.id"
      :date="selectedOrder?.date"
      :time="selectedOrder?.time"
      @loaded="handleModalLoaded"
      @close="closeModal" 
      @cancel="handleModalCancel"
      @complete="handleModalComplete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { IceCream } from 'lucide-vue-next'
import QuotesDetailModal from './QuotesDetailModal.vue';
import quoteService from '@/services/quoteService';
import distributorService from '@/services/distributorService';
import userService from '@/services/userService';
import quotationStatusService from '@/services/quotationStatusService';
import { 
  ChevronsUpDown, 
  UserPlus, 
  UserMinus,
  Eye, 
  Pencil,
  User, 
  Calendar, 
  ChevronLeft, 
  ChevronRight,
  Search,
  Filter,
  ChevronDown,
  ClockAlert,
  FileSearch,
  LayoutGrid,Download,
  Loader2
} from 'lucide-vue-next';
import { useNotification } from '@/composables/useNotification'; // Importamos el composable de notificaciones
import * as XLSX from 'xlsx';


// Pagination logic
const itemsPerPage = 10;
const currentPage = ref(1);
const totalPages = computed(() => Math.max(1, Math.ceil(totalResults.value / itemsPerPage)));
const isExporting = ref(false);


// Filter logic
const activeFilter = ref('actual'); // 'actual' or 'completed'
const searchQuery = ref('');
const statusFilter = ref('all');
const isStatusDropdownOpen = ref(false);


// Modal state
const isModalOpen = ref(false);
const selectedOrderId = ref<number | string>('');
const loadingOrderId = ref<number | string>(''); // <--- Esta es clave para el botón

const { notify } = useNotification(); // Extraemos la función de notificación del composable

const exportarExcel = () => {
  // Validamos contra la lista total (orders), no la filtrada
  if (orders.value.length === 0) {
    notify('No hay datos para exportar', 'error');
    return;
  }

  isExporting.value = true;
  try {
    // Función auxiliar para formatear las columnas del Excel
    const formatForExcel = (dataList: any[]) => {
      return dataList.map(o => ({
        'ID Cotización': o.id,
        'Distribuidor': o.distributor,
        'Teléfono': o.distributorPhone || 'N/A',
        'Gestionado Por': o.managedBy ? o.managedBy.name : 'Sin asignar',
        'Estado': o.status,
        'Fecha': o.date,
        'Hora': o.time,
        // Convertimos el string formateado "15.000" a número puro
        'Total ($)': parseFloat(o.total.replace(/\./g, '')) || 0
      }));
    };

    const workbook = XLSX.utils.book_new();

    // 1. Hoja: Cotizaciones Actuales (Por tomar y En revisión)
    const actuales = orders.value.filter((o: any) => ['Por Tomar', 'En Revision'].includes(o.status));
    const sheetActuales = XLSX.utils.json_to_sheet(formatForExcel(actuales));
    XLSX.utils.book_append_sheet(workbook, sheetActuales, "Actuales");

    // 2. Hoja: Cotizaciones Completadas
    const completadas = orders.value.filter((o: any) => o.status === 'Completado');
    const sheetCompletadas = XLSX.utils.json_to_sheet(formatForExcel(completadas));
    XLSX.utils.book_append_sheet(workbook, sheetCompletadas, "Completadas");

    // 3. Hoja: Cotizaciones Canceladas
    const canceladas = orders.value.filter((o: any) => o.status === 'Cancelado');
    const sheetCanceladas = XLSX.utils.json_to_sheet(formatForExcel(canceladas));
    XLSX.utils.book_append_sheet(workbook, sheetCanceladas, "Canceladas");

    // 4. Guardar archivo con nombre descriptivo
    const dateStr = new Date().toLocaleDateString('es-CL').replace(/\//g, '-');
    XLSX.writeFile(workbook, `Reporte_General_Cotizaciones_${dateStr}.xlsx`);
    
    notify('Excel generado correctamente con las 3 categorías', 'success');
  } catch (error) {
    console.error('Error al exportar:', error);
    notify('Hubo un error al exportar los datos', 'error');
  } finally {
    isExporting.value = false;
  }
};


const selectedOrder = computed(() => {
  return orders.value.find((o: any) => o.id === selectedOrderId.value);
});

const prefetchedData = ref(new Map());

const prefetchOrder = (id: number | string) => {
  if (prefetchedData.value.has(id)) return;
  quoteService.getQuoteProducts(Number(id)).then(res => {
    prefetchedData.value.set(id, res.data);
  });
};

const openModal = (id: number | string) => {
  loadingOrderId.value = id; // Activa el spinner en el botón presionado
  selectedOrderId.value = id;
};

const handleModalLoaded = () => {
  loadingOrderId.value = ''; // Quita el spinner
  isModalOpen.value = true;  // Abre el modal solo cuando está listo
};

const closeModal = () => {
  isModalOpen.value = false;
  setTimeout(() => {
    selectedOrderId.value = ''; 
  }, 300);
};

const handleModalCancel = async () => {
  closeModal();
  await fetchQuotes();
};

const handleModalComplete = async () => {
  closeModal();
  await fetchQuotes();
};

const toggleStatusDropdown = () => {
  isStatusDropdownOpen.value = !isStatusDropdownOpen.value;
};

const selectStatus = (status: string) => {
  statusFilter.value = status;
  isStatusDropdownOpen.value = false;
};

// Close dropdown on click outside
const closeDropdown = (e: MouseEvent) => {
  const target = e.target as HTMLElement;
  if (!target.closest('.dropdown-container')) {
    isStatusDropdownOpen.value = false;
  }
};

const currentUser = ref({ id: 0, name: '' });
const orders = ref<any[]>([]);
const isLoading = ref(true);

const fetchQuotes = async () => {
  isLoading.value = true;
  try {
    const [quotesRes, distsRes, usersRes, statsRes] = await Promise.all([
      quoteService.getQuotes(),
      distributorService.getDistributors(),
      userService.getUsers(),
      quotationStatusService.getStatuses()
    ]);

    // MAPA 1 (Intacto): Sigue guardando solo el nombre para no romper nada en el resto del software
    const distMap = new Map(distsRes.data.map((d: any) => [d.id, d.nombre_empresa]));
    
    // 🚀 MAPA 2 (NUEVO): Guarda exclusivamente los teléfonos para esta implementación
    // Reemplaza 'telefono' por el nombre real de tu columna en PostgreSQL (ej: celular, telefono_contacto)
    const distPhoneMap = new Map(distsRes.data.map((d: any) => [d.id, d.telefono || '']));

    const userMap = new Map(usersRes.data.map((u: any) => [u.id, u.nombre_usuario]));
    const statMap = new Map(statsRes.data.map((s: any) => [s.id, s.nombre_estado]));

    orders.value = quotesRes.data.map((q: any) => ({
      id: q.id,
      distributor: distMap.get(q.id_distribuidor) || 'Desconocido', // 👈 Sigue funcionando igual que antes
      
      // 🚀 Extraemos el teléfono usando el mapa nuevo sin interferir con el anterior
      distributorPhone: distPhoneMap.get(q.id_distribuidor) || '', 
      
      managedBy: q.id_usuario_dicreme ? { id: q.id_usuario_dicreme, name: userMap.get(q.id_usuario_dicreme) } : null,
      status: statMap.get(q.id_estado_cotizacion) || 'Por Tomar',
      total: Number(q.total_cotizacion).toLocaleString('es-CL'),
      date: formatDate(q.fecha_creacion),
      time: q.hora_creacion ? q.hora_creacion.substring(0, 5) : '',
      rawStatus: q.id_estado_cotizacion
    }));

  } catch (error) {
    console.error('Error fetching data:', error);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString: string) => {
  if (!dateString) return '';
  const [year, month, day] = dateString.split('-');
  return `${day}/${month}/${year}`;
};

const takeQuote = async (quoteId: number) => {
  try {
    const responseValidacion = await quoteService.takeQuote(quoteId, currentUser.value.id);
    notify(responseValidacion?.data?.message || 'Cotización tomada exitosamente', 'success');

    await fetchQuotes();
  } catch (error: any) { // <--- Agregamos :any aquí
    notify(error.response?.data?.message || 'Error al tomar la cotización', 'error');
  } 
};

const leaveQuote = async (quoteId: number) => {
  try {
    const response = await quoteService.leaveQuote(quoteId, currentUser.value.id);
    notify(response?.data?.message || 'Cotización dejada exitosamente', 'success');
    
    await fetchQuotes();
  } catch (error: any) { // <--- Agregamos :any aquí
    notify(error.response?.data?.message || 'Error al dejar la cotización', 'error');
  }
};

onMounted(async () => {
  window.addEventListener('click', closeDropdown);
  
  const userStored = localStorage.getItem('user');
  if (userStored) {
    const userObj = JSON.parse(userStored);
    currentUser.value = {
      id: userObj.id,
      name: userObj.nombre_usuario || userObj.name
    };
  }
  
  await fetchQuotes();
});

onUnmounted(() => {
  window.removeEventListener('click', closeDropdown);
});

const counts = computed(() => {
  const actual = orders.value.filter((o: any) => ['Por Tomar', 'En Revision'].includes(o.status)).length;
  const completed = orders.value.filter((o: any) => o.status === 'Completado').length;
  const cancelled = orders.value.filter((o: any) => o.status === 'Cancelado').length;
  return { actual, completed, cancelled };
});

const stats = computed(() => {
  const pending = orders.value.filter((o: any) => o.status === 'Por Tomar').length;
  const inReview = orders.value.filter((o: any) => o.status === 'En Revision').length;
  const totalActual = pending + inReview;
  return { pending, inReview, totalActual };
});

const filteredOrders = computed(() => {
  let result = orders.value;

  // 1. Category Filter (Actual vs Completed vs Cancelled)
  if (activeFilter.value === 'actual') {
    result = result.filter((o: any) => ['Por Tomar', 'En Revision'].includes(o.status));
  } else if (activeFilter.value === 'completed') {
    result = result.filter((o: any) => o.status === 'Completado');
  } else if (activeFilter.value === 'cancelled') {
    result = result.filter((o: any) => o.status === 'Cancelado');
  }

  // 2. Search Query (ID or Distributor)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((o: any) => 
      o.id.toString().includes(query) || 
      o.distributor.toLowerCase().includes(query)
    );
  }

  // 3. Status Dropdown Filter
  if (statusFilter.value !== 'all') {
    result = result.filter((o: any) => o.status === statusFilter.value);
  }

  return result;
});

const totalResults = computed(() => filteredOrders.value.length);


// Sorting logic
const sortConfig = ref({
  key: 'id',
  direction: 'desc'
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
  const dataToSort = filteredOrders.value || [];
  if (!sortConfig.value.key) return dataToSort;

  return [...dataToSort].sort((a: any, b: any) => {
    let aValue = a[sortConfig.value.key];
    let bValue = b[sortConfig.value.key];

    if (sortConfig.value.key === 'managedBy') {
      aValue = aValue ? aValue.name : 'Sin asignar';
      bValue = bValue ? bValue.name : 'Sin asignar';
    }

    if (sortConfig.value.key === 'total') {
      aValue = parseFloat(aValue.replace(/\./g, ''));
      bValue = parseFloat(bValue.replace(/\./g, ''));
    }

    if (sortConfig.value.key === 'date') {
      const parseDate = (d: string, t: string) => {
        const [day, month, year] = d.split('/').map(Number);
        const [hours, minutes] = t.split(':').map(Number);
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

const getStatusClass = (status: string) => {
  switch (status) {
    case 'Por Tomar': return 'status-pending';
    case 'En Revision': return 'status-review';
    case 'Completado': return 'status-completed';
    case 'Cancelado': return 'status-cancelled';
    default: return '';
  }
};

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return sortedOrders.value.slice(start, end);
});

const changePage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

watch([searchQuery, statusFilter, activeFilter], () => {
  currentPage.value = 1;
});


</script>

<style scoped>
.orders-container {
  padding: 40px 20px;
  font-family: 'Inter', sans-serif;
  background-color: #eeedee;
  min-height: 100vh;
}

.filter-card {
  background-color: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  border: 1px solid #ddd;
  max-width: 1200px;
  margin: 0 auto 24px auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.filter-row {
  display: flex;
  width: 100%;
  align-items: center;
}

.filter-row-actions {
  justify-content: space-between;
}

.filter-actions {
  display: flex;
  gap: 12px;
  align-items: center;
  width: 100%;
  justify-content: flex-end;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
  background-color: #f1f3f4;
  border-radius: 10px;
  padding: 0 12px;
  border: 1px solid #ddd;
  width: 450px;
}

.search-icon {
  color: #777;
}

.search-box input {
  border: none;
  background: transparent;
  padding: 10px 8px;
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  color: #322c44;
  width: 100%;
  outline: none;
}

.btn-filter {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 10px;
  color: #322c44;
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-filter:hover {
  background-color: #f8f9fa;
  border-color: #ccc;
}

.dropdown-container {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border: 1px solid #ddd;
  width: 200px;
  z-index: 100;
  overflow: hidden;
  padding: 6px;
}

.dropdown-item {
  padding: 10px 16px;
  font-size: 0.85rem;
  font-weight: 500;
  color: #322c44;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.dropdown-item:hover {
  background-color: #fce4ec;
  color: #e4869f;
}

.dropdown-divider {
  height: 1px;
  background-color: #eee;
  margin: 6px 0;
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

.summary-cards {
  display: flex;
  gap: 16px;
}

.summary-card {
  flex: 1;
  background-color: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.summary-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.summary-text {
  display: flex;
  flex-direction: column;
}

.summary-icon-box {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-pending { 
  background-color: #fff9db; 
  color: #f08c00; 
}
.bg-review { 
  background-color: #e7f5ff; 
  color: #1c7ed6; 
}
.bg-total { 
  background-color: #e5dbff; 
  color: #7950f2; 
}

.summary-label {
  font-size: 0.9rem;
  font-weight: 800;
  color: #000;
  line-height: 1.2;
}

.summary-description {
  font-size: 0.8rem;
  font-weight: 500;
  color: #999;
  margin-top: 2px;
}

.summary-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #322c44;
}

.table-card {
  background-color: white;
  border-radius: 0 16px 16px 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  border: 1px solid #ddd;
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
  z-index: 2;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.orders-table th {
  padding: 16px 20px;
  text-align: left;
  border-bottom: 2px solid #ddd;
  background-color: #e5e5e5 !important;
  color: #777777 !important;
  font-weight: 700 !important;
  cursor: pointer;
  user-select: none;
  font-size: 0.75rem;
}

.orders-table th.text-center {
  text-align: center;
}

.orders-table td {
  padding: 24px 20px;
  text-align: left;
  border-bottom: 1px solid #ddd;
  font-size: 0.9rem;
}

.bold-text {
  font-weight: 700;
  color: #322c44;
}

.user-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-icon-circle {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid transparent;
}

.case-unassigned {
  background-color: #e5e5e5;
  color: #777777;
}

.case-others {
  background-color: transparent;
  border-color: #1c7ed6;
  color: #1c7ed6;
}

.case-me {
  background-color: transparent;
  border-color: #e4869f;
  color: #e4869f;
}

.date-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.date-icon {
  color: #777777;
}

.date-time {
  display: flex;
  flex-direction: column;
}

.time {
  font-size: 0.75rem;
  color: #9793a0;
}

.status-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  display: inline-block;
  border: 1px solid transparent;
}

.status-pending {
  background-color: #fff9db;
  color: #f08c00;
  border-color: #f08c00;
}

.status-review {
  background-color: #e7f5ff;
  color: #1c7ed6;
  border-color: #1c7ed6;
}

.status-completed {
  background-color: #ebfbee;
  color: #37b24d;
  border-color: #37b24d;
}

.status-cancelled {
  background-color: #fff5f5;
  color: #fa5252;
  border-color: #fa5252;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
}

.sort-icon {
  color: #999;
}

.actions-content {
  display: flex;
  justify-content: center;
  gap: 8px;
}

.btn-action {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 8px;
  border: none;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 105px;
}

.btn-take {
  background-color: #e4869f;
  color: white;
}

.btn-take:hover:not(:disabled) {
  background-color: #d1758e;
}

.btn-take:disabled {
  background-color: #e5e5e5;
  color: #777777;
  cursor: not-allowed;
  border: 1px solid #ddd;
}

.btn-leave {
  background-color: #fa5252;
  color: white;
}

.btn-leave:hover {
  background-color: #e03131;
}

.btn-detail {
  background-color: #e5e5e5;
  color: #777777;
  border: 1px solid #ddd;
}

.btn-detail:hover {
  background-color: #d8d8d8;
}

.btn-detail-managed {
  background-color: #fce4ec;
  color: #e4869f;
  border-color: #e4869f;
}

.btn-detail-managed:hover {
  background-color: #f8bbd0;
}

.orders-table tr:last-child td {
  border-bottom: none;
}

.orders-table tfoot td {
  border-top: 1px solid #ddd;
  padding: 16px 20px;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.results-info {
  color: #777777;
  font-size: 0.85rem;
  font-weight: 500;
}

.pagination {
   display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  padding: 20px;
  background-color: #fff;
  border-top: 1px solid #dee2e6;
}

.btn-pagination {
  padding: 8px 16px;
  background-color: white;
  border: 1px solid #e4869f;
  color: #e4869f;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-pagination:hover:not(:disabled) {
  background-color: #e4869f;
  color: white;
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 4px;
}

.page-num {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #777777;
  cursor: pointer;
  transition: all 0.2s ease;
}

.page-num:hover {
  background-color: #f8f9fa;
}

.page-num.active {
  background-color: #e4869f;
  color: white;
}

.btn-export {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background-color: #322c44; /* Color corporativo oscuro */
  border: 1px solid #322c44;
  border-radius: 10px;
  color: white;
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-export:hover:not(:disabled) {
  background-color: #4a445c;
  border-color: #4a445c;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(50, 44, 68, 0.15);
}

.btn-export:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  background-color: #9793a0;
  border-color: #9793a0;
}


@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.spinner {
  animation: spin 1s linear infinite;
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

.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from, .modal-fade-leave-to {
  opacity: 0;
}

.page-info {
  font-size: 0.85rem;
  font-weight: 600;
  color: #495057;
}

</style>  
