import { defineStore } from 'pinia'
import { campaignService } from '@/services/campaignService'
import { io } from 'socket.io-client'

export const useCampaignStore = defineStore('campaign', {
  state: () => ({
    campaigns: [],
    currentCampaign: null,
    loading: false,
    error: null,
    generationStatus: null,
    preview: null,
    socket: null,
    currentTaskId: null
  }),

  getters: {
    getCampaignById: (state) => (id) => {
      return state.campaigns.find(campaign => campaign.id === id)
    },

    getCampaignByUuid: (state) => (uuid) => {
      return state.campaigns.find(campaign => campaign.uuid === uuid)
    },

    getCampaignsByStatus: (state) => (status) => {
      return state.campaigns.filter(campaign => campaign.generation_status === status)
    },

    isGenerating: (state) => {
      return state.generationStatus?.status === 'generating'
    },

    generationProgress: (state) => {
      return state.generationStatus?.progress || 0
    }
  },

  actions: {
    // Fetch all campaigns
    async fetchCampaigns(params = {}) {
      this.loading = true
      this.error = null
      
      try {
        const response = await campaignService.getCampaigns(params)
        this.campaigns = response.data.data || []
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch campaigns'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Fetch single campaign
    async fetchCampaign(uuid) {
      this.loading = true
      this.error = null
      
      try {
        const response = await campaignService.getCampaign(uuid)
        this.currentCampaign = response.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch campaign'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Create new campaign
    async createCampaign(data) {
      this.loading = true
      this.error = null
      
      try {
        const response = await campaignService.createCampaign(data)
        this.campaigns.unshift(response.data)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create campaign'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Update campaign
    async updateCampaign(uuid, data) {
      this.loading = true
      this.error = null
      
      try {
        const response = await campaignService.updateCampaign(uuid, data)
        const index = this.campaigns.findIndex(campaign => campaign.uuid === uuid)
        if (index !== -1) {
          this.campaigns[index] = response.data
        }
        if (this.currentCampaign?.uuid === uuid) {
          this.currentCampaign = response.data
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update campaign'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Delete campaign
    async deleteCampaign(uuid) {
      this.loading = true
      this.error = null
      
      try {
        await campaignService.deleteCampaign(uuid)
        this.campaigns = this.campaigns.filter(campaign => campaign.uuid !== uuid)
        if (this.currentCampaign?.uuid === uuid) {
          this.currentCampaign = null
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete campaign'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Generate campaign preview
    async generatePreview(data) {
      this.loading = true
      this.error = null
      
      try {
        const response = await campaignService.generatePreview(data)
        this.preview = response.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to generate preview'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Start campaign generation
    async generateCampaign(uuid) {
      this.loading = true
      this.error = null
      
      try {
        const response = await campaignService.generateCampaign(uuid)
        const payload = response?.data || response
        // If simple mode returns completed immediately, reflect it and return
        if (payload?.status === 'completed') {
          this.generationStatus = { status: 'completed', progress: 100 }
          return payload
        }
        this.generationStatus = { status: 'generating', progress: 0 }
        return payload
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to start generation'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Get generation status with timeout and retry limit
    async fetchGenerationStatus(uuid, maxRetries = 3) {
      for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
          const response = await campaignService.getGenerationStatus(uuid)
          this.generationStatus = response.data
          return response.data
        } catch (error) {
          console.error(`Failed to fetch generation status (attempt ${attempt}/${maxRetries}):`, error)

          // Stop immediately on 404 or explicit not-exists messages
          const statusCode = error.response?.status
          const message = error.response?.data?.message || error.message || ''
          if (statusCode === 404 || /does not exist/i.test(message) || /does not exists/i.test(message)) {
            this.generationStatus = { status: 'failed', progress: 0 }
            throw error
          }
          
          if (attempt === maxRetries) {
            // Stop polling after max retries
            this.generationStatus = { status: 'failed', progress: 0 }
            return null
          }
          
          // Wait before retry
          await new Promise(resolve => setTimeout(resolve, 2000 * attempt))
        }
      }
      return null
    },

    // Suggest brand colors
    async suggestColors(description) {
      this.loading = true
      this.error = null
      
      try {
        const response = await campaignService.suggestColors(description)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to suggest colors'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Clear preview
    clearPreview() {
      this.preview = null
    },

    // Clear error
    clearError() {
      this.error = null
    },

    // Set current campaign
    setCurrentCampaign(campaign) {
      this.currentCampaign = campaign
    },

    // Initialize Socket.IO connection - Disabled for now
    initializeSocket() {
      // WebSocket temporarily disabled - using API polling instead
      return null
    },

    // Disconnect Socket.IO - Disabled for now
    disconnectSocket() {
      // WebSocket temporarily disabled
      return
    },

    // Set current task ID for tracking
    setCurrentTaskId(taskId) {
      this.currentTaskId = taskId
    }
  }
})
