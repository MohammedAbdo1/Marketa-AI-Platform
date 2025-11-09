<template>
  <div class="editor-layout" :class="{ 'rtl': isRTL }">
    <!-- Top Bar -->
    <EditorTopBar
      :design-title="designTitle"
      :is-saving="isSaving"
      :last-saved="lastSaved"
      :selected-object="selectedObject"
      :can-undo="canUndo"
      :can-redo="canRedo"
      @close="handleClose"
      @save="handleSave"
      @export="handleExport"
      @share="handleShare"
      @undo="handleUndo"
      @redo="handleRedo"
      @update-property="handlePropertyChange"
    />

    <!-- Main Editor Area -->
    <div class="editor-main">
      <!-- Left Sidebar (Tools) -->
      <EditorLeftSidebar
        :active-panel="activePanel"
        @panel-change="handlePanelChange"
      />

      <!-- Active Panel Content -->
      <div v-if="activePanel" class="editor-panel-content">
        <component 
          :is="activePanelComponent"
          @add-element="handleAddElement"
          @close-panel="activePanel = null"
        />
      </div>

      <!-- Main Canvas -->
      <div class="editor-canvas-area">
        <MainCanvas
        ref="canvasRef"
        :design-data="designData"
        @canvas-ready="handleCanvasReady"
        @object-selected="handleObjectSelected"
        @canvas-modified="handleCanvasModified"
        @canvas-background-selected="handleCanvasBackgroundSelected"
        @context-menu="handleContextMenu"
        @history-change="handleHistoryChange"
      />
        
      <!-- Floating Toolbar (appears above selected object) -->
      <ObjectFloatingToolbar
        v-if="selectedObject && selectedObject.type && selectedObject.type !== 'canvas-background'"
        :selected-object="selectedObject"
        :canvas-width="1080"
        :canvas-height="1080"
        @toggle-lock="toggleLock"
        @duplicate="duplicateObject"
        @delete="deleteObject"
      />
      </div>

      <!-- Properties moved to Top Bar (Canva style) -->

      <!-- Right Sidebar (Recent Designs) -->
      <EditorRightSidebar
        :recent-designs="recentDesigns"
        @design-select="handleDesignSelect"
      />
    </div>

    <!-- Bottom Bar -->
    <EditorBottomBar
      :current-page="currentPage"
      :total-pages="totalPages"
      :zoom-level="zoomLevel"
      @page-change="handlePageChange"
      @zoom-change="handleZoomChange"
      @add-page="handleAddPage"
    />

    <!-- Export Modal -->
    <ExportModal
      :is-open="showExportModal"
      :design-width="1080"
      :design-height="1080"
      @close="showExportModal = false"
      @export="handleExportWithOptions"
    />

    <!-- Context Menu -->
    <ContextMenu
      :is-visible="showContextMenu"
      :position="contextMenuPosition"
      :selected-object="selectedObject"
      @close="showContextMenu = false"
      @action="handleContextMenuAction"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useDesignStore } from '@/stores/design'

// Components
import EditorTopBar from '@/components/editor-v2/EditorTopBar.vue'
import EditorLeftSidebar from '@/components/editor-v2/EditorLeftSidebar.vue'
import MainCanvas from '@/components/editor-v2/MainCanvas.vue'
import ObjectFloatingToolbar from '@/components/editor-v2/ObjectFloatingToolbar.vue'
import ContextMenu from '@/components/editor-v2/ContextMenu.vue'
import EditorRightSidebar from '@/components/editor-v2/EditorRightSidebar.vue'
import EditorBottomBar from '@/components/editor-v2/EditorBottomBar.vue'
import ExportModal from '@/components/editor-v2/ExportModal.vue'

// Panel Components
import DesignPanel from '@/components/editor-v2/panels/DesignPanel.vue'
import ElementsPanel from '@/components/editor-v2/panels/ElementsPanel.vue'
import TextPanel from '@/components/editor-v2/panels/TextPanel.vue'
import UploadsPanel from '@/components/editor-v2/panels/UploadsPanel.vue'
import BrandPanel from '@/components/editor-v2/panels/BrandPanel.vue'
import ToolsPanel from '@/components/editor-v2/panels/ToolsPanel.vue'

const route = useRoute()
const router = useRouter()
const { locale } = useI18n()
const designStore = useDesignStore()

// State
const canvasRef = ref(null)
const activePanel = ref(null) // 'design', 'elements', 'text', etc.
const selectedObject = ref(null)
const designData = ref(null)
const designTitle = ref('Untitled Design')
const isSaving = ref(false)
const lastSaved = ref(null)
const currentPage = ref(1)
const totalPages = ref(1)
const zoomLevel = ref(30) // Default zoom: 30%
const recentDesigns = ref([])
const canUndo = ref(false)
const canRedo = ref(false)
const showExportModal = ref(false)
const showContextMenu = ref(false)
const contextMenuPosition = ref({ x: 0, y: 0 })

const isRTL = computed(() => locale.value === 'ar')

// Panel component mapping
const panelComponents = {
  design: DesignPanel,
  elements: ElementsPanel,
  text: TextPanel,
  uploads: UploadsPanel,
  brand: BrandPanel,
  tools: ToolsPanel
}

const activePanelComponent = computed(() => {
  return panelComponents[activePanel.value] || null
})

// Handlers
const handlePanelChange = (panel) => {
  activePanel.value = activePanel.value === panel ? null : panel
}

const handleClose = () => {
  if (confirm('هل تريد إغلاق المحرر؟ التغييرات غير المحفوظة ستفقد.')) {
    window.close()
    // If window.close() doesn't work (popup blocker), navigate back
    setTimeout(() => {
      router.push('/dashboard/designs')
    }, 100)
  }
}

const handleSave = async () => {
  isSaving.value = true
  try {
    const canvasData = canvasRef.value?.exportData()
    
    // Update design with new composition data
    await designStore.updateDesign(route.params.uuid, {
      composition_data: canvasData,
      title: designTitle.value
    })
    
    lastSaved.value = new Date()
    
    const updated = designStore.currentDesign
    if (updated && window.opener && !window.opener.closed) {
      window.opener.postMessage(
        {
          type: 'creative-asset:updated',
          payload: updated
        },
        window.location.origin
      )
    }

    console.log('Design saved successfully:', canvasData)
  } catch (error) {
    console.error('Save failed:', error)
  } finally {
    isSaving.value = false
  }
}

const handleExport = () => {
  showExportModal.value = true
}

const handleExportWithOptions = (options) => {
  console.log('Export options:', options)
  
  if (!canvasRef.value?.canvas) {
    console.error('Canvas not available')
    return
  }
  
  const canvas = canvasRef.value.canvas
  console.log('Canvas found:', canvas)
  
  try {
    let dataURL = ''
    let fileExtension = options.format
    
    // Handle different export formats
    if (options.format === 'svg') {
      // Export as SVG
      const svgData = canvas.toSVG()
      const blob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' })
      dataURL = URL.createObjectURL(blob)
      fileExtension = 'svg'
      
    } else if (options.format === 'pdf-standard' || options.format === 'pdf-print') {
      // For PDF, we'll export as high-quality PNG first
      // (Full PDF support requires jsPDF library - can be added later)
      const originalBg = canvas.backgroundColor
      if (options.transparentBackground) {
        canvas.backgroundColor = null
        canvas.renderAll()
      }
      
      dataURL = canvas.toDataURL({
        format: 'png',
        quality: 1,
        multiplier: options.sizeMultiplier
      })
      
      if (options.transparentBackground) {
        canvas.backgroundColor = originalBg
        canvas.renderAll()
      }
      
      fileExtension = 'png' // Will be PDF in future with jsPDF
      
    } else if (options.format === 'mp4' || options.format === 'gif' || options.format === 'pptx') {
      // These formats require special handling - export as PNG for now
      alert('هذه الميزة قريبًا! سيتم التصدير كـ PNG الآن.')
      
      const originalBg = canvas.backgroundColor
      if (options.transparentBackground && options.format !== 'jpg') {
        canvas.backgroundColor = null
        canvas.renderAll()
      }
      
      dataURL = canvas.toDataURL({
        format: 'png',
        quality: options.compress ? 0.7 : 1,
        multiplier: options.sizeMultiplier
      })
      
      if (options.transparentBackground) {
        canvas.backgroundColor = originalBg
        canvas.renderAll()
      }
      
      fileExtension = 'png'
      
    } else {
      // PNG or JPG
      const originalBg = canvas.backgroundColor
      if (options.transparentBackground && options.format === 'png') {
        canvas.backgroundColor = null
        canvas.renderAll()
      }
      
      const exportFormat = options.format === 'jpg' ? 'jpeg' : 'png'
      
      dataURL = canvas.toDataURL({
        format: exportFormat,
        quality: options.compress ? 0.7 : 1,
        multiplier: options.sizeMultiplier
      })
      
      if (options.transparentBackground && options.format === 'png') {
        canvas.backgroundColor = originalBg
        canvas.renderAll()
      }
      
      fileExtension = options.format
    }
    
    console.log('Exporting as:', fileExtension)
    console.log('DataURL generated, length:', dataURL.length || 'blob')
    
    // Download file
    const link = document.createElement('a')
    const fileName = `${designTitle.value || 'تصميم'}.${fileExtension}`
    link.download = fileName
    link.href = dataURL
    document.body.appendChild(link)
    console.log('Triggering download:', fileName)
    link.click()
    document.body.removeChild(link)
    
    // Clean up blob URLs
    if (options.format === 'svg') {
      setTimeout(() => URL.revokeObjectURL(dataURL), 100)
    }
    
    console.log('Export completed successfully')
  } catch (error) {
    console.error('Export failed:', error)
  }
}

const handleShare = () => {
  // TODO: Implement share modal
  console.log('Share design')
}

const handleUndo = () => {
  canvasRef.value?.undo()
}

const handleRedo = () => {
  canvasRef.value?.redo()
}

const handleAddElement = async (element) => {
  await canvasRef.value?.addElement(element)
  console.log('Element added to canvas:', element)
}

const handleCanvasReady = (canvas) => {
  console.log('Canvas ready:', canvas)
}

const handleObjectSelected = (obj) => {
  const selectedObj = obj
  
  // Calculate object bounds for toolbar positioning
  if (selectedObj && selectedObj.getBoundingRect) {
    const bounds = selectedObj.getBoundingRect()
    selectedObj._bounds = bounds // Store bounds on the object for the toolbar
  }
  
  selectedObject.value = selectedObj
}

const handleCanvasBackgroundSelected = (canvasObj) => {
  // Set canvas background as selected object (for color control in top bar)
  selectedObject.value = canvasObj
}

const handleCanvasModified = () => {
  // Auto-save after modification (debounced)
  clearTimeout(window.autoSaveTimeout)
  window.autoSaveTimeout = setTimeout(() => {
    handleSave()
  }, 2000) // Auto-save after 2 seconds of no changes
}

const handleHistoryChange = (data) => {
  canUndo.value = data.canUndo
  canRedo.value = data.canRedo
}

const handleContextMenu = (data) => {
  contextMenuPosition.value = { x: data.x, y: data.y }
  showContextMenu.value = true
}

const handleContextMenuAction = (action) => {
  console.log('Context menu action:', action)
  
  switch (action) {
    case 'copy':
      handleCopy()
      break
      
    case 'copy-style':
      // TODO: Implement copy style
      console.log('Copy style')
      break
      
    case 'paste':
      handlePaste()
      break
      
    case 'duplicate':
      duplicateObject()
      break
      
    case 'delete':
      deleteObject()
      break
      
    case 'toggle-lock':
      toggleLock()
      break
      
    case 'align':
      // TODO: Show alignment options
      console.log('Align to page')
      break
      
    case 'ungroup':
      // TODO: Ungroup objects
      console.log('Ungroup')
      break
      
    case 'bring-forward':
      canvasRef.value?.bringForward()
      break
      
    case 'send-backward':
      canvasRef.value?.sendBackward()
      break
      
    case 'edit-image':
      // TODO: Open image editor
      console.log('Edit image')
      break
      
    case 'show-timing':
      // TODO: Show animation timing
      console.log('Show timing')
      break
      
    case 'add-comment':
      // TODO: Add comment
      console.log('Add comment')
      break
      
    case 'alt-text':
      // TODO: Edit alt text
      console.log('Alt text')
      break
      
    case 'translate':
      // TODO: Translate text
      console.log('Translate')
      break
      
    case 'info':
      // TODO: Show object info
      console.log('Info')
      break
      
    default:
      console.log('Unknown action:', action)
  }
}

const handlePropertyChange = (property, value) => {
  canvasRef.value?.updateObjectProperty(property, value)
}

const toggleLock = () => {
  const isLocked = selectedObject.value?.lockMovementX
  canvasRef.value?.updateObjectProperty('lockMovementX', !isLocked)
  canvasRef.value?.updateObjectProperty('lockMovementY', !isLocked)
}

const duplicateObject = () => {
  // TODO: Implement duplication
  console.log('Duplicate object')
}

const bringForward = () => {
  canvasRef.value?.bringForward()
}

const sendBackward = () => {
  canvasRef.value?.sendBackward()
}

const deleteObject = () => {
  canvasRef.value?.deleteSelected()
}

const handleDesignSelect = (design) => {
  router.push(`/editor/${design.uuid}`)
}

const handlePageChange = (page) => {
  currentPage.value = page
  canvasRef.value?.setPage(page)
}

const handleZoomChange = (zoom) => {
  zoomLevel.value = Number(zoom)
  canvasRef.value?.setZoom(Number(zoom))
}

const handleAddPage = () => {
  totalPages.value++
  canvasRef.value?.addPage()
}

// Keyboard shortcuts handler
const handleKeyboardShortcuts = (e) => {
  // Check if user is typing in an input field
  const isTyping = ['INPUT', 'TEXTAREA'].includes(e.target.tagName)
  
  // Ctrl/Cmd key combinations
  const ctrlKey = e.ctrlKey || e.metaKey
  
  // Prevent default shortcuts we handle
  if (ctrlKey && ['z', 'y', 'c', 'x', 'v', 'd', 'a', 's', 'k'].includes(e.key.toLowerCase())) {
    e.preventDefault()
  }
  
  // Delete key
  if (e.key === 'Delete' && !isTyping && selectedObject.value) {
    e.preventDefault()
    deleteObject()
    return
  }
  
  // Ctrl+Z - Undo
  if (ctrlKey && e.key.toLowerCase() === 'z' && !e.shiftKey) {
    handleUndo()
    return
  }
  
  // Ctrl+Y or Ctrl+Shift+Z - Redo
  if (ctrlKey && (e.key.toLowerCase() === 'y' || (e.key.toLowerCase() === 'z' && e.shiftKey))) {
    handleRedo()
    return
  }
  
  // Ctrl+C - Copy
  if (ctrlKey && e.key.toLowerCase() === 'c' && !isTyping && selectedObject.value) {
    handleCopy()
    return
  }
  
  // Ctrl+X - Cut
  if (ctrlKey && e.key.toLowerCase() === 'x' && !isTyping && selectedObject.value) {
    handleCut()
    return
  }
  
  // Ctrl+V - Paste
  if (ctrlKey && e.key.toLowerCase() === 'v' && !isTyping) {
    handlePaste()
    return
  }
  
  // Ctrl+D - Duplicate
  if (ctrlKey && e.key.toLowerCase() === 'd' && !isTyping && selectedObject.value) {
    duplicateObject()
    return
  }
  
  // Ctrl+A - Select All
  if (ctrlKey && e.key.toLowerCase() === 'a' && !isTyping) {
    handleSelectAll()
    return
  }
  
  // Ctrl+S - Save
  if (ctrlKey && e.key.toLowerCase() === 's') {
    handleSave()
    return
  }
  
  // Ctrl+] - Bring Forward
  if (ctrlKey && e.key === ']' && !isTyping && selectedObject.value) {
    canvasRef.value?.bringForward()
    return
  }
  
  // Ctrl+[ - Send Backward
  if (ctrlKey && e.key === '[' && !isTyping && selectedObject.value) {
    canvasRef.value?.sendBackward()
    return
  }
  
  // Arrow keys - Move object
  if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key) && !isTyping && selectedObject.value) {
    e.preventDefault()
    handleArrowMove(e.key, e.shiftKey)
    return
  }
}

const handleCopy = () => {
  if (!selectedObject.value) return
  
  try {
    const json = JSON.stringify(selectedObject.value.toJSON(['id', 'selectable', 'evented']))
    localStorage.setItem('clipboard', json)
    console.log('Object copied to clipboard')
  } catch (error) {
    console.error('Copy failed:', error)
  }
}

const handleCut = () => {
  if (!selectedObject.value) return
  
  handleCopy()
  deleteObject()
  console.log('Object cut')
}

const handlePaste = async () => {
  try {
    const json = localStorage.getItem('clipboard')
    if (!json) {
      console.log('Nothing to paste')
      return
    }
    
    const objData = JSON.parse(json)
    
    // Offset the pasted object
    objData.left = (objData.left || 0) + 20
    objData.top = (objData.top || 0) + 20
    
    await canvasRef.value?.addElement(objData)
    console.log('Object pasted')
  } catch (error) {
    console.error('Paste failed:', error)
  }
}

const handleSelectAll = () => {
  if (!canvasRef.value?.canvas) return
  
  const canvas = canvasRef.value.canvas
  const objects = canvas.getObjects()
  
  if (objects.length > 0) {
    const fabricLib = canvas.fabric || canvas.constructor
    const selection = new fabricLib.ActiveSelection(objects, { canvas })
    canvas.setActiveObject(selection)
    canvas.renderAll()
    console.log('All objects selected')
  }
}

const handleArrowMove = (key, shiftKey) => {
  if (!selectedObject.value) return
  
  const step = shiftKey ? 10 : 1 // Shift = faster movement
  
  switch (key) {
    case 'ArrowUp':
      selectedObject.value.top -= step
      break
    case 'ArrowDown':
      selectedObject.value.top += step
      break
    case 'ArrowLeft':
      selectedObject.value.left -= step
      break
    case 'ArrowRight':
      selectedObject.value.left += step
      break
  }
  
  canvasRef.value?.canvas.renderAll()
  handleCanvasModified()
}

// Load design data
onMounted(async () => {
  try {
    // Add keyboard shortcuts listener
    window.addEventListener('keydown', handleKeyboardShortcuts)
    
    const uuid = route.params.uuid
    if (uuid) {
      // Fetch design from backend
      const design = await designStore.fetchDesign(uuid)
      
      // Set design data for canvas
      let composition = design.composition_data || {
        layers: [],
        dimensions: { width: 1080, height: 1080 }
      }
      
      // If layers are empty but thumbnail/export URL exists, create initial image layer
      if ((!composition.layers || composition.layers.length === 0) && 
          (design.thumbnail_url || design.export_url)) {
        console.log('Creating initial layer from thumbnail:', design.thumbnail_url || design.export_url)
        composition.layers = [{
          type: 'image',
          url: design.thumbnail_url || design.export_url,
          x: 0,
          y: 0,
          left: 0,
          top: 0,
          width: design.width || 1080,
          height: design.height || 1080,
          scaleX: 1,
          scaleY: 1
        }]
      }
      
      designData.value = composition
      designTitle.value = design.title || 'تصميم بدون عنوان'
      
      console.log('Design loaded:', design.title, 'Layers:', composition.layers?.length)
    }

    // Load recent designs for right sidebar
    await designStore.fetchDesigns({ per_page: 10 })
    recentDesigns.value = designStore.designs.slice(0, 10)
  } catch (error) {
    console.error('Failed to load design:', error)
  }
})

// Watch for route changes (when switching between designs)
watch(() => route.params.uuid, async (newUuid) => {
  if (newUuid) {
    const design = await designStore.fetchDesign(newUuid)
    
    let composition = design.composition_data || {
      layers: [],
      dimensions: { width: 1080, height: 1080 }
    }
    
    // If layers are empty but thumbnail exists, create initial image layer
    if ((!composition.layers || composition.layers.length === 0) && 
        (design.thumbnail_url || design.export_url)) {
      composition.layers = [{
        type: 'image',
        url: design.thumbnail_url || design.export_url,
        x: 0,
        y: 0,
        left: 0,
        top: 0,
        width: design.width || 1080,
        height: design.height || 1080,
        scaleX: 1,
        scaleY: 1
      }]
    }
    
    designData.value = composition
    designTitle.value = design.title || 'تصميم بدون عنوان'
  }
})

// Cleanup
onBeforeUnmount(() => {
  // Remove keyboard shortcuts listener
  window.removeEventListener('keydown', handleKeyboardShortcuts)
  
  canvasRef.value?.dispose()
})
</script>

<style scoped>
.editor-layout {
  width: 100vw;
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--color-bg-primary);
  overflow: hidden;
}

.editor-main {
  display: flex;
  flex: 1;
  overflow: hidden;
  position: relative;
}

.editor-panel-content {
  width: var(--panel-width, 320px);
  background: var(--color-bg-primary);
  border-right: 1px solid var(--color-border-light);
  overflow-y: auto;
  z-index: var(--z-dropdown);
  transition: var(--transition-all);
}

.editor-canvas-area {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
  background: var(--color-bg-secondary);
}

.editor-properties-panel {
  width: var(--panel-width, 320px);
  background: var(--color-bg-primary);
  border-left: 1px solid var(--color-border-light);
  overflow-y: auto;
  z-index: var(--z-dropdown);
  transition: var(--transition-all);
}

/* RTL Support - Handled automatically by design system */

/* Responsive */
@media (max-width: 1024px) {
  .editor-properties-panel {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    box-shadow: var(--shadow-xl);
  }
}

@media (max-width: 768px) {
  .editor-panel-content {
    position: absolute;
    left: 60px;
    top: 0;
    bottom: 0;
    box-shadow: var(--shadow-xl);
  }
}
</style>

