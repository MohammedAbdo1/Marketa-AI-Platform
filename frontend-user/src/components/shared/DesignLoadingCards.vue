<template>
  <div class="design-loading-cards">
    <!-- Status Message -->
    <div v-if="statusMessage" class="loading-status">
      <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <span class="text-muted">{{ statusMessage }}</span>
    </div>

    <!-- Cards Container -->
    <div class="cards-container">
      <button 
        v-if="showNavigation && currentIndex > 0"
        class="nav-btn nav-btn-prev"
        @click="$emit('navigate', 'prev')"
        aria-label="Previous"
      >
        <i class='bx bx-chevron-left'></i>
      </button>

      <div class="cards-grid">
        <div 
          v-for="index in count" 
          :key="index"
          class="loading-card"
          :style="getCardStyle(index)"
        >
          <div class="card-shimmer"></div>
          <div class="card-content">
            <div class="placeholder-icon">
              <i class='bx bx-image-add'></i>
            </div>
            <div v-if="showLabels" class="card-label">
              {{ $t('ai.generating_design') }} {{ index }}
            </div>
          </div>
        </div>
      </div>

      <button 
        v-if="showNavigation && hasMore"
        class="nav-btn nav-btn-next"
        @click="$emit('navigate', 'next')"
        aria-label="Next"
      >
        <i class='bx bx-chevron-right'></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  count: {
    type: Number,
    default: 1
  },
  statusMessage: {
    type: String,
    default: null
  },
  showLabels: {
    type: Boolean,
    default: false
  },
  showNavigation: {
    type: Boolean,
    default: false
  },
  currentIndex: {
    type: Number,
    default: 0
  },
  hasMore: {
    type: Boolean,
    default: false
  },
  gradientType: {
    type: String,
    default: 'purple-blue', // purple-blue, pink-orange, green-teal
    validator: (value) => ['purple-blue', 'pink-orange', 'green-teal'].includes(value)
  }
})

defineEmits(['navigate'])

const gradients = {
  'purple-blue': [
    'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'
  ],
  'pink-orange': [
    'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
    'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
    'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)'
  ],
  'green-teal': [
    'linear-gradient(135deg, #0ba360 0%, #3cba92 100%)',
    'linear-gradient(135deg, #00d2ff 0%, #3a47d5 100%)',
    'linear-gradient(135deg, #f857a6 0%, #ff5858 100%)'
  ]
}

const getCardStyle = (index) => {
  const selectedGradients = gradients[props.gradientType]
  const gradientIndex = (index - 1) % selectedGradients.length
  return {
    background: selectedGradients[gradientIndex]
  }
}
</script>

<style scoped>
.design-loading-cards {
  width: 100%;
  margin: 1rem 0;
}

.loading-status {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.cards-container {
  position: relative;
  padding: 0 2rem;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
  padding: 0.5rem 0;
}

@media (max-width: 768px) {
  .cards-grid {
    grid-template-columns: 1fr;
  }
}

.loading-card {
  position: relative;
  width: 100%;
  aspect-ratio: 1 / 1;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}

.card-shimmer {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(255, 255, 255, 0.3) 50%,
    transparent 100%
  );
  animation: shimmer 2s infinite;
}

@keyframes shimmer {
  0% {
    left: -100%;
  }
  100% {
    left: 100%;
  }
}

.card-content {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: white;
}

.placeholder-icon {
  font-size: 4rem;
  opacity: 0.7;
  margin-bottom: 1rem;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.card-label {
  font-size: 0.9rem;
  font-weight: 500;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: none;
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
}

.nav-btn:hover {
  background: #f8f9fa;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  transform: translateY(-50%) scale(1.1);
}

.nav-btn i {
  font-size: 1.5rem;
  color: #667eea;
}

.nav-btn-prev {
  left: 0;
}

.nav-btn-next {
  right: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .cards-container {
    padding: 0;
  }

  .nav-btn {
    width: 32px;
    height: 32px;
  }

  .nav-btn i {
    font-size: 1.2rem;
  }
}
</style>

