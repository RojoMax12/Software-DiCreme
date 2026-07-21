<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import Navbar from './components/Navbar.vue';
import AdminNavbar from './components/AdminNavbar.vue';
import AdminSideMenu from './components/AdminSideMenu.vue';
import NotificationContainer from '@/components/NotificationContainer.vue';
import GlobalLoader from '@/components/LoadingScreen.vue';
import { globalLoading } from '@/composables/useLoading';

const route = useRoute();
const isAdminSidebarOpen = ref(false);

const toggleAdminSidebar = () => {
  isAdminSidebarOpen.value = !isAdminSidebarOpen.value;
};
</script>

<template>
  <template v-if="!route.meta?.hideNavbar">
    <template v-if="route.path?.startsWith('/admin')">
      <AdminNavbar @toggleSidebar="toggleAdminSidebar" />
      <AdminSideMenu :isOpen="isAdminSidebarOpen" @close="isAdminSidebarOpen = false" />
    </template>
    <Navbar v-else />
  </template>
  
  <GlobalLoader />
  <router-view v-if="!globalLoading"/>

  <NotificationContainer />
</template>

<style scoped>
/* Base App Layout Styles */
</style>