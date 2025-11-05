<template>
  <div 
    class="loading-spinner"
    :class="[
      sizeClass,
      { 'fullscreen': fullscreen },
      { 'inline': inline }
    ]"
  >
    <!-- 3 Dots Animation (ChatGPT/Claude style) -->
    <div class="dots-container" :class="spinnerSizeClass">
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
    </div>

    <!-- Loading Text -->
    <p v-if="showText" class="loading-text" :class="textSizeClass">
      {{ message || $t('common.loading') }}
    </p>

    <!-- Optional Description -->
    <p v-if="description" class="loading-description">
      {{ description }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // حجم الـ Spinner
  size: {
    type: String,
    default: 'md', // 'sm', 'md', 'lg', 'xl'
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  },
  
  // رسالة Loading مخصصة
  message: {
    type: String,
    default: ''
  },
  
  // وصف إضافي تحت النص
  description: {
    type: String,
    default: ''
  },
  
  // عرض النص
  showText: {
    type: Boolean,
    default: true
  },
  
  // وضع FullScreen (يغطي الشاشة كاملة)
  fullscreen: {
    type: Boolean,
    default: false
  },
  
  // وضع Inline (بجانب المحتوى)
  inline: {
    type: Boolean,
    default: false
  },
  
  // لون الـ Spinner
  color: {
    type: String,
    default: 'primary', // 'primary', 'white', 'gray'
    validator: (value) => ['primary', 'white', 'gray'].includes(value)
  }
})

const sizeClass = computed(() => `size-${props.size}`)
const spinnerSizeClass = computed(() => `spinner-${props.size}`)
const textSizeClass = computed(() => `text-${props.size}`)
</script>

<style scoped>
/* Container */
.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--space-8);
  color: var(--color-text-secondary);
}

/* Fullscreen Mode */
.loading-spinner.fullscreen {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(4px);
  z-index: 9999;
  padding: 0;
}

[dir="rtl"] .loading-spinner.fullscreen {
  right: 0;
  left: 0;
}

/* Inline Mode */
.loading-spinner.inline {
  padding: var(--space-4);
  flex-direction: row;
  gap: var(--space-3);
}

/* 3 Dots Container (ChatGPT/Claude Style) */
.dots-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
}

.dot {
  border-radius: 50%;
  background: var(--color-brand-primary);
  animation: bounce 1.4s ease-in-out infinite;
}

/* Dot Animation - each dot bounces with delay */
.dot:nth-child(1) {
  animation-delay: 0s;
}

.dot:nth-child(2) {
  animation-delay: 0.2s;
}

.dot:nth-child(3) {
  animation-delay: 0.4s;
}

/* Dots Sizes */
.spinner-sm .dot {
  width: 6px;
  height: 6px;
}

.spinner-md .dot {
  width: 8px;
  height: 8px;
}

.spinner-lg .dot {
  width: 10px;
  height: 10px;
}

.spinner-xl .dot {
  width: 12px;
  height: 12px;
}

/* Bounce Animation */
@keyframes bounce {
  0%, 80%, 100% {
    transform: scale(0.8);
    opacity: 0.5;
  }
  40% {
    transform: scale(1.2);
    opacity: 1;
  }
}

/* Loading Text */
.loading-text {
  margin: var(--space-4) 0 0 0;
  font-weight: var(--font-medium);
  color: var(--color-text-secondary);
}

.inline .loading-text {
  margin: 0;
}

/* Text Sizes */
.text-sm {
  font-size: var(--text-xs);
}

.text-md {
  font-size: var(--text-sm);
}

.text-lg {
  font-size: var(--text-md);
}

.text-xl {
  font-size: var(--text-lg);
}

/* Loading Description */
.loading-description {
  margin: var(--space-2) 0 0 0;
  font-size: var(--text-xs);
  color: var(--color-text-tertiary);
  text-align: center;
  max-width: 300px;
}

/* Alternative: Pulse Animation for fullscreen */
.loading-spinner.fullscreen .dot {
  animation: pulse 1.4s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(0.8);
    opacity: 0.5;
  }
  50% {
    transform: scale(1.3);
    opacity: 1;
  }
}

/* Size Variants Padding */
.size-sm {
  padding: var(--space-4);
}

.size-md {
  padding: var(--space-8);
}

.size-lg {
  padding: var(--space-10);
}

.size-xl {
  padding: var(--space-12);
}

/* Responsive */
@media (max-width: 768px) {
  .loading-spinner:not(.inline) {
    padding: var(--space-6);
  }
  
  .spinner-md .dot {
    width: 7px;
    height: 7px;
  }
  
  .spinner-lg .dot {
    width: 9px;
    height: 9px;
  }
  
  .spinner-xl .dot {
    width: 11px;
    height: 11px;
  }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  .dot {
    animation: none;
    opacity: 0.8;
  }
}
</style>

