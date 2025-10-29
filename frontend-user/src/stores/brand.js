import { defineStore } from 'pinia'
import axios from '@/axios'

export const useBrandStore = defineStore('brand', {
  state: () => ({
    brands: [],
    currentBrand: null,
    loading: false,
    error: null
  }),

  getters: {
    getBrandById: (state) => (id) => {
      return state.brands.find(brand => brand.id === id)
    },

    activeBrands: (state) => {
      return state.brands.filter(brand => brand.is_active)
    }
  },

  actions: {
    // Fetch all brands
    async fetchBrands() {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.get('/brands')
        this.brands = response.data.data || []
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch brands'
        console.error('Failed to fetch brands:', error)
        // Don't throw error, just return empty array
        this.brands = []
        return { data: [] }
      } finally {
        this.loading = false
      }
    },

    // Fetch single brand
    async fetchBrand(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.get(`/brands/${id}`)
        this.currentBrand = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch brand'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Create new brand
    async createBrand(data) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.post('/brands', data)
        this.brands.unshift(response.data.data)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create brand'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Update brand
    async updateBrand(id, data) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.put(`/brands/${id}`, data)
        const index = this.brands.findIndex(brand => brand.id === id)
        if (index !== -1) {
          this.brands[index] = response.data.data
        }
        if (this.currentBrand?.id === id) {
          this.currentBrand = response.data.data
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update brand'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Delete brand
    async deleteBrand(id) {
      this.loading = true
      this.error = null
      
      try {
        await axios.delete(`/brands/${id}`)
        this.brands = this.brands.filter(brand => brand.id !== id)
        if (this.currentBrand?.id === id) {
          this.currentBrand = null
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete brand'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Upload brand logo
    async uploadLogo(id, file) {
      this.loading = true
      this.error = null
      
      try {
        const formData = new FormData()
        formData.append('logo', file)
        
        const response = await axios.post(`/brands/${id}/logo`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        // Update brand in list
        const index = this.brands.findIndex(brand => brand.id === id)
        if (index !== -1) {
          this.brands[index] = response.data.data
        }
        if (this.currentBrand?.id === id) {
          this.currentBrand = response.data.data
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to upload logo'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Clear error
    clearError() {
      this.error = null
    },

    // Set current brand
    setCurrentBrand(brand) {
      this.currentBrand = brand
    }
  }
})

