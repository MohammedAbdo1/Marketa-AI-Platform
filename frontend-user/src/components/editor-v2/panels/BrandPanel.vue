<template>
  <div class="brand-panel">
    <div class="panel-header">
      <h5 class="panel-title">{{ $t('editor.panels.brand') }}</h5>
      <button class="btn-close" @click="$emit('close-panel')">
        <i class='bx bx-x'></i>
      </button>
    </div>

    <div class="panel-content">
      <div class="pro-feature-notice">
        <i class='bx bx-crown'></i>
        <p>{{ $t('editor.brand_kit_pro') }}</p>
        <button class="btn-upgrade">{{ $t('common.upgrade') }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
defineEmits(['close-panel'])
const { t } = useI18n()
</script>

<style scoped>
.brand-panel {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: white;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.panel-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
}

.btn-close {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 1.5rem;
  cursor: pointer;
}

.btn-close:hover {
  background: #f8f9fa;
}

.panel-content {
  flex: 1;
  padding: 2rem 1rem;
}

.pro-feature-notice {
  text-align: center;
  padding: 2rem 1rem;
  border-radius: 12px;
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.pro-feature-notice i {
  font-size: 3rem;
  color: #f59e0b;
  margin-bottom: 1rem;
}

.pro-feature-notice p {
  color: #78350f;
  margin-bottom: 1rem;
}

.btn-upgrade {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  border: none;
  background: #f59e0b;
  color: white;
  font-weight: 600;
  cursor: pointer;
}
</style>

