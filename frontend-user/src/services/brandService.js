import axios from '@/axios'

export const brandService = {
  // Get all brands
  async getBrands(params = {}) {
    const response = await axios.get('/brands', { params })
    return response.data
  },

  // Get single brand
  async getBrand(id) {
    const response = await axios.get(`/brands/${id}`)
    return response.data
  },

  // Create new brand
  async createBrand(data) {
    const response = await axios.post('/brands', data)
    return response.data
  },

  // Update brand
  async updateBrand(id, data) {
    const response = await axios.put(`/brands/${id}`, data)
    return response.data
  },

  // Delete brand
  async deleteBrand(id) {
    const response = await axios.delete(`/brands/${id}`)
    return response.data
  },

  // Upload brand logo
  async uploadLogo(id, file) {
    const formData = new FormData()
    formData.append('logo', file)
    
    const response = await axios.post(`/brands/${id}/logo`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  }
}

