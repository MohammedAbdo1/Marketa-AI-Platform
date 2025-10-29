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
  /* background: #1a1a1a; */
  color: rgb(31, 31, 31);
  position: relative;
  overflow: hidden;
}

.hero-section::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
  opacity: 0.3;
}

.hero-content {
  position: relative;
  text-align: center;
  max-width: 800px;
  margin: 0 auto;
  padding: 4rem 1rem;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  margin-bottom: 1.5rem;
  line-height: 1.2;
}

.hero-subtitle {
  font-size: 1.5rem;
  margin-bottom: 2.5rem;
  opacity: 0.95;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-lg {
  padding: 1rem 2.5rem;
  font-size: 1.125rem;
}

.btn-outline {
  border-color: white;
  color: white;
}

.btn-outline:hover {
  background: white;
  color: #2383E2;
}

/* Mobile */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-subtitle {
    font-size: 1.125rem;
  }
  
  .hero-actions {
    flex-direction: column;
  }
}
</style>

