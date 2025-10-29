<template>
  <div class="form-group" :class="groupClasses">
    <label 
      v-if="label" 
      :for="inputId" 
      class="form-label"
      :class="{ 'required': required }"
    >
      {{ label }}
      <span v-if="required" class="required-asterisk">*</span>
    </label>
    
    <div class="form-control-wrapper">
      <slot :id="inputId"></slot>
    </div>
    
    <div v-if="helpText" class="form-help">
      {{ helpText }}
    </div>
    
    <div v-if="errors && errors.length" class="form-errors">
      <div v-for="error in errors" :key="error" class="form-error">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    default: true
  },
  name: {
    type: String,
    default: true
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  helpText: {
    type: String,
    default: ''
  },
  errors: {
    type: Array,
    default: () => []
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  layout: {
    type: String,
    default: 'vertical',
    validator: (value) => ['vertical', 'horizontal', 'inline'].includes(value)
  }
})

const inputId = computed(() => {
  return props.name ? `${props.name}-${Math.random().toString(36).substr(2, 9)}` : `input-${Math.random().toString(36).substr(2, 9)}`
})

const groupClasses = computed(() => {
  const classes = ['form-group']
  
  classes.push(`form-group-${props.size}`)
  classes.push(`form-group-${props.layout}`)
  
  if (props.required) {
    classes.push('form-group-required')
  }
  
  if (props.disabled) {
    classes.push('form-group-disabled')
  }
  
  if (props.errors && props.errors.length) {
    classes.push('form-group-error')
  }
  
  return classes.join(' ')
})
</script>

<style scoped>
.form-group {
  margin-bottom: 1rem;
}

.form-group-sm {
  margin-bottom: 0.75rem;
}

.form-group-lg {
  margin-bottom: 1.25rem;
}

.form-label {
  display: block;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.form-group-sm .form-label {
  font-size: 0.75rem;
  margin-bottom: 0.25rem;
}

.form-group-lg .form-label {
  font-size: 1rem;
  margin-bottom: 0.75rem;
}

.required-asterisk {
  color: #ef4444;
  margin-left: 0.25rem;
}

.form-control-wrapper {
  width: 100%;
}

.form-help {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: #6b7280;
}

.form-errors {
  margin-top: 0.25rem;
}

.form-error {
  font-size: 0.75rem;
  color: #ef4444;
  margin-bottom: 0.125rem;
}

.form-error:last-child {
  margin-bottom: 0;
}

/* Layout variations */
.form-group-horizontal {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.form-group-horizontal .form-label {
  width: 150px;
  flex-shrink: 0;
  margin-bottom: 0;
  padding-top: 0.5rem;
}

.form-group-inline {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-group-inline .form-label {
  margin-bottom: 0;
  flex-shrink: 0;
}

.form-group-inline .form-control-wrapper {
  flex: 1;
}

/* Error state */
.form-group-error .form-label {
  color: #ef4444;
}

.form-group-error .form-control-wrapper :deep(input),
.form-group-error .form-control-wrapper :deep(select),
.form-group-error .form-control-wrapper :deep(textarea) {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Disabled state */
.form-group-disabled .form-label {
  color: #9ca3af;
}

.form-group-disabled .form-control-wrapper :deep(input),
.form-group-disabled .form-control-wrapper :deep(select),
.form-group-disabled .form-control-wrapper :deep(textarea) {
  background-color: #f9fafb;
  color: #9ca3af;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .form-group-horizontal {
    flex-direction: column;
    align-items: stretch;
  }
  
  .form-group-horizontal .form-label {
    width: auto;
    margin-bottom: 0.5rem;
    padding-top: 0;
  }
  
  .form-group-inline {
    flex-direction: column;
    align-items: stretch;
  }
  
  .form-group-inline .form-label {
    margin-bottom: 0.5rem;
  }
}
</style>
