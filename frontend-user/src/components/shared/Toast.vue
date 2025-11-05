<template>
  <Teleport to="body">
    <Transition name="toast-slide">
      <div v-if="visible" class="toast" :class="`toast-${type}`">
        <i class="bx" :class="iconClass"></i>
        <span>{{ message }}</span>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'success', // success, error, info
    validator: (value) => ['success', 'error', 'info'].includes(value)
  },
  duration: {
    type: Number,
    default: 2000
  },
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['hide'])

const visible = ref(props.show)

const iconClass = computed(() => {
  switch (props.type) {
    case 'success':
      return 'bx-check-circle'
    case 'error':
      return 'bx-error-circle'
    case 'info':
      return 'bx-info-circle'
    default:
      return 'bx-check-circle'
  }
})

watch(() => props.show, (newVal) => {
  if (newVal) {
    visible.value = true
    setTimeout(() => {
      visible.value = false
      emit('hide')
    }, props.duration)
  }
})
</script>

<style scoped>
.toast {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-3) var(--space-4);
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-dropdown);
  z-index: 10000;
  font-size: var(--text-sm);
  color: var(--color-text-primary);
  min-width: 280px;
  max-width: 400px;
}

.toast i {
  font-size: var(--text-lg);
}

.toast-success {
  border-left: 4px solid var(--color-green-text);
}

[dir="rtl"] .toast-success {
  border-left: none;
  border-right: 4px solid var(--color-green-text);
}

.toast-success i {
  color: var(--color-green-text);
}

.toast-error {
  border-left: 4px solid var(--color-error);
}

[dir="rtl"] .toast-error {
  border-left: none;
  border-right: 4px solid var(--color-error);
}

.toast-error i {
  color: var(--color-error);
}

.toast-info {
  border-left: 4px solid var(--color-brand-primary);
}

[dir="rtl"] .toast-info {
  border-left: none;
  border-right: 4px solid var(--color-brand-primary);
}

.toast-info i {
  color: var(--color-brand-primary);
}

/* Animations */
.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: all 0.3s ease;
}

.toast-slide-enter-from {
  opacity: 0;
  transform: translateX(-50%) translateY(20px);
}

.toast-slide-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(-20px);
}
</style>



