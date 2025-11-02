<template>
  <div class="uploads-panel">
    <!-- Panel Header -->
    <div class="panel-header">
      <h5 class="panel-title">{{ $t('editor.panels.uploads') }}</h5>
      <button class="btn-close" @click="$emit('close-panel')">
        <i class='bx bx-x'></i>
      </button>
    </div>

    <!-- Upload Button -->
    <div class="panel-section">
      <button class="btn-upload" @click="triggerUpload">
        <i class='bx bx-cloud-upload'></i>
        {{ $t('editor.upload_image') }}
      </button>
      <input 
        ref="fileInput"
        type="file"
        accept="image/*"
        multiple
        @change="handleFileUpload"
        style="display: none"
      />
    </div>

    <!-- Uploaded Images -->
    <div v-if="uploadedImages.length > 0" class="panel-section">
      <h6 class="section-title">{{ $t('editor.your_uploads') }}</h6>
      <div class="uploads-grid">
        <div
          v-for="image in uploadedImages"
          :key="image.id"
          class="upload-card"
          @click="addImageToCanvas(image)"
        >
          <img :src="image.url" :alt="image.name" />
          <div class="upload-overlay">
            <button class="btn-sm" @click.stop="deleteUpload(image.id)">
              <i class='bx bx-trash'></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Photos (Pro Feature) -->
    <div class="panel-section">
      <div class="pro-feature-header">
        <h6 class="section-title">{{ $t('editor.stock_photos') }}</h6>
        <span class="pro-badge">
          <i class='bx bx-crown'></i> Pro
        </span>
      </div>
      <p class="text-muted small">{{ $t('editor.upgrade_for_stock') }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const emit = defineEmits(['add-element', 'close-panel'])

const { t } = useI18n()

const fileInput = ref(null)
const uploadedImages = ref([])

const triggerUpload = () => {
  fileInput.value?.click()
}

const handleFileUpload = (event) => {
  const files = Array.from(event.target.files)
  
  files.forEach(file => {
    const reader = new FileReader()
    reader.onload = (e) => {
      uploadedImages.value.push({
        id: Date.now() + Math.random(),
        name: file.name,
        url: e.target.result
      })
    }
    reader.readAsDataURL(file)
  })
}

const addImageToCanvas = (image) => {
  emit('add-element', {
    type: 'image',
    url: image.url,
    x: 100,
    y: 100
  })
}

const deleteUpload = (id) => {
  uploadedImages.value = uploadedImages.value.filter(img => img.id !== id)
}
</script>

<style scoped>
.uploads-panel {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: white;
  overflow-y: auto;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1rem;
  border-bottom: 1px solid #e2e8f0;
  position: sticky;
  top: 0;
  background: white;
  z-index: 10;
}

.panel-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
}

.btn-close {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-close:hover {
  background: #f8f9fa;
}

.panel-section {
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.section-title {
  margin: 0 0 0.75rem 0;
  font-size: 0.85rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
}

.btn-upload {
  width: 100%;
  padding: 1rem;
  border-radius: 8px;
  border: 2px dashed #cbd5e1;
  background: #f8f9fa;
  color: #667eea;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  font-size: 1rem;
}

.btn-upload:hover {
  border-color: #667eea;
  background: #ede9fe;
}

.btn-upload i {
  font-size: 1.5rem;
}

.uploads-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.upload-card {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.upload-card:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.upload-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.upload-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.upload-card:hover .upload-overlay {
  opacity: 1;
}

.upload-overlay .btn-sm {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  background: white;
  color: #e53e3e;
  font-size: 1.2rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pro-feature-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.pro-badge {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: #f59e0b;
  font-weight: 600;
}
</style>

