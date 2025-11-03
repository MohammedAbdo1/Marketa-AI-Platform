<template>
  <div class="editor-top-bar">
    <!-- Left Section -->
    <div class="top-bar-left">
      <button class="btn-icon close-btn" @click="$emit('close')" title="إغلاق">
        <i class='bx bx-x'></i>
      </button>

      <div class="divider"></div>

      <!-- File Menu -->
      <div class="dropdown">
        <button class="btn-menu" @click="toggleMenu('file')">
          ملف
          <i class='bx bx-chevron-down'></i>
        </button>
        <div v-if="activeMenu === 'file'" class="dropdown-menu show">
          <button class="dropdown-item" @click="$emit('save')">
            <i class='bx bx-save'></i> حفظ
          </button>
          <button class="dropdown-item" @click="$emit('export')">
            <i class='bx bx-download'></i> تصدير
          </button>
          <div class="dropdown-divider"></div>
          <button class="dropdown-item" @click="$emit('close')">
            <i class='bx bx-x'></i> إغلاق
          </button>
        </div>
      </div>

      <!-- Resize Button (Pro Feature) -->
      <button class="btn-menu" @click="$emit('resize')">
        <i class='bx bx-crown text-warning'></i>
        تغيير الحجم
      </button>

      <!-- Edit Menu -->
      <div class="dropdown">
        <button class="btn-menu" @click="toggleMenu('edit')">
          <i class='bx bx-edit-alt'></i>
          التعديل
          <i class='bx bx-chevron-down'></i>
        </button>
        <div v-if="activeMenu === 'edit'" class="dropdown-menu show">
          <button class="dropdown-item" @click="$emit('undo')" :disabled="!canUndo">
            <i class='bx bx-undo'></i> تراجع
            <span class="shortcut">Ctrl+Z</span>
          </button>
          <button class="dropdown-item" @click="$emit('redo')" :disabled="!canRedo">
            <i class='bx bx-redo'></i> إعادة
            <span class="shortcut">Ctrl+Y</span>
          </button>
        </div>
      </div>

      <div class="divider"></div>

      <!-- Undo/Redo -->
      <button class="btn-icon" @click="$emit('undo')" :disabled="!canUndo" title="تراجع">
        <i class='bx bx-undo'></i>
      </button>
      <button class="btn-icon" @click="$emit('redo')" :disabled="!canRedo" title="إعادة">
        <i class='bx bx-redo'></i>
      </button>
    </div>

    <!-- Center Section - Object Properties (Canva Style) or Design Title -->
    <div class="top-bar-center">
      <!-- Show Text Properties when text is selected -->
      <div v-if="isTextSelected" class="text-properties">
        <!-- Font Family -->
        <select 
          :value="selectedObject.fontFamily || 'Cairo'" 
          @change="updateProperty('fontFamily', $event.target.value)"
          class="font-select"
        >
          <option value="Cairo">Cairo</option>
          <option value="Tajawal">Tajawal</option>
          <option value="Almarai">Almarai</option>
          <option value="Amiri">Amiri</option>
          <option value="Scheherazade">Scheherazade</option>
          <option value="Arial">Arial</option>
          <option value="Helvetica">Helvetica</option>
        </select>

        <div class="divider"></div>

        <!-- Font Size -->
        <button class="btn-icon" @click="changeFontSize(-2)" title="تصغير">
          <i class='bx bx-minus'></i>
        </button>
        <input 
          type="number" 
          :value="Math.round(selectedObject.fontSize || 16)" 
          @change="updateProperty('fontSize', parseInt($event.target.value))"
          class="size-input"
          min="8"
          max="200"
        />
        <button class="btn-icon" @click="changeFontSize(2)" title="تكبير">
          <i class='bx bx-plus'></i>
        </button>

        <div class="divider"></div>

        <!-- Text Color -->
        <button class="btn-icon" title="لون النص">
          <i class='bx bx-font-color'></i>
        </button>
        <input 
          type="color" 
          :value="selectedObject.fill || '#000000'" 
          @input="updateProperty('fill', $event.target.value)"
          class="color-picker"
          title="لون النص"
        />

        <div class="divider"></div>

        <!-- Bold -->
        <button 
          class="btn-icon" 
          :class="{ active: isBold }"
          @click="toggleBold" 
          title="عريض"
        >
          <i class='bx bx-bold'></i>
        </button>

        <!-- Italic -->
        <button 
          class="btn-icon" 
          :class="{ active: isItalic }"
          @click="toggleItalic" 
          title="مائل"
        >
          <i class='bx bx-italic'></i>
        </button>

        <!-- Underline -->
        <button 
          class="btn-icon" 
          :class="{ active: isUnderline }"
          @click="toggleUnderline" 
          title="تحته خط"
        >
          <i class='bx bx-underline'></i>
        </button>

        <div class="divider"></div>

        <!-- Text Align -->
        <button 
          class="btn-icon" 
          :class="{ active: selectedObject.textAlign === 'left' }"
          @click="updateProperty('textAlign', 'left')" 
          title="محاذاة لليسار"
        >
          <i class='bx bx-align-left'></i>
        </button>
        <button 
          class="btn-icon" 
          :class="{ active: selectedObject.textAlign === 'center' }"
          @click="updateProperty('textAlign', 'center')" 
          title="محاذاة للوسط"
        >
          <i class='bx bx-align-middle'></i>
        </button>
        <button 
          class="btn-icon" 
          :class="{ active: selectedObject.textAlign === 'right' }"
          @click="updateProperty('textAlign', 'right')" 
          title="محاذاة لليمين"
        >
          <i class='bx bx-align-right'></i>
        </button>

        <div class="divider"></div>

        <!-- Letter Spacing -->
        <button class="btn-icon" title="تباعد الأحرف">
          <i class='bx bx-text'></i>
        </button>
        <input 
          type="range" 
          min="-50" 
          max="500" 
          step="10"
          :value="selectedObject.charSpacing || 0" 
          @input="updateProperty('charSpacing', parseInt($event.target.value))"
          class="spacing-slider"
        />

        <div class="divider"></div>

        <!-- Opacity -->
        <button class="btn-icon" title="الشفافية">
          <i class='bx bx-droplet'></i>
        </button>
        <input 
          type="range" 
          min="0" 
          max="1" 
          step="0.01" 
          :value="selectedObject.opacity || 1" 
          @input="updateProperty('opacity', parseFloat($event.target.value))"
          class="opacity-slider"
        />
        <span class="property-value">{{ Math.round((selectedObject.opacity || 1) * 100) }}%</span>
      </div>

      <!-- Show Object Properties when non-text object is selected -->
      <div v-else-if="selectedObject && selectedObject.type !== 'canvas-background' && !isTextSelected" class="object-properties">
        <!-- Rotation -->
        <button class="btn-icon" @click="rotateObject(-15)" title="تدوير لليسار">
          <i class='bx bx-rotate-left'></i>
        </button>
        <span class="property-value">{{ Math.round(selectedObject.angle || 0) }}°</span>
        <button class="btn-icon" @click="rotateObject(15)" title="تدوير لليمين">
          <i class='bx bx-rotate-right'></i>
        </button>

        <div class="divider"></div>

        <!-- Opacity -->
        <button class="btn-icon" title="الشفافية">
          <i class='bx bx-droplet'></i>
        </button>
        <input 
          type="range" 
          min="0" 
          max="1" 
          step="0.01" 
          :value="selectedObject.opacity || 1" 
          @input="updateProperty('opacity', $event.target.value)"
          class="opacity-slider"
        />
        <span class="property-value">{{ Math.round((selectedObject.opacity || 1) * 100) }}%</span>

        <div class="divider"></div>

        <!-- Color (for shapes/text) -->
        <input 
          v-if="selectedObject.fill" 
          type="color" 
          :value="selectedObject.fill" 
          @input="updateProperty('fill', $event.target.value)"
          class="color-picker"
          title="اللون"
              />

            </div>

            <!-- Show Canvas Background Color when canvas is selected -->
            <div v-else-if="selectedObject && selectedObject.type === 'canvas-background'" class="object-properties">
              <button class="btn-icon" title="لون الخلفية">
                <i class='bx bx-palette'></i>
              </button>
              <span class="property-label">{{ $t('editor.canvas_background') || 'خلفية التصميم' }}</span>
              <input 
                type="color" 
                :value="selectedObject.backgroundColor || '#ffffff'" 
                @input="updateCanvasBackground($event.target.value)"
                class="color-picker"
                title="لون الخلفية"
              />
            </div>

            <!-- Show Design Title when no object is selected -->
      <input 
        v-else
        v-model="editableTitle"
        class="design-title-input"
        :placeholder="$t('editor.untitled_design')"
        @blur="updateTitle"
        @keypress.enter="updateTitle"
      />
    </div>

    <!-- Right Section -->
    <div class="top-bar-right">
      <!-- Auto-save Status -->
      <div class="autosave-status">
        <template v-if="isSaving">
          <i class='bx bx-loader-alt bx-spin'></i>
          <span>{{ $t('editor.saving') }}...</span>
        </template>
        <template v-else-if="lastSaved">
          <i class='bx bx-cloud-check text-success'></i>
          <span>{{ $t('editor.saved') }}</span>
        </template>
      </div>

      <div class="divider"></div>

      <!-- Share Button -->
      <button class="btn-primary-sm" @click="$emit('share')">
        <i class='bx bx-share-alt'></i>
        مشاركة
      </button>

      <!-- User Profile -->
      <div class="user-profile">
        <img 
          :src="userAvatar" 
          :alt="userName"
          class="user-avatar"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  designTitle: {
    type: String,
    default: 'Untitled Design'
  },
  isSaving: Boolean,
  lastSaved: Date,
  canUndo: {
    type: Boolean,
    default: false
  },
  canRedo: {
    type: Boolean,
    default: false
  },
  selectedObject: {
    type: Object,
    default: null
  }
})

const emit = defineEmits([
  'close', 'save', 'export', 'share', 
  'undo', 'redo', 'resize', 'title-change',
  'update-property'
])

const { t } = useI18n()
const authStore = useAuthStore()

const editableTitle = ref(props.designTitle)
const activeMenu = ref(null)

const userName = authStore.user?.name || 'User'
const userAvatar = authStore.user?.avatar || '/default-avatar.png'

// Computed properties for text
const isTextSelected = computed(() => {
  const type = props.selectedObject?.type
  return type === 'text' || type === 'i-text' || type === 'textbox'
})

const isBold = computed(() => {
  return props.selectedObject?.fontWeight === 'bold' || props.selectedObject?.fontWeight >= 700
})

const isItalic = computed(() => {
  return props.selectedObject?.fontStyle === 'italic'
})

const isUnderline = computed(() => {
  return props.selectedObject?.underline === true
})

const toggleMenu = (menu) => {
  activeMenu.value = activeMenu.value === menu ? null : menu
}

const updateTitle = () => {
  if (editableTitle.value !== props.designTitle) {
    emit('title-change', editableTitle.value)
  }
}

const updateProperty = (property, value) => {
  emit('update-property', property, parseFloat(value) || value)
}

const rotateObject = (delta) => {
  const currentAngle = props.selectedObject?.angle || 0
  emit('update-property', 'angle', currentAngle + delta)
}

const changeFontSize = (delta) => {
  const currentSize = props.selectedObject?.fontSize || 16
  const newSize = Math.max(8, Math.min(200, currentSize + delta))
  emit('update-property', 'fontSize', newSize)
}

const toggleBold = () => {
  const newWeight = isBold.value ? 'normal' : 'bold'
  emit('update-property', 'fontWeight', newWeight)
}

const toggleItalic = () => {
  const newStyle = isItalic.value ? 'normal' : 'italic'
  emit('update-property', 'fontStyle', newStyle)
}

const toggleUnderline = () => {
  emit('update-property', 'underline', !isUnderline.value)
}

const updateCanvasBackground = (color) => {
  if (props.selectedObject?.setBackgroundColor) {
    props.selectedObject.setBackgroundColor(color)
    props.selectedObject.backgroundColor = color // Update the value for reactivity
  }
}


watch(() => props.designTitle, (newTitle) => {
  editableTitle.value = newTitle
})
</script>

<style scoped>
.editor-top-bar {
  height: 56px;
  background: var(--color-bg-primary);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 var(--space-3);
  gap: var(--space-2);
  box-shadow: var(--shadow-sm);
  border-bottom: 1px solid var(--color-border-light);
  z-index: var(--z-sticky);
  color: var(--color-text-primary);
  flex-shrink: 0;
}

.top-bar-left,
.top-bar-right {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  flex-shrink: 0;
}

.top-bar-center {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0 var(--space-2);
  min-width: 0;
  overflow-x: auto;
  overflow-y: hidden;
}

/* Buttons - Notion Style */
.btn-icon {
  background: transparent;
  border: none;
  color: var(--color-text-secondary);
  padding: var(--space-2);
  cursor: pointer;
  border-radius: var(--radius-md);
  transition: var(--transition-fast);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: var(--text-xl);
}

.btn-icon:hover:not(:disabled) {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
}

.btn-icon:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-delete:hover {
  background: var(--color-error-bg) !important;
  color: var(--color-error) !important;
}

.btn-menu {
  background: transparent;
  border: none;
  color: var(--color-text-primary);
  padding: var(--space-2) var(--space-3);
  cursor: pointer;
  border-radius: var(--radius-md);
  transition: var(--transition-fast);
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-sm);
  white-space: nowrap;
}

.btn-menu:hover {
  background: var(--color-bg-hover);
}

.btn-primary-sm {
  background: var(--color-brand-primary);
  border: 1px solid var(--color-brand-primary);
  color: var(--color-bg-primary);
  padding: var(--space-2) var(--space-4);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition-fast);
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-weight: var(--font-medium);
  font-size: var(--text-sm);
}

.btn-primary-sm:hover {
  background: var(--color-brand-primary-hover);
}

/* Divider */
.divider {
  width: 1px;
  height: 20px;
  background: var(--color-border-light);
  margin: 0 var(--space-1);
  flex-shrink: 0;
}

/* Design Title Input */
.design-title-input {
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border-medium);
  color: var(--color-text-primary);
  padding: var(--space-2) var(--space-4);
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  max-width: 400px;
  width: 100%;
  text-align: center;
}

.design-title-input::placeholder {
  color: var(--color-text-placeholder);
}

.design-title-input:focus {
  outline: none;
  border-color: var(--color-brand-primary);
  background: var(--color-bg-primary);
  box-shadow: var(--shadow-focus);
}

/* Object Properties (Notion Style) */
.object-properties {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  background: var(--color-bg-secondary);
  padding: var(--space-2) var(--space-4);
  border-radius: var(--radius-md);
  flex-wrap: nowrap;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--color-border-light);
  max-width: 100%;
  overflow-x: auto;
}

.property-group {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.property-group label {
  font-size: var(--text-sm);
  display: flex;
  align-items: center;
  background: transparent;
  padding: var(--space-1);
  border-radius: var(--radius-sm);
  color: var(--color-text-secondary);
}

.property-input {
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-medium);
  color: var(--color-text-primary);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  width: 55px;
  font-size: var(--text-sm);
  text-align: center;
  font-weight: var(--font-medium);
}

.property-input:focus {
  outline: none;
  border-color: var(--color-brand-primary);
  box-shadow: var(--shadow-focus);
}

.property-input::placeholder {
  color: var(--color-text-placeholder);
}

.property-value {
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  min-width: 40px;
  text-align: center;
  background: var(--color-bg-tertiary);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  white-space: nowrap;
  color: var(--color-text-primary);
}

.property-label {
  font-size: var(--text-sm);
  color: var(--color-text-primary);
  font-weight: var(--font-medium);
}

.opacity-slider {
  width: 70px;
  cursor: pointer;
}

.color-picker {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-sm);
  border: 1.5px solid var(--color-border-medium);
  cursor: pointer;
  background: transparent;
}

.color-picker::-webkit-color-swatch-wrapper {
  padding: 0;
}

.color-picker::-webkit-color-swatch {
  border: none;
  border-radius: var(--radius-sm);
}

/* Text Properties */
.text-properties {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-4);
  background: var(--color-bg-secondary);
  border-radius: var(--radius-md);
  flex-wrap: nowrap;
  overflow-x: auto;
  max-width: 100%;
  border: 1px solid var(--color-border-light);
}

.font-select {
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-medium);
  color: var(--color-text-primary);
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  cursor: pointer;
  min-width: 120px;
}

.font-select option {
  background: var(--color-bg-primary);
  color: var(--color-text-primary);
}

.size-input {
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-medium);
  color: var(--color-text-primary);
  padding: var(--space-2) var(--space-2);
  border-radius: var(--radius-sm);
  width: 55px;
  font-size: var(--text-sm);
  text-align: center;
  font-weight: var(--font-medium);
}

.size-input:focus {
  outline: none;
  border-color: var(--color-brand-primary);
  box-shadow: var(--shadow-focus);
}

.spacing-slider {
  width: 90px;
  cursor: pointer;
}

.btn-icon.active {
  background: var(--color-bg-hover);
  color: var(--color-brand-primary);
}

/* User Profile */
.user-profile {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  cursor: pointer;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-full);
  object-fit: cover;
  border: 1.5px solid var(--color-border-medium);
}

/* Autosave Status */
.autosave-status {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
}

.text-success {
  color: var(--color-success);
}

.text-warning {
  color: var(--color-warning);
}

/* Dropdown - استخدام design system dropdowns */

.shortcut {
  margin-right: auto;
  font-size: var(--text-xs);
  color: var(--color-text-tertiary);
  font-family: var(--font-mono);
  background: var(--color-bg-tertiary);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
}

[dir="rtl"] .shortcut {
  margin-right: 0;
  margin-left: auto;
}

/* Responsive */
@media (max-width: 1024px) {
  .object-properties {
    gap: 0.5rem;
    padding: 0.4rem 0.8rem;
  }

  .property-input {
    width: 50px;
    font-size: 0.85rem;
  }

  .btn-menu {
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
  }

  .divider {
    display: none;
  }
}

@media (max-width: 768px) {
  .editor-top-bar {
    padding: 0 0.5rem;
    gap: 0.25rem;
  }

  .btn-menu span {
    display: none;
  }

  .autosave-status span {
    display: none;
  }

  .object-properties {
    flex-wrap: nowrap;
    overflow-x: auto;
  }
}
</style>
