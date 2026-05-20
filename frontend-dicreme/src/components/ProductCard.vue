<template>
  <div class="card">
    <div class="card-image">
      <img :src="image" :alt="name" />
    </div>

    <div class="card-content">
      <h3 class="product-name">{{ name }}</h3>
      <p class="product-category" :style="{ color: categoryColor }">
        - {{ category }}
      </p>

      <div class="sizes-container">
        <button class="size-btn">10L</button>
        <button class="size-btn">5L</button>
        <button class="size-btn">1L</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  name: string;
  category: string;
  categoryColor: string;
  image: string;
}>();
</script>

<style scoped>
.card {
  background-color: #fff;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
  width: 220px; /* Ajusta según el diseño */
  transition: transform 0.2s;
}

.card:hover {
  transform: translateY(-5px);
}

.card-image {
  width: 100%;
  height: 140px;
  overflow: hidden;
}

.card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-content {
  padding: 15px;
  text-align: left;
}

.product-name {
  margin: 0;
  font-size: 1.1rem;
  color: #333;
  font-weight: 700;
}

.product-category {
  margin: 2px 0 15px 0;
  font-size: 0.85rem;
  font-weight: 500;
}

.sizes-container {
  display: flex;
  gap: 8px;
}

.size-btn {
  background-color: var(--DC-gray); 
  color: white;
  border: none;
  border-radius: 4px;
  padding: 5px 8px;
  font-size: 0.75rem;
  font-weight: bold;
  cursor: pointer;
  flex: 1;
}

.size-btn:hover {
  filter: brightness(1.3);
}
</style>