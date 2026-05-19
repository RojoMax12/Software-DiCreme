<template>
  <div class="orders-container">
    <header class="orders-header">
      <h1 class="orders-title">Pedidos</h1>
      <p class="orders-description">Gestiona y Monitorea todos los pedidos ingresados por los distribuidores.</p>
    </header>

    <div class="status-cards">
      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-validation">
            <ClipboardCheck :size="24" />
          </div>
          <span class="card-label">En validación</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.validation }}</span>
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
              <div class="dropdown-item" @click="selectStatus('En validación')">En validación</div>
              <div class="dropdown-item" @click="selectStatus('En preparación')">En preparación</div>
              <div class="dropdown-item" @click="selectStatus('En despacho')">En despacho</div>
              <div class="dropdown-item" @click="selectStatus('Entregado')">Entregado</div>
            </div>
          </div>

          <div class="dropdown-container">
            <button class="btn-secondary" @click.stop="toggleDateDropdown">
              <Calendar :size="18" />
              <span>{{ dateFilterLabel }}</span>
              <ChevronDown :size="16" />
            </button>
            
            <div class="dropdown-menu" v-if="isDateDropdownOpen">
              <div class="dropdown-item" @click="selectDateFilter('last30', 'Fecha: Últimos 30 días')">Últimos 30 días</div>
              <div class="dropdown-item" @click="selectDateFilter('last3months', 'Fecha: Últimos 3 meses')">Últimos 3 meses</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item" @click="selectDateFilter('custom', 'Rango personalizado')">Rango personalizado</div>
            </div>
          </div>
        </div>

        <div class="actions-right">
          <button class="btn-export">
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
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in sortedOrders" :key="order.id">
            <td class="bold-text">#{{ order.id }}</td>
            <td class="bold-text">{{ order.distributor }}</td>
            <td>
              <span class="status-badge" :class="getStatusClass(order.status)">
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
            <td>
              <div class="actions-content">
                <button class="btn-action btn-detail" @click="openModal(order.id)">
                  <Eye :size="18" />
                  <span>Detalle</span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <OrdersDetailModal 
      v-if="isModalOpen" 
      :order-id="selectedOrderId" 
      :distributor="selectedOrder?.distributor"
      :status="selectedOrder?.status"
      :date="selectedOrder?.date"
      :time="selectedOrder?.time"
      :total="selectedOrder?.total"
      @close="closeModal" 
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import OrdersDetailModal from './OrdersDetailModal.vue';
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
  ChevronsUpDown
} from 'lucide-vue-next';

const stats = ref({
  validation: 0,
  preparation: 0,
  shipping: 0,
  delivered: 0
});

const orders = ref<any[]>([]);

const formatPrice = (price: number) => {
  return price.toLocaleString('es-CL');
};

const getStatusClass = (status: string) => {
  switch (status) {
    case 'En validación': return 'status-validation';
    case 'En preparación': return 'status-preparation';
    case 'En despacho': return 'status-shipping';
    case 'Entregado': return 'status-completed';
    default: return '';
  }
};

const searchQuery = ref('');
const statusFilter = ref('all');
const dateFilterLabel = ref('Fecha: Últimos 30 días');

const isStatusDropdownOpen = ref(false);
const isDateDropdownOpen = ref(false);

const isModalOpen = ref(false);
const selectedOrderId = ref<number | string>('');

const selectedOrder = computed(() => {
  return orders.value.find((o: any) => o.id === selectedOrderId.value);
});

const openModal = (id: number | string) => {
  selectedOrderId.value = id;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const toggleStatusDropdown = () => {
  isStatusDropdownOpen.value = !isStatusDropdownOpen.value;
  isDateDropdownOpen.value = false;
};

const toggleDateDropdown = () => {
  isDateDropdownOpen.value = !isDateDropdownOpen.value;
  isStatusDropdownOpen.value = false;
};

const selectStatus = (status: string) => {
  statusFilter.value = status;
  isStatusDropdownOpen.value = false;
};

const selectDateFilter = (type: string, label: string) => {
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

  // 1. Search Query (ID or Distributor)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((o: any) => 
      o.id.toString().includes(query) || 
      o.distributor.toLowerCase().includes(query)
    );
  }

  // 2. Status Filter
  if (statusFilter.value !== 'all') {
    result = result.filter((o: any) => o.status === statusFilter.value);
  }

  // Note: Date filter logic can be added here once we have real dates or more mock data

  return result;
});

// Sorting logic
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

onMounted(() => {
  window.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
  window.removeEventListener('click', closeDropdowns);
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
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin: 0 auto 40px auto;
  max-width: 1200px;
}

.status-card {
  background-color: white;
  border-radius: 16px;
  padding: 20px;
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
  gap: 16px;
}

.icon-box {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-validation { background-color: #fff4e6; color: #fd7e14; }
.bg-preparation { background-color: #e7f5ff; color: #1c7ed6; }
.bg-shipping { background-color: #dcd5ff; color: #6741d9; }
.bg-delivered { background-color: #ebfbee; color: #37b24d; }

.card-label {
  font-size: 1rem;
  font-weight: 700;
  color: #322c44;
}

.card-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.card-count {
  font-size: 2rem;
  font-weight: 800;
  color: #322c44;
  line-height: 1;
}

.card-subtext {
  font-size: 0.8rem;
  font-weight: 500;
  color: #868e96;
  margin-top: 4px;
}

.main-table-card {
  background-color: white;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border: 1px solid #e9ecef;
  overflow: hidden;
  max-width: 1200px;
  margin: 0 auto;
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
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  display: inline-block;
}

.status-validation { background-color: #fff4e6; color: #fd7e14; border: 1px solid #fd7e14; }
.status-preparation { background-color: #e7f5ff; color: #1c7ed6; border: 1px solid #1c7ed6; }
.status-shipping { background-color: #dcd5ff; color: #6741d9; border: 1px solid #6741d9; }
.status-completed { background-color: #ebfbee; color: #37b24d; border: 1px solid #37b24d; }

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
</style>