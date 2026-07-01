import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../components/LoginView.vue'
import RegisterView from '../components/RegisterView.vue'
import ForgotPasswordView from '../components/ForgotPasswordView.vue'
import HomeView from '../views/Home/HomeView.vue'
import { globalLoading } from '@/composables/useLoading';
import { nextTick } from 'vue';


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [

    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { hideNavbar: false }
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { hideNavbar: true, useLoader: true }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { hideNavbar: true, useLoader: true }
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: ForgotPasswordView,
      meta: { hideNavbar: true, useLoader: true}
    },
    {
      path: '/admin',
      name: 'admin-home',
      component: () => import('../views/Admin/AdminHomeView.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/admin/quotes',
      name: 'admin-quotes',
      component: () => import('../views/Admin/Quotes.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/admin/orders',
      name: 'admin-orders',
      component: () => import('../views/Admin/Orders.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/admin/generate-quote',
      name: 'admin-generate-quote',
      component: () => import('../views/Admin/AdminGenerateQuoteView.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/cotizacion',
      name: 'quotation',
      component: () => import('../views/Checkout/QuotationView.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/cotizacion-exitosa',
      name: 'CotizacionExitosa',
      component: () => import('@/views/Checkout/SuccesfulQuotationView.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/mis-cotizaciones',
      name: 'my-quotations',
      component: () => import('@/views/Distributor/MyQuotationsView.vue'),
      meta: {useLoader: true}
    },
    {
    path: '/cotizacion/:id', 
    name: 'quotation-detail',
    component: () => import('@/views/Distributor/QuotationDetailView.vue'),
    meta: {useLoader: true}
    },
    {
      path: '/mis-pedidos',
      name: 'my-orders',
      component: () => import('@/views/Distributor/MyOrdersView.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/pedido/:id', 
      name: 'order-detail',
      component: () => import('@/views/Distributor/OrderDetailView.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/admin/user-management',
      name: 'admin-users',
      component: () => import('../views/Admin/UserManagementView.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/admin/inventario',
      name: 'admin-inventory',
      component: () => import('../views/Admin/InventoryView.vue'),
      meta: {useLoader: true}
    },
    {
      path: '/admin/lotes/:id',
      name: 'admin-batches-detail',
      component: () => import('../views/Admin/BatchesView.vue'),

    }
  ]
})


// router/index.ts
router.beforeEach((to, from, next) => {
  // Solo activamos si la ruta lo requiere
  if (to.meta.useLoader) {
    globalLoading.value = true;
  }
  
  // Usamos un pequeño retraso para asegurar que Vue procese el estado "true"
  // antes de renderizar la nueva ruta
  setTimeout(() => {
    next();
  }, 50); 
});

router.afterEach((to) => {
  // Si la ruta no usa loader, aseguramos que esté apagado
  if (!to.meta.useLoader) {
    globalLoading.value = false;
  } else {
    // Si la ruta sí usa loader, apagamos después de un tiempo
    setTimeout(() => {
      globalLoading.value = false;
    }, 600);
  }
});


export default router
