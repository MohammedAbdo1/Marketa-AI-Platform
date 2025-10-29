<template>
  <div class="not-found">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="text-center">
            <h1 class="display-1 fw-bold text-primary">404</h1>
            <h4 class="mb-3">{{ t('notfound.title') }}</h4>
            <p class="text-muted mb-4">{{ t('notfound.message') }}</p>
            <router-link to="/dashboard" class="btn btn-primary">
              {{ t('notfound.back_home') }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<style scoped>
.not-found {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
}
</style>
