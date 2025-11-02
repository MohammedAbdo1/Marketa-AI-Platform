<template>
  <div class="text-panel">
    <!-- Panel Header -->
    <div class="panel-header">
      <h5 class="panel-title">{{ $t('editor.panels.text') }}</h5>
      <button class="btn-close" @click="$emit('close-panel')">
        <i class='bx bx-x'></i>
      </button>
    </div>

    <!-- Quick Text Styles -->
    <div class="panel-section">
      <h6 class="section-title">{{ $t('editor.add_text') }}</h6>
      <div class="text-styles-grid">
        <button
          v-for="style in textStyles"
          :key="style.type"
          class="text-style-btn"
          @click="addText(style)"
        >
          <div class="text-preview" :style="style.preview">
            {{ style.label }}
          </div>
        </button>
      </div>
    </div>

    <!-- Font Families -->
    <div class="panel-section">
      <h6 class="section-title">{{ $t('editor.fonts') }}</h6>
      <div class="fonts-list">
        <button
          v-for="font in fonts"
          :key="font.family"
          class="font-item"
          @click="selectFont(font)"
          :class="{ active: selectedFont === font.family }"
        >
          <span :style="{ fontFamily: font.family }">{{ font.name }}</span>
          <i v-if="font.isPro" class='bx bx-crown text-warning'></i>
        </button>
      </div>
    </div>

    <!-- Font Combinations (Canva Feature) -->
    <div class="panel-section">
      <h6 class="section-title">{{ $t('editor.font_combinations') }}</h6>
      <div class="combinations-grid">
        <div
          v-for="combo in fontCombinations"
          :key="combo.id"
          class="combo-card"
          @click="applyFontCombo(combo)"
        >
          <div class="combo-preview">
            <p :style="{ fontFamily: combo.title.font, fontSize: '1.2rem', fontWeight: 700 }">
              {{ combo.title.text }}
            </p>
            <p :style="{ fontFamily: combo.body.font, fontSize: '0.85rem' }">
              {{ combo.body.text }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const emit = defineEmits(['add-element', 'close-panel'])

const { t } = useI18n()

const searchQuery = ref('')
const selectedFont = ref('Cairo')

const textStyles = [
  {
    type: 'heading',
    label: 'عنوان رئيسي',
    text: 'عنوان رئيسي',
    preview: {
      fontSize: '32px',
      fontWeight: '700',
      fontFamily: 'Cairo'
    }
  },
  {
    type: 'subheading',
    label: 'عنوان فرعي',
    text: 'عنوان فرعي',
    preview: {
      fontSize: '24px',
      fontWeight: '600',
      fontFamily: 'Cairo'
    }
  },
  {
    type: 'body',
    label: 'نص عادي',
    text: 'نص عادي',
    preview: {
      fontSize: '16px',
      fontWeight: '400',
      fontFamily: 'Cairo'
    }
  }
]

const fonts = [
  { family: 'Cairo', name: 'Cairo', isPro: false },
  { family: 'Tajawal', name: 'Tajawal', isPro: false },
  { family: 'Almarai', name: 'Almarai', isPro: false },
  { family: 'Amiri', name: 'Amiri', isPro: true },
  { family: 'Scheherazade', name: 'Scheherazade', isPro: true }
]

const fontCombinations = [
  {
    id: 1,
    title: { font: 'Cairo', text: 'العنوان' },
    body: { font: 'Tajawal', text: 'النص الأساسي' }
  },
  {
    id: 2,
    title: { font: 'Almarai', text: 'العنوان' },
    body: { font: 'Cairo', text: 'النص الأساسي' }
  }
]

const addText = (style) => {
  emit('add-element', {
    type: 'text',
    text: style.text,
    fontSize: parseInt(style.preview.fontSize),
    fontWeight: style.preview.fontWeight,
    fontFamily: style.preview.fontFamily,
    fill: '#000000',
    x: 100,
    y: 100
  })
}

const selectFont = (font) => {
  selectedFont.value = font.family
  // TODO: Apply to selected text
}

const applyFontCombo = (combo) => {
  console.log('Apply font combination:', combo)
  // TODO: Apply combination to canvas
}
</script>

<style scoped>
.text-panel {
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

.panel-search {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  position: sticky;
  top: 60px;
  background: white;
  z-index: 9;
}

.panel-search i {
  font-size: 1.2rem;
  color: #94a3b8;
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 0.95rem;
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

.text-styles-grid {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.text-style-btn {
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: right;
}

.text-style-btn:hover {
  border-color: #667eea;
  background: #f8f9fa;
}

.text-preview {
  color: #2d3748;
}

.fonts-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.font-item {
  padding: 0.75rem;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: space-between;
  text-align: right;
}

.font-item:hover {
  border-color: #667eea;
  background: #f8f9fa;
}

.font-item.active {
  border-color: #667eea;
  background: #ede9fe;
}

.combinations-grid {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.combo-card {
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
}

.combo-card:hover {
  border-color: #667eea;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(102, 126, 234, 0.2);
}

.combo-preview p {
  margin: 0.25rem 0;
  color: #2d3748;
}
</style>

