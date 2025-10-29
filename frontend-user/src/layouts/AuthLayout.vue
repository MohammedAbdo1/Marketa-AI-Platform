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
  /* background: #1a1a1a; */
}

.auth-header {
  padding: 2rem;
  text-align: center;
}

.auth-header .logo {
  text-decoration: none;
  color: white;
  font-size: 2rem;
  font-weight: bold;
}

.auth-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.auth-footer {
  padding: 1.5rem;
  text-align: center;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.875rem;
}
.form-control {
  font-size: 0.875rem;
  height: 3px !important;
}
</style>

