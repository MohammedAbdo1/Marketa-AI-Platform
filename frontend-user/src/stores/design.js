import { defineStore } from 'pinia'
import axios from '../axios'

export const useDesignStore = defineStore('design', {
  state: () => ({
    designs: [],
    currentDesign: null,
    filters: {
      type: null,
      source_type: null,
      is_template: false,
      search: ''
    },
    loading: false,
    error: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 20,
      total: 0
    }
  }),

  getters: {
    /**
     * Get designs by type
     */
    designsByType: (state) => (type) => {
      return state.designs.filter(design => design.design_type === type)
    },

    /**
     * Get AI-generated designs
     */
    aiDesigns: (state) => {
      return state.designs.filter(design => design.source_type === 'ai')
    },

    /**
     * Get manual designs
     */
    manualDesigns: (state) => {
      return state.designs.filter(design => design.source_type === 'manual')
    },

    /**
     * Get template designs
     */
    templateDesigns: (state) => {
      return state.designs.filter(design => design.is_template)
    },

    /**
     * Get designs linked to campaigns
     */
    campaignDesigns: (state) => {
      return state.designs.filter(design => design.context_type === 'campaign')
    },

    /**
     * Check if there are more pages
     */
    hasMorePages: (state) => {
      return state.pagination.current_page < state.pagination.last_page
    }
  },

  actions: {
    /**
     * Fetch user's designs with filters
     */
    async fetchDesigns(page = 1) {
      this.loading = true
      this.error = null

      try {
        const params = {
          page,
          per_page: this.pagination.per_page,
          ...this.filters
        }

        // Remove null/empty filters
        Object.keys(params).forEach(key => {
          if (params[key] === null || params[key] === '') {
            delete params[key]
          }
        })

        const response = await axios.get('/designs', { params })
        
        this.designs = response.data.data
        this.pagination = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total
        }

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch designs'
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Fetch single design by UUID
     */
    async fetchDesign(uuid) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.get(`/designs/${uuid}`)
        this.currentDesign = response.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch design'
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Create new design
     */
    async createDesign(designData) {
      this.loading = true
      this.error = null

      try {
        // Ensure required fields
        const payload = {
          title: designData.title || 'تصميم بدون عنوان',
          type: designData.type || 'manual',
          width: designData.width || 1080,
          height: designData.height || 1080,
          composition_data: designData.composition_data || {
            layers: [],
            dimensions: { width: 1080, height: 1080 }
          },
          ...designData
        }

        const response = await axios.post('/designs', payload)
        const newDesign = response.data.design || response.data.data
        
        this.designs.unshift(newDesign)
        this.currentDesign = newDesign
        
        console.log('Design created:', newDesign)
        return newDesign
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create design'
        console.error('Create design error:', error.response?.data)
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Update existing design
     */
    async updateDesign(uuid, updates) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.put(`/designs/${uuid}`, updates)
        
        // Update in list
        const index = this.designs.findIndex(d => d.uuid === uuid)
        if (index !== -1) {
          this.designs[index] = response.data.design
        }
        
        // Update current if it's the same
        if (this.currentDesign?.uuid === uuid) {
          this.currentDesign = response.data.design
        }

        return response.data.design
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update design'
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Delete design
     */
    async deleteDesign(uuid) {
      this.loading = true
      this.error = null

      try {
        await axios.delete(`/designs/${uuid}`)
        
        // Remove from list
        this.designs = this.designs.filter(d => d.uuid !== uuid)
        
        // Clear current if deleted
        if (this.currentDesign?.uuid === uuid) {
          this.currentDesign = null
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete design'
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Duplicate design
     */
    async duplicateDesign(uuid) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.post(`/designs/${uuid}/duplicate`)
        this.designs.unshift(response.data.design)
        return response.data.design
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to duplicate design'
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Export design as image
     */
    async exportDesign(uuid) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.post(`/designs/${uuid}/export`)
        return response.data.export_url
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to export design'
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Convert design to public template
     */
    async convertToTemplate(uuid) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.post(`/designs/${uuid}/template`)
        
        // Update in list
        const index = this.designs.findIndex(d => d.uuid === uuid)
        if (index !== -1) {
          this.designs[index] = response.data.design
        }

        return response.data.design
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to convert to template'
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Fetch public templates
     */
    async fetchTemplates(type = null, search = '') {
      this.loading = true
      this.error = null

      try {
        const params = { type, search }
        Object.keys(params).forEach(key => {
          if (!params[key]) delete params[key]
        })

        const response = await axios.get('/designs/templates', { params })
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch templates'
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Load design into editor
     */
    async loadInEditor(uuid) {
      try {
        const design = await this.fetchDesign(uuid)
        
        // Import editor store
        const { usePostEditorStore } = await import('./postEditor')
        const editorStore = usePostEditorStore()
        
        // Load design data into editor
        editorStore.loadDesign(design)
        
        return design
      } catch (error) {
        throw error
      }
    },

    /**
     * Set filters
     */
    setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
    },

    /**
     * Clear filters
     */
    clearFilters() {
      this.filters = {
        type: null,
        source_type: null,
        is_template: false,
        search: ''
      }
    },

    /**
     * Clear error
     */
    clearError() {
      this.error = null
    }
  }
})

