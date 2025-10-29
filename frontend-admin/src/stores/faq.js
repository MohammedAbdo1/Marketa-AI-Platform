import { defineStore } from 'pinia'
import faqService from '@/services/faqService'

export const useFaqStore = defineStore('faq', {
  state: () => ({
    faqs: [],
    currentFaq: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchAll() {
      this.loading = true
      try {
        const response = await faqService.getAll()
        this.faqs = response.data.data
        this.error = null
      } catch (error) {
        this.error = error.response?.data?.message || 'Error loading FAQs'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchById(id) {
      this.loading = true
      try {
        const response = await faqService.getById(id)
        this.currentFaq = response.data.data
        this.error = null
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Error loading FAQ'
        throw error
      } finally {
        this.loading = false
      }
    },

    async create(data) {
      this.loading = true
      try {
        const response = await faqService.create(data)
        this.faqs.push(response.data.data)
        this.error = null
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Error creating FAQ'
        throw error
      } finally {
        this.loading = false
      }
    },

    async update(id, data) {
      this.loading = true
      try {
        const response = await faqService.update(id, data)
        const index = this.faqs.findIndex(f => f.id === id)
        if (index !== -1) {
          this.faqs[index] = response.data.data
        }
        this.error = null
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Error updating FAQ'
        throw error
      } finally {
        this.loading = false
      }
    },

    async delete(id) {
      this.loading = true
      try {
        await faqService.delete(id)
        this.faqs = this.faqs.filter(f => f.id !== id)
        this.error = null
      } catch (error) {
        this.error = error.response?.data?.message || 'Error deleting FAQ'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})

