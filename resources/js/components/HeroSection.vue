<template>
  <section class="hero-section">
    <div class="hero-content">
      <h1>{{ hero.title }}</h1>
      <p>{{ hero.subtitle }}</p>
      <a :href="hero.cta_link" class="cta-button">{{ hero.cta_text }}</a>
    </div>
  </section>
</template>

<script>
export default {
  props: {
    hero: {
      type: Object,
      required: true,
    },
  },
};
</script>
