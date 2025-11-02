<template>
  <div class="elements-panel">
    <!-- Panel Header -->
    <div class="panel-header">
      <h5 class="panel-title">{{ $t('editor.panels.elements') }}</h5>
      <button class="btn-close" @click="$emit('close-panel')">
        <i class='bx bx-x'></i>
      </button>
    </div>

    <!-- Search -->
    <div class="panel-search">
      <i class='bx bx-search'></i>
      <input 
        v-model="searchQuery"
        type="text"
        :placeholder="$t('editor.search_elements')"
        class="search-input"
      />
    </div>

    <!-- Shapes -->
    <div class="panel-section">
      <h6 class="section-title">{{ $t('editor.shapes') }}</h6>
      <div class="elements-grid">
        <button
          v-for="shape in shapes"
          :key="shape.type"
          class="element-btn"
          @click="addShape(shape)"
          :title="shape.name"
        >
          <div class="shape-preview" :style="getShapeStyle(shape)"></div>
          <span class="element-name">{{ shape.name }}</span>
        </button>
      </div>
    </div>

    <!-- Lines -->
    <div class="panel-section">
      <h6 class="section-title">{{ $t('editor.lines') }}</h6>
      <div class="elements-grid">
        <button
          v-for="line in lines"
          :key="line.type"
          class="element-btn"
          @click="addLine(line)"
        >
          <div class="line-preview" :style="getLineStyle(line)"></div>
          <span class="element-name">{{ line.name }}</span>
        </button>
      </div>
    </div>

    <!-- Graphics/Icons -->
    <div class="panel-section">
      <h6 class="section-title">{{ $t('editor.graphics') }}</h6>
      <div class="graphics-grid">
        <div
          v-for="graphic in graphics"
          :key="graphic.id"
          class="graphic-card"
          @click="addGraphic(graphic)"
        >
          <i :class="graphic.icon" class="graphic-icon"></i>
          <span v-if="graphic.isPro" class="pro-badge">
            <i class='bx bx-crown'></i>
          </span>
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

const shapes = [
  { type: 'rect', name: 'مربع', width: 200, height: 200, fill: '#667eea' },
  { type: 'circle', name: 'دائرة', radius: 100, fill: '#f59e0b' },
  { type: 'triangle', name: 'مثلث', width: 200, height: 200, fill: '#10b981' },
  { type: 'star', name: 'نجمة', points: 5, radius: 100, fill: '#ef4444' }
]

const lines = [
  { type: 'line', name: 'خط', stroke: '#2d3748', strokeWidth: 2 },
  { type: 'dashed', name: 'خط متقطع', stroke: '#2d3748', strokeWidth: 2, strokeDashArray: [5, 5] },
  { type: 'arrow', name: 'سهم', stroke: '#2d3748', strokeWidth: 2 }
]

const graphics = [
  { id: 1, icon: 'bx bx-heart', isPro: false },
  { id: 2, icon: 'bx bx-star', isPro: false },
  { id: 3, icon: 'bx bx-crown', isPro: true },
  { id: 4, icon: 'bx bx-moon', isPro: false },
  { id: 5, icon: 'bx bx-sun', isPro: false },
  { id: 6, icon: 'bx bx-cloud', isPro: false }
]

const getShapeStyle = (shape) => {
  if (shape.type === 'circle') {
    return {
      width: '40px',
      height: '40px',
      borderRadius: '50%',
      background: shape.fill
    }
  } else if (shape.type === 'triangle') {
    return {
      width: 0,
      height: 0,
      borderLeft: '20px solid transparent',
      borderRight: '20px solid transparent',
      borderBottom: `40px solid ${shape.fill}`
    }
  } else if (shape.type === 'star') {
    return {
      color: shape.fill,
      fontSize: '2rem'
    }
  }
  
  return {
    width: '40px',
    height: '40px',
    background: shape.fill,
    borderRadius: '4px'
  }
}

const getLineStyle = (line) => {
  return {
    width: '60px',
    height: line.strokeWidth + 'px',
    background: line.stroke,
    borderRadius: '2px'
  }
}

const addShape = (shape) => {
  emit('add-element', {
    type: shape.type,
    ...shape,
    x: 100,
    y: 100
  })
}

const addLine = (line) => {
  emit('add-element', {
    type: line.type,
    ...line,
    x1: 100,
    y1: 100,
    x2: 300,
    y2: 100
  })
}

const addGraphic = (graphic) => {
  emit('add-element', {
    type: 'graphic',
    icon: graphic.icon,
    x: 100,
    y: 100
  })
}
</script>

<style scoped>
.elements-panel {
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

.elements-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
}

.element-btn {
  aspect-ratio: 1;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
}

.element-btn:hover {
  border-color: #667eea;
  background: #f8f9fa;
  transform: scale(1.05);
}

.shape-preview {
  transition: transform 0.2s ease;
}

.element-name {
  font-size: 0.75rem;
  color: #64748b;
  text-align: center;
}

.graphics-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.75rem;
}

.graphic-card {
  aspect-ratio: 1;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.graphic-card:hover {
  border-color: #667eea;
  background: #f8f9fa;
  transform: scale(1.05);
}

.graphic-icon {
  font-size: 1.8rem;
  color: #667eea;
}

.pro-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  font-size: 0.7rem;
  color: #f59e0b;
}

.line-preview {
  margin: 1rem 0;
}

.categories-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.category-chip {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.category-chip:hover {
  border-color: #667eea;
  color: #667eea;
}

.category-chip.active {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

.templates-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.template-card {
  cursor: pointer;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.template-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.template-preview {
  width: 100%;
  aspect-ratio: 1 / 1;
  background: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
}

.template-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.template-placeholder {
  font-size: 2.5rem;
  color: #cbd5e1;
}

.template-info {
  padding: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.template-title {
  margin: 0;
  font-size: 0.85rem;
  color: #2d3748;
}
</style>

