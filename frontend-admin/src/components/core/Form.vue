<template>
  <form @submit.prevent="handleSubmit" :class="formClasses">
    <slot></slot>
    
    <div v-if="showActions" class="form-actions">
      <slot name="actions">
        <!-- <Button
          type="button"
          variant="secondary"
          @click="handleCancel"
          :disabled="loading"
        >
          {{ cancelText }}
        </Button> -->
        <Button
          type="submit"
          variant="primary"
          :loading="loading"
          :disabled="!isValid"
        >
          {{ submitText }}
        </Button>
      </slot>
    </div>
  </form>
</template>

<script setup>
import { computed } from 'vue'
import Button from './Button.vue'

const props = defineProps({
  loading: {
    type: Boolean,
    default: false
  },
  showActions: {
    type: Boolean,
    default: true
  },
  submitText: {
    type: String,
    default: 'Save'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  layout: {
    type: String,
    default: 'vertical',
    validator: (value) => ['vertical', 'horizontal', 'inline'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  validateOnSubmit: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['submit', 'cancel', 'validate'])

const formClasses = computed(() => {
  const classes = ['form']
  
  classes.push(`form-${props.layout}`)
  classes.push(`form-${props.size}`)
  
  if (props.disabled) {
    classes.push('form-disabled')
  }
  
  return classes.join(' ')
})

const isValid = computed(() => {
  // This would typically be connected to form validation logic
  // For now, we'll assume the form is valid unless explicitly set otherwise
  return true
})

function handleSubmit() {
  if (props.validateOnSubmit && !isValid.value) {
    return
  }
  
  emit('submit')
}

function handleCancel() {
  emit('cancel')
}
</script>

<style scoped>
.form {
  width: 100%;
}

.form-vertical {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-horizontal {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-inline {
  display: flex;
  flex-wrap: wrap;
  align-items: end;
  gap: 1rem;
}

.form-sm .form-group {
  margin-bottom: 0.75rem;
}

.form-md .form-group {
  margin-bottom: 1rem;
}

.form-lg .form-group {
  margin-bottom: 1.25rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.form-disabled {
  opacity: 0.6;
  pointer-events: none;
}

@media (min-width: 768px) {
  .form-horizontal .form-group {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .form-horizontal .form-group label {
    width: 150px;
    flex-shrink: 0;
  }
  
  .form-horizontal .form-group .form-control {
    flex: 1;
  }
  
  .form-inline .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .form-inline .form-group label {
    font-size: 0.875rem;
    font-weight: 500;
  }
}

@media (max-width: 767px) {
  .form-actions {
    flex-direction: column;
  }
  
  .form-inline {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
