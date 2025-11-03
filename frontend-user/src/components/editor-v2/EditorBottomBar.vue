<template>
  <div class="editor-bottom-bar">
    <!-- Left Section -->
    <div class="bottom-bar-left">
      <button class="bar-btn" title="Premium Features">
        <i class='bx bx-dollar'></i>
      </button>
      <button class="bar-btn" @click="toggleFullscreen" title="ملء الشاشة">
        <i class='bx bx-fullscreen'></i>
      </button>
      <button class="bar-btn" @click="toggleGrid" title="شبكة">
        <i class='bx bx-grid-alt'></i>
      </button>
    </div>

    <!-- Center Section (Pages) -->
    <div class="bottom-bar-center">
      <span class="pages-label">{{ $t('editor.pages') }}</span>
      <div class="pages-navigation">
        <button 
          class="page-nav-btn"
          @click="$emit('page-change', currentPage - 1)"
          :disabled="currentPage <= 1"
        >
          <i class='bx bx-chevron-right'></i>
        </button>
        <span class="page-indicator">{{ currentPage }}/{{ totalPages }}</span>
        <button 
          class="page-nav-btn"
          @click="$emit('page-change', currentPage + 1)"
          :disabled="currentPage >= totalPages"
        >
          <i class='bx bx-chevron-left'></i>
        </button>
      </div>

      <div class="divider"></div>

      <!-- Zoom Controls -->
      <div class="zoom-controls">
        <button class="zoom-btn" @click="$emit('zoom-change', zoomLevel - 10)">
          <i class='bx bx-minus'></i>
        </button>
        <input 
          type="range"
          min="10"
          max="200"
          :value="zoomLevel"
          @input="$emit('zoom-change', $event.target.value)"
          class="zoom-slider"
        />
        <span class="zoom-label">{{ Math.round(zoomLevel) }}%</span>
        <button class="zoom-btn" @click="$emit('zoom-change', zoomLevel + 10)">
          <i class='bx bx-plus'></i>
        </button>
      </div>
    </div>

    <!-- Right Section -->
    <div class="bottom-bar-right">
      <button class="bar-btn" @click="toggleNotes" title="ملاحظات">
        <i class='bx bx-note'></i>
      </button>
      <button class="bar-btn" @click="toggleTimer" title="مؤقت">
        <i class='bx bx-timer'></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  currentPage: {
    type: Number,
    default: 1
  },
  totalPages: {
    type: Number,
    default: 1
  },
  zoomLevel: {
    type: Number,
    default: 100
  }
})

defineEmits(['page-change', 'zoom-change', 'add-page'])

const { t } = useI18n()

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen()
  } else {
    document.exitFullscreen()
  }
}

const toggleGrid = () => {
  console.log('Toggle grid')
}

const toggleNotes = () => {
  console.log('Toggle notes')
}

const toggleTimer = () => {
  console.log('Toggle timer')
}
</script>

<style scoped>
.editor-bottom-bar {
  height: 48px;
  background: var(--color-bg-primary);
  border-top: 1px solid var(--color-border-light);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 var(--space-4);
  gap: var(--space-4);
  z-index: var(--z-sticky);
}

.bottom-bar-left,
.bottom-bar-right {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.bottom-bar-center {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-4);
}

.bar-btn {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-md);
  border: none;
  background: transparent;
  color: var(--color-text-secondary);
  font-size: var(--text-xl);
  cursor: pointer;
  transition: var(--transition-fast);
  display: flex;
  align-items: center;
  justify-content: center;
}

.bar-btn:hover {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
}

.pages-label {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  font-weight: var(--font-medium);
}

.pages-navigation {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.page-nav-btn {
  width: 28px;
  height: 28px;
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-medium);
  background: var(--color-bg-primary);
  color: var(--color-text-secondary);
  font-size: var(--text-base);
  cursor: pointer;
  transition: var(--transition-fast);
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-nav-btn:hover:not(:disabled) {
  background: var(--color-bg-hover);
  border-color: var(--color-brand-primary);
  color: var(--color-brand-primary);
}

.page-nav-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.page-indicator {
  min-width: 50px;
  text-align: center;
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
}

.divider {
  width: 1px;
  height: 24px;
  background: var(--color-border-light);
}

.zoom-controls {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.zoom-btn {
  width: 28px;
  height: 28px;
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-medium);
  background: var(--color-bg-primary);
  color: var(--color-text-secondary);
  font-size: var(--text-base);
  cursor: pointer;
  transition: var(--transition-fast);
  display: flex;
  align-items: center;
  justify-content: center;
}

.zoom-btn:hover {
  background: var(--color-bg-hover);
  border-color: var(--color-brand-primary);
  color: var(--color-brand-primary);
}

.zoom-slider {
  width: 120px;
}

.zoom-label {
  min-width: 50px;
  text-align: center;
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
}

/* Responsive */
@media (max-width: 768px) {
  .editor-bottom-bar {
    padding: 0 var(--space-3);
    gap: var(--space-2);
  }
  
  .pages-label {
    display: none;
  }

  .zoom-slider {
    width: 80px;
  }
}
</style>

