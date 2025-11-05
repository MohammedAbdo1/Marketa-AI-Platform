<template>
  <Transition name="modal-fade">
    <div v-if="show" class="modal-backdrop" @click="handleBackdropClick">
      <div class="confirm-dialog" @click.stop>
        <!-- Header -->
        <div class="dialog-header">
          <h3 class="dialog-title">{{ title }}</h3>
          <button class="btn-close" @click="handleCancel" :aria-label="$t('common.close')">
            <i class="bx bx-x"></i>
          </button>
        </div>

        <!-- Body -->
        <div class="dialog-body">
          <p class="dialog-message">{{ message }}</p>
          <p v-if="description" class="dialog-description">{{ description }}</p>
        </div>

        <!-- Footer -->
        <div class="dialog-footer">
          <button 
            class="btn btn-secondary" 
            @click="handleCancel"
            :disabled="loading"
          >
            {{ cancelText || $t('common.cancel') }}
          </button>
          <button 
            class="btn" 
            :class="dangerMode ? 'btn-danger' : 'btn-primary'"
            @click="handleConfirm"
            :disabled="loading"
          >
            <i v-if="loading" class="bx bx-loader-alt bx-spin"></i>
            <span v-else>{{ confirmText || $t('common.confirm') }}</span>
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  confirmText: {
    type: String,
    default: ''
  },
  cancelText: {
    type: String,
    default: ''
  },
  dangerMode: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['confirm', 'cancel', 'close'])

const handleConfirm = () => {
  if (props.loading) return
  emit('confirm')
}

const handleCancel = () => {
  if (props.loading) return
  emit('cancel')
  emit('close')
}

const handleBackdropClick = () => {
  if (props.closeOnBackdrop && !props.loading) {
    emit('cancel')
    emit('close')
  }
}
</script>

<style scoped>
/* Modal Backdrop */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(15, 15, 15, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: var(--space-4);
}

/* Confirm Dialog */
.confirm-dialog {
  background: var(--color-bg-primary);
  border-radius: var(--radius-lg);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  width: 100%;
  max-width: 480px;
  overflow: hidden;
  animation: slideUp 0.3s ease;
}

/* Dialog Header */
.dialog-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-5) var(--space-5) var(--space-4) var(--space-5);
  border-bottom: 1px solid var(--color-border-light);
}

.dialog-title {
  font-size: var(--text-lg);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
  margin: 0;
}

.btn-close {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  color: var(--color-text-tertiary);
  font-size: var(--text-xl);
  transition: var(--transition-fast);
}

.btn-close:hover {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
}

/* Dialog Body */
.dialog-body {
  padding: var(--space-5);
}

.dialog-message {
  font-size: var(--text-md);
  font-weight: var(--font-medium);
  color: var(--color-text-primary);
  margin: 0 0 var(--space-2) 0;
  line-height: 1.5;
}

.dialog-description {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  margin: 0;
  line-height: 1.6;
}

/* Dialog Footer */
.dialog-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: var(--space-3);
  padding: var(--space-4) var(--space-5) var(--space-5) var(--space-5);
  background: var(--color-bg-secondary);
  border-top: 1px solid var(--color-border-light);
}

.dialog-footer .btn {
  min-width: 100px;
}

/* Animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .confirm-dialog {
  animation: slideUp 0.3s ease;
}

.modal-fade-leave-active .confirm-dialog {
  animation: slideDown 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideDown {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(20px);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .confirm-dialog {
    max-width: 100%;
  }
  
  .dialog-header,
  .dialog-body,
  .dialog-footer {
    padding: var(--space-4);
  }
  
  .dialog-footer {
    flex-direction: column-reverse;
    gap: var(--space-2);
  }
  
  .dialog-footer .btn {
    width: 100%;
  }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  .confirm-dialog {
    animation: none;
  }
  
  .modal-fade-enter-active,
  .modal-fade-leave-active {
    transition: none;
  }
}
</style>

