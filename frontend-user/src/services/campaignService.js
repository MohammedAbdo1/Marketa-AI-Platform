import axios from '@/axios'

export const campaignService = {
  // Draft listing (resumable wizard)
  async getDrafts() {
    const response = await axios.get('/campaign-drafts')
    return response.data
  },

  // Get all campaigns
  async getCampaigns(params = {}) {
    const response = await axios.get('/campaigns', { params })
    return response.data
  },

  // Get single campaign
  async getCampaign(uuid) {
    const response = await axios.get(`/campaigns/${uuid}`)
    return response.data
  },

  // Create new campaign
  async createCampaign(data) {
    const response = await axios.post('/campaigns', data)
    return response.data
  },

  // Update campaign
  async updateCampaign(uuid, data) {
    const response = await axios.put(`/campaigns/${uuid}`, data)
    return response.data
  },

  // Delete campaign
  async deleteCampaign(uuid) {
    const response = await axios.delete(`/campaigns/${uuid}`)
    return response.data
  },

  // Generate campaign preview
  async generatePreview(data) {
    const response = await axios.post('/campaigns/preview', data, {
      timeout: 120000
    })
    return response.data
  },

  // Generate comprehensive campaign intelligence
  async generateIntelligence(data) {
    const response = await axios.post('/campaigns/preview', data, {
      timeout: 120000
    })
    return response.data
  },

  // Start campaign generation
  async generateCampaign(uuid, data = {}) {
    const response = await axios.post(`/campaigns/${uuid}/generate`, data)
    return response.data
  },

  // Get generation status
  async getGenerationStatus(uuid) {
    const response = await axios.get(`/campaigns/${uuid}/status`)
    return response.data
  },

  // Suggest brand colors
  async suggestColors(description) {
    const response = await axios.post('/campaigns/suggest-colors', { description })
    return response.data
  },

  // Get campaign posts
  async getCampaignPosts(campaignUuid) {
    const response = await axios.get(`/campaigns/${campaignUuid}/posts`)
    return response.data
  },

  // Get campaign calendar
  async getCampaignCalendar(campaignUuid) {
    const response = await axios.get(`/campaigns/${campaignUuid}/calendar`)
    return response.data
  }
}
