import { defineStore } from 'pinia'
import axios from '@/axios'

export const usePostEditorStore = defineStore('postEditor', {
  state: () => ({
    currentPost: null,
    layers: [],
    baseImageUrl: null,
    dimensions: { width: 1024, height: 1024 },
    history: [],
    historyIndex: -1,
    maxHistory: 15,
    isDirty: false,
    loading: false,
    saving: false,
  }),

  getters: {
    canUndo: (state) => state.historyIndex > 0,
    canRedo: (state) => state.historyIndex < state.history.length - 1,
    hasChanges: (state) => state.isDirty,
  },

  actions: {
    /**
     * Load post for editing
     */
    async loadPost(postId) {
      this.loading = true
      try {
        const response = await axios.get(`/campaign-posts/${postId}/layers`)
        
        if (response.data.success) {
          const data = response.data.data
          this.currentPost = { id: postId, ...data }
          this.layers = data.layers || []
          this.baseImageUrl = data.base_image_url
          this.dimensions = data.dimensions || { width: 1024, height: 1024 }
          
          // Initialize history
          this.history = [JSON.parse(JSON.stringify(this.layers))]
          this.historyIndex = 0
          this.isDirty = false
          
          console.log('[Editor] Post loaded:', postId, this.layers.length, 'layers')
          return data
        }
      } catch (error) {
        console.error('[Editor] Failed to load post:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Save current state to history
     */
    saveToHistory() {
      // Remove any history after current index (when undoing then making changes)
      this.history = this.history.slice(0, this.historyIndex + 1)
      
      // Add current state
      this.history.push(JSON.parse(JSON.stringify(this.layers)))
      
      // Limit history size
      if (this.history.length > this.maxHistory) {
        this.history.shift()
      } else {
        this.historyIndex++
      }
      
      this.isDirty = true
    },

    /**
     * Undo last change
     */
    undo() {
      if (this.canUndo) {
        this.historyIndex--
        this.layers = JSON.parse(JSON.stringify(this.history[this.historyIndex]))
        console.log('[Editor] Undo:', this.historyIndex)
      }
    },

    /**
     * Redo last undone change
     */
    redo() {
      if (this.canRedo) {
        this.historyIndex++
        this.layers = JSON.parse(JSON.stringify(this.history[this.historyIndex]))
        console.log('[Editor] Redo:', this.historyIndex)
      }
    },

    /**
     * Add layer
     */
    addLayer(layer) {
      this.layers.push(layer)
      this.saveToHistory()
      console.log('[Editor] Layer added:', layer.type)
    },

    /**
     * Update layer
     */
    updateLayer(layerIndex, changes) {
      if (this.layers[layerIndex]) {
        this.layers[layerIndex] = { ...this.layers[layerIndex], ...changes }
        this.saveToHistory()
        console.log('[Editor] Layer updated:', layerIndex)
      }
    },

    /**
     * Delete layer
     */
    deleteLayer(layerIndex) {
      if (layerIndex >= 0 && layerIndex < this.layers.length) {
        this.layers.splice(layerIndex, 1)
        this.saveToHistory()
        console.log('[Editor] Layer deleted:', layerIndex)
      }
    },

    /**
     * Reorder layers
     */
    reorderLayers(oldIndex, newIndex) {
      const layer = this.layers.splice(oldIndex, 1)[0]
      this.layers.splice(newIndex, 0, layer)
      this.saveToHistory()
      console.log('[Editor] Layers reordered:', oldIndex, '->', newIndex)
    },

    /**
     * Save changes to backend
     */
    async savePost() {
      if (!this.currentPost) {
        throw new Error('No post loaded')
      }

      this.saving = true
      try {
        const response = await axios.post(
          `/campaign-posts/${this.currentPost.id}/layers/import`,
          {
            layers: this.layers,
            dimensions: this.dimensions
          }
        )

        if (response.data.success) {
          this.isDirty = false
          console.log('[Editor] Post saved successfully')
          return response.data.data
        }
      } catch (error) {
        console.error('[Editor] Save failed:', error)
        throw error
      } finally {
        this.saving = false
      }
    },

    /**
     * Export as image (handled by backend)
     */
    async exportAsImage() {
      // Will be implemented when backend supports rendering
      console.log('[Editor] Export as image - not yet implemented')
    },

    /**
     * Reset editor state
     */
    reset() {
      this.currentPost = null
      this.layers = []
      this.baseImageUrl = null
      this.dimensions = { width: 1024, height: 1024 }
      this.history = []
      this.historyIndex = -1
      this.isDirty = false
      console.log('[Editor] Reset')
    }
  }
})

