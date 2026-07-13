<template>
  <div class="admin-panel-box">
    <div class="panel-controls-bar">
      <div class="switch-container">
        <div class="switch-slider" :class="`pos-${activeFilter}`"></div>
        <button class="switch-btn" :class="{ active: activeFilter === 'admin' }" @click="activeFilter = 'admin'">Admins</button>
        <button class="switch-btn" :class="{ active: activeFilter === 'trabajador' }" @click="activeFilter = 'trabajador'">Trabajadores</button>
        <button class="switch-btn" :class="{ active: activeFilter === 'distribuidor' }" @click="activeFilter = 'distribuidor'">Distribuidores</button>
      </div>
      <button class="btn-primary" @click="openModal()">+ Añadir Usuario</button>
    </div>

    <hr class="panel-divider" />

    <div class="table-header-row">
      <div class="col-id">ID</div>
      <div class="col-user">Nombre</div>
      <div class="col-email">Correo</div>
      <div class="col-status">Estado</div>
      <div class="col-actions">Acciones</div>
    </div>

    <div class="users-list">
      <div v-for="user in paginatedUsers" :key="user.id" class="user-table-row">
        <div class="col-id">#{{ user.id }}</div>
        <div class="col-user-name">{{ user.nombre_usuario || user.nombre_empresa }}</div>
        <div class="col-email-text">{{ user.correo_electronico }}</div>
        
        <div class="col-status">
            <button class="toggle-status-btn" :class="{ 'is-active': isUserActive(user) }" @click="toggleEstado(user)">
                <span class="toggle-circle"></span>
                <span class="toggle-text">{{ isUserActive(user) ? 'Activo' : 'Inactivo' }}</span>
            </button>
        </div>
        
        <div class="col-actions-btns">
            <template v-if="activeFilter !== 'distribuidor'">
                <button class="btn-edit" @click="openModal(user)">Editar</button>
                <button class="btn-delete" @click="eliminarUsuario(user.id)">Eliminar</button>
            </template>
            <span v-else class="no-actions">Solo lectura</span>
        </div>
      </div>
    </div>

    <div class="pagination-container" v-if="totalPages > 1">
        <button class="btn-pagination" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
            Anterior
        </button>
        <span class="page-info">Página {{ currentPage }} de {{ totalPages }}</span>
        <button class="btn-pagination" :disabled="currentPage >= totalPages" @click="changePage(currentPage + 1)">
            Siguiente
        </button>
    </div>

    <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-header">
            <h3>{{ isEditing ? 'Editar Usuario' : 'Registrar Usuario' }}</h3>
            <button class="close-x" @click="closeModal">✕</button>
        </div>
        <form @submit.prevent="handleSaveUser">
          <div class="modal-body">
            <div class="form-group">
                <label>Nombre</label>
                <input v-model="form.nombre_usuario" required />
            </div>
            <div class="form-group">
                <label>Correo</label>
                <input v-model="form.correo_electronico" type="email" required />
            </div>
            <div class="form-group" v-if="!isEditing">
                <label>Contraseña</label>
                <input v-model="form.contrasena" type="password" required />
            </div>
            <div class="form-group">
                <label>Rol</label>
                <select v-model="form.id_rol" required>
                    <option value="1">Administrador</option>
                    <option value="2">Trabajador</option>
                    <option value="3">Distribuidor</option>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-secondary" @click="closeModal">Cancelar</button>
            <button type="submit" class="btn-submit">{{ isEditing ? 'Guardar Cambios' : 'Crear Usuario' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import userService from '@/services/userService';
import { ref, onMounted, reactive } from 'vue';
import { useNotification } from '@/composables/useNotification';
import distributorService from '@/services/distributorService';
import { watch, computed } from 'vue';

const usersAdmin = ref<any[]>([]);
const usersTrabajador = ref<any[]>([]);
const usersDistribuidor = ref<any[]>([]);
const isModalOpen = ref(false);
const { notify } = useNotification();
const itemsPerPage = 10; // Puedes ajustar este número
const currentPage = ref(1);

const activeFilter = ref('admin'); // 'admin', 'trabajador', 'distribuidor'
const isEditing = ref(false);
const editingId = ref<number | null>(null);
const isUserActive = (user: any) => user.estado_usuario === 'Activo' || user.estado_usuario == 1 || user.estado_usuario === true;

const form = reactive({
    nombre_usuario: '',
    correo_electronico: '',
    contrasena: '',
    id_rol: ''
});

const getFilteredUsers = () => {
    if (activeFilter.value === 'admin') return usersAdmin.value;
    if (activeFilter.value === 'trabajador') return usersTrabajador.value;
    return usersDistribuidor.value;
};

onMounted(() => {
    fetchUsers();
});


const toggleEstado = async (user: any) => {
    const estadoOriginal = user.estado_usuario;
    user.estado_usuario = !estadoOriginal; // Optimista

    try {
        // Detecta el servicio según el filtro
        if (activeFilter.value === 'distribuidor') {
            await distributorService.toggledistristatus(user.id);
        } else {
            await userService.toggleUserStatus(user.id);
        }
        notify("Estado actualizado", "success");
    } catch (e) {
        user.estado_usuario = estadoOriginal;
        notify("Error al actualizar", "error");
    }
};



const fetchUsers = async () => {
    try {
        const [uRes, dRes] = await Promise.all([userService.getUsers(), distributorService.getDistributors()]);
        usersAdmin.value = uRes.data.filter((u: any) => u.id_rol == 1);
        usersTrabajador.value = uRes.data.filter((u: any) => u.id_rol == 2);
        usersDistribuidor.value = dRes.data.filter((u: any) => u.id_rol == 3);
    } catch (e) { notify("Error cargando usuarios", "error"); }
};

const openModal = (user: any = null) => {
    isEditing.value = !!user;
    if (user) {
        editingId.value = user.id;
        form.nombre_usuario = user.nombre_usuario || user.nombre_empresa;
        form.correo_electronico = user.correo_electronico;
        form.id_rol = user.id_rol;
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.nombre_usuario = '';
    form.correo_electronico = '';
    form.contrasena = '';
    form.id_rol = '';
};

const handleSaveUser = async () => {
    try {
        isEditing.value ? await userService.updateuser(editingId.value, form) : await userService.createUser(form);
        notify("Guardado exitoso", "success");
        closeModal();
        fetchUsers();
    } catch (e) { notify("Error al guardar", "error"); }
};

const eliminarUsuario = async (id: number) => {
    if (confirm("¿Estás seguro de eliminar este usuario del sistema?")) {
        try {
            await userService.deleteUser(id);
            notify("Usuario eliminado correctamente.", "success");
            fetchUsers();
        } catch (error) {   
            console.error(error);
            notify("Error al eliminar el usuario.", "error");
        }
    }
};

const paginatedUsers = computed(() => {
    const list = getFilteredUsers(); // Usamos tu función existente
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return list.slice(start, end);
});

// 3. Cálculo de páginas totales
const totalPages = computed(() => {
    return Math.max(1, Math.ceil(getFilteredUsers().length / itemsPerPage));
});

// 4. Acción de cambio
const changePage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

watch(activeFilter, () => {
    currentPage.value = 1;
});
</script>

<style scoped>  
/* --- CAJA PRINCIPAL --- */
.admin-panel-box {
    width: 95%;
    max-width: 950px;
    margin: 40px auto;
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    padding: 30px;
    font-family: 'Inter', sans-serif;
}

.panel-controls-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.panel-divider {
    border: 0;
    height: 1px;
    background-color: #f0f0f0;
    margin: 25px 0 15px 0;
}

.content-header h2 {
    font-size: 1.25rem;
    color: #322c44;
    margin-bottom: 20px;
    font-weight: 700;
}

/* --- TABLA SIMÉTRICA REFACTORIZADA --- */
.table-header-row, .user-table-row {
    display: flex;
    align-items: center;
    padding: 14px 16px;
}

.table-header-row {
    background-color: #f8f9fa;
    border-radius: 8px 8px 0 0;
    font-weight: 700;
    color: #6c757d;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.user-table-row {
    background-color: #ffffff;
    border-bottom: 1px solid #f1f3f5;
    transition: background-color 0.2s ease;
    font-size: 0.95rem;
    color: #322c44;
}

.user-table-row:hover {
    background-color: #fffafb;
}

/* Rediseño de anchos para cuadrar al 100% exacto sin desfases */
.col-id { flex: 0 0 10%; font-weight: 600; color: #adb5bd; }
.col-user, .col-user-name { flex: 0 0 25%; font-weight: 500; }
.col-email, .col-email-text { flex: 0 0 30%; color: #495057; word-break: break-all; }
.col-status { flex: 0 0 15%; display: flex; align-items: center; } /* 🌟 Añadido para que cuadre el Toggle */
.col-actions, .col-actions-btns { flex: 0 0 20%; text-align: right; display: flex; justify-content: flex-end; }

/* --- REFACTORIZACIÓN DEL SWITCH FILTRO --- */
.switch-container {
    display: flex;
    background-color: #f1f3f5;
    padding: 4px;
    border-radius: 10px;
    position: relative;
    width: 100%;
    max-width: 360px;
}

.btn-edit { background-color: #f0f7ff; color: #007bff; border: 1px solid #cce5ff; padding: 6px 12px; border-radius: 6px; cursor: pointer; margin-right: 8px; }
.no-actions { font-size: 0.8rem; color: #adb5bd; font-style: italic; }

.switch-slider {
    position: absolute;
    top: 4px; bottom: 4px;
    width: calc(33.33% - 4px);
    background-color: #ffffff;
    border-radius: 7px;
    transition: transform 0.3s ease;
}

.switch-slider.slide-right { 
    transform: translateX(calc(100% + 4px)); 
}

.switch-btn {
    flex: 1; 
    display: flex; 
    align-items: center; 
    justify-content: center;
    padding: 10px 16px; 
    border: none; 
    background: transparent !important;
    cursor: pointer; 
    font-size: 0.85rem;
    font-weight: 600; 
    color: #6c757d; 
    position: relative; 
    z-index: 1;
    transition: color 0.2s ease;
}

.switch-btn.active { 
    color: #e4869f; 
    font-weight: 700;
}

/* --- ACCIONES Y BOTONES --- */
.btn-primary {
    background-color: #e4869f; 
    color: white; 
    border: none;
    padding: 10px 20px; 
    border-radius: 8px; 
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer; 
    transition: background-color 0.2s ease, transform 0.1s ease;
}

.btn-primary:hover { 
    background-color: #d1758e; 
}

.btn-primary:active {
    transform: scale(0.98);
}

.btn-delete {
    background-color: #fff5f5; 
    color: #e11d48; 
    border: 1px solid #ffe4e6;
    padding: 6px 14px; 
    border-radius: 6px; 
    font-size: 0.8rem; 
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-delete:hover { 
    background-color: #e11d48; 
    color: white; 
    border-color: #e11d48;
}

/* --- BOTÓN SWITCH TOGGLE (ESTADO INTERNO DE FILA) --- */
.toggle-status-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background-color: #f1f3f5;
    border: 1px solid #dee2e6;
    padding: 6px 12px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.25s ease;
    min-width: 95px;
}

.toggle-circle {
    width: 12px;
    height: 12px;
    background-color: #adb5bd;
    border-radius: 50%;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.toggle-text {
    font-size: 0.8rem;
    font-weight: 600;
    color: #495057;
}

.toggle-status-btn.is-active {
    background-color: #ecfdf5; 
    border-color: #a7f3d0;
}

.toggle-status-btn.is-active .toggle-circle {
    background-color: #10b981; 
    transform: translateX(2px);
}

.toggle-status-btn.is-active .toggle-text {
    color: #065f46;
}

.toggle-status-btn:hover {
    background-color: #e9ecef;
    border-color: #ced4da;
}

.toggle-status-btn.is-active:hover {
    background-color: #d1fae5;
    border-color: #6ee7b7;
}

/* --- MODAL --- */
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(50, 44, 68, 0.3);
    backdrop-filter: blur(4px); 
    display: flex; justify-content: center; align-items: center;
    z-index: 999;
}

.modal-card {
    background-color: #ffffff; 
    width: 90%; 
    max-width: 440px;
    border-radius: 14px; 
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    overflow: hidden; 
    animation: fadeIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-header {
    background-color: #ffffff; 
    padding: 18px 24px;
    display: flex; 
    justify-content: space-between; 
    align-items: center;
    border-bottom: 1px solid #f1f3f5;
}

.modal-header h3 {
    color: #322c44;
    font-weight: 700;
    font-size: 1.1rem;
}

.close-x { background: none; border: none; font-size: 1.6rem; cursor: pointer; color: #adb5bd; }
.close-x:hover { color: #495057; }

.modal-body { padding: 24px; }
.form-group { margin-bottom: 18px; display: flex; flex-direction: column; }
.form-group label { font-size: 0.8rem; font-weight: 700; color: #495057; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }

.form-group input, .form-group select {
    padding: 11px 14px; 
    border: 1px solid #dee2e6; 
    border-radius: 8px; 
    font-size: 0.95rem; 
    color: #322c44;
    outline: none;
    transition: border-color 0.2s ease;
}

.form-group input:focus, .form-group select:focus { 
    border-color: #e4869f; 
}

.modal-footer {
    padding: 16px 24px; 
    background-color: #f8f9fa; 
    border-top: 1px solid #f1f3f5;
    display: flex; 
    justify-content: flex-end; 
    gap: 12px;
}

.pos-admin { transform: translateX(0); }
.pos-trabajador { transform: translateX(100%); }
.pos-distribuidor { transform: translateX(200%); }

.btn-secondary { background: none; border: 1px solid #dee2e6; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-weight: 600; color: #495057; }
.btn-secondary:hover { background-color: #f1f3f5; }

.btn-submit { background-color: #322c44; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; }
.btn-submit:hover { background-color: #231e30; }

.empty-state { text-align: center; color: #adb5bd; padding: 50px 20px; font-size: 0.95rem; }

.btn-cancel {
  padding: 10px 20px; border: 1px solid #ddd; background: white;
  border-radius: 8px; cursor: pointer; font-weight: 600;
}
.btn-save {
  padding: 10px 20px; border: none; background: #e4869f; /* Tu color rosa */
  color: white; border-radius: 8px; cursor: pointer; font-weight: 600;
}
.btn-save:hover { background: #d1758e; }

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

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>