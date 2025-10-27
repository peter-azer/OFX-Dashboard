<template>
  <section class="work-section">
    <h2>Our Work</h2>
    <div class="work-list">
      <div v-for="work in works" :key="work.id" class="work-item">
        <img :src="work.image_url" :alt="work.title" />
        <h3>{{ work.title }}</h3>
        <p>{{ work.service?.service_name || '' }}</p>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  props: {
    works: {
      type: Array,
      required: true,
    },
  },
};
</script>
