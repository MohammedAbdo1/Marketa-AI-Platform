<template>
  <div class="tools-panel">
    <div class="panel-header">
      <h5 class="panel-title">{{ $t('editor.panels.tools') }}</h5>
      <button class="btn-close" @click="$emit('close-panel')">
        <i class='bx bx-x'></i>
      </button>
    </div>

    <div class="panel-section">
      <h6 class="section-title">{{ $t('editor.drawing_tools') }}</h6>
      <button class="tool-btn">
        <i class='bx bx-pencil'></i>
        {{ $t('editor.draw') }}
      </button>
      <button class="tool-btn">
        <i class='bx bx-eraser'></i>
        {{ $t('editor.eraser') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
defineEmits(['close-panel'])
const { t } = useI18n()
</script>

<style scoped>
.tools-panel {
  height: 100%;
  background: white;
}

.panel-header {
  padding: 1.25rem 1rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
}

.panel-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.btn-close {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1.5rem;
}

.panel-section {
  padding: 1rem;
}

.section-title {
  margin: 0 0 0.75rem 0;
  font-size: 0.85rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
}

.tool-btn {
  width: 100%;
  padding: 0.75rem;
  margin-bottom: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  transition: all 0.2s ease;
}

.tool-btn:hover {
  border-color: #667eea;
  background: #f8f9fa;
}

.tool-btn i {
  font-size: 1.2rem;
  color: #667eea;
}
</style>

