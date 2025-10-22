<template>
  <section class="brand-section">
    <h2>Trusted by the best</h2>
    <div class="brand-list">
      <div v-for="brand in brands" :key="brand.id" class="brand-item">
        <img :src="brand.logo_url" :alt="brand.name" />
      </div>
    </div>
  </section>
</template>

<script>
export default {
  props: {
    brands: {
      type: Array,
      required: true,
    },
  },
};
</script>
