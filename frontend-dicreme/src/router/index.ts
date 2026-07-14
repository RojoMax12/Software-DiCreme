import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../components/LoginView.vue'
import RegisterView from '../components/RegisterView.vue'
import ForgotPasswordView from '../components/ForgotPasswordView.vue'
import ResetPasswordView from '@/components/ResetPasswordView.vue'
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
      path: '/reset-password',
      name: 'reset-password',
      component: ResetPasswordView,
      meta: { hideNavbar: true, useLoader: true}
    },

    {
      path: '/perfile',
      name: 'perfile-distribuidor',
      component: () => import('../views/Distributor/PerfileView.vue'),
      meta: {useLoader: true, requiresAuth: true }

    },
    {
      path: '/admin',
      name: 'admin-home',
      component: () => import('../views/Admin/AdminHomeView.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [1, 2] }
    },
    {
      path: '/admin/quotes',
      name: 'admin-quotes',
      component: () => import('../views/Admin/Quotes.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [1] }
    },
    {
      path: '/admin/orders',
      name: 'admin-orders',
      component: () => import('../views/Admin/Orders.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [1, 2] }
    },
    {
      path: '/admin/generate-quote',
      name: 'admin-generate-quote',
      component: () => import('../views/Admin/AdminGenerateQuoteView.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [1, 2] }
    },
    {
      path: '/cotizacion',
      name: 'quotation',
      component: () => import('../views/Checkout/QuotationView.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [3] }
    },
    {
      path: '/cotizacion-exitosa',
      name: 'CotizacionExitosa',
      component: () => import('@/views/Checkout/SuccesfulQuotationView.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [3] }
    },
    {
      path: '/mis-cotizaciones',
      name: 'my-quotations',
      component: () => import('@/views/Distributor/MyQuotationsView.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [3] }
    },
    {
    path: '/cotizacion/:id', 
    name: 'quotation-detail',
    component: () => import('@/views/Distributor/QuotationDetailView.vue'),
    meta: {useLoader: true, requiresAuth: true, roles: [3] }
    },
    {
      path: '/mis-pedidos',
      name: 'my-orders',
      component: () => import('@/views/Distributor/MyOrdersView.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [3] }
    },
    {
      path: '/pedido/:id', 
      name: 'order-detail',
      component: () => import('@/views/Distributor/OrderDetailView.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [3] }
    },
    {
      path: '/admin/user-management',
      name: 'admin-users',
      component: () => import('../views/Admin/UserManagementView.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [1] }
    },
    {
      path: '/admin/inventario',
      name: 'admin-inventory',
      component: () => import('../views/Admin/InventoryView.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [1, 2] }
    },
    {
      path: '/admin/lotes/:id',
      name: 'admin-batches-detail',
      component: () => import('../views/Admin/BatchesView.vue'),
      meta: {useLoader: true, requiresAuth: true, roles: [1, 2] }

    },
  ]
})


// router/index.ts
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth) {
    const token = localStorage.getItem('token')
    const userRaw = localStorage.getItem('user')
    const user = userRaw ? JSON.parse(userRaw) : null

    // Si no hay token o usuario guardado, patada al login
    if (!token || !user || !user.id_rol) {
      console.warn('Falta token o datos de usuario incompletos. Redirigiendo a Login.')
      next('/login')
      return
    }

    // Convertimos el rol a número para evitar errores de comparación ("1" vs 1)
    const userRole = Number(user.id_rol)

    // 2. Verificar Roles (Si la ruta tiene roles definidos)
    const rolesPermitidos = to.meta.roles as number[] | undefined
    
    if (rolesPermitidos && !rolesPermitidos.includes(userRole)) {
      console.warn(`Acceso denegado: Se requiere rol ${rolesPermitidos}, pero el usuario tiene rol ${userRole}.`)
      
      // Redirigir a su panel correspondiente
      if (userRole === 1 || userRole === 2) {
        next('/admin')
      } else {
        next('/') 
      }
      return
    }
  }

  if (to.meta.useLoader) {
    globalLoading.value = true
  }

  setTimeout(() => next(), 50)
})

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
