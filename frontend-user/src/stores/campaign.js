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
    async fetchCampaign(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await campaignService.getCampaign(id)
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
    async updateCampaign(id, data) {
      this.loading = true
      this.error = null
      
      try {
        const response = await campaignService.updateCampaign(id, data)
        const index = this.campaigns.findIndex(campaign => campaign.id === id)
        if (index !== -1) {
          this.campaigns[index] = response.data
        }
        if (this.currentCampaign?.id === id) {
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
    async deleteCampaign(id) {
      this.loading = true
      this.error = null
      
      try {
        await campaignService.deleteCampaign(id)
        this.campaigns = this.campaigns.filter(campaign => campaign.id !== id)
        if (this.currentCampaign?.id === id) {
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
    async generateCampaign(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await campaignService.generateCampaign(id)
        this.generationStatus = { status: 'generating', progress: 0 }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to start generation'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Get generation status with timeout and retry limit
    async fetchGenerationStatus(id, maxRetries = 3) {
      for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
          const response = await campaignService.getGenerationStatus(id)
          this.generationStatus = response.data
          return response.data
        } catch (error) {
          console.error(`Failed to fetch generation status (attempt ${attempt}/${maxRetries}):`, error)
          
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
