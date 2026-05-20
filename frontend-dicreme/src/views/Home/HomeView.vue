<template>
  <div class="home-page">
    <CartModal 
      :isOpen="isCartOpen"
      :cart-items="cartItems"
      @close="isCartOpen = false" 
      @update-quantity="handleUpdateQuantity"
      @remove-item="handleRemoveItem"
    />

    <ProductDetailModal 
      :isOpen="isDetailOpen" 
      :product="selectedProduct" 
      @close="isDetailOpen = false" 
      @add-to-cart="addToCart"
    />
    
    <Banner />
    
    <main class="content-container">
      <SearchBar />
      
      <div class="products-grid">
        <ProductCard 
          v-for="item in iceCreams" 
          :key="item.id"
          :name="item.name"
          :category="item.category"
          :categoryColor="item.color"
          :image="item.image"
          @view-details="openDetails(item)"
        />
      </div>
    </main>


    <button class="floating-cart" @click="isCartOpen = true">
      <ShoppingCart :size="28" color="white" :stroke-width="2" />
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Banner from '@/components/Banner.vue'
import SearchBar from '@/components/SearchBar.vue'
import ProductCard from '@/components/ProductCard.vue'
import CartModal from '@/components/CartModal.vue'
import ProductDetailModal from '@/components/ProductDetailModal.vue';
import fotoCaja from '@/assets/caja_dicreme.jpg'
import { ShoppingCart } from 'lucide-vue-next'
import categoryService from '@/services/categoryService';
import productService from '@/services/productService';

// Estados reactivos
const isCartOpen = ref(false);
const isDetailOpen = ref(false);
const selectedProduct = ref<any>(null);
const cartItems = ref<any[]>([]);
const iceCreams = ref<any[]>([]);

// Abrir el modal de detalles
const openDetails = (iceCream: any) => {
  selectedProduct.value = iceCream;
  isDetailOpen.value = true;
};

// Agregar un producto al carrito
const addToCart = (purchaseItem: any) => {
  //buscamos si ya existe el producto con el mismo sabor y tamaño en el carrito
  const existingItem = cartItems.value.find(
    item => item.id === purchaseItem.id && item.size === purchaseItem.size
  );

  if (existingItem) {
    existingItem.quantity += purchaseItem.quantity;
  } else {
    cartItems.value.push(purchaseItem);
  }
  
  console.log("Estado actual del carrito Di Creme:", cartItems.value);
};

// Función para cambiar cantidades desde el carrito lateral
const handleUpdateQuantity = (payload: { id: number, size: string, change: number }) => {
  const targetItem = cartItems.value.find(
    item => item.id === payload.id && item.size === payload.size
  );
  
  if (targetItem) {
    targetItem.quantity += payload.change;
    // Si la cantidad baja a 0 o menos, removemos el producto por completo
    if (targetItem.quantity <= 0) {
      handleRemoveItem(payload);
    }
  }
};

// Función para eliminar un producto del carrito
const handleRemoveItem = (payload: { id: number, size: string }) => {
  cartItems.value = cartItems.value.filter(
    item => !(item.id === payload.id && item.size === payload.size)
  );
};

// Función para cargar los productos
const fetchIceCreams = async () => {
  try {

    //Llamada al backend 
    const [productsResponse, categoriesResponse] = await Promise.all([
      productService.getProducts(),
      categoryService.getCategory()
    ]);

    if (!productsResponse || !categoriesResponse) {
      throw new Error('Error al obtener los datos');
    }

    const dbProducts = productsResponse.data;
    const dbCategories = categoriesResponse.data;

    // Diccionario de categorías para asignar IDs
    const categoryMap: Record<number, string> = {};
    dbCategories.forEach((cat: any) => {
      const catId= cat.id || cat.ID;
      if (catId){
        categoryMap[catId] = cat.nombre_categoria;
      }
    });

    // auxiliar para agrupar los formatos por sabor de helado
    const grouped: Record<string, any> = {};

    dbProducts.forEach((prod: any) => {
      const flavorName = prod.nombre_producto;
      const catId = prod.id_categoria || prod.ID_categoria;
      // obtenemos el nombre real desde el backend o lo definimos como "Sin categoría" si no se encuentra
      const categoryName = categoryMap[catId] || 'Sin categoría';

      //si es la primera vez que vemos este sabor, lo inicializamos
      if (!grouped[flavorName]){
        grouped[flavorName] = {
          name: flavorName,
          category: categoryName,
          color: 'var(--DC-pink)',
          image:fotoCaja,
          id10l: null, price10dl: 'No disponible',
          id5l: null, price5l: 'No disponible',
          id1l: null, price1l: 'No disponible'

        };
      }

      //formateamos el precio a moneda local
      const rawPrice = prod.precio_producto || 0;
      const formattedPrice = `$${Number(rawPrice).toLocaleString('es-CL')}`;
      console.log(`Producto: ${flavorName}, Formato ID: ${prod.id_formato || prod.ID_formato}, Precio: ${rawPrice}`);

      // identificamos y agrupamos por formato (ID 1 =10L, ID 2 = 5L, ID 3 = 1L)
      const formatId = prod.id_formato || prod.ID_formato;
      if (formatId === 1) {
        grouped[flavorName].id10l = prod.id || prod.ID;
        grouped[flavorName].price10l = formattedPrice;
      } else if (formatId === 2) {
        grouped[flavorName].id5l = prod.id || prod.ID;
        grouped[flavorName].price5l = formattedPrice;
      } else if (formatId === 3) {
        grouped[flavorName].id1l = prod.id || prod.ID;
        grouped[flavorName].price1l = formattedPrice;
      }
    });
    
    // Convertimos el objeto agrupado a un array para usar en la UI
    iceCreams.value = Object.values(grouped);
  } catch (error) {
    console.error('Error al cargar los productos:', error);
  }  

}

onMounted(() => {
  fetchIceCreams();
});

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
  width: 65px;
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