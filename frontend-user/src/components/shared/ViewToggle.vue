<template>
  <div class="view-toggle">
    <button 
      class="view-toggle-btn"
      :class="{ active: modelValue === 'grid' }"
      @click="toggleView"
      :aria-label="buttonLabel"
      :title="buttonLabel"
    >
      <!-- عرض أيقونة List عندما يكون العرض Grid (Cards) -->
      <svg 
        v-if="modelValue === 'grid'" 
        class="view-icon"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <!-- List Icon: 3 خطوط مع نقاط -->
        <circle cx="4" cy="6" r="1" fill="currentColor" />
        <line x1="8" y1="6" x2="20" y2="6" />
        <circle cx="4" cy="12" r="1" fill="currentColor" />
        <line x1="8" y1="12" x2="20" y2="12" />
        <circle cx="4" cy="18" r="1" fill="currentColor" />
        <line x1="8" y1="18" x2="20" y2="18" />
      </svg>

      <!-- عرض أيقونة Grid عندما يكون العرض List -->
      <svg 
        v-else 
        class="view-icon"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <!-- Grid Icon: 4 مربعات -->
        <rect x="3" y="3" width="7" height="7" rx="1" />
        <rect x="14" y="3" width="7" height="7" rx="1" />
        <rect x="3" y="14" width="7" height="7" rx="1" />
        <rect x="14" y="14" width="7" height="7" rx="1" />
      </svg>

      <!-- Label Text (optional) -->
      <span v-if="showLabel" class="view-label">
        {{ buttonLabel }}
      </span>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: {
    type: String,
    default: 'grid', // 'grid' or 'list'
    validator: (value) => ['grid', 'list'].includes(value)
  },
  showLabel: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const { t } = useI18n()

const buttonLabel = computed(() => {
  // إذا العرض حالياً Grid → نعرض "عرض القائمة"
  // إذا العرض حالياً List → نعرض "عرض البطاقات"
  return props.modelValue === 'grid' 
    ? t('common.list_view')
    : t('common.grid_view')
})

const toggleView = () => {
  const newView = props.modelValue === 'grid' ? 'list' : 'grid'
  emit('update:modelValue', newView)
}
</script>

<style scoped>
.view-toggle {
  display: inline-flex;
}

.view-toggle-btn {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-3);
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition-fast);
  color: var(--color-text-secondary);
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  white-space: nowrap;
  min-width: 40px;
  height: 40px;
  justify-content: center;
}

.view-toggle-btn:hover {
  background: var(--color-bg-hover);
  border-color: var(--color-border);
  color: var(--color-text-primary);
}

.view-toggle-btn.active {
  background: var(--color-bg-primary);
  border-color: var(--color-brand-primary);
  color: var(--color-brand-primary);
  box-shadow: 0 0 0 3px rgba(11, 110, 153, 0.1);
}

/* View Icon */
.view-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.view-toggle-btn:hover .view-icon {
  transform: scale(1.1);
}

.view-toggle-btn.active .view-icon {
  transform: scale(1.05);
}

/* Label */
.view-label {
  display: inline-block;
}

/* Animation */
.view-toggle-btn:active {
  transform: scale(0.95);
}

/* Responsive */
@media (max-width: 768px) {
  .view-label {
    display: none; /* إخفاء النص على الموبايل */
  }
  
  .view-toggle-btn {
    min-width: 36px;
    height: 36px;
    padding: var(--space-2);
  }
  
  .view-icon {
    width: 18px;
    height: 18px;
  }
}
</style>

