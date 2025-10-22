<template>
  <section class="about-section">
    <h2>{{ about.title }}</h2>
    <p>{{ about.description }}</p>
  </section>
</template>

<script>
export default {
  props: {
    about: {
      type: Object,
      required: true,
    },
  },
};
</script>
