<template>
  <div :class="['canvas-preview', { 'fit-parent': fitParent }]">
    <canvas ref="canvasRef" :id="canvasId"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import { fabric } from 'fabric'

const props = defineProps({
  compositionData: {
    type: Object,
    required: true
  },
  width: {
    type: Number,
    default: 1080
  },
  height: {
    type: Number,
    default: 1080
  },
  scale: {
    type: Number,
    default: 0.3 // للعرض في الكارد
  },
  fitParent: {
    type: Boolean,
    default: false
  }
})

const canvasRef = ref(null)
const canvasId = ref(`canvas-preview-${Math.random().toString(36).substr(2, 9)}`)
const fabricCanvas = ref(null)

const renderCanvas = async () => {
  if (!canvasRef.value || !props.compositionData) return

  try {
    // Clean up existing canvas
    if (fabricCanvas.value) {
      fabricCanvas.value.dispose()
    }

    const baseScale = props.fitParent ? 1 : props.scale
    const canvasWidth = props.fitParent ? props.width : props.width * baseScale
    const canvasHeight = props.fitParent ? props.height : props.height * baseScale

    // Create new Fabric canvas
    fabricCanvas.value = new fabric.Canvas(canvasId.value, {
      width: canvasWidth,
      height: canvasHeight,
      backgroundColor: props.compositionData.backgroundColor || '#FFFFFF',
      selection: false, // Disable selection in preview
      renderOnAddRemove: true,
      interactive: false // Read-only preview
    })

    // Load from JSON
    if (props.compositionData.layers && Array.isArray(props.compositionData.layers)) {
      // Load from layers format (our custom format)
      for (const layer of props.compositionData.layers) {
        await loadLayer(layer)
      }
    } else if (props.compositionData.objects && Array.isArray(props.compositionData.objects)) {
      // Load from objects format (Fabric.js format)
      for (const objData of props.compositionData.objects) {
        await loadObject(objData)
      }
    } else if (props.compositionData.version) {
      // Load from Fabric.js JSON format directly
      await new Promise((resolve) => {
        fabricCanvas.value.loadFromJSON(props.compositionData, () => {
          resolve()
        })
      })
    }

    // Scale down for preview
    fabricCanvas.value.setZoom(baseScale)
    fabricCanvas.value.renderAll()

    if (props.fitParent && canvasRef.value) {
      fabricCanvas.value.setDimensions({
        width: props.width,
        height: props.height
      })
      fabricCanvas.value.setZoom(1)
      canvasRef.value.style.width = '100%'
      canvasRef.value.style.height = '100%'
    }
  } catch (error) {
    console.error('Canvas rendering error:', error)
  }
}

const loadLayer = async (layer) => {
  // Convert layer format to Fabric object
  return loadObject(layer)
}

const loadObject = async (objData) => {
  return new Promise((resolve) => {
    try {
      if (objData.type === 'image' || objData.layerType === 'image') {
        // Load image
        fabric.Image.fromURL(objData.src || objData.url, (img) => {
          img.set({
            left: objData.left || 0,
            top: objData.top || 0,
            scaleX: objData.scaleX || 1,
            scaleY: objData.scaleY || 1,
            angle: objData.angle || 0,
            opacity: objData.opacity || 1,
            selectable: false
          })
          fabricCanvas.value.add(img)
          resolve()
        }, { crossOrigin: 'anonymous' })
      } else if (objData.type === 'textbox' || objData.type === 'text' || objData.type === 'i-text' || objData.layerType === 'text') {
        // Load text
        const text = new fabric.Textbox(objData.text || '', {
          left: objData.left || 0,
          top: objData.top || 0,
          width: objData.width || 200,
          fontSize: objData.fontSize || 20,
          fontFamily: objData.fontFamily || 'Cairo',
          fill: objData.fill || '#000000',
          textAlign: objData.textAlign || 'right',
          angle: objData.angle || 0,
          opacity: objData.opacity || 1,
          fontWeight: objData.fontWeight || 'normal',
          selectable: false
        })
        fabricCanvas.value.add(text)
        resolve()
      } else if (objData.type === 'rect' || objData.layerType === 'rect') {
        // Load rectangle
        const rect = new fabric.Rect({
          left: objData.left || 0,
          top: objData.top || 0,
          width: objData.width || 100,
          height: objData.height || 100,
          fill: objData.fill || '#000000',
          stroke: objData.stroke || null,
          strokeWidth: objData.strokeWidth || 0,
          angle: objData.angle || 0,
          opacity: objData.opacity || 1,
          rx: objData.rx || 0,
          ry: objData.ry || 0,
          selectable: false
        })
        fabricCanvas.value.add(rect)
        resolve()
      } else if (objData.type === 'circle' || objData.layerType === 'circle') {
        // Load circle
        const circle = new fabric.Circle({
          left: objData.left || 0,
          top: objData.top || 0,
          radius: objData.radius || 50,
          fill: objData.fill || '#000000',
          stroke: objData.stroke || null,
          strokeWidth: objData.strokeWidth || 0,
          angle: objData.angle || 0,
          opacity: objData.opacity || 1,
          selectable: false
        })
        fabricCanvas.value.add(circle)
        resolve()
      } else if (objData.type === 'path' || objData.layerType === 'path') {
        // Load path (for arrows, etc)
        const path = new fabric.Path(objData.path, {
          left: objData.left || 0,
          top: objData.top || 0,
          fill: objData.fill || '#000000',
          stroke: objData.stroke || null,
          strokeWidth: objData.strokeWidth || 0,
          angle: objData.angle || 0,
          opacity: objData.opacity || 1,
          scaleX: objData.scaleX || 1,
          scaleY: objData.scaleY || 1,
          selectable: false
        })
        fabricCanvas.value.add(path)
        resolve()
      } else {
        // Generic object loading
        fabric.util.enlivenObjects([objData], (objects) => {
          objects.forEach(obj => {
            obj.selectable = false
            fabricCanvas.value.add(obj)
          })
          resolve()
        })
      }
    } catch (error) {
      console.error('Error loading object:', error)
      resolve()
    }
  })
}

// Watch for composition/scale/dimension changes
watch(
  () => [
    props.compositionData,
    props.scale,
    props.width,
    props.height,
    props.fitParent
  ],
  () => {
    renderCanvas()
  },
  { deep: true }
)

onMounted(async () => {
  await nextTick()
  renderCanvas()
})
</script>

<style scoped>
.canvas-preview {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-bg-secondary);
  overflow: hidden;
}

canvas {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.canvas-preview.fit-parent {
  width: 100%;
  height: 100%;
}

.canvas-preview.fit-parent canvas {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover;
}
</style>

