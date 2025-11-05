<template>
  <div class="inline-editable-name">
    <div v-if="!isEditing" class="name-display" @mouseenter="showEdit = true" @mouseleave="showEdit = false">
      <span class="name-text">{{ modelValue || placeholder }}</span>
      <button 
        v-show="showEdit" 
        class="btn-edit" 
        @click.stop="startEdit"
        :aria-label="$t('common.edit')"
      >
        <i class="bx bx-pencil"></i>
      </button>
    </div>
    
    <div v-else class="name-edit">
      <input
        ref="inputRef"
        v-model="editValue"
        type="text"
        class="form-control form-control-sm"
        :placeholder="placeholder"
        :maxlength="maxLength"
        @blur="save"
        @keydown.enter="save"
        @keydown.esc="cancel"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'بدون عنوان'
  },
  maxLength: {
    type: Number,
    default: 255
  }
})

const emit = defineEmits(['update:modelValue', 'save'])

const isEditing = ref(false)
const showEdit = ref(false)
const editValue = ref(props.modelValue)
const inputRef = ref(null)
const saveTimeout = ref(null)

const startEdit = async () => {
  isEditing.value = true
  editValue.value = props.modelValue
  await nextTick()
  inputRef.value?.focus()
  inputRef.value?.select()
}

const save = () => {
  // Clear any pending save
  if (saveTimeout.value) {
    clearTimeout(saveTimeout.value)
  }
  
  // Debounce save by 500ms
  saveTimeout.value = setTimeout(() => {
    const trimmed = editValue.value.trim()
    if (trimmed && trimmed !== props.modelValue) {
      emit('update:modelValue', trimmed)
      emit('save', trimmed)
    }
    isEditing.value = false
    showEdit.value = false
  }, 500)
}

const cancel = () => {
  if (saveTimeout.value) {
    clearTimeout(saveTimeout.value)
  }
  editValue.value = props.modelValue
  isEditing.value = false
  showEdit.value = false
}
</script>

<style scoped>
.inline-editable-name {
  display: inline-block;
  width: 100%;
}

.name-display {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  cursor: pointer;
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  transition: var(--transition-fast);
}

.name-display:hover {
  background: var(--color-bg-hover);
}

.name-text {
  flex: 1;
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  color: var(--color-text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.btn-edit {
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  border-radius: var(--radius-sm);
  border: none;
  background: var(--color-bg-secondary);
  color: var(--color-text-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition-fast);
  opacity: 0;
  animation: fadeIn var(--duration-fast) forwards;
}

@keyframes fadeIn {
  to {
    opacity: 1;
  }
}

.btn-edit:hover {
  background: var(--color-bg-tertiary);
  color: var(--color-text-primary);
}

.name-edit {
  width: 100%;
}

.name-edit input {
  width: 100%;
}
</style>

