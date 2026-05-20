import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../components/LoginView.vue'
import RegisterView from '../components/RegisterView.vue'
import ForgotPasswordView from '../components/ForgotPasswordView.vue'
import HomeView from '../views/Home/HomeView.vue'

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
      meta: { hideNavbar: true }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { hideNavbar: true }
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: ForgotPasswordView,
      meta: { hideNavbar: true }
    },
    {
      path: '/admin/quotes',
      name: 'admin-quotes',
      component: () => import('../views/Admin/Quotes.vue')
    },
    {
      path: '/admin/orders',
      name: 'admin-orders',
      component: () => import('../views/Admin/Orders.vue')
    }

  ],
})

export default router
