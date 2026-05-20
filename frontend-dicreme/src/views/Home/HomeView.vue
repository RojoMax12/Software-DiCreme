<template>
  <div class="home-page">
    <FloatingCart :isOpen="isCartOpen" @close="isCartOpen = false" />

    <Banner />
    
    <main class="content-container">
      <SearchBar />
      
      <div class="products-grid">
        <ProductCard 
          v-for="item in helados" 
          :key="item.id"
          :name="item.nombre"
          :category="item.categoria"
          :categoryColor="item.color"
          :image="item.imagen"
        />
      </div>
    </main>

    <button class="floating-cart" @click="isCartOpen = true">
      <ShoppingCart :size="28" color="white" :stroke-width="2" />
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Banner from '@/components/Banner.vue'
import SearchBar from '@/components/SearchBar.vue'
import ProductCard from '@/components/ProductCard.vue'
import FloatingCart from '@/components/FloatingCart.vue'
import fotoCaja from '@/assets/caja_dicreme.jpg'
import { ShoppingCart } from 'lucide-vue-next'


const isCartOpen = ref(false);

// Datos de los helados
const helados = [
  { id: 1, nombre: 'Menta Chips', categoria: 'Premium', color: 'var(--DC-pink)', imagen: fotoCaja },
  { id: 2, nombre: 'Limón Menta Jengibre', categoria: 'Vegano', color: 'var(--DC-pink)', imagen: fotoCaja },
  { id: 3, nombre: 'Cookies and Cream', categoria: 'Premium', color: 'var(--DC-pink)', imagen: fotoCaja },
  { id: 4, nombre: 'Chocolate', categoria: 'Tradicional', color: 'var(--DC-pink)', imagen: fotoCaja },
  { id: 5, nombre: 'Arroz con Leche', categoria: 'Tradicional', color: 'var(--DC-pink)', imagen: fotoCaja },
]
</script>

<style scoped>
.content-container {
  padding: 20px 8%;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  justify-items: center;
}

.floating-cart {
  position: fixed;
  bottom: 30px;
  left: 30px;
  background-color: var(--DC-pink);
  color: white;
  width: 65px; /* Un poco más grande para que luzca como el mockup */
  height: 65px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0,0,0,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 900; 
  transition: transform 0.2s ease;
}

.floating-cart:hover {
  transform: scale(1.1);
}

.floating-cart:active {
  transform: scale(0.9);
}
</style>