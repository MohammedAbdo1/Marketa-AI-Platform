<template>
  <Teleport to="body">
    <div 
      v-if="isVisible" 
      class="context-menu-overlay"
      @click="close"
      @contextmenu.prevent
    >
      <div 
        class="context-menu"
        :style="menuStyle"
        @click.stop
      >
        <!-- Copy -->
        <button 
          class="menu-item" 
          @click="handleAction('copy')"
          :disabled="!canCopy"
        >
          <span class="menu-label">نسخ</span>
          <div class="menu-right">
            <i class='bx bx-copy'></i>
            <span class="shortcut">مفتاح CTRL+C</span>
          </div>
        </button>

        <!-- Copy Style -->
        <button 
          v-if="selectedObject?.type !== 'image'"
          class="menu-item" 
          @click="handleAction('copy-style')"
          :disabled="!canCopyStyle"
        >
          <span class="menu-label">نسخ النمط</span>
          <div class="menu-right">
            <i class='bx bx-paint'></i>
            <span class="shortcut">مفتاح CTRL+Alt+C</span>
          </div>
        </button>

        <!-- Paste -->
        <button 
          class="menu-item" 
          @click="handleAction('paste')"
          :disabled="!canPaste"
        >
          <span class="menu-label">لصق</span>
          <div class="menu-right">
            <i class='bx bx-paste'></i>
            <span class="shortcut">مفتاح CTRL+V</span>
          </div>
        </button>

        <!-- Duplicate -->
        <button 
          class="menu-item" 
          @click="handleAction('duplicate')"
        >
          <span class="menu-label">تكرار</span>
          <div class="menu-right">
            <i class='bx bx-duplicate'></i>
            <span class="shortcut">مفتاح CTRL+D</span>
          </div>
        </button>

        <!-- Delete -->
        <button 
          class="menu-item danger" 
          @click="handleAction('delete')"
        >
          <span class="menu-label">حذف</span>
          <div class="menu-right">
            <i class='bx bx-trash'></i>
          </div>
        </button>

        <div class="menu-divider"></div>

        <!-- Lock/Unlock -->
        <button 
          class="menu-item" 
          @click="handleAction('toggle-lock')"
        >
          <span class="menu-label">{{ isLocked ? 'فتح القفل' : 'قفل' }}</span>
          <div class="menu-right">
            <i :class="isLocked ? 'bx bx-lock-open-alt' : 'bx bx-lock-alt'"></i>
          </div>
        </button>

        <!-- Align to Page -->
        <button 
          class="menu-item" 
          @click="handleAction('align')"
        >
          <span class="menu-label">محاذاة إلى الصفحة</span>
          <div class="menu-right">
            <i class='bx bx-align-middle'></i>
            <i class='bx bx-chevron-left arrow'></i>
          </div>
        </button>

        <div class="menu-divider"></div>

        <!-- Ungroup (for groups only) -->
        <button 
          v-if="isGroup"
          class="menu-item" 
          @click="handleAction('ungroup')"
        >
          <span class="menu-label">فك التجميع</span>
          <div class="menu-right">
            <i class='bx bx-unite'></i>
            <span class="shortcut">مفتاح CTRL+Shift+G</span>
          </div>
        </button>

        <!-- Edit (for images) -->
        <button 
          v-if="selectedObject?.type === 'image'"
          class="menu-item" 
          @click="handleAction('edit-image')"
        >
          <span class="menu-label">تعديل الصورة</span>
          <div class="menu-right">
            <i class='bx bx-edit'></i>
          </div>
        </button>

        <div class="menu-divider"></div>

        <!-- Bring Forward -->
        <button 
          class="menu-item" 
          @click="handleAction('bring-forward')"
        >
          <span class="menu-label">إحضار للأمام</span>
          <div class="menu-right">
            <i class='bx bx-up-arrow-alt'></i>
            <span class="shortcut">مفتاح CTRL+]</span>
          </div>
        </button>

        <!-- Send Backward -->
        <button 
          class="menu-item" 
          @click="handleAction('send-backward')"
        >
          <span class="menu-label">إرسال للخلف</span>
          <div class="menu-right">
            <i class='bx bx-down-arrow-alt'></i>
            <span class="shortcut">مفتاح CTRL+[</span>
          </div>
        </button>

        <div class="menu-divider"></div>

        <!-- Show Element Timing (for videos/animations) -->
        <button 
          class="menu-item" 
          @click="handleAction('show-timing')"
        >
          <span class="menu-label">عرض توقيت العنصر</span>
          <div class="menu-right">
            <i class='bx bx-time'></i>
          </div>
        </button>

        <!-- Add Comment -->
        <button 
          class="menu-item" 
          @click="handleAction('add-comment')"
        >
          <span class="menu-label">كتابة تعليق</span>
          <div class="menu-right">
            <i class='bx bx-comment-add'></i>
            <span class="shortcut">مفتاح CTRL+Alt+N</span>
          </div>
        </button>

        <div class="menu-divider"></div>

        <!-- Alt Text (for images) -->
        <button 
          v-if="selectedObject?.type === 'image'"
          class="menu-item" 
          @click="handleAction('alt-text')"
        >
          <span class="menu-label">نص بديل</span>
          <div class="menu-right">
            <i class='bx bx-user'></i>
          </div>
        </button>

        <!-- Translate Text (for text elements) -->
        <button 
          v-if="selectedObject?.type === 'text' || selectedObject?.type === 'i-text'"
          class="menu-item" 
          @click="handleAction('translate')"
        >
          <span class="menu-label">ترجمة نص</span>
          <div class="menu-right">
            <i class='bx bx-text'></i>
          </div>
        </button>

        <!-- Information -->
        <button 
          class="menu-item" 
          @click="handleAction('info')"
        >
          <span class="menu-label">معلومات</span>
          <div class="menu-right">
            <i class='bx bx-info-circle'></i>
          </div>
        </button>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false
  },
  position: {
    type: Object,
    default: () => ({ x: 0, y: 0 })
  },
  selectedObject: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'action'])

// Computed properties
const menuStyle = computed(() => ({
  left: `${props.position.x}px`,
  top: `${props.position.y}px`
}))

const isLocked = computed(() => props.selectedObject?.lockMovementX || false)
const isGroup = computed(() => props.selectedObject?.type === 'group')
const canCopy = computed(() => !!props.selectedObject)
const canCopyStyle = computed(() => !!props.selectedObject)
const canPaste = computed(() => {
  // Check if clipboard has content (simplified)
  return true
})

// Methods
const close = () => {
  emit('close')
}

const handleAction = (action) => {
  emit('action', action)
  close()
}
</script>

<style scoped>
.context-menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: var(--z-modal-backdrop);
  background: transparent;
}

.context-menu {
  position: fixed;
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-dropdown);
  min-width: 240px;
  padding: var(--space-2);
  z-index: var(--z-modal);
  animation: fadeInDown var(--duration-fast) var(--ease-out);
  direction: inherit;
}

.menu-item {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-2) var(--space-3);
  background: transparent;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition-fast);
  text-align: right;
  font-size: var(--text-sm);
  color: var(--color-text-primary);
}

[dir="ltr"] .menu-item {
  text-align: left;
}

.menu-item:hover:not(:disabled) {
  background: var(--color-bg-hover);
}

.menu-item:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.menu-item.danger {
  color: var(--color-error);
}

.menu-item.danger:hover:not(:disabled) {
  background: var(--color-error-bg);
}

.menu-label {
  font-weight: var(--font-medium);
  flex: 1;
}

.menu-right {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  color: var(--color-text-tertiary);
}

.menu-right i {
  font-size: var(--text-base);
}

.shortcut {
  font-size: var(--text-xs);
  color: var(--color-text-tertiary);
  font-weight: var(--font-normal);
  background: var(--color-bg-tertiary);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  font-family: var(--font-mono);
}

.menu-divider {
  height: 1px;
  background: var(--color-border-light);
  margin: var(--space-2) 0;
}
</style>

