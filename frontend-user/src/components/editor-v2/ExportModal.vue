<template>
  <div v-if="isOpen" class="export-modal-overlay" @click.self="close">
    <div class="export-modal" :class="{ 'rtl': isRTL }">
      <!-- Header -->
      <div class="modal-header">
        <button class="btn-back" @click="close">
          <i class='bx bx-arrow-back'></i>
        </button>
        <h3 class="modal-title">{{ $t('editor.export.title') || 'تنزيل' }}</h3>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <!-- File Type -->
        <div class="form-group">
          <label class="form-label">{{ $t('editor.export.file_type') || 'نوع الملف' }}</label>
          <div class="file-type-dropdown">
            <button 
              type="button"
              class="dropdown-trigger" 
              @click="showFileTypes = !showFileTypes"
            >
              <i :class="currentFileType.icon"></i>
              <span class="dropdown-label">{{ currentFileType.label }}</span>
              <span v-if="currentFileType.isSuggested" class="badge-suggested">مقترح</span>
              <i class='bx bx-chevron-down arrow-icon'></i>
            </button>
            
            <div v-if="showFileTypes" class="dropdown-options">
              <button 
                type="button"
                v-for="type in fileTypes" 
                :key="type.value"
                class="option-item"
                :class="{ selected: selectedFileType === type.value }"
                @click="selectFileType(type.value)"
              >
                <div class="option-main">
                  <i :class="type.icon"></i>
                  <span class="option-label">{{ type.label }}</span>
                  <span v-if="type.isSuggested" class="inline-badge">مقترح</span>
                  <span v-if="type.isPro" class="inline-badge pro">
                    <i class='bx bx-crown'></i>
                  </span>
                  <i v-if="selectedFileType === type.value" class='bx bx-check check-mark'></i>
                </div>
                <p class="option-desc">{{ type.description }}</p>
              </button>
            </div>
          </div>
        </div>

        <!-- Size/Quality -->
        <div class="form-group">
          <label class="form-label">
            {{ $t('editor.export.size') || 'المقاس' }} ×
          </label>
          <div class="size-control">
            <input 
              v-model.number="sizeMultiplier"
              type="number"
              min="0.5"
              max="4"
              step="0.5"
              class="size-input"
            />
            <span class="badge-pro">
              <i class='bx bx-crown'></i>
            </span>
            <input 
              v-model="sizeMultiplier"
              type="range"
              min="0.5"
              max="4"
              step="0.5"
              class="size-slider"
            />
          </div>
          <p class="size-info">
            {{ Math.round(designWidth * sizeMultiplier) }} × {{ Math.round(designHeight * sizeMultiplier) }} {{ $t('editor.export.pixels') || 'بكسل' }}
          </p>
        </div>

        <!-- Transparent Background (PNG only) -->
        <div v-if="selectedFileType === 'png'" class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="transparentBackground" />
            <span>{{ $t('editor.export.transparent_bg') || 'خلفية شفافة' }}</span>
            <span class="badge-pro">
              <i class='bx bx-crown'></i>
            </span>
          </label>
        </div>

        <!-- Compress File -->
        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="compressFile" />
            <span>{{ $t('editor.export.compress') || 'ضغط حجم الملف (جودة أقل)' }}</span>
            <span class="badge-pro">
              <i class='bx bx-crown'></i>
            </span>
          </label>
        </div>

        <!-- Save Settings -->
        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="saveSettings" />
            <span>{{ $t('editor.export.save_settings') || 'حفظ إعدادات التنزيل' }}</span>
          </label>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button class="btn-download" @click="handleDownload">
          <i class='bx bx-download'></i>
          {{ $t('editor.export.download') || 'تنزيل' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  designWidth: {
    type: Number,
    default: 1080
  },
  designHeight: {
    type: Number,
    default: 1080
  }
})

const emit = defineEmits(['close', 'export'])

const { locale } = useI18n()
const isRTL = computed(() => locale.value === 'ar')

// State
const selectedFileType = ref('png')
const showFileTypes = ref(false)
const sizeMultiplier = ref(1)
const transparentBackground = ref(false)
const compressFile = ref(false)
const saveSettings = ref(false)

const fileTypes = [
  { 
    value: 'jpg', 
    label: 'JPG', 
    description: 'الأفضل للمشاركة',
    icon: 'bx bx-image', 
    isPro: false 
  },
  { 
    value: 'png', 
    label: 'PNG', 
    description: 'الأفضل للصور المعقدة والرسوم التوضيحية',
    icon: 'bx bx-image', 
    isPro: false,
    isSuggested: true 
  },
  { 
    value: 'pdf-standard', 
    label: 'PDF عادي', 
    description: 'الأفضل للوثائق (ورسائل البريد الإلكتروني)',
    icon: 'bx bx-file', 
    isPro: true 
  },
  { 
    value: 'pdf-print', 
    label: 'PDF طباعة', 
    description: 'الأفضل للطباعة',
    icon: 'bx bx-file', 
    isPro: true 
  },
  { 
    value: 'svg', 
    label: 'SVG', 
    description: 'الأفضل لتصميم الويب والرسوم المتحركة',
    icon: 'bx bx-vector', 
    isPro: true 
  },
  { 
    value: 'mp4', 
    label: 'فيديو بتنسيق MP4', 
    description: 'فيديو عالي الجودة',
    icon: 'bx bx-video', 
    isPro: true 
  },
  { 
    value: 'gif', 
    label: 'GIF', 
    description: 'مقطع قصير، بلا صوت',
    icon: 'bx bx-image-alt', 
    isPro: true 
  },
  { 
    value: 'pptx', 
    label: 'PPTX', 
    description: 'مستند Microsoft PowerPoint',
    icon: 'bx bx-slideshow', 
    isPro: true 
  }
]

// Computed
const currentFileType = computed(() => {
  return fileTypes.find(t => t.value === selectedFileType.value) || fileTypes[1]
})

// Methods
const selectFileType = (type) => {
  selectedFileType.value = type
  showFileTypes.value = false
}

const close = () => {
  emit('close')
}

const handleDownload = () => {
  emit('export', {
    format: selectedFileType.value,
    sizeMultiplier: sizeMultiplier.value,
    transparentBackground: transparentBackground.value,
    compress: compressFile.value,
    width: Math.round(props.designWidth * sizeMultiplier.value),
    height: Math.round(props.designHeight * sizeMultiplier.value)
  })
  
  if (saveSettings.value) {
    localStorage.setItem('export_settings', JSON.stringify({
      format: selectedFileType.value,
      sizeMultiplier: sizeMultiplier.value,
      transparentBackground: transparentBackground.value,
      compress: compressFile.value
    }))
  }
  
  close()
}
</script>

<style scoped>
.export-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

.export-modal {
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 480px;
  max-height: 90vh;
  overflow: visible; /* Allow dropdown to overflow */
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.btn-back {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  background: #f8f9fa;
  color: #64748b;
  font-size: 1.3rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-back:hover {
  background: #e2e8f0;
  color: #667eea;
}

.modal-title {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: #2d3748;
}

.modal-body {
  padding: 1.5rem;
  max-height: calc(90vh - 200px);
  overflow-y: auto;
  overflow-x: visible; /* Allow dropdown to overflow horizontally */
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: #2d3748;
}

/* File Type Dropdown - Canva Style */
.file-type-dropdown {
  position: relative;
}

.dropdown-trigger {
  width: 100%;
  padding: 0.85rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.95rem;
  font-weight: 500;
  color: #2d3748;
}

.dropdown-trigger:hover {
  border-color: #667eea;
  background: #f8f9fa;
}

.dropdown-trigger i:first-child {
  font-size: 1.2rem;
  color: #667eea;
}

.dropdown-label {
  flex: 1;
  text-align: right;
  font-weight: 600;
}

.arrow-icon {
  font-size: 1.1rem;
  color: #64748b;
  transition: transform 0.2s;
}

.badge-suggested {
  padding: 0.25rem 0.6rem;
  background: #667eea;
  color: white;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
}

.dropdown-options {
  position: absolute;
  top: calc(100% + 0.5rem);
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  z-index: 100;
  max-height: 400px;
  overflow-y: auto;
}

.option-item {
  width: 100%;
  padding: 0.85rem 1rem;
  border: none;
  background: transparent;
  text-align: right;
  cursor: pointer;
  transition: background 0.15s;
  border-bottom: 1px solid #f1f5f9;
}

.option-item:last-child {
  border-bottom: none;
}

.option-item:hover {
  background: #f8f9fa;
}

.option-item.selected {
  background: #f0f4ff;
}

.option-main {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 0.3rem;
}

.option-main i:first-child {
  font-size: 1.2rem;
  color: #64748b;
}

.option-label {
  font-weight: 600;
  color: #2d3748;
  flex: 1;
  text-align: right;
}

.inline-badge {
  padding: 0.2rem 0.5rem;
  background: #667eea;
  color: white;
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: 600;
}

.inline-badge.pro {
  background: #fef3c7;
  color: #f59e0b;
  display: inline-flex;
  align-items: center;
  gap: 0.2rem;
}

.check-mark {
  font-size: 1.2rem;
  color: #667eea;
  margin-right: auto;
}

.option-desc {
  margin: 0;
  font-size: 0.82rem;
  color: #64748b;
  padding-right: 2rem;
}

.badge-pro {
  padding: 0.25rem 0.5rem;
  background: #fef3c7;
  color: #f59e0b;
  border-radius: 6px;
  font-size: 0.8rem;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.size-control {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.size-input {
  width: 60px;
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  text-align: center;
  font-weight: 600;
}

.size-slider {
  flex: 1;
  height: 6px;
  border-radius: 3px;
  background: #e2e8f0;
  outline: none;
  -webkit-appearance: none;
}

.size-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #667eea;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.size-slider::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #667eea;
  cursor: pointer;
  border: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.size-info {
  margin: 0.5rem 0 0 0;
  font-size: 0.85rem;
  color: #64748b;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  padding: 0.75rem;
  border-radius: 8px;
  transition: all 0.2s;
}

.checkbox-label:hover {
  background: #f8f9fa;
}

.checkbox-label input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.checkbox-label span {
  flex: 1;
  font-size: 0.95rem;
  color: #2d3748;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.btn-download {
  width: 100%;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

.btn-download:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
}

.btn-download i {
  font-size: 1.2rem;
}

/* RTL Support */
.rtl .select-btn span:nth-child(2),
.rtl .dropdown-item {
  text-align: right;
}

.rtl .btn-back i {
  transform: rotate(180deg);
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
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
</style>

