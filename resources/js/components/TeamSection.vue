<template>
  <section class="team-section">
    <h2>Our Team</h2>
    <div class="team-list">
      <div v-for="member in teams" :key="member.id" class="team-member">
        <img :src="member.image_url" :alt="member.name" />
        <h3>{{ member.name }}</h3>
        <p>{{ member.position }}</p>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  props: {
    teams: {
      type: Array,
      required: true,
    },
  },
};
</script>
