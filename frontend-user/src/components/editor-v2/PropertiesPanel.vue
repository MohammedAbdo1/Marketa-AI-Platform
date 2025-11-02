<template>
  <div class="properties-panel">
    <!-- Panel Header -->
    <div class="panel-header">
      <h5 class="panel-title">{{ $t('editor.properties') }}</h5>
      <button class="btn-close" @click="$emit('close')">
        <i class='bx bx-x'></i>
      </button>
    </div>

    <!-- Properties Content -->
    <div class="properties-content">
      <!-- Position -->
      <div class="property-section">
        <h6 class="property-title">{{ $t('editor.position') }}</h6>
        <div class="property-grid-2">
          <div class="property-field">
            <label>X</label>
            <input 
              type="number"
              :value="Math.round(selectedObject.left)"
              @change="updateProperty('left', $event.target.value)"
              class="property-input"
            />
          </div>
          <div class="property-field">
            <label>Y</label>
            <input 
              type="number"
              :value="Math.round(selectedObject.top)"
              @change="updateProperty('top', $event.target.value)"
              class="property-input"
            />
          </div>
        </div>
      </div>

      <!-- Size -->
      <div class="property-section">
        <h6 class="property-title">{{ $t('editor.size') }}</h6>
        <div class="property-grid-2">
          <div class="property-field">
            <label>{{ $t('editor.width') }}</label>
            <input 
              type="number"
              :value="Math.round(selectedObject.width * selectedObject.scaleX)"
              @change="updateProperty('width', $event.target.value)"
              class="property-input"
            />
          </div>
          <div class="property-field">
            <label>{{ $t('editor.height') }}</label>
            <input 
              type="number"
              :value="Math.round(selectedObject.height * selectedObject.scaleY)"
              @change="updateProperty('height', $event.target.value)"
              class="property-input"
            />
          </div>
        </div>
      </div>

      <!-- Rotation -->
      <div class="property-section">
        <h6 class="property-title">{{ $t('editor.rotation') }}</h6>
        <div class="property-field">
          <input 
            type="range"
            min="0"
            max="360"
            :value="selectedObject.angle || 0"
            @input="updateProperty('angle', $event.target.value)"
            class="property-slider"
          />
          <span class="property-value">{{ Math.round(selectedObject.angle || 0) }}°</span>
        </div>
      </div>

      <!-- Opacity -->
      <div class="property-section">
        <h6 class="property-title">{{ $t('editor.opacity') }}</h6>
        <div class="property-field">
          <input 
            type="range"
            min="0"
            max="100"
            :value="(selectedObject.opacity || 1) * 100"
            @input="updateProperty('opacity', $event.target.value / 100)"
            class="property-slider"
          />
          <span class="property-value">{{ Math.round((selectedObject.opacity || 1) * 100) }}%</span>
        </div>
      </div>

      <!-- Fill Color (for shapes/text) -->
      <div v-if="selectedObject.type !== 'image'" class="property-section">
        <h6 class="property-title">{{ $t('editor.fill_color') }}</h6>
        <div class="color-picker-wrapper">
          <input 
            type="color"
            :value="selectedObject.fill || '#000000'"
            @change="updateProperty('fill', $event.target.value)"
            class="color-input"
          />
          <span class="color-value">{{ selectedObject.fill || '#000000' }}</span>
        </div>
      </div>

      <!-- Text Properties (for text objects) -->
      <div v-if="selectedObject.type === 'text'" class="property-section">
        <h6 class="property-title">{{ $t('editor.text_properties') }}</h6>
        
        <div class="property-field">
          <label>{{ $t('editor.font_size') }}</label>
          <input 
            type="number"
            :value="selectedObject.fontSize"
            @change="updateProperty('fontSize', $event.target.value)"
            class="property-input"
          />
        </div>

        <div class="property-field">
          <label>{{ $t('editor.font_weight') }}</label>
          <select 
            :value="selectedObject.fontWeight || 'normal'"
            @change="updateProperty('fontWeight', $event.target.value)"
            class="property-select"
          >
            <option value="normal">عادي</option>
            <option value="bold">عريض</option>
            <option value="900">عريض جداً</option>
          </select>
        </div>

        <div class="text-align-buttons">
          <button @click="updateProperty('textAlign', 'left')">
            <i class='bx bx-align-left'></i>
          </button>
          <button @click="updateProperty('textAlign', 'center')">
            <i class='bx bx-align-middle'></i>
          </button>
          <button @click="updateProperty('textAlign', 'right')">
            <i class='bx bx-align-right'></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const props = defineProps({
  selectedObject: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['property-change', 'close'])

const { t } = useI18n()

const updateProperty = (property, value) => {
  emit('property-change', property, parseFloat(value) || value)
}
</script>

<style scoped>
.properties-panel {
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

.properties-content {
  flex: 1;
  padding: 0.5rem 0;
}

.property-section {
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.property-title {
  margin: 0 0 0.75rem 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #2d3748;
}

.property-grid-2 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.property-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.property-field label {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}

.property-input,
.property-select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.9rem;
  color: #2d3748;
  transition: all 0.2s ease;
}

.property-input:focus,
.property-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.property-slider {
  width: 100%;
  flex: 1;
}

.property-value {
  min-width: 50px;
  text-align: center;
  font-size: 0.85rem;
  font-weight: 600;
  color: #64748b;
}

.color-picker-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.color-picker-wrapper:hover {
  border-color: #667eea;
}

.color-input {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.color-value {
  flex: 1;
  font-size: 0.9rem;
  color: #2d3748;
  font-family: 'Courier New', monospace;
}

.text-align-buttons {
  display: flex;
  gap: 0.5rem;
}

.text-align-buttons button {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: white;
  color: #64748b;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.text-align-buttons button:hover {
  border-color: #667eea;
  color: #667eea;
  background: #f8f9fa;
}
</style>

