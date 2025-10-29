<template>
  <div class="signature-container border rounded p-3 bg-light">
    <!-- Signature Canvas -->
    <div class="signature-wrapper position-relative">
      <canvas 
        ref="signatureCanvas"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseleave="stopDrawing"
        @touchstart="startDrawingTouch"
        @touchmove="drawTouch"
        @touchend="stopDrawing"
        class="signature-canvas border rounded bg-white"
        :width="width"
        :height="height"
      ></canvas>
      
      <!-- Clear Button -->
      <button 
        type="button"
        @click="clearSignature"
        class="btn btn-danger btn-sm position-absolute"
        style="bottom: 10px; right: 10px;"
        :title="$t('lang.clear_signature')"
      >
        <i class="fas fa-eraser"></i>
      </button>
    </div>
    
    <!-- Signature Actions -->
    <div class="mt-3 d-flex gap-2">
      <button 
        type="button"
        @click="saveSignature"
        class="btn btn-primary btn-sm"
        :disabled="!hasSignature"
      >
        <i class="fas fa-save me-1"></i>
        {{ $t('lang.save_signature') }}
      </button>
      
      <button 
        type="button"
        @click="loadExistingSignature"
        v-if="existingSignatureUrl && !signatureData"
        class="btn btn-info btn-sm"
      >
        <i class="fas fa-image me-1"></i>
        {{ $t('lang.load_existing_signature') }}
      </button>
      
      <button 
        type="button"
        @click="downloadSignature"
        v-if="signatureData"
        class="btn btn-success btn-sm"
      >
        <i class="fas fa-download me-1"></i>
        {{ $t('lang.download_signature') }}
      </button>
    </div>
    
    <!-- Current Signature Display -->
    <div v-if="existingSignatureUrl" class="mt-3">
      <label class="form-label">{{ $t('lang.current_signature') }}</label>
      <div class="border rounded p-2 bg-white">
        <img :src="existingSignatureUrl" alt="Current Signature" class="img-fluid" style="max-height: 100px;">
      </div>
    </div>
    
    <!-- Signature Preview -->
    <div v-if="signatureData && showPreview" class="mt-3">
      <label class="form-label">{{ $t('lang.signature_preview') }}</label>
      <div class="border rounded p-2 bg-white">
        <img :src="signatureData" alt="Signature Preview" class="img-fluid" style="max-height: 100px;">
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import '@/css/signature.css';

// Props
const props = defineProps({
  width: {
    type: Number,
    default: 600
  },
  height: {
    type: Number,
    default: 200
  },
  existingSignatureUrl: {
    type: String,
    default: null
  },
  showPreview: {
    type: Boolean,
    default: true
  },
  strokeColor: {
    type: String,
    default: '#000000'
  },
  strokeWidth: {
    type: Number,
    default: 2
  }
});

// Emits
const emit = defineEmits(['signature-changed', 'signature-saved']);

// Refs
const signatureCanvas = ref(null);
const isDrawing = ref(false);
const lastX = ref(0);
const lastY = ref(0);
const signatureData = ref(null);
const hasSignature = ref(false);
const signatureFile = ref(null);

// Methods
function initSignatureCanvas() {
  if (signatureCanvas.value) {
    const canvas = signatureCanvas.value;
    const ctx = canvas.getContext('2d');
    
    // Set canvas style
    ctx.strokeStyle = props.strokeColor;
    ctx.lineWidth = props.strokeWidth;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    
    // Set canvas background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    console.log('ElectronicSignature Canvas initialized:', {
      width: canvas.width,
      height: canvas.height,
      strokeColor: props.strokeColor,
      strokeWidth: props.strokeWidth
    });
  }
}

function startDrawing(event) {
  isDrawing.value = true;
  const canvas = signatureCanvas.value;
  const rect = canvas.getBoundingClientRect();
  lastX.value = event.clientX - rect.left;
  lastY.value = event.clientY - rect.top;
}

function startDrawingTouch(event) {
  event.preventDefault();
  isDrawing.value = true;
  const canvas = signatureCanvas.value;
  const rect = canvas.getBoundingClientRect();
  const touch = event.touches[0];
  lastX.value = touch.clientX - rect.left;
  lastY.value = touch.clientY - rect.top;
}

function draw(event) {
  if (!isDrawing.value) return;
  
  const canvas = signatureCanvas.value;
  const ctx = canvas.getContext('2d');
  const rect = canvas.getBoundingClientRect();
  
  const currentX = event.clientX - rect.left;
  const currentY = event.clientY - rect.top;
  
  ctx.beginPath();
  ctx.moveTo(lastX.value, lastY.value);
  ctx.lineTo(currentX, currentY);
  ctx.stroke();
  
  lastX.value = currentX;
  lastY.value = currentY;
  
  hasSignature.value = true;
  emitSignatureChanged();
}

function drawTouch(event) {
  if (!isDrawing.value) return;
  
  event.preventDefault();
  const canvas = signatureCanvas.value;
  const ctx = canvas.getContext('2d');
  const rect = canvas.getBoundingClientRect();
  const touch = event.touches[0];
  
  const currentX = touch.clientX - rect.left;
  const currentY = touch.clientY - rect.top;
  
  ctx.beginPath();
  ctx.moveTo(lastX.value, lastY.value);
  ctx.lineTo(currentX, currentY);
  ctx.stroke();
  
  lastX.value = currentX;
  lastY.value = currentY;
  
  hasSignature.value = true;
  emitSignatureChanged();
}

function stopDrawing() {
  isDrawing.value = false;
}

function clearSignature() {
  const canvas = signatureCanvas.value;
  const ctx = canvas.getContext('2d');
  
  // Clear canvas
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  
  // Reset background
  ctx.fillStyle = '#ffffff';
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  
  hasSignature.value = false;
  signatureData.value = null;
  signatureFile.value = null;
  
  emitSignatureChanged();
}

function saveSignature() {
  return new Promise((resolve) => {
    const canvas = signatureCanvas.value;
    
    // Convert canvas to blob
    canvas.toBlob((blob) => {
      if (blob) {
        // Create file from blob
        const file = new File([blob], 'signature.png', { type: 'image/png' });
        signatureFile.value = file;
        signatureData.value = canvas.toDataURL('image/png');
        
        emit('signature-saved', {
          file: file,
          dataUrl: signatureData.value,
          hasSignature: hasSignature.value
        });
        
        resolve(file);
      } else {
        resolve(null);
      }
    }, 'image/png');
  });
}

function loadExistingSignature() {
  if (props.existingSignatureUrl) {
    const img = new Image();
    img.onload = () => {
      const canvas = signatureCanvas.value;
      const ctx = canvas.getContext('2d');
      
      // Clear canvas first
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      
      // Draw existing signature
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      hasSignature.value = true;
      
      emitSignatureChanged();
    };
    img.src = props.existingSignatureUrl;
  }
}

function downloadSignature() {
  if (signatureData.value) {
    const link = document.createElement('a');
    link.download = `signature-${Date.now()}.png`;
    link.href = signatureData.value;
    link.click();
  }
}

function emitSignatureChanged() {
  emit('signature-changed', {
    hasSignature: hasSignature.value,
    signatureData: signatureData.value,
    signatureFile: signatureFile.value
  });
}

// Expose methods for parent components
defineExpose({
  clearSignature,
  saveSignature,
  loadExistingSignature,
  hasSignature: () => hasSignature.value,
  getSignatureFile: () => signatureFile.value,
  getSignatureData: () => signatureData.value
});

// Lifecycle
onMounted(async () => {
  await nextTick();
  initSignatureCanvas();
});

// Watch for prop changes
watch(() => props.existingSignatureUrl, (newUrl) => {
  if (newUrl && !signatureData.value) {
    loadExistingSignature();
  }
});

watch(() => props.strokeColor, (newColor) => {
  if (signatureCanvas.value) {
    const ctx = signatureCanvas.value.getContext('2d');
    ctx.strokeStyle = newColor;
  }
});

watch(() => props.strokeWidth, (newWidth) => {
  if (signatureCanvas.value) {
    const ctx = signatureCanvas.value.getContext('2d');
    ctx.lineWidth = newWidth;
  }
});
</script>

<style scoped>
.signature-container {
  border: 2px solid #e9ecef;
  border-radius: 8px;
  background-color: #f8f9fa;
  padding: 1rem;
}

.signature-wrapper {
  position: relative;
  display: inline-block;
}

.signature-canvas {
  border: 2px solid #dee2e6;
  border-radius: 8px;
  background-color: #ffffff;
  cursor: crosshair;
  touch-action: none;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: border-color 0.2s ease;
}

.signature-canvas:hover {
  border-color: #007bff;
}

.signature-canvas:active {
  border-color: #0056b3;
}

@media (max-width: 768px) {
  .signature-canvas {
    width: 100%;
    height: 150px;
  }
}

@media (pointer: coarse) {
  .signature-canvas {
    height: 180px;
  }
}
</style>
