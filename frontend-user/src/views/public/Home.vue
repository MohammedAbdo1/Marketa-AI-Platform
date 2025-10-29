<template>
  <div class="home-page">
    <HeroSection />
    <FeaturesSection />
    <TestimonialsSection />
    <PricingSection />
    <CtaSection />
  </div>
</template>

<script setup>
import HeroSection from '@/components/public/landing/HeroSection.vue'
import FeaturesSection from '@/components/public/landing/FeaturesSection.vue'
import TestimonialsSection from '@/components/public/landing/TestimonialsSection.vue'
import PricingSection from '@/components/public/landing/PricingSection.vue'
import CtaSection from '@/components/public/landing/CtaSection.vue'
</script>

<style scoped>
.home-page {
  padding-top: 80px; /* Account for fixed header */
}
</style>

