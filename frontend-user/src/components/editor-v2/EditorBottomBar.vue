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
  height: 50px;
  background: white;
  border-top: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1rem;
  gap: 1rem;
  z-index: 100;
}

.bottom-bar-left,
.bottom-bar-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.bottom-bar-center {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.bar-btn {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bar-btn:hover {
  background: #f8f9fa;
  color: #667eea;
}

.pages-label {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}

.pages-navigation {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.page-nav-btn {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-nav-btn:hover:not(:disabled) {
  background: #f8f9fa;
  border-color: #667eea;
  color: #667eea;
}

.page-nav-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.page-indicator {
  min-width: 50px;
  text-align: center;
  font-size: 0.85rem;
  font-weight: 600;
  color: #2d3748;
}

.divider {
  width: 1px;
  height: 24px;
  background: #e2e8f0;
}

.zoom-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.zoom-btn {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.zoom-btn:hover {
  background: #f8f9fa;
  border-color: #667eea;
  color: #667eea;
}

.zoom-slider {
  width: 120px;
}

.zoom-label {
  min-width: 50px;
  text-align: center;
  font-size: 0.85rem;
  font-weight: 600;
  color: #2d3748;
}

.bottom-nav {
  margin-top: auto;
  padding: 1rem;
  border-top: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-item {
  width: 100%;
  height: 36px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.nav-item:hover {
  background: #f8f9fa;
  color: #667eea;
}

.nav-item.has-badge .badge {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #e53e3e;
  color: white;
  font-size: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-divider {
  height: 1px;
  background: #e2e8f0;
  margin: 0.25rem 0;
}

/* Responsive */
@media (max-width: 768px) {
  .pages-label {
    display: none;
  }

  .zoom-slider {
    width: 80px;
  }
}
</style>

