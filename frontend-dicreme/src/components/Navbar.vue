<template>
  <nav class="navbar">
    <div class="logo-container">
      <img src="@/assets/logo_dicreme.png" alt="Di Creme Logo" class="logo-icon" />
      <span class="logo-text">Di Creme</span>
    </div>

    <div class="actions">
      <button class="btn-ingresar">INGRESAR</button>
    </div>
  </nav>
</template>

<script setup lang="ts">
</script>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 8%; 
  background-color: #ffffff;
  height: 80px;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-icon {
  height: 100px; 
  width: auto;
}

.logo-text {
  font-family: 'Brush Script MT', 'Pacifico', cursive; 
  font-size: 2rem;
  font-weight: 600;
  color: #000;
}

.btn-ingresar {
  background-color: var(--DC-pink); 
  color: white;
  border: none;
  padding: 8px 25px;
  border-radius: 20px; 
  font-weight: bold;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-ingresar:hover {
  filter: brightness(1.1); /* Efecto sutil al pasar el mouse */
}
</style>