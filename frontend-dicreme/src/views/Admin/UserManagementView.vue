<template>
    <div class="admin-panel-box">
        
        <div class="panel-controls-bar">
            <div class="switch-container">
                <div class="switch-slider" :class="{ 'slide-right': activeFilter === 'completed' }"></div>
                
                <button 
                    class="switch-btn" 
                    :class="{ active: activeFilter === 'actual' }"
                    @click="activeFilter = 'actual'"
                >
                    <span>Administradores</span>
                </button>
                
                <button 
                    class="switch-btn" 
                    :class="{ active: activeFilter === 'completed' }"
                    @click="activeFilter = 'completed'"
                >
                    <span>Trabajadores</span>
                </button>
            </div>

            <button class="btn-primary" @click="openModal">
                <span class="icon">+</span> Añadir Usuario
            </button>
        </div>

        <hr class="panel-divider" />

        <div class="content-header">
            <h2 v-if="activeFilter === 'actual'">Gestión de Administradores</h2>
            <h2 v-else>Gestión de Trabajadores</h2>
        </div>
        
        <div class="table-header-row">
            <div class="col-id">ID</div>
            <div class="col-user">Nombre Usuario</div>
            <div class="col-email">Correo Electrónico</div>
            <div class="col-status">Estado</div>
            <div class="col-actions">Acciones</div>
        </div>

        <div class="users-list">
            <div v-if="activeFilter === 'actual'">
                <div v-for="user in usersAdmin" :key="user.id" class="user-table-row">
                    <div class="col-id">#{{ user.id }}</div>
                    <div class="col-user-name">{{ user.nombre_usuario }}</div>
                    <div class="col-email-text">{{ user.correo_electronico }}</div>
                    
                    <div class="col-status">
                        <button 
                            class="toggle-status-btn" 
                            :class="{ 'is-active': user.estado_usuario === 'Activo' || user.estado_usuario === 1 || user.estado_usuario === true }"
                            @click="toggleEstado(user)"
                        >
                            <span class="toggle-circle"></span>
                            <span class="toggle-text">
                                {{ (user.estado_usuario === 'Activo' || user.estado_usuario === 1 || user.estado_usuario === true) ? 'Activo' : 'Inactivo' }}
                            </span>
                        </button>
                    </div>

                    <div class="col-actions-btns">
                        <button class="btn-delete" @click="eliminarUsuario(user.id)">Eliminar</button>
                    </div>
                </div>
                <div v-if="usersAdmin.length === 0" class="empty-state">
                    No hay administradores registrados.
                </div>
            </div>

            <div v-else>
                <div v-for="user in usersTrabajador" :key="user.id" class="user-table-row">
                    <div class="col-id">#{{ user.id }}</div>
                    <div class="col-user-name">{{ user.nombre_usuario }}</div>
                    <div class="col-email-text">{{ user.correo_electronico }}</div>
                    
                    <div class="col-status">
                        <button 
                            class="toggle-status-btn" 
                            :class="{ 'is-active': user.estado_usuario === 'Activo' || user.estado_usuario === 1 || user.estado_usuario === true }"
                            @click="toggleEstado(user)"
                        >
                            <span class="toggle-circle"></span>
                            <span class="toggle-text">
                                {{ (user.estado_usuario === 'Activo' || user.estado_usuario === 1 || user.estado_usuario === true) ? 'Activo' : 'Inactivo' }}
                            </span>
                        </button>
                    </div>

                    <div class="col-actions-btns">
                        <button class="btn-delete" @click="eliminarUsuario(user.id)">Eliminar</button>
                    </div>
                </div>
                <div v-if="usersTrabajador.length === 0" class="empty-state">
                    No hay trabajadores registrados.
                </div>
            </div>
        </div>
    </div>

    <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Registrar Nuevo Usuario</h3>
                <button class="close-x" @click="closeModal">&times;</button>
            </div>
            
            <form @submit.prevent="handleCreateUser">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre de Usuario</label>
                        <input v-model="form.nombre_usuario" type="text" placeholder="Ej. johan_neira" required />
                    </div>

                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input v-model="form.correo_electronico" type="email" placeholder="Ej. admin@dicreme.cl" required />
                    </div>

                    <div class="form-group">
                        <label>Contraseña</label>
                        <input v-model="form.contrasena" type="password" placeholder="Mínimo 6 caracteres" required />
                    </div>

                    <div class="form-group">
                        <label>Rol de Acceso del Sistema</label>
                        <select v-model="form.id_rol" required>
                            <option value="" disabled selected>Selecciona un rol...</option>
                            <option value="1">Administrador (Acceso Total)</option>
                            <option value="2">Trabajador (Operaciones)</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" @click="closeModal">Cancelar</button>
                    <button type="submit" class="btn-submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import userService from '@/services/userService';
import { ref, onMounted, reactive } from 'vue';
import { useNotification } from '@/composables/useNotification';

const activeFilter = ref('actual'); 
const usersAdmin = ref<any[]>([]);
const usersTrabajador = ref<any[]>([]);
const isModalOpen = ref(false);
const { notify } = useNotification();

const form = reactive({
    nombre_usuario: '',
    correo_electronico: '',
    contrasena: '',
    id_rol: ''
});

onMounted(() => {
    fetchUsers();
});

// Función de Cambio de Estado Descomentada y lista
const toggleEstado = async (user: any) => {
    const estadoOriginal = user.estado_usuario;

    let nuevoEstado: any;
    if (typeof estadoOriginal === 'string') {
        nuevoEstado = estadoOriginal === 'Activo' ? 'Inactivo' : 'Activo';
    } else {
        nuevoEstado = (estadoOriginal == 1 || estadoOriginal === true) ? 0 : 1;
    }

    // Actualización optimista en la interfaz
    user.estado_usuario = nuevoEstado;

    try {
        await userService.toggleUserStatus(user.id); 
        notify("Estado del usuario actualizado correctamente.", "success");
    } catch (error: any) {
        // Revertir en caso de error
        user.estado_usuario = estadoOriginal;
        console.error("Error al cambiar estado:", error);
        notify("No se pudo cambiar el estado del usuario.", "error");
    }
};

const fetchUsers = async () => {
    try {
        const response = await userService.getUsers();
        const users = response.data || response;

        if (Array.isArray(users)) {
            usersAdmin.value = users.filter(user => user.id_rol == 1 || user.rol === '1');
            usersTrabajador.value = users.filter(user => user.id_rol == 2 || user.rol === '2');
        }
    } catch (error) {
        console.error("Error cargando los usuarios:", error);
    }
};

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.nombre_usuario = '';
    form.correo_electronico = '';
    form.contrasena = '';
    form.id_rol = '';
};

const handleCreateUser = async () => {
    try {
        await userService.createUser({
            nombre_usuario: form.nombre_usuario,
            correo_electronico: form.correo_electronico,
            contrasena: form.contrasena,
            id_rol: parseInt(form.id_rol)
        });

        notify("Usuario insertado correctamente.", "success");
        closeModal();
        fetchUsers();
    } catch (error: any) {
        notify("Error al guardar: " + (error.response?.data?.message || error.message), "error");
    }
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

.switch-slider {
    position: absolute;
    top: 4px; left: 4px; bottom: 4px;
    width: calc(50% - 4px);
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border-radius: 7px;
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 0;
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

.btn-secondary { background: none; border: 1px solid #dee2e6; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-weight: 600; color: #495057; }
.btn-secondary:hover { background-color: #f1f3f5; }

.btn-submit { background-color: #322c44; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; }
.btn-submit:hover { background-color: #231e30; }

.empty-state { text-align: center; color: #adb5bd; padding: 50px 20px; font-size: 0.95rem; }

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>