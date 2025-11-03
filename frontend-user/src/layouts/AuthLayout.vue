<template>
  <div class="auth-layout">
    <div class="auth-header">
      <router-link to="/" class="logo">
        <img src="@/assets/logo.png" alt="Logo" class="logo-img">
      </router-link>
    </div>
    
    <main class="auth-content">
      <router-view />
    </main>
    
    <div class="auth-footer">
      <p>&copy; 2025 {{ $t('app.name') }}. {{ $t('footer.all_rights_reserved') }}</p>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--color-bg-primary);
}

.auth-header {
  padding: var(--space-8);
  text-align: center;
}

.auth-header .logo {
  text-decoration: none;
  color: var(--color-text-primary);
  font-size: var(--text-4xl);
  font-weight: var(--font-bold);
}

.auth-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-8);
}

.auth-footer {
  padding: var(--space-6);
  text-align: center;
  color: var(--color-text-secondary);
  font-size: var(--text-sm);
}
</style>

