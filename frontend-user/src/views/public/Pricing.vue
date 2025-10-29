<template>
  <div class="pricing-page">
    <div class="container">
      <section class="pricing-hero">
        <h1>{{ $t('nav.pricing') }}</h1>
        <p>{{ $t('landing.pricing.subtitle') }}</p>
      </section>
      
      <PricingSection />
    </div>
  </div>
</template>

<script setup>
import PricingSection from '@/components/public/landing/PricingSection.vue'
</script>

<style scoped>
.pricing-page {
  padding-top: 100px;
}

.pricing-hero {
  text-align: center;
  padding: 4rem 0 2rem;
}

.pricing-hero h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
}
</style>

