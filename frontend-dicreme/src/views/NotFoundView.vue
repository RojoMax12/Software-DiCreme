<script setup lang="ts">
import { useRouter } from 'vue-router';
import { Home, ArrowLeft, AlertTriangle } from 'lucide-vue-next';

const router = useRouter();

const goHome = () => {
  const userRaw = localStorage.getItem('user');
  if (userRaw) {
    try {
      const user = JSON.parse(userRaw);
      const role = Number(user.id_rol);
      if (role === 1 || role === 2) {
        router.push('/admin');
        return;
      } else if (role === 4) {
        router.push('/despachador');
        return;
      }
    } catch (e) {
      console.error(e);
    }
  }
  router.push('/');
};

const goBack = () => {
  router.back();
};
</script>

<template>
  <div class="not-found-container">
    <div class="not-found-card">
      <div class="logo-box">
        <img src="@/assets/logo_dicreme.png" alt="DiCreme Logo" class="brand-logo" />
      </div>

      <div class="error-badge">
        <AlertTriangle :size="20" color="#e4869f" />
        <span>Error 404</span>
      </div>

      <h1 class="error-title">Página No Encontrada</h1>
      <p class="error-description">
        Lo sentimos, la página que estás buscando no existe, ha sido movida o no tienes permisos para acceder a ella.
      </p>

      <div class="actions-group">
        <button class="btn-primary" @click="goHome">
          <Home :size="18" />
          Volver al Inicio
        </button>
        <button class="btn-secondary" @click="goBack">
          <ArrowLeft :size="18" />
          Ir Atrás
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.not-found-container {
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.not-found-card {
  background: white;
  border-radius: 24px;
  padding: 45px 35px;
  max-width: 500px;
  width: 100%;
  text-align: center;
  box-shadow: 0 15px 35px rgba(50, 44, 68, 0.08);
  border: 1px solid #eaeaea;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.logo-box {
  margin-bottom: 20px;
}

.brand-logo {
  height: 50px;
  object-fit: contain;
}

.error-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #fdf2f4;
  color: #e4869f;
  padding: 6px 16px;
  border-radius: 30px;
  font-weight: 700;
  font-size: 0.88rem;
  margin-bottom: 15px;
  border: 1px solid #fbcfe8;
}

.error-title {
  font-size: 1.8rem;
  font-weight: 800;
  color: #322c44;
  margin-bottom: 12px;
}

.error-description {
  font-size: 0.95rem;
  color: #6c757d;
  line-height: 1.5;
  margin-bottom: 30px;
}

.actions-group {
  display: flex;
  gap: 12px;
  width: 100%;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: #e4869f;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 30px;
  font-weight: 700;
  font-size: 0.92rem;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(228, 134, 159, 0.3);
  transition: all 0.2s ease;
}

.btn-primary:hover {
  background-color: #d1728c;
  transform: translateY(-2px);
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: #f8f9fa;
  color: #322c44;
  border: 1px solid #dee2e6;
  padding: 12px 24px;
  border-radius: 30px;
  font-weight: 700;
  font-size: 0.92rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background-color: #e9ecef;
  transform: translateY(-2px);
}
</style>
