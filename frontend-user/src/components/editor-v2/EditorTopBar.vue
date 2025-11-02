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
            <!-- Show Object Properties when object is selected -->
            <div v-if="selectedObject && selectedObject.type !== 'canvas-background'" class="object-properties">
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
import { ref, watch } from 'vue'
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 0.75rem;
  gap: 0.4rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  color: white;
  flex-shrink: 0;
}

.top-bar-left,
.top-bar-right {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  flex-shrink: 0;
}

.top-bar-center {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0 0.5rem;
  min-width: 0;
  overflow-x: auto;
  overflow-y: hidden;
}

/* Buttons */
.btn-icon {
  background: transparent;
  border: none;
  color: white;
  padding: 0.4rem;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.btn-icon:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.1);
}

.btn-icon:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.2) !important;
  color: #fca5a5 !important;
}

.btn-menu {
  background: transparent;
  border: none;
  color: white;
  padding: 0.4rem 0.75rem;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.85rem;
  white-space: nowrap;
}

.btn-menu:hover {
  background: rgba(255, 255, 255, 0.1);
}

.btn-primary-sm {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
}

.btn-primary-sm:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Divider */
.divider {
  width: 1px;
  height: 20px;
  background: rgba(255, 255, 255, 0.2);
  margin: 0 0.2rem;
  flex-shrink: 0;
}

/* Design Title Input */
.design-title-input {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 1rem;
  max-width: 400px;
  width: 100%;
  text-align: center;
}

.design-title-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.design-title-input:focus {
  outline: none;
  border-color: rgba(255, 255, 255, 0.4);
  background: rgba(255, 255, 255, 0.15);
}

/* Object Properties (Canva Style) */
.object-properties {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.4rem 1rem;
  border-radius: 8px;
  flex-wrap: nowrap;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.25);
  max-width: 100%;
  overflow-x: auto;
}

.property-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.property-group label {
  font-size: 1rem;
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.12);
  padding: 0.3rem;
  border-radius: 4px;
}

.property-input {
  background: rgba(255, 255, 255, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.35rem 0.5rem;
  border-radius: 4px;
  width: 55px;
  font-size: 0.85rem;
  text-align: center;
  font-weight: 500;
}

.property-input:focus {
  outline: none;
  border-color: rgba(255, 255, 255, 0.4);
  background: rgba(255, 255, 255, 0.2);
}

.property-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.property-value {
  font-size: 0.85rem;
  font-weight: 600;
  min-width: 40px;
  text-align: center;
  background: rgba(255, 255, 255, 0.12);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  white-space: nowrap;
}

.property-label {
  font-size: 0.9rem;
  color: #2d3748;
  font-weight: 500;
}

.opacity-slider {
  width: 70px;
  cursor: pointer;
}

.color-picker {
  width: 32px;
  height: 32px;
  border-radius: 4px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  cursor: pointer;
  background: transparent;
}

.color-picker::-webkit-color-swatch-wrapper {
  padding: 0;
}

.color-picker::-webkit-color-swatch {
  border: none;
  border-radius: 4px;
}

/* User Profile */
.user-profile {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

/* Autosave Status */
.autosave-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  opacity: 0.9;
}

.text-success {
  color: #4ade80;
}

.text-warning {
  color: #fbbf24;
}

/* Dropdown */
.dropdown {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  margin-top: 0.5rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  min-width: 200px;
  z-index: 1001;
  display: none;
}

.dropdown-menu.show {
  display: block;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: transparent;
  border: none;
  color: #2d3748;
  cursor: pointer;
  width: 100%;
  text-align: right;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.dropdown-item:hover:not(:disabled) {
  background: #f7fafc;
}

.dropdown-item:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.dropdown-divider {
  height: 1px;
  background: #e2e8f0;
  margin: 0.5rem 0;
}

.shortcut {
  margin-right: auto;
  font-size: 0.8rem;
  color: #a0aec0;
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
