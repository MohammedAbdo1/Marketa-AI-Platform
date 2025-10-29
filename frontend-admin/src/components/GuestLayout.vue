<template>
  <div class="auth-wrapper" :dir="direction">
    <!-- Main Content -->
    <div class="auth-content">
      <div class="auth-container">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()

const direction = computed(() => locale.value === 'ar' ? 'rtl' : 'ltr')
</script>

<style scoped>
.auth-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.auth-content {
  width: 100%;
  max-width: 400px;
}

.auth-container {
  background: white;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  padding: 40px;
}
</style>