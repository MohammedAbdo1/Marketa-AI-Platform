import { defineStore } from 'pinia'
import axios from '../axios'

export const useFavoritesStore = defineStore('favorites', {
  state: () => ({
    sections: [],
    unsectionedFavorites: [],
    loading: false,
    error: null
  }),

  getters: {
    /**
     * Get section by UUID
     */
    getSectionByUuid: (state) => (uuid) => {
      return state.sections.find(s => s.uuid === uuid)
    },

    /**
     * Get total favorites count
     */
    totalFavoritesCount: (state) => {
      const sectionCount = state.sections.reduce((sum, section) => {
        return sum + (section.designs?.length || 0)
      }, 0)
      return sectionCount + state.unsectionedFavorites.length
    }
  },

  actions: {
    /**
     * Fetch all favorites with sections
     */
    async fetchFavorites() {
      this.loading = true
      this.error = null

      try {
        const response = await axios.get('/favorites')
        this.sections = response.data.sections || []
        this.unsectionedFavorites = response.data.unsectioned || []
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch favorites'
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Create new section
     */
    async createSection(name = 'قسم بدون عنوان', emoji = '📁') {
      try {
        const response = await axios.post('/favorite-sections', { name, emoji })
        const newSection = response.data.section
        newSection.designs = []
        this.sections.unshift(newSection)
        return newSection
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create section'
        throw error
      }
    },

    /**
     * Update section
     */
    async updateSection(uuid, data) {
      try {
        const response = await axios.patch(`/favorite-sections/${uuid}`, data)
        
        const index = this.sections.findIndex(s => s.uuid === uuid)
        if (index !== -1) {
          this.sections[index] = { ...this.sections[index], ...data }
        }

        return response.data.section
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update section'
        throw error
      }
    },

    /**
     * Delete section
     */
    async deleteSection(uuid) {
      try {
        await axios.delete(`/favorite-sections/${uuid}`)
        
        // Move designs to unsectioned
        const section = this.sections.find(s => s.uuid === uuid)
        if (section && section.designs) {
          this.unsectionedFavorites.push(...section.designs)
        }
        
        // Remove section
        this.sections = this.sections.filter(s => s.uuid !== uuid)
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete section'
        throw error
      }
    },

    /**
     * Reorder sections
     */
    async reorderSections(orderedUuids) {
      try {
        const sections = orderedUuids.map((uuid, index) => ({
          uuid,
          order: index
        }))

        await axios.post('/favorite-sections/reorder', { sections })
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to reorder sections'
        throw error
      }
    },

    /**
     * Add design to favorites
     */
    async addToFavorites(designId, sectionId = null) {
      try {
        const response = await axios.post('/favorites', {
          design_id: designId,
          section_id: sectionId
        })

        // Refresh favorites
        await this.fetchFavorites()

        return response.data.favorite
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to add to favorites'
        throw error
      }
    },

    /**
     * Remove design from favorites
     */
    async removeFromFavorites(designId) {
      try {
        await axios.delete(`/favorites/${designId}`)
        
        // Remove from sections
        this.sections.forEach(section => {
          if (section.designs) {
            section.designs = section.designs.filter(d => d.id !== designId)
          }
        })
        
        // Remove from unsectioned
        this.unsectionedFavorites = this.unsectionedFavorites.filter(d => d.id !== designId)
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to remove from favorites'
        throw error
      }
    },

    /**
     * Move favorite to different section
     */
    async moveFavorite(designId, targetSectionId) {
      try {
        await axios.patch(`/favorites/${designId}`, {
          section_id: targetSectionId
        })

        // Refresh favorites to update UI
        await this.fetchFavorites()
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to move favorite'
        throw error
      }
    },

    /**
     * Reorder designs within a section
     */
    async reorderDesigns(sectionUuid, orderedDesignIds) {
      try {
        // Update order in backend
        const updates = orderedDesignIds.map((designId, index) => {
          return axios.patch(`/favorites/${designId}`, { order: index })
        })

        await Promise.all(updates)

        // Update local state
        const section = this.sections.find(s => s.uuid === sectionUuid)
        if (section && section.designs) {
          section.designs.sort((a, b) => {
            return orderedDesignIds.indexOf(a.id) - orderedDesignIds.indexOf(b.id)
          })
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to reorder designs'
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

