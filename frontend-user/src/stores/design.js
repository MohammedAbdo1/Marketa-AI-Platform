import { defineStore } from 'pinia'
import axios from '../axios'

const normalizeCreativeAssetAsDesign = (asset) => {
  if (!asset || typeof asset !== 'object') {
    return null
  }

  const content = asset.content || {}
  const settings = asset.settings || {}
  const width =
    asset.width ||
    content?.dimensions?.width ||
    settings?.dimensions?.width ||
    1080
  const height =
    asset.height ||
    content?.dimensions?.height ||
    settings?.dimensions?.height ||
    1080

  const compositionLayers = content?.composition_layers || []
  const compositionData = {
    layers: Array.isArray(compositionLayers) ? compositionLayers : [],
    dimensions: {
      width,
      height
    }
  }

  if (
    (!compositionData.layers || compositionData.layers.length === 0) &&
    (asset.thumbnail_url || asset.preview_url || content?.final_image_url)
  ) {
    const imageUrl =
      asset.thumbnail_url || asset.preview_url || content?.final_image_url

    compositionData.layers = [
      {
        type: 'image',
        url: imageUrl,
        x: 0,
        y: 0,
        left: 0,
        top: 0,
        width,
        height,
        scaleX: 1,
        scaleY: 1
      }
    ]
  }

  return {
    id: asset.id,
    uuid: asset.uuid,
    title: asset.title || 'تصميم بدون عنوان',
    description: asset.description,
    status: asset.status,
    width,
    height,
    composition_data: compositionData,
    thumbnail_url: asset.thumbnail_url,
    preview_url: asset.preview_url,
    export_url:
      asset.export_url || asset.preview_url || asset.thumbnail_url || content?.final_image_url,
    is_template: asset.is_template || false,
    is_public: asset.is_public || false,
    source_type: asset.source_type || 'creative_asset',
    source_id: asset.source_id,
    source_model: asset.source_model,
    context_type: asset.context_type,
    context_id: asset.context_id,
    metadata: asset.metadata || {},
    settings,
    content,
    creative_asset: true
  }
}

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
        let designData = null

        // Try creative asset first (most campaign posts)
        try {
          const creativeAssetResponse = await axios.get(
            `/creative-assets/${uuid}`
          )
          const normalized = normalizeCreativeAssetAsDesign(
            creativeAssetResponse.data
          )

          if (normalized) {
            designData = normalized
          }
        } catch (assetError) {
          if (assetError.response?.status !== 404) {
            this.error =
              assetError.response?.data?.message ||
              'Failed to fetch creative asset'
            throw assetError
          }
        }

        if (!designData) {
          const response = await axios.get(`/designs/${uuid}`)
          designData = response.data
        }

        this.currentDesign = designData
        return designData
      } catch (error) {
        if (!this.error) {
          this.error =
            error.response?.data?.message || 'Failed to fetch design'
        }
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
        const isCreativeAsset =
          this.currentDesign?.creative_asset === true ||
          this.currentDesign?.asset_type === 'campaign_post'

        const endpoint = isCreativeAsset
          ? `/creative-assets/${uuid}`
          : `/designs/${uuid}`

        const response = await axios.put(endpoint, updates)

        let updatedDesign = response.data.design

        if (isCreativeAsset) {
          updatedDesign = normalizeCreativeAssetAsDesign(response.data.design) || this.currentDesign
        }

        if (updatedDesign) {
          const index = this.designs.findIndex(d => d.uuid === uuid)
          if (index !== -1) {
            this.designs[index] = updatedDesign
          }

          if (this.currentDesign?.uuid === uuid) {
            this.currentDesign = updatedDesign
          }
        }

        return updatedDesign
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
      const design = await this.fetchDesign(uuid)

      if (typeof window !== 'undefined') {
        window.open(`/editor/${design?.uuid || uuid}`, '_blank', 'noopener')
      }

      return design
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
     * Update design title only
     */
    async updateDesignTitle(uuid, title) {
      try {
        const response = await axios.patch(`/designs/${uuid}/title`, { title })
        
        // Update in list
        const index = this.designs.findIndex(d => d.uuid === uuid)
        if (index !== -1) {
          this.designs[index].title = title
        }
        
        // Update current if it's the same
        if (this.currentDesign?.uuid === uuid) {
          this.currentDesign.title = title
        }

        return response.data.design
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update title'
        throw error
      }
    },

    /**
     * Move design to trash
     */
    async moveToTrash(uuid) {
      try {
        const response = await axios.post(`/designs/${uuid}/trash`)
        
        // Remove from designs list
        this.designs = this.designs.filter(d => d.uuid !== uuid)
        
        // Clear current if trashed
        if (this.currentDesign?.uuid === uuid) {
          this.currentDesign = null
        }

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to move to trash'
        throw error
      }
    },

    /**
     * Restore design from trash
     */
    async restoreDesign(uuid) {
      try {
        const response = await axios.post(`/designs/${uuid}/restore`)
        return response.data.design
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to restore design'
        throw error
      }
    },

    /**
     * Force delete design permanently
     */
    async forceDeleteDesign(uuid) {
      try {
        await axios.delete(`/designs/${uuid}/force`)
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete permanently'
        throw error
      }
    },

    /**
     * Fetch trashed designs
     */
    async fetchTrashedDesigns(page = 1) {
      this.loading = true
      this.error = null

      try {
        const response = await axios.get('/designs/trash', {
          params: { page, per_page: this.pagination.per_page }
        })
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch trash'
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Toggle favorite status
     */
    async toggleFavorite(designId, isFavorited, sectionId = null) {
      try {
        if (isFavorited) {
          // Remove from favorites
          await axios.delete(`/favorites/${designId}`)
        } else {
          // Add to favorites
          await axios.post('/favorites', { creative_asset_id: designId, section_id: sectionId })
        }

        // Update in designs list
        const design = this.designs.find(d => d.id === designId)
        if (design) {
          design.is_favorited = !isFavorited
        }

        return !isFavorited
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to toggle favorite'
        throw error
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

