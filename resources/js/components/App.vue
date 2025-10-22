<template>
    <div>
        <hero-section></hero-section>
        <brand-section></brand-section>
        <about-section></about-section>
        <service-section></service-section>
        <work-section></work-section>
        <team-section></team-section>
    </div>
</template>

<script>
import HeroSection from './HeroSection.vue';
import BrandSection from './BrandSection.vue';
import AboutSection from './AboutSection.vue';
import ServiceSection from './ServiceSection.vue';
import WorkSection from './WorkSection.vue';
import TeamSection from './TeamSection.vue';

export default {
    components: {
        HeroSection,
        BrandSection,
        AboutSection,
        ServiceSection,
        WorkSection,
        TeamSection,
    },
};
</script>
