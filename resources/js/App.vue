<template>
  <HeroSection :hero="pageData.hero" />
  <BrandSection :brands="pageData.brands" />
  <AboutSection :about="pageData.about" />
  <ServiceSection :services="pageData.services" />
  <WorkSection :works="pageData.works" />
  <TeamSection :teams="pageData.teams" />
</template>

<script>
import HeroSection from './components/HeroSection.vue';
import BrandSection from './components/BrandSection.vue';
import AboutSection from './components/AboutSection.vue';
import ServiceSection from './components/ServiceSection.vue';
import WorkSection from './components/WorkSection.vue';
import TeamSection from './components/TeamSection.vue';

export default {
  components: {
    HeroSection,
    BrandSection,
    AboutSection,
    ServiceSection,
    WorkSection,
    TeamSection,
  },
  data() {
    return {
      pageData: {},
    };
  },
  created() {
    this.fetchPageData();
  },
  methods: {
    async fetchPageData() {
      const response = await fetch('/api/home');
      this.pageData = await response.json();
    },
  },
};
</script>
