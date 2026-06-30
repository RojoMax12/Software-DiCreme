<template>
  <div class="home-page">
    <CartModal 
      :isOpen="isCartOpen"
      :cart-items="cartItems"
      @close="isCartOpen = false" 
      @update-quantity="handleUpdateQuantity"
      @remove-item="handleRemoveItem"
      @checkout="goToQuotation"
    />

    <ProductDetailModal 
      :isOpen="isDetailOpen" 
      :product="selectedProduct" 
      @close="isDetailOpen = false" 
      @add-to-cart="addToCart"
    />

    <LoginNoticeModal
      :isOpen="isNoticeOpen"
      @close="isNoticeOpen = false"
      @confirm="router.push('/login')"
    />

    <Carousel :images="bannerImages" :autoPlayInterval="5000"/>
    
    <main class="content-container">
      <SearchBar 
        v-model="selectedCategory" 
        v-model:searchQuery="searchQueryText"
        :categories="categoriesList"
      />
      
      <div class="products-grid">
        <ProductCard 
          v-for="item in filteredIceCreams" 
          :key="item.name"
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
  <div>
    <Footer class="main-footer"/>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import Banner from '@/components/Banner.vue'
import SearchBar from '@/components/SearchBar.vue'
import ProductCard from '@/components/ProductCard.vue'
import CartModal from '@/components/CartModal.vue'
import ProductDetailModal from '@/components/ProductDetailModal.vue';
import LoginNoticeModal from '@/components/LoginNoticeModal.vue';
import fotoCaja from '@/assets/caja_dicreme.webp'
import { ShoppingCart } from 'lucide-vue-next'
import categoryService from '@/services/productCategoryService';
import productService from '@/services/productService';
import Footer from '@/components/Footer.vue'
import Carousel from '@/components/Carousel.vue';
import imgBanner1 from '@/assets/banner1.webp'
import imgBanner2 from '@/assets/banner2.webp'


const bannerImages = [
  imgBanner1,
  imgBanner2
];

// Estados reactivos
const isCartOpen = ref(false);
const isDetailOpen = ref(false);
const isNoticeOpen = ref(false);
const selectedProduct = ref<any>(null);
const cartItems = ref<any[]>([]);
const iceCreams = ref<any[]>([]);
const router = useRouter();
const categoriesList = ref<any[]>([]);
const selectedCategory = ref<string>('Todas');
const searchQueryText = ref<string>('');

// Estados autenticación
const isLoggedIn = ref(false); 
const currentUser = ref<any>(null);

// Revisar el estado de autenticación
const checkAuthStatus = () => {
  const token = localStorage.getItem('token');
  const userParsed = localStorage.getItem('user');

  if (token){
    isLoggedIn.value = true;
    if (userParsed) {
      try {
        const userObj = JSON.parse(userParsed);
        console.log("Contenido real de lo que hay en 'user':", userObj);
        currentUser.value = userObj.nombre_empresa || 'Distribuidor';
      } catch (error) {
        console.error("Error al parsear el usuario:", error);
        currentUser.value = 'Distribuidor';
      }
    } else {
      currentUser.value = 'Distribuidor';
    }
  } else {
    isLoggedIn.value = false;
    currentUser.value = null;
  }
};

watch(() => router.currentRoute.value.path, () => {
  checkAuthStatus();
});

// Función para cerrar sesión
const handleLogout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  isLoggedIn.value = false;
  currentUser.value = null;
  alert('Has cerrado sesión exitosamente.');
};

// Computado para filtrar helados por categoría y búsqueda de texto
const filteredIceCreams = computed(() => {
  let results = iceCreams.value;

  // Filtro 1: por categoría
  if (selectedCategory.value !== 'Todas' && selectedCategory.value !== '') {
    if (selectedCategory.value === 'Vegano' || selectedCategory.value === 'Sin lactosa') {
      results = results.filter(
        item => item.category === 'Al agua' || item.category === 'Leche de avena'
      );
    } else {
      results = results.filter(item => item.category === selectedCategory.value);
    }
  }
  
  // Filtro 2: por texto de búsqueda
  if (searchQueryText.value.trim() !== '') {
    const searchLow = searchQueryText.value.toLowerCase();
    results = results.filter(item => 
      item.name.toLowerCase().includes(searchLow)
    );
  }
  return results;  
});

// Abrir el modal de detalles
const openDetails = (iceCream: any) => {
  selectedProduct.value = iceCream;
  isDetailOpen.value = true;
};

// Agregar un producto al carrito
const addToCart = (purchaseItem: any) => {
const baseProduct = iceCreams.value.find(p => p.name === purchaseItem.name);

  if (baseProduct && !purchaseItem.id) {
    // Le inyectamos el ID exacto dependiendo del tamaño que eligió el usuario
    if (purchaseItem.size === '10L') purchaseItem.id = baseProduct.id10l;
    else if (purchaseItem.size === '5L') purchaseItem.id = baseProduct.id5l;
    else if (purchaseItem.size === '2.5L') purchaseItem.id = baseProduct.id25l;
    else if (purchaseItem.size === '1L') purchaseItem.id = baseProduct.id1l;
  }

  // Comparamos usando el sabor exacto y el tamaño físico
  const existingItem = cartItems.value.find(
    item => item.name === purchaseItem.name && item.size === purchaseItem.size
  );

  if (existingItem) {
    // Si el helado coincide completamente en sabor y formato, incrementamos
    existingItem.quantity += purchaseItem.quantity;
  } else {
    // Si es un sabor nuevo (aunque compartan el ID de formato general), se añade como una línea independiente
    cartItems.value.push(purchaseItem);
  }
  
  console.log("Estado actual del carrito Di Creme:", cartItems.value);

}

// Función para cambiar cantidades desde el carrito lateral
const handleUpdateQuantity = (payload: { id: number, size: string, change: number }) => {
  // Buscamos al item específico por su ID único de producto y su formato
  const targetItem = cartItems.value.find(
    item => item.id === payload.id && item.size === payload.size
  );
  
  if (targetItem) {
    targetItem.quantity += payload.change;
    // Si la cantidad llega a cero, lo sacamos del carrito
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

// Procesar la cotización hacia la siguiente pantalla
const goToQuotation = () => {
  if (cartItems.value.length === 0) {
    alert("Tu carrito está vacío.");
    return;
  }
  
  const token = localStorage.getItem('token');  
  
  if (token) {
      isCartOpen.value = false;
      router.push('/cotizacion');
  } else {
      isNoticeOpen.value = true;
  }
};

const handleGoToLogin = () => {
  isNoticeOpen.value = false;
  isCartOpen.value = false;
  router.push('/login');
};

const clpFormatter = new Intl.NumberFormat('es-CL', { 
  style: 'currency', 
  currency: 'CLP', 
  maximumFractionDigits: 0 
});

// Función para cargar los productos desde la API
const fetchIceCreams = async () => {
  try {
    // 1. Una sola petición HTTP. Ya no dependemos de categoryService.getCategory()
    const response = await productService.getProducts();

    if (!response?.data) {
      throw new Error('Error al obtener los datos del catálogo');
    }

    const dbProducts = response.data;
    
    // Usamos Map porque es el mecanismo más rápido en JS para agrupar elementos dinámicos
    const grouped = new Map<string, any>();
    
    // Instanciamos el formateador de pesos chilenos una sola vez fuera del bucle (ahorra CPU)
    const clpFormatter = new Intl.NumberFormat('es-CL', { 
      style: 'currency', 
      currency: 'CLP', 
      maximumFractionDigits: 0 
    });

    // 2. Un único bucle lineal para agrupar los formatos por sabor
    for (let i = 0; i < dbProducts.length; i++) {
      const prod = dbProducts[i];
      const flavorName = prod.nombre_producto;
      
      // Accedemos al nombre directo que nos envía Laravel gracias al JOIN
      const categoryName = prod.nombre_categoria || 'Sin categoría'; 

      // Si es la primera vez que vemos este sabor de helado, creamos su base
      if (!grouped.has(flavorName)) {
        grouped.set(flavorName, {
          name: flavorName,
          category: categoryName,
          color: 'var(--DC-pink)',
          image: fotoCaja,
          id10l: null, price10l: 'No disponible',
          id5l: null, price5l: 'No disponible',
          id25l: null, price25l: 'No disponible',
          id1l: null, price1l: 'No disponible'
        });
      }

      // Obtenemos la referencia de la tarjeta que estamos armando
      const item = grouped.get(flavorName);
      const formattedPrice = clpFormatter.format(prod.precio_producto || 0);
      const formatId = prod.id_formato;
      const prodId = prod.id;

      // Asignamos el ID y precio al formato que corresponda
      switch (formatId) {
        case 1: item.id10l = prodId; item.price10l = formattedPrice; break;
        case 2: item.id5l = prodId; item.price5l = formattedPrice; break;
        case 3: item.id25l = prodId; item.price25l = formattedPrice; break;
        case 4: item.id1l = prodId; item.price1l = formattedPrice; break;
      }
    }

    // 3. Convertimos el mapa de sabores en un arreglo limpio para la vista de Vue
    iceCreams.value = Array.from(grouped.values());

  } catch (error) {
    console.error('Error al cargar los productos:', error);
  }  
}

onMounted(() => {
  fetchIceCreams();
  checkAuthStatus();

  // Recuperación segura del estado persistido del carrito temporal
  const savedCart = localStorage.getItem('dicreme_temp_cart');
  if (savedCart) {
    try {
      cartItems.value = JSON.parse(savedCart);
    } catch (error) {
      console.error('Error al cargar el carrito guardado:', error);
    }
  }
});

// Guardado reactivo profundo en LocalStorage para no perder la persistencia de compra
watch(
  cartItems,
  (newCart) => {
    localStorage.setItem('dicreme_temp_cart', JSON.stringify(newCart));
  },
  { deep: true }
);
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

.main-footer {
  margin-top: auto;
  width: 100%;
}
</style>