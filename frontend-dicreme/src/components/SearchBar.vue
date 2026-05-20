<script setup lang="ts">
// 1. Importamos el componente del icono
import { Search } from 'lucide-vue-next';
</script>

<template>
  <div class="search-section">
    <div class="search-controls">
      <h2 class="title">Cotiza con DiCreme</h2>
      
      <div class="inputs-group">
        <select class="category-select">
          <option selected disabled>Categoría</option>
          <option>Premium</option>
          <option>Vegano</option>
          <option>Tradicional</option>
        </select>
        
        <div class="search-input-wrapper">
          <input type="text" placeholder="Busca tu helado" class="search-input" />
          <Search class="search-icon" :size="20" />
        </div>
      </div>
    </div>
    <div class="pink-divider"></div>
  </div>
</template>

<style scoped>
.search-section {
  margin-bottom: 30px;
}

.search-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
}

.title {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--DC-gray);
}

.inputs-group {
  display: flex;
  gap: 15px;
}

.category-select, .search-input {
  padding: 8px 35px 8px 15px;
  border-radius: 20px;
  border: 1px solid var(--DC-text-gray);
  background-color: white;
  outline: none;
  font-size: 0.9rem;
  cursor: pointer;
  
}

/* El wrapper debe ser relativo para que la lupa se posicione adentro */
.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  width: 250px; /* Ajusta el ancho según tu mockup */
  padding-right: 40px; /* Espacio para que el texto no tape la lupa */
}

/* 3. Estilo para la lupa de Lucide */
.search-icon {
  position: absolute;
  right: 12px;
  color: var(--DC-text-gray);
  pointer-events: none; /* Para que el click pase al input */
}

.pink-divider {
  height: 2px;
  background-color: var(--DC-pink);
  width: 100%;
  margin-top: 5px;
}
</style>