<template>
  <div class="main-canvas-wrapper">
    <!-- Canvas Controls (Top) -->
    <div class="canvas-controls-top">
      <button class="control-btn" @click="duplicatePage" title="نسخ الصفحة">
        <i class='bx bx-copy'></i>
      </button>
      <button class="control-btn" @click="addPage" title="إضافة صفحة">
        <i class='bx bx-plus'></i>
      </button>
      <button 
        class="control-btn" 
        @click="toggleLock" 
        :title="isLocked ? 'فك القفل' : 'قفل'"
      >
        <i :class="isLocked ? 'bx bx-lock' : 'bx bx-lock-open'"></i>
      </button>
    </div>

    <!-- Canvas Container -->
    <div class="canvas-container" ref="canvasContainer">
      <div class="canvas-wrapper" :style="canvasWrapperStyle">
        <canvas ref="fabricCanvas" :id="canvasId"></canvas>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import * as fabric from 'fabric'

const props = defineProps({
  designData: Object,
  width: {
    type: Number,
    default: 1080
  },
  height: {
    type: Number,
    default: 1080
  }
})

const emit = defineEmits([
  'canvas-ready',
  'object-selected',
  'canvas-modified',
  'zoom-change'
])

const canvasId = `canvas-${Math.random().toString(36).substr(2, 9)}`
const fabricCanvas = ref(null)
const canvasContainer = ref(null)
const canvas = ref(null)
const currentZoom = ref(30) // Default zoom: 30%
const isLocked = ref(false)

const canvasWrapperStyle = computed(() => ({
  width: `${props.width}px`,
  height: `${props.height}px`,
  transform: `scale(${currentZoom.value / 100})`
}))

// Initialize Fabric.js
onMounted(async () => {
  try {
    const fabricLib = fabric.fabric || fabric
    
    canvas.value = new fabricLib.Canvas(canvasId, {
      width: props.width,
      height: props.height,
      backgroundColor: '#ffffff',
      preserveObjectStacking: true,
      selection: true,
      enableRetinaScaling: true
    })
    
    // Customize Fabric.js controls to be more visible like Canva
    const defaultControls = {
      // Visual appearance
      transparentCorners: false,
      cornerColor: '#fff',
      cornerStrokeColor: '#667eea',
      borderColor: '#667eea',
      cornerSize: 14,
      padding: 0,
      cornerStyle: 'circle',
      borderDashArray: [5, 5],
      borderScaleFactor: 2,
      
      // Enable all controls
      hasControls: true,
      hasBorders: true,
      selectable: true,
      evented: true,
      
      // Enable resizing and rotation
      lockScalingX: false,
      lockScalingY: false,
      lockRotation: false,
      lockMovementX: false,
      lockMovementY: false,
      
      // Enable all corner controls
      hasRotatingPoint: true,
      centeredScaling: false,
      centeredRotation: true
    }
    
    // Apply to all object types
    Object.assign(fabricLib.Object.prototype, defaultControls)
    if (fabricLib.Rect) Object.assign(fabricLib.Rect.prototype, defaultControls)
    if (fabricLib.Circle) Object.assign(fabricLib.Circle.prototype, defaultControls)
    if (fabricLib.Triangle) Object.assign(fabricLib.Triangle.prototype, defaultControls)
    if (fabricLib.Image) Object.assign(fabricLib.Image.prototype, defaultControls)
    if (fabricLib.Text) Object.assign(fabricLib.Text.prototype, defaultControls)
    if (fabricLib.IText) Object.assign(fabricLib.IText.prototype, defaultControls)
    if (fabricLib.Textbox) Object.assign(fabricLib.Textbox.prototype, defaultControls)

    console.log('Canvas initialized, designData:', props.designData)

    // Event listeners
    canvas.value.on('selection:created', handleSelection)
    canvas.value.on('selection:updated', handleSelection)
    canvas.value.on('selection:cleared', () => emit('object-selected', null))
    canvas.value.on('object:modified', () => emit('canvas-modified'))
    canvas.value.on('mouse:down', handleCanvasClick)

    emit('canvas-ready', canvas.value)

    // Load design data if available (after a small delay to ensure canvas is ready)
    setTimeout(async () => {
      if (props.designData) {
        console.log('Loading design data into canvas:', props.designData)
          await loadDesignData(props.designData)
        }
      }, 100)
  } catch (error) {
    console.error('Canvas initialization failed:', error)
  }
})

onBeforeUnmount(() => {
  if (canvas.value) {
    canvas.value.dispose()
  }
})

// Methods
const handleSelection = (e) => {
  const selectedObj = e.selected?.[0] || null
  
  // Calculate object bounds for toolbar positioning
  if (selectedObj) {
    const bounds = selectedObj.getBoundingRect()
    selectedObj._bounds = bounds
  }
  
  emit('object-selected', selectedObj)
}

const loadDesignData = async (data) => {
  if (!canvas.value || !data) {
    console.warn('Canvas or data not available:', { canvas: !!canvas.value, data: !!data })
    return
  }
  
  try {
    console.log('Loading design data, layers count:', data.layers?.length)
    
    // Clear existing objects
    canvas.value.clear()
    canvas.value.backgroundColor = '#ffffff'
    
    if (data.layers && Array.isArray(data.layers)) {
      // Load each layer/element
      for (let i = 0; i < data.layers.length; i++) {
        const layer = data.layers[i]
        console.log(`Loading layer ${i}:`, layer.type, layer)
        await addLayerToCanvas(layer)
      }
      
      canvas.value.renderAll()
      console.log('All layers loaded successfully. Total objects:', canvas.value.getObjects().length)
    } else {
      console.warn('No layers found in data')
    }
  } catch (error) {
    console.error('Failed to load design data:', error)
  }
}

// Watch for design data changes
watch(() => props.designData, (newData) => {
  console.log('Design data changed:', newData)
  if (newData && canvas.value) {
    loadDesignData(newData)
  }
}, { deep: true })

const addLayerToCanvas = async (layer) => {
  const fabricLib = fabric.fabric || fabric
  
  try {
    switch (layer.type) {
      case 'text':
        const text = new fabricLib.FabricText(layer.text || 'نص', {
          left: layer.left || layer.x || 100,
          top: layer.top || layer.y || 100,
          fontSize: layer.fontSize || 32,
          fill: layer.fill || '#000000',
          fontFamily: layer.fontFamily || 'Cairo',
          fontWeight: layer.fontWeight || 'normal',
          angle: layer.angle || 0,
          scaleX: layer.scaleX || 1,
          scaleY: layer.scaleY || 1,
          opacity: layer.opacity !== undefined ? layer.opacity : 1
        })
        canvas.value.add(text)
        break

      case 'image':
        if (layer.url || layer.src) {
          let imageUrl = layer.url || layer.src
          
          // Convert AI service URLs to Laravel proxy URLs for CORS support
          if (imageUrl.includes('/static/images/')) {
            const filename = imageUrl.split('/').pop()
            // Use Laravel backend URL (port 8000), not AI service URL (port 8001)
            imageUrl = `http://localhost:8000/api/images/${filename}`
          }
          
          console.log('Loading image from URL:', imageUrl)
          
          return new Promise((resolve, reject) => {
            // Create image element with CORS enabled FIRST
            const imgElement = document.createElement('img')
            imgElement.crossOrigin = 'anonymous'
            
            imgElement.onload = () => {
              // Now create Fabric image from the loaded element
              fabricLib.Image.fromURL(imageUrl, (img) => {
                if (!img) {
                  console.error('Failed to load image from:', imageUrl)
                  reject(new Error('Image load failed'))
                  return
                }
                
                // Replace the internal image element with our CORS-enabled one
                if (img.getElement()) {
                  img.setElement(imgElement)
                }
                
                img.set({
                  left: layer.left || layer.x || 0,
                  top: layer.top || layer.y || 0,
                  scaleX: layer.scaleX || 1,
                  scaleY: layer.scaleY || 1,
                  angle: layer.angle || 0,
                  opacity: layer.opacity !== undefined ? layer.opacity : 1
                })
                
                canvas.value.add(img)
                canvas.value.renderAll()
                console.log('✅ Image added with CORS support from:', imageUrl)
                resolve()
              })
            }
            
            imgElement.onerror = () => {
              console.error('Failed to load image:', imageUrl)
              reject(new Error('Image load failed'))
            }
            
            imgElement.src = imageUrl
          })
        }
        break

      case 'rect':
        const rect = new fabricLib.Rect({
          left: layer.left || layer.x || 100,
          top: layer.top || layer.y || 100,
          width: layer.width || 200,
          height: layer.height || 200,
          fill: layer.fill || '#667eea',
          angle: layer.angle || 0,
          scaleX: layer.scaleX || 1,
          scaleY: layer.scaleY || 1,
          opacity: layer.opacity !== undefined ? layer.opacity : 1,
          rx: layer.rx || 0,
          ry: layer.ry || 0
        })
        canvas.value.add(rect)
        break

      case 'circle':
        const circle = new fabricLib.Circle({
          left: layer.left || layer.x || 100,
          top: layer.top || layer.y || 100,
          radius: layer.radius || 50,
          fill: layer.fill || '#667eea',
          angle: layer.angle || 0,
          scaleX: layer.scaleX || 1,
          scaleY: layer.scaleY || 1,
          opacity: layer.opacity !== undefined ? layer.opacity : 1
        })
        canvas.value.add(circle)
        break

      case 'triangle':
        const triangle = new fabricLib.Triangle({
          left: layer.left || layer.x || 100,
          top: layer.top || layer.y || 100,
          width: layer.width || 100,
          height: layer.height || 100,
          fill: layer.fill || '#10b981',
          angle: layer.angle || 0,
          scaleX: layer.scaleX || 1,
          scaleY: layer.scaleY || 1,
          opacity: layer.opacity !== undefined ? layer.opacity : 1
        })
        canvas.value.add(triangle)
        break

      case 'line':
        const line = new fabricLib.Line(
          [layer.x1 || 50, layer.y1 || 50, layer.x2 || 200, layer.y2 || 50],
          {
            stroke: layer.stroke || '#2d3748',
            strokeWidth: layer.strokeWidth || 2,
            strokeDashArray: layer.strokeDashArray || null,
            angle: layer.angle || 0,
            opacity: layer.opacity !== undefined ? layer.opacity : 1
          }
        )
        canvas.value.add(line)
        break

      default:
        console.warn('Unknown layer type:', layer.type)
    }
  } catch (error) {
    console.error('Error adding layer to canvas:', error, layer)
  }
}

const addElement = async (element) => {
  // Will be called from panels to add new elements
  await addLayerToCanvas(element)
  canvas.value.renderAll()
  
  // Auto-select the newly added element
  const objects = canvas.value.getObjects()
  if (objects.length > 0) {
    const newObject = objects[objects.length - 1]
    canvas.value.setActiveObject(newObject)
    canvas.value.renderAll()
  }
  
  // Trigger save after adding element
  emit('canvas-modified')
}

const updateObjectProperty = (property, value) => {
  const activeObject = canvas.value?.getActiveObject()
  if (activeObject) {
    activeObject.set(property, value)
    canvas.value.renderAll()
  }
}

const exportData = () => {
  if (!canvas.value) return null
  
  // Export all objects with their properties
  const layers = canvas.value.getObjects().map(obj => {
    const baseProps = {
      type: obj.type,
      left: obj.left,
      top: obj.top,
      width: obj.width,
      height: obj.height,
      scaleX: obj.scaleX,
      scaleY: obj.scaleY,
      angle: obj.angle,
      opacity: obj.opacity,
      fill: obj.fill,
      stroke: obj.stroke,
      strokeWidth: obj.strokeWidth
    }

    // Add type-specific properties
    if (obj.type === 'text' || obj.type === 'i-text' || obj.type === 'textbox') {
      return {
        ...baseProps,
        text: obj.text,
        fontSize: obj.fontSize,
        fontFamily: obj.fontFamily,
        fontWeight: obj.fontWeight,
        textAlign: obj.textAlign
      }
    } else if (obj.type === 'image') {
      return {
        ...baseProps,
        url: obj.getSrc ? obj.getSrc() : obj._originalElement?.src,
        src: obj.getSrc ? obj.getSrc() : obj._originalElement?.src
      }
    } else if (obj.type === 'circle') {
      return {
        ...baseProps,
        radius: obj.radius
      }
    } else if (obj.type === 'line') {
      return {
        ...baseProps,
        x1: obj.x1,
        y1: obj.y1,
        x2: obj.x2,
        y2: obj.y2,
        strokeDashArray: obj.strokeDashArray
      }
    }

    return baseProps
  })

  return {
    layers,
    dimensions: {
      width: props.width,
      height: props.height
    }
  }
}

const exportImage = () => {
  if (!canvas.value) return
  
  const dataURL = canvas.value.toDataURL({
    format: 'png',
    quality: 1
  })

  const link = document.createElement('a')
  link.download = 'design.png'
  link.href = dataURL
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const undo = () => {
  // TODO: Implement undo/redo with history
  console.log('Undo')
}

const redo = () => {
  // TODO: Implement undo/redo with history
  console.log('Redo')
}

const setZoom = (zoom) => {
  currentZoom.value = zoom
  emit('zoom-change', currentZoom.value)
}

const setPage = (page) => {
  // TODO: Implement multi-page support
  console.log('Set page:', page)
}

const addPage = () => {
  emit('add-page')
}

const duplicatePage = () => {
  // TODO: Implement page duplication
  console.log('Duplicate page')
}

const toggleLock = () => {
  const activeObject = canvas.value?.getActiveObject()
  if (activeObject) {
    activeObject.set({
      lockMovementX: !isLocked.value,
      lockMovementY: !isLocked.value,
      lockRotation: !isLocked.value,
      lockScalingX: !isLocked.value,
      lockScalingY: !isLocked.value
    })
    isLocked.value = !isLocked.value
    canvas.value.renderAll()
  }
}

const dispose = () => {
  if (canvas.value) {
    canvas.value.dispose()
    canvas.value = null
  }
}

const deleteSelected = () => {
  const activeObject = canvas.value?.getActiveObject()
  if (activeObject) {
    canvas.value.remove(activeObject)
    canvas.value.discardActiveObject()
    canvas.value.renderAll()
    emit('object-selected', null)
    emit('canvas-modified')
  }
}

const bringForward = () => {
  const activeObject = canvas.value?.getActiveObject()
  if (activeObject) {
    canvas.value.bringForward(activeObject)
    canvas.value.renderAll()
    emit('canvas-modified')
  }
}

const sendBackward = () => {
  const activeObject = canvas.value?.getActiveObject()
  if (activeObject) {
    canvas.value.sendBackwards(activeObject)
    canvas.value.renderAll()
    emit('canvas-modified')
  }
}

const handleCanvasClick = (e) => {
  // If clicked on empty canvas (not on any object), emit canvas background selection
  if (!e.target) {
    emit('canvas-background-selected', {
      type: 'canvas-background',
      backgroundColor: canvas.value.backgroundColor || '#ffffff',
      setBackgroundColor: (color) => {
        canvas.value.backgroundColor = color
        canvas.value.renderAll()
      }
    })
  }
}

// Expose methods and canvas
defineExpose({
  canvas,
  addElement,
  updateObjectProperty,
  exportData,
  exportImage,
  undo,
  redo,
  setZoom,
  setPage,
  addPage,
  deleteSelected,
  bringForward,
  sendBackward,
  dispose
})
</script>

<style scoped>
.main-canvas-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
}

.canvas-controls-top {
  position: absolute;
  top: 1rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 0.5rem;
  background: white;
  padding: 0.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 10;
}

.control-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.control-btn:hover {
  background: #f8f9fa;
  color: #667eea;
  border-color: #667eea;
}

.canvas-container {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: auto;
  padding: 2rem;
}

.canvas-wrapper {
  background: white;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  border-radius: 4px;
  transform-origin: center;
  transition: transform 0.3s ease;
}
</style>

