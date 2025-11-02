<template>
  <div class="post-editor">
    <!-- Toolbar -->
    <div class="editor-toolbar">
      <div class="toolbar-section">
        <button @click="addText" class="btn btn-sm btn-outline-primary" title="إضافة نص">
          <i class="fas fa-font"></i> نص
        </button>
        <button @click="addShape('rect')" class="btn btn-sm btn-outline-primary" title="مربع">
          <i class="fas fa-square"></i>
        </button>
        <button @click="addShape('circle')" class="btn btn-sm btn-outline-primary" title="دائرة">
          <i class="fas fa-circle"></i>
        </button>
        <button @click="triggerImageUpload" class="btn btn-sm btn-outline-primary" title="رفع صورة">
          <i class="fas fa-image"></i> صورة
        </button>
        <input 
          ref="imageUploadInput" 
          type="file" 
          accept="image/*" 
          @change="handleImageUpload" 
          style="display: none"
        />
      </div>
      
      <div class="toolbar-section">
        <button @click="undo" :disabled="!canUndo" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-undo"></i>
        </button>
        <button @click="redo" :disabled="!canRedo" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-redo"></i>
        </button>
        <button @click="deleteSelected" :disabled="!hasSelection" class="btn btn-sm btn-outline-danger">
          <i class="fas fa-trash"></i>
        </button>
      </div>

      <div class="toolbar-section">
        <button @click="exportImage" class="btn btn-sm btn-success">
          <i class="fas fa-download"></i> تصدير
        </button>
        <button @click="saveManually" class="btn btn-sm btn-primary">
          <i class="fas fa-save"></i> حفظ
        </button>
      </div>

      <!-- Auto-save indicator -->
      <div class="autosave-indicator" v-if="isSaving">
        <i class="fas fa-spinner fa-spin"></i> جاري الحفظ...
      </div>
      <div class="autosave-indicator text-success" v-else-if="lastSaved">
        <i class="fas fa-check"></i> آخر حفظ: {{ lastSavedTime }}
      </div>
    </div>

    <div class="editor-content">
      <!-- Layers Panel -->
      <div class="layers-panel">
        <h6 class="panel-title">الطبقات</h6>
        <div class="layers-list">
          <div 
            v-for="layer in layers" 
            :key="layer.id"
            @click="selectLayer(layer)"
            :class="['layer-item', { active: layer.id === activeLayerId }]"
            draggable="true"
            @dragstart="dragStart(layer)"
            @dragover.prevent
            @drop="drop(layer)"
          >
            <div class="layer-info">
              <i :class="getLayerIcon(layer.type)"></i>
              <span class="layer-name">{{ layer.name || layer.type }}</span>
            </div>
            <div class="layer-actions">
              <button 
                @click.stop="toggleLayerVisibility(layer)" 
                class="btn-icon"
                :title="layer.visible ? 'إخفاء' : 'إظهار'"
              >
                <i :class="layer.visible ? 'fas fa-eye' : 'fas fa-eye-slash'"></i>
              </button>
              <button 
                @click.stop="deleteLayer(layer)" 
                class="btn-icon"
                title="حذف"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Canvas Container -->
      <div class="canvas-container">
        <canvas ref="fabricCanvas" id="editor-canvas"></canvas>
      </div>

      <!-- Properties Panel -->
      <div class="properties-panel" v-if="selectedObject">
        <h6 class="panel-title">الخصائص</h6>
        
        <!-- Text Properties -->
        <div v-if="selectedObject.type === 'text'" class="property-group">
          <label>النص:</label>
          <textarea 
            v-model="textContent" 
            @input="updateTextContent"
            class="form-control form-control-sm"
            rows="3"
          ></textarea>
          
          <label class="mt-2">حجم الخط:</label>
          <input 
            type="range" 
            v-model.number="fontSize" 
            @input="updateFontSize"
            min="12" 
            max="120" 
            class="form-range"
          />
          <span class="font-size-value">{{ fontSize }}px</span>
          
          <label class="mt-2">اللون:</label>
          <input 
            type="color" 
            v-model="fillColor" 
            @input="updateFillColor"
            class="form-control form-control-color"
          />
        </div>

        <!-- Shape Properties -->
        <div v-if="selectedObject.type !== 'text' && selectedObject.type !== 'image'" class="property-group">
          <label>لون التعبئة:</label>
          <input 
            type="color" 
            v-model="fillColor" 
            @input="updateFillColor"
            class="form-control form-control-color"
          />
          
          <label class="mt-2">سماكة الحدود:</label>
          <input 
            type="range" 
            v-model.number="strokeWidth" 
            @input="updateStrokeWidth"
            min="0" 
            max="20"
            class="form-range"
          />
        </div>

        <!-- Common Properties -->
        <div class="property-group mt-3">
          <label>الشفافية:</label>
          <input 
            type="range" 
            v-model.number="opacity" 
            @input="updateOpacity"
            min="0" 
            max="1" 
            step="0.1"
            class="form-range"
          />
          <span class="opacity-value">{{ Math.round(opacity * 100) }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import * as fabric from 'fabric';
import { usePostEditorStore } from '@/stores/postEditor';
import { useDesignStore } from '@/stores/design';
import { useRoute, useRouter } from 'vue-router';

// Stores
const editorStore = usePostEditorStore();
const designStore = useDesignStore();
const route = useRoute();
const router = useRouter();

// Editor Mode: 'post' or 'design'
const editorMode = ref('post');

// Refs
const fabricCanvas = ref(null);
const imageUploadInput = ref(null);
let canvas = null;
let autosaveTimer = null;

// State
const isSaving = ref(false);
const lastSaved = ref(null);
const selectedObject = ref(null);
const activeLayerId = ref(null);

// Properties
const textContent = ref('');
const fontSize = ref(24);
const fillColor = ref('#000000');
const strokeWidth = ref(1);
const opacity = ref(1);

// Computed
const layers = computed(() => editorStore.layers || []);
const canUndo = computed(() => editorStore.history?.past?.length > 0);
const canRedo = computed(() => editorStore.history?.future?.length > 0);
const hasSelection = computed(() => selectedObject.value !== null);
const lastSavedTime = computed(() => {
  if (!lastSaved.value) return '';
  const now = new Date();
  const diff = Math.floor((now - lastSaved.value) / 1000);
  if (diff < 60) return 'الآن';
  if (diff < 3600) return `منذ ${Math.floor(diff / 60)} دقيقة`;
  return `منذ ${Math.floor(diff / 3600)} ساعة`;
});

// Initialize Canvas
onMounted(async () => {
  // Initialize Fabric.js Canvas (v5) - fabric.fabric for namespace import
  const fabricLib = fabric.fabric || fabric;
  canvas = new fabricLib.Canvas('editor-canvas', {
    width: 1080,
    height: 1080,
    backgroundColor: '#ffffff'
  });

  // Determine editor mode and load data
  const designUuid = route.params.uuid;
  const postId = route.params.id;
  
  if (designUuid) {
    // Design mode
    editorMode.value = 'design';
    await loadDesign(designUuid);
  } else if (postId) {
    // Post mode (existing)
    editorMode.value = 'post';
    await editorStore.loadPost(postId);
    loadLayersToCanvas();
  }

  // Setup event listeners
  canvas.on('object:modified', handleObjectModified);
  canvas.on('object:added', handleObjectAdded);
  canvas.on('object:removed', handleObjectRemoved);
  canvas.on('selection:created', handleSelectionCreated);
  canvas.on('selection:updated', handleSelectionUpdated);
  canvas.on('selection:cleared', handleSelectionCleared);

  // Setup keyboard shortcuts
  document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  if (canvas) {
    canvas.dispose();
  }
  if (autosaveTimer) {
    clearTimeout(autosaveTimer);
  }
  document.removeEventListener('keydown', handleKeyDown);
});

// Load layers from store to canvas
function loadLayersToCanvas() {
  canvas.clear();
  
  const fabricLib = fabric.fabric || fabric;
  
  editorStore.layers.forEach(layer => {
    let fabricObject = null;
    
    switch (layer.type) {
      case 'text':
        fabricObject = new fabricLib.Text(layer.content, {
          left: layer.x,
          top: layer.y,
          fontSize: layer.fontSize || 24,
          fill: layer.fill || '#000000',
          opacity: layer.opacity || 1
        });
        break;
        
      case 'rect':
        fabricObject = new fabricLib.Rect({
          left: layer.x,
          top: layer.y,
          width: layer.width || 100,
          height: layer.height || 100,
          fill: layer.fill || '#3498db',
          opacity: layer.opacity || 1
        });
        break;
        
      case 'circle':
        fabricObject = new fabricLib.Circle({
          left: layer.x,
          top: layer.y,
          radius: layer.radius || 50,
          fill: layer.fill || '#e74c3c',
          opacity: layer.opacity || 1
        });
        break;
        
      case 'image':
        // Fabric v5: Callback-based
        fabricLib.Image.fromURL(layer.url, (img) => {
          img.set({
            left: layer.x,
            top: layer.y,
            scaleX: layer.scaleX || 1,
            scaleY: layer.scaleY || 1,
            opacity: layer.opacity || 1
          });
          img.layerId = layer.id;
          canvas.add(img);
          canvas.renderAll();
        });
        return; // Skip adding to canvas for now (async)
    }
    
    if (fabricObject) {
      fabricObject.layerId = layer.id;
      canvas.add(fabricObject);
    }
  });
  
  canvas.renderAll();
}

// Add Text
function addText() {
  const fabricLib = fabric.fabric || fabric;
  const text = new fabricLib.Text('اكتب النص هنا', {
    left: 100,
    top: 100,
    fontSize: 24,
    fill: '#000000'
  });
  
  canvas.add(text);
  canvas.setActiveObject(text);
  canvas.renderAll();
  
  triggerAutosave();
}

// Add Shape
function addShape(type) {
  const fabricLib = fabric.fabric || fabric;
  let shape = null;
  
  if (type === 'rect') {
    shape = new fabricLib.Rect({
      left: 150,
      top: 150,
      width: 200,
      height: 100,
      fill: '#3498db'
    });
  } else if (type === 'circle') {
    shape = new fabricLib.Circle({
      left: 150,
      top: 150,
      radius: 50,
      fill: '#e74c3c'
    });
  }
  
  if (shape) {
    canvas.add(shape);
    canvas.setActiveObject(shape);
    canvas.renderAll();
    triggerAutosave();
  }
}

// Image Upload
function triggerImageUpload() {
  imageUploadInput.value.click();
}

function handleImageUpload(event) {
  const file = event.target.files[0];
  if (!file) return;
  
  const fabricLib = fabric.fabric || fabric;
  const reader = new FileReader();
  reader.onload = (e) => {
    // Fabric v5: Callback-based
    fabricLib.Image.fromURL(e.target.result, (img) => {
      
      // Scale image to fit canvas
      const scale = Math.min(
        canvas.width / img.width,
        canvas.height / img.height,
        1
      );
      
      img.set({
        left: 50,
        top: 50,
        scaleX: scale,
        scaleY: scale
      });
      
      canvas.add(img);
      canvas.setActiveObject(img);
      canvas.renderAll();
      triggerAutosave();
    });
  };
  reader.readAsDataURL(file);
  
  // Reset input
  event.target.value = '';
}

// Event Handlers
function handleObjectModified(e) {
  updateLayerFromObject(e.target);
  triggerAutosave();
}

function handleObjectAdded(e) {
  const obj = e.target;
  if (!obj.layerId) {
    // New object - create layer
    const layer = createLayerFromObject(obj);
    editorStore.addLayer(layer);
    obj.layerId = layer.id;
  }
  triggerAutosave();
}

function handleObjectRemoved(e) {
  const layerId = e.target.layerId;
  if (layerId) {
    editorStore.deleteLayer(layerId);
  }
  triggerAutosave();
}

function handleSelectionCreated(e) {
  selectedObject.value = e.selected[0];
  updatePropertiesFromObject();
}

function handleSelectionUpdated(e) {
  selectedObject.value = e.selected[0];
  updatePropertiesFromObject();
}

function handleSelectionCleared() {
  selectedObject.value = null;
  activeLayerId.value = null;
}

// Properties Update
function updatePropertiesFromObject() {
  if (!selectedObject.value) return;
  
  const obj = selectedObject.value;
  activeLayerId.value = obj.layerId;
  
  if (obj.type === 'text') {
    textContent.value = obj.text;
    fontSize.value = obj.fontSize;
  }
  
  fillColor.value = obj.fill || '#000000';
  strokeWidth.value = obj.strokeWidth || 0;
  opacity.value = obj.opacity || 1;
}

function updateTextContent() {
  if (selectedObject.value && selectedObject.value.type === 'text') {
    selectedObject.value.set('text', textContent.value);
    canvas.renderAll();
    updateLayerFromObject(selectedObject.value);
    triggerAutosave();
  }
}

function updateFontSize() {
  if (selectedObject.value && selectedObject.value.type === 'text') {
    selectedObject.value.set('fontSize', fontSize.value);
    canvas.renderAll();
    updateLayerFromObject(selectedObject.value);
    triggerAutosave();
  }
}

function updateFillColor() {
  if (selectedObject.value) {
    selectedObject.value.set('fill', fillColor.value);
    canvas.renderAll();
    updateLayerFromObject(selectedObject.value);
    triggerAutosave();
  }
}

function updateStrokeWidth() {
  if (selectedObject.value) {
    selectedObject.value.set('strokeWidth', strokeWidth.value);
    canvas.renderAll();
    updateLayerFromObject(selectedObject.value);
    triggerAutosave();
  }
}

function updateOpacity() {
  if (selectedObject.value) {
    selectedObject.value.set('opacity', opacity.value);
    canvas.renderAll();
    updateLayerFromObject(selectedObject.value);
    triggerAutosave();
  }
}

// Layer Management
function createLayerFromObject(obj) {
  const layer = {
    id: Date.now() + Math.random(),
    type: obj.type,
    name: obj.type === 'text' ? obj.text : obj.type,
    x: obj.left,
    y: obj.top,
    visible: true,
    locked: false
  };
  
  if (obj.type === 'text') {
    layer.content = obj.text;
    layer.fontSize = obj.fontSize;
    layer.fill = obj.fill;
  } else if (obj.type === 'rect') {
    layer.width = obj.width;
    layer.height = obj.height;
    layer.fill = obj.fill;
  } else if (obj.type === 'circle') {
    layer.radius = obj.radius;
    layer.fill = obj.fill;
  } else if (obj.type === 'image') {
    layer.url = obj.getSrc();
    layer.scaleX = obj.scaleX;
    layer.scaleY = obj.scaleY;
  }
  
  layer.opacity = obj.opacity;
  layer.rotation = obj.angle || 0;
  
  return layer;
}

function updateLayerFromObject(obj) {
  const layer = editorStore.layers.find(l => l.id === obj.layerId);
  if (!layer) return;
  
  const updates = {
    x: obj.left,
    y: obj.top,
    rotation: obj.angle || 0,
    opacity: obj.opacity || 1
  };
  
  if (obj.type === 'text') {
    updates.content = obj.text;
    updates.fontSize = obj.fontSize;
    updates.fill = obj.fill;
  } else if (obj.type === 'rect') {
    updates.width = obj.width * obj.scaleX;
    updates.height = obj.height * obj.scaleY;
    updates.fill = obj.fill;
  } else if (obj.type === 'circle') {
    updates.radius = obj.radius * obj.scaleX;
    updates.fill = obj.fill;
  } else if (obj.type === 'image') {
    updates.scaleX = obj.scaleX;
    updates.scaleY = obj.scaleY;
  }
  
  editorStore.updateLayer(layer.id, updates);
}

function selectLayer(layer) {
  const obj = canvas.getObjects().find(o => o.layerId === layer.id);
  if (obj) {
    canvas.setActiveObject(obj);
    canvas.renderAll();
  }
}

function toggleLayerVisibility(layer) {
  const obj = canvas.getObjects().find(o => o.layerId === layer.id);
  if (obj) {
    obj.set('visible', !layer.visible);
    editorStore.updateLayer(layer.id, { visible: !layer.visible });
    canvas.renderAll();
    triggerAutosave();
  }
}

function deleteLayer(layer) {
  const obj = canvas.getObjects().find(o => o.layerId === layer.id);
  if (obj) {
    canvas.remove(obj);
    // handleObjectRemoved will be triggered
  }
}

function getLayerIcon(type) {
  const icons = {
    text: 'fas fa-font',
    rect: 'fas fa-square',
    circle: 'fas fa-circle',
    image: 'fas fa-image'
  };
  return icons[type] || 'fas fa-layer-group';
}

// Drag & Drop for layers
let draggedLayer = null;

function dragStart(layer) {
  draggedLayer = layer;
}

function drop(targetLayer) {
  if (!draggedLayer || draggedLayer.id === targetLayer.id) return;
  
  const fromIndex = layers.value.findIndex(l => l.id === draggedLayer.id);
  const toIndex = layers.value.findIndex(l => l.id === targetLayer.id);
  
  // Reorder layers in store
  const newLayers = [...layers.value];
  const [removed] = newLayers.splice(fromIndex, 1);
  newLayers.splice(toIndex, 0, removed);
  
  editorStore.layers = newLayers;
  
  // Reorder on canvas
  const obj = canvas.getObjects().find(o => o.layerId === draggedLayer.id);
  if (obj) {
    canvas.moveTo(obj, toIndex);
    canvas.renderAll();
  }
  
  draggedLayer = null;
  triggerAutosave();
}

// Actions
function deleteSelected() {
  const activeObj = canvas.getActiveObject();
  if (activeObj) {
    canvas.remove(activeObj);
    canvas.renderAll();
  }
}

function undo() {
  editorStore.undo();
  loadLayersToCanvas();
}

function redo() {
  editorStore.redo();
  loadLayersToCanvas();
}

async function exportImage() {
  const dataURL = canvas.toDataURL({
    format: 'png',
    quality: 1
  });
  
  // Create download link
  const link = document.createElement('a');
  link.download = `post-${Date.now()}.png`;
  link.href = dataURL;
  link.click();
}

// Autosave System
function triggerAutosave() {
  if (autosaveTimer) {
    clearTimeout(autosaveTimer);
  }
  
  autosaveTimer = setTimeout(async () => {
    await saveToBackend();
  }, 1500); // Auto-save after 1.5 seconds of inactivity
}

async function saveManually() {
  await saveToBackend();
}

async function saveToBackend() {
  isSaving.value = true;
  
  try {
    // Sync canvas state to store layers
    const canvasObjects = canvas.getObjects();
    canvasObjects.forEach(obj => {
      updateLayerFromObject(obj);
    });
    
    if (editorMode.value === 'design') {
      // Save design
      const designUuid = route.params.uuid;
      await designStore.updateDesign(designUuid, {
        composition_data: {
          layers: editorStore.layers,
          dimensions: {
            width: canvas.width,
            height: canvas.height
          }
        },
        canvas_settings: {
          background: canvas.backgroundColor
        },
        width: canvas.width,
        height: canvas.height
      });
    } else {
      // Save post (existing behavior)
      const postId = route.params.id;
      await editorStore.savePost(postId);
    }
    
    lastSaved.value = new Date();
  } catch (error) {
    console.error('Auto-save failed:', error);
  } finally {
    isSaving.value = false;
  }
}

// Load Design (for design mode)
async function loadDesign(uuid) {
  try {
    const design = await designStore.fetchDesign(uuid);
    
    // Set canvas dimensions
    if (design.width && design.height) {
      canvas.setDimensions({
        width: design.width,
        height: design.height
      });
    }
    
    // Load composition data
    if (design.composition_data && design.composition_data.layers) {
      editorStore.layers = design.composition_data.layers;
      loadLayersToCanvas();
    }
    
    // Set canvas settings if available
    if (design.canvas_settings && design.canvas_settings.background) {
      canvas.backgroundColor = design.canvas_settings.background;
      canvas.renderAll();
    }
  } catch (error) {
    console.error('Failed to load design:', error);
    alert('فشل تحميل التصميم');
  }
}

// Keyboard Shortcuts
function handleKeyDown(e) {
  // Ctrl/Cmd + Z: Undo
  if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
    e.preventDefault();
    undo();
  }
  
  // Ctrl/Cmd + Shift + Z: Redo
  if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) {
    e.preventDefault();
    redo();
  }
  
  // Delete/Backspace: Delete selected
  if ((e.key === 'Delete' || e.key === 'Backspace') && selectedObject.value) {
    e.preventDefault();
    deleteSelected();
  }
  
  // Ctrl/Cmd + S: Save
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    saveManually();
  }
}
</script>

<style scoped>
.post-editor {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #f5f5f5;
}

.editor-toolbar {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  background: white;
  border-bottom: 1px solid #e0e0e0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toolbar-section {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.toolbar-section:not(:last-child) {
  padding-right: 1rem;
  border-right: 1px solid #e0e0e0;
}

.autosave-indicator {
  margin-right: auto;
  font-size: 0.875rem;
  color: #666;
}

.editor-content {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.layers-panel {
  width: 280px;
  background: white;
  border-left: 1px solid #e0e0e0;
  padding: 1rem;
  overflow-y: auto;
}

.panel-title {
  font-weight: 600;
  margin-bottom: 1rem;
  color: #333;
}

.layers-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.layer-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s;
}

.layer-item:hover {
  background: #e9ecef;
}

.layer-item.active {
  background: #e3f2fd;
  border: 2px solid #2196f3;
}

.layer-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.layer-name {
  font-size: 0.875rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 150px;
}

.layer-actions {
  display: flex;
  gap: 0.25rem;
}

.btn-icon {
  background: none;
  border: none;
  padding: 0.25rem 0.5rem;
  cursor: pointer;
  color: #666;
  transition: color 0.2s;
}

.btn-icon:hover {
  color: #2196f3;
}

.canvas-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  overflow: auto;
}

#editor-canvas {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  border-radius: 0.5rem;
}

.properties-panel {
  width: 300px;
  background: white;
  border-right: 1px solid #e0e0e0;
  padding: 1rem;
  overflow-y: auto;
}

.property-group {
  margin-bottom: 1rem;
}

.property-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #555;
}

.font-size-value,
.opacity-value {
  font-size: 0.875rem;
  color: #666;
  margin-top: 0.25rem;
  display: inline-block;
}

.form-control-color {
  width: 100%;
  height: 40px;
  padding: 0.25rem;
  border-radius: 0.375rem;
  cursor: pointer;
}

/* RTL Support */
[dir="rtl"] .layers-panel {
  border-left: none;
  border-right: 1px solid #e0e0e0;
}

[dir="rtl"] .properties-panel {
  border-right: none;
  border-left: 1px solid #e0e0e0;
}
</style>

