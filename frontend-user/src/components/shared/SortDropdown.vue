<template>
  <div class="sort-dropdown" ref="dropdownRef">
    <!-- Dropdown Toggle Button -->
    <button 
      class="sort-toggle"
      :class="{ active: isOpen }"
      @click="toggleDropdown"
      :aria-label="$t('common.sort')"
    >
      <i class="bx bx-sort-alt-2"></i>
      <span class="sort-label">{{ selectedOption?.label || $t('common.sort') }}</span>
      <i class="bx" :class="isOpen ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
    </button>

    <!-- Dropdown Menu -->
    <Transition name="dropdown-fade">
      <div v-if="isOpen" class="sort-menu">
        <div class="sort-menu-header">
          <h4>{{ $t('common.sort_by') }}</h4>
        </div>
        
        <div class="sort-menu-body">
          <button
            v-for="option in options"
            :key="option.value"
            class="sort-option"
            :class="{ active: modelValue === option.value }"
            @click="selectOption(option.value)"
          >
            <!-- Checkmark (left side for active) -->
            <i v-if="modelValue === option.value" class="bx bx-check option-check"></i>
            <span v-else class="option-check-placeholder"></span>
            
            <!-- Option Label -->
            <span class="option-label">{{ option.label }}</span>
            
            <!-- Option Icon (right side) -->
            <i class="bx option-icon" :class="option.icon"></i>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    required: true
  },
  options: {
    type: Array,
    required: true,
    validator: (options) => {
      return options.every(opt => 
        opt.value && opt.label && opt.icon
      )
    }
  }
})

const emit = defineEmits(['update:modelValue'])

const dropdownRef = ref(null)
const isOpen = ref(false)

const selectedOption = computed(() => {
  return props.options.find(opt => opt.value === props.modelValue)
})

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const selectOption = (value) => {
  emit('update:modelValue', value)
  isOpen.value = false
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.sort-dropdown {
  position: relative;
  display: inline-block;
}

/* Toggle Button */
.sort-toggle {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-3);
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition-fast);
  font-size: var(--text-sm);
  color: var(--color-text-primary);
  font-weight: var(--font-medium);
  white-space: nowrap;
}

.sort-toggle:hover {
  background: var(--color-bg-hover);
  border-color: var(--color-border);
}

.sort-toggle.active {
  background: var(--color-bg-primary);
  border-color: var(--color-brand-primary);
  box-shadow: 0 0 0 3px rgba(11, 110, 153, 0.1);
}

.sort-toggle i:first-child {
  font-size: var(--text-md);
  color: var(--color-text-secondary);
}

.sort-label {
  flex: 1;
  text-align: start;
}

.sort-toggle i:last-child {
  font-size: var(--text-sm);
  color: var(--color-text-tertiary);
  transition: transform 0.2s ease;
}

.sort-toggle.active i:last-child {
  transform: rotate(180deg);
}

/* Dropdown Menu */
.sort-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 280px;
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-dropdown);
  overflow: hidden;
  z-index: 1000;
}

[dir="rtl"] .sort-menu {
  right: auto;
  left: 0;
}

.sort-menu-header {
  padding: var(--space-3) var(--space-4);
  border-bottom: 1px solid var(--color-border-light);
}

.sort-menu-header h4 {
  margin: 0;
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
}

.sort-menu-body {
  padding: var(--space-2);
}

/* Sort Options */
.sort-option {
  width: 100%;
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-2) var(--space-3);
  background: transparent;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition-fast);
  text-align: start;
  font-size: var(--text-sm);
  color: var(--color-text-primary);
}

.sort-option:hover {
  background: var(--color-bg-hover);
}

.sort-option.active {
  background: var(--color-bg-secondary);
}

/* Checkmark */
.option-check {
  font-size: var(--text-md);
  color: var(--color-brand-primary);
  flex-shrink: 0;
}

.option-check-placeholder {
  width: 20px;
  flex-shrink: 0;
}

/* Option Label */
.option-label {
  flex: 1;
  font-weight: var(--font-medium);
}

/* Option Icon */
.option-icon {
  font-size: var(--text-md);
  color: var(--color-text-tertiary);
  flex-shrink: 0;
}

.sort-option.active .option-icon {
  color: var(--color-brand-primary);
}

/* Dropdown Animation */
.dropdown-fade-enter-active {
  animation: dropdownFadeIn 0.2s ease;
}

.dropdown-fade-leave-active {
  animation: dropdownFadeOut 0.15s ease;
}

@keyframes dropdownFadeIn {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes dropdownFadeOut {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-8px);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .sort-menu {
    min-width: 240px;
  }
  
  .sort-label {
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}
</style>

