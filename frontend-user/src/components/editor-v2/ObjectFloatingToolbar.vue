<template>
  <div 
    v-if="selectedObject" 
    class="object-floating-toolbar"
    :style="toolbarStyle"
  >
    <!-- Lock/Unlock -->
    <button 
      class="toolbar-btn" 
      @click="$emit('toggle-lock')"
      :title="isLocked ? 'فتح القفل' : 'قفل'"
    >
      <i :class="isLocked ? 'bx bx-lock-alt' : 'bx bx-lock-open-alt'"></i>
    </button>

    <!-- Duplicate -->
    <button class="toolbar-btn" @click="$emit('duplicate')" title="تكرار">
      <i class='bx bx-copy'></i>
    </button>

    <!-- Delete -->
    <button class="toolbar-btn btn-delete" @click="$emit('delete')" title="حذف">
      <i class='bx bx-trash'></i>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  selectedObject: {
    type: Object,
    required: true
  },
  canvasWidth: {
    type: Number,
    default: 1080
  },
  canvasHeight: {
    type: Number,
    default: 1080
  }
})

defineEmits(['toggle-lock', 'duplicate', 'delete'])

const isLocked = computed(() => props.selectedObject?.lockMovementX || false)

// Calculate toolbar position EXACTLY above the selected object
const toolbarStyle = computed(() => {
  if (!props.selectedObject) return { display: 'none' }
  
  const obj = props.selectedObject
  const bounds = obj._bounds || obj.getBoundingRect()
  
  // Position toolbar centered horizontally and 8px above the object
  const toolbarLeft = bounds.left + (bounds.width / 2)
  const toolbarTop = bounds.top - 42
  
  return {
    left: `${toolbarLeft}px`,
    top: `${toolbarTop}px`,
    transform: 'translateX(-50%)',
    display: 'flex'
  }
})
</script>

<style scoped>
.object-floating-toolbar {
  position: absolute;
  display: flex;
  align-items: center;
  gap: 2px;
  background: rgba(0, 0, 0, 0.88);
  padding: 4px 6px;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  z-index: 150;
  pointer-events: all;
  white-space: nowrap;
}

.toolbar-btn {
  background: transparent;
  border: none;
  color: white;
  padding: 5px 7px;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.toolbar-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.2);
  color: #fca5a5;
}

.toolbar-divider {
  width: 1px;
  height: 20px;
  background: rgba(255, 255, 255, 0.2);
  margin: 0 4px;
}
</style>

