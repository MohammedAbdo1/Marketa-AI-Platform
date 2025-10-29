import { defineStore } from 'pinia'
import testimonialService from '@/services/testimonialService'

export const useTestimonialStore = defineStore('testimonial', {
  state: () => ({
    testimonials: [],
    currentTestimonial: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchAll() {
      this.loading = true
      try {
        const response = await testimonialService.getAll()
        this.testimonials = response.data.data
        this.error = null
      } catch (error) {
        this.error = error.response?.data?.message || 'Error loading testimonials'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchById(id) {
      this.loading = true
      try {
        const response = await testimonialService.getById(id)
        this.currentTestimonial = response.data.data
        this.error = null
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Error loading testimonial'
        throw error
      } finally {
        this.loading = false
      }
    },

    async create(data) {
      this.loading = true
      try {
        const response = await testimonialService.create(data)
        this.testimonials.push(response.data.data)
        this.error = null
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Error creating testimonial'
        throw error
      } finally {
        this.loading = false
      }
    },

    async update(id, data) {
      this.loading = true
      try {
        const response = await testimonialService.update(id, data)
        const index = this.testimonials.findIndex(t => t.id === id)
        if (index !== -1) {
          this.testimonials[index] = response.data.data
        }
        this.error = null
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Error updating testimonial'
        throw error
      } finally {
        this.loading = false
      }
    },

    async delete(id) {
      this.loading = true
      try {
        await testimonialService.delete(id)
        this.testimonials = this.testimonials.filter(t => t.id !== id)
        this.error = null
      } catch (error) {
        this.error = error.response?.data?.message || 'Error deleting testimonial'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})

