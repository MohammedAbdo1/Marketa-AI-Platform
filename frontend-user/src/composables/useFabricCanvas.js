// Fabric.js v6 Composable for Vue 3
import { ref, onMounted, onUnmounted } from 'vue';
import { Canvas } from 'fabric';

export function useFabricCanvas(canvasId, options = {}) {
  const canvas = ref(null);
  const isReady = ref(false);

  onMounted(async () => {
    try {
      // Initialize canvas (Fabric v6)
      canvas.value = new Canvas(canvasId, {
        width: options.width || 1080,
        height: options.height || 1080,
        backgroundColor: options.backgroundColor || '#ffffff',
        ...options
      });
      
      isReady.value = true;
    } catch (error) {
      console.error('[useFabricCanvas] Failed to initialize:', error);
    }
  });

  onUnmounted(() => {
    if (canvas.value) {
      canvas.value.dispose();
    }
  });

  return {
    canvas,
    isReady
  };
}

