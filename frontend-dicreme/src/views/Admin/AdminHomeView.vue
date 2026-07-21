<template>
  <div class="admin-home-container">
    <div class="admin-home-header">
      <h1>Panel de Administración</h1>
      <p>Seleccione el módulo que desea gestionar</p>
    </div>
    
    <div class="admin-options-grid">
      <router-link v-role="[1]" to="/admin/quotes" class="admin-option-card">
        <div class="icon-container">
          <FileText :size="48" />
        </div>
        <h2>Cotizaciones</h2>
        <p>Gestionar y revisar las cotizaciones de los distribuidores</p>
      </router-link>

      <router-link v-role="[1,2]" to="/admin/orders" class="admin-option-card">
        <div class="icon-container">
          <ShoppingBag :size="48" />
        </div>
        <h2>Pedidos</h2>
        <p>Administrar los pedidos y estados de despacho</p>
      </router-link>

      <router-link v-role="[1,2]" to="/admin/generate-quote" class="admin-option-card">
        <div class="icon-container">
          <FilePlus :size="48" />
        </div>
        <h2>Generar Cotización</h2>
        <p>Crear una nueva cotización para un distribuidor (existente o nuevo)</p>
      </router-link>

      <router-link v-role="1" to="/admin/user-management" class="admin-option-card">
        <div class="icon-container">
          <Users :size="48" />
        </div>
        <h2>Gestión de Usuarios</h2>
        <p>Administrar cuentas de usuarios, roles y permisos</p>
      </router-link>

      <router-link v-role="[1,2]" to="/admin/inventario" class="admin-option-card">
        <div class="icon-container">
          <Package2  :size="48" />
        </div>
        <h2>Inventario</h2>
        <p>Administra el inventario de los productos y sus lotes</p>
      </router-link>

      <router-link v-role="[1,2]" to="/admin/catalogo" class="admin-option-card">
        <div class="icon-container">
          <Package2 :size="48" />
        </div>
        <h2>Catálogo</h2>
        <p>Administrar el catálogo de los productos y sus precios</p>
      </router-link>

    </div>
  </div>
</template>

<script setup lang="ts">
import { FileText, ShoppingBag, FilePlus,Package2 ,Users } from 'lucide-vue-next';
</script>

<style scoped>
.admin-home-container {
  max-width: 1000px;
  margin: 4rem auto;
  padding: 0 2rem;
}

.admin-home-header {
  text-align: center;
  margin-bottom: 3rem;
}

.admin-home-header h1 {
  color: var(--DC-gray);
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.admin-home-header p {
  color: var(--DC-text-gray);
  font-size: 1.1rem;
}

.admin-options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.5rem;
}

.admin-option-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem 1rem;
  text-align: center;
  text-decoration: none;
  color: var(--DC-gray);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
  border: 1px solid #eeedee;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.admin-option-card:hover {
  transform: translateY(-4px);
  border-color: var(--DC-pink);
  box-shadow: 0 12px 20px rgba(228, 134, 159, 0.15);
}

.icon-container {
  width: 60px;
  height: 60px;
  background: var(--DC-bg-gray);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
  color: var(--DC-pink);
  transition: all 0.3s ease;
}

.admin-option-card:hover .icon-container {
  background: var(--DC-pink);
  color: white;
}

.admin-option-card h2 {
  font-size: 1.8rem;
  margin-bottom: 1rem;
}

.admin-option-card p {
  color: var(--DC-text-gray);
  line-height: 1.6;
}

@media (max-width: 640px) {
  .admin-home-container {
    margin: 2rem auto;
  }
  
  .admin-home-header h1 {
    font-size: 2rem;
  }
}
</style>
