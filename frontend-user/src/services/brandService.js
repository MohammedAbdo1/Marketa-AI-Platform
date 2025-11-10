import axios from '@/axios'

const resource = '/brands'

export const brandService = {
  async getBrands(params = {}) {
    const { data } = await axios.get(resource, { params })
    return data
  },

  async getBrand(id) {
    const { data } = await axios.get(`${resource}/${id}`)
    return data
  },

  async createBrand(payload) {
    const { data } = await axios.post(resource, payload)
    return data
  },

  async updateBrand(id, payload) {
    const { data } = await axios.put(`${resource}/${id}`, payload)
    return data
  },

  async deleteBrand(id) {
    const { data } = await axios.delete(`${resource}/${id}`)
    return data
  },

  async uploadLogo(id, file, label = null) {
    const formData = new FormData()
    formData.append('logo', file)
    if (label) {
      formData.append('label', label)
    }

    const { data } = await axios.post(`${resource}/${id}/logo`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    return data
  },

  async listAssets(brandId) {
    const { data } = await axios.get(`${resource}/${brandId}/assets`)
    return data
  },

  async createAsset(brandId, payload) {
    const formData = new FormData()
    Object.entries(payload).forEach(([key, value]) => {
      if (value === undefined || value === null) return
      if (key === 'file' && value instanceof File) {
        formData.append('file', value)
      } else if (Array.isArray(value) || typeof value === 'object') {
        formData.append(key, JSON.stringify(value))
      } else {
        formData.append(key, value)
      }
    })

    const { data } = await axios.post(`${resource}/${brandId}/assets`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return data
  },

  async updateAsset(brandId, assetId, payload) {
    const formData = new FormData()
    Object.entries(payload).forEach(([key, value]) => {
      if (value === undefined) return
      if (key === 'file' && value instanceof File) {
        formData.append('file', value)
      } else if (Array.isArray(value) || typeof value === 'object') {
        formData.append(key, JSON.stringify(value))
      } else {
        formData.append(key, value)
      }
    })

    const { data } = await axios.post(`${resource}/${brandId}/assets/${assetId}?_method=PUT`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return data
  },

  async deleteAsset(brandId, assetId) {
    const { data } = await axios.delete(`${resource}/${brandId}/assets/${assetId}`)
    return data
  },

  async markAssetPrimary(brandId, assetId) {
    const { data } = await axios.post(`${resource}/${brandId}/assets/${assetId}/primary`)
    return data
  },

  async reorderAssets(brandId, order) {
    const { data } = await axios.post(`${resource}/${brandId}/assets/reorder`, {
      order
    })
    return data
  }
}

