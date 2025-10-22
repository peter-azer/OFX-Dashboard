<template>
  <section class="service-section">
    <h2>Our Services</h2>
    <div class="service-list">
      <div v-for="service in services" :key="service.id" class="service-item">
        <h3>{{ service.title }}</h3>
        <p>{{ service.description }}</p>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  props: {
    services: {
      type: Array,
      required: true,
    },
  },
};
</script>
