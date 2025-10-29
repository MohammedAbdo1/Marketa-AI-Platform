import axios from '@/axios'

export const campaignService = {
  // Get all campaigns
  async getCampaigns(params = {}) {
    const response = await axios.get('/campaigns', { params })
    return response.data
  },

  // Get single campaign
  async getCampaign(id) {
    const response = await axios.get(`/campaigns/${id}`)
    return response.data
  },

  // Create new campaign
  async createCampaign(data) {
    const response = await axios.post('/campaigns', data)
    return response.data
  },

  // Update campaign
  async updateCampaign(id, data) {
    const response = await axios.put(`/campaigns/${id}`, data)
    return response.data
  },

  // Delete campaign
  async deleteCampaign(id) {
    const response = await axios.delete(`/campaigns/${id}`)
    return response.data
  },

  // Generate campaign preview
  async generatePreview(data) {
    const response = await axios.post('/campaigns/preview', data)
    return response.data
  },

  // Start campaign generation
  async generateCampaign(id) {
    const response = await axios.post(`/campaigns/${id}/generate`)
    return response.data
  },

  // Get generation status
  async getGenerationStatus(id) {
    const response = await axios.get(`/campaigns/${id}/status`)
    return response.data
  },

  // Suggest brand colors
  async suggestColors(description) {
    const response = await axios.post('/campaigns/suggest-colors', { description })
    return response.data
  },

  // Get campaign posts
  async getCampaignPosts(campaignId) {
    const response = await axios.get(`/campaigns/${campaignId}/posts`)
    return response.data
  },

  // Get campaign calendar
  async getCampaignCalendar(campaignId) {
    const response = await axios.get(`/campaigns/${campaignId}/calendar`)
    return response.data
  }
}
