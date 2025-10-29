import { defineStore } from 'pinia'
import cmsService from '@/services/cmsService'

export const useCmsStore = defineStore('cms', {
  state: () => ({
    pages: [],
    sections: [],
    currentPage: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchPages() {
      this.loading = true
      try {
        const response = await cmsService.getPages()
        this.pages = response.data.data
        this.error = null
      } catch (error) {
        this.error = error.response?.data?.message || 'Error loading pages'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updatePage(id, data) {
      this.loading = true
      try {
        const response = await cmsService.updatePage(id, data)
        const index = this.pages.findIndex(p => p.id === id)
        if (index !== -1) {
          this.pages[index] = response.data.data
        }
        this.error = null
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Error updating page'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchSections() {
      this.loading = true
      try {
        const response = await cmsService.getSections()
        this.sections = response.data.data
        this.error = null
      } catch (error) {
        this.error = error.response?.data?.message || 'Error loading sections'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createSection(data) {
      this.loading = true
      try {
        const response = await cmsService.createSection(data)
        this.sections.push(response.data.data)
        this.error = null
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Error creating section'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateSection(id, data) {
      this.loading = true
      try {
        const response = await cmsService.updateSection(id, data)
        const index = this.sections.findIndex(s => s.id === id)
        if (index !== -1) {
          this.sections[index] = response.data.data
        }
        this.error = null
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Error updating section'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteSection(id) {
      this.loading = true
      try {
        await cmsService.deleteSection(id)
        this.sections = this.sections.filter(s => s.id !== id)
        this.error = null
      } catch (error) {
        this.error = error.response?.data?.message || 'Error deleting section'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createContent(data) {
      this.loading = true
      try {
        const response = await cmsService.createContent(data)
        // Update the section with new content
        await this.fetchSections()
        this.error = null
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Error creating content'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateContent(id, data) {
      this.loading = true
      try {
        const response = await cmsService.updateContent(id, data)
        await this.fetchSections()
        this.error = null
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Error updating content'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteContent(id) {
      this.loading = true
      try {
        await cmsService.deleteContent(id)
        await this.fetchSections()
        this.error = null
      } catch (error) {
        this.error = error.response?.data?.message || 'Error deleting content'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})

