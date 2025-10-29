<template>
  <section class="cta-section">
    <div class="container">
      <div class="cta-content text-center">
        <h2>{{ $t('landing.cta.title') }}</h2>
        <p>{{ $t('landing.cta.subtitle') }}</p>
        <router-link to="/auth/register" class="btn btn-primary btn-lg">
          {{ $t('landing.cta.button') }}
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>

<style scoped>
.cta-section {
  padding: 6rem 0;
  background: #f6f5f4;
    color: rgb(51, 51, 51);
}

.cta-content h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.cta-content p {
  font-size: 1.25rem;
  margin-bottom: 2rem;
  opacity: 0.95;
}

.btn-primary {
  background: white;
  color: #2383E2;
}

.btn-primary:hover {
  background: #f8f9fa;
}

.btn-lg {
  padding: 1rem 3rem;
  font-size: 1.125rem;
}
</style>

