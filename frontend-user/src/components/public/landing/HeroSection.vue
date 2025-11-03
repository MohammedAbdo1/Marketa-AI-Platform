<template>
  <section class="hero-section">
    <div class="container">
      <div class="hero-content fade-in">
        <h1 class="hero-title">{{ $t('landing.hero.title') }}</h1>
        <p class="hero-subtitle">{{ $t('landing.hero.subtitle') }}</p>
        
        <div class="hero-actions">
          <router-link to="/auth/register" class="btn btn-primary btn-lg">
            {{ $t('landing.hero.cta_primary') }}
          </router-link>
          <button class="btn btn-primary btn-lg">
            {{ $t('landing.hero.cta_secondary') }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>

<style scoped>
.hero-section {
  min-height: 600px;
  display: flex;
  align-items: center;
  background: var(--color-bg-primary);
  color: var(--color-text-primary);
  position: relative;
  overflow: hidden;
  padding: var(--space-20) 0;
}

.hero-content {
  position: relative;
  text-align: center;
  max-width: 800px;
  margin: 0 auto;
  padding: var(--space-16) var(--space-4);
  animation: fadeInUp var(--duration-slow) var(--ease-out);
}

.hero-title {
  font-size: var(--text-5xl);
  font-weight: var(--font-bold);
  margin-bottom: var(--space-6);
  line-height: var(--leading-tight);
  color: var(--color-text-primary);
}

.hero-subtitle {
  font-size: var(--text-xl);
  margin-bottom: var(--space-10);
  color: var(--color-text-secondary);
  line-height: var(--leading-relaxed);
}

.hero-actions {
  display: flex;
  gap: var(--space-4);
  justify-content: center;
  flex-wrap: wrap;
}

/* Mobile */
@media (max-width: 768px) {
  .hero-title {
    font-size: var(--text-3xl);
  }
  
  .hero-subtitle {
    font-size: 1.125rem;
  }
  
  .hero-actions {
    flex-direction: column;
  }
}
</style>

