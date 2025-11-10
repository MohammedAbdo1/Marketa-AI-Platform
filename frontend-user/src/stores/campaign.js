import { defineStore } from 'pinia'
import { campaignService } from '@/services/campaignService'
import { io } from 'socket.io-client'

const normalizeArrayValue = (value) => {
  if (Array.isArray(value)) return value

  if (typeof value === 'string' && value.trim().length > 0) {
    try {
      const parsed = JSON.parse(value)
      if (Array.isArray(parsed)) return parsed
    } catch (error) {
      // ignore parse error and fall back to splitting
    }

    return value
      .split(/[,\\s]+/)
      .map((item) => item.trim())
      .filter(Boolean)
  }

  if (value && typeof value === 'object') {
    return Object.values(value)
      .flat()
      .filter(Boolean)
  }

  return []
}

const normalizePostPayload = (post) => {
  if (!post || typeof post !== 'object') return {}

  const mediaUrls = normalizeArrayValue(post.media_urls)
  if (mediaUrls.length === 0) {
    const fallbackUrl =
      post.creative_asset?.preview_url ||
      post.creative_asset?.thumbnail_url ||
      post.creative_asset?.storage_path ||
      post.base_image_url ||
      post.final_image_url

    if (fallbackUrl) {
      mediaUrls.push(fallbackUrl)
    }
  }

  let hashtags = []
  if (Array.isArray(post.hashtags)) {
    hashtags = post.hashtags
  } else if (post.hashtags && typeof post.hashtags === 'object') {
    const primary = post.primary_language || 'ar'
    const primaryTags = post.hashtags[primary]
    hashtags = Array.isArray(primaryTags)
      ? primaryTags
      : Object.values(post.hashtags)
          .flat()
          .filter(Boolean)
  } else {
    hashtags = normalizeArrayValue(post.hashtags)
  }

  return {
    ...post,
    media_urls: mediaUrls,
    hashtags,
    creative_asset_uuid:
      post.creative_asset_uuid || post.creative_asset?.uuid || post.uuid,
    creative_asset_id:
      post.creative_asset_id || post.creative_asset?.id || post.id,
  }
}

const normalizeCampaignPayload = (payload = {}) => {
  const posts = Array.isArray(payload.posts) ? payload.posts : []
  const creativeAssets = Array.isArray(payload.creative_assets)
    ? payload.creative_assets
    : []

  const rawStatus = typeof payload.status === 'string' ? payload.status.toLowerCase() : payload.status
  const normalizedStatus = rawStatus === 'building' ? 'generating' : rawStatus

  const platforms = Array.isArray(payload.platforms)
    ? payload.platforms
    : (typeof payload.platforms === 'string'
        ? payload.platforms.split(',').map(item => item.trim()).filter(Boolean)
        : [])

  const derivedPostsCount =
    typeof payload.posts_count === 'number'
      ? payload.posts_count
      : posts.length

  const hasGeneratedContent =
    typeof payload.has_generated_content === 'boolean'
      ? payload.has_generated_content
      : derivedPostsCount > 0 ||
        creativeAssets.length > 0

  return {
    ...payload,
    status: normalizedStatus ?? payload.status ?? 'draft',
    platforms,
    posts: posts.map(normalizePostPayload),
    creative_assets: creativeAssets,
    posts_count: derivedPostsCount,
    has_generated_content: hasGeneratedContent
  }
}

export const useCampaignStore = defineStore('campaign', {
  state: () => ({
    campaigns: [],
    currentCampaign: null,
    drafts: [],
    loading: false,
    error: null,
    generationStatus: null,
    preview: null,
    intelligence: null,
    intelligenceMeta: null,
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
    async fetchDrafts() {
      this.loading = true
      this.error = null

      try {
        const response = await campaignService.getDrafts()
        this.drafts = response.data || []
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch drafts'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Fetch all campaigns
    async fetchCampaigns(params = {}) {
      this.loading = true
      this.error = null

      try {
        const response = await campaignService.getCampaigns(params)
        this.campaigns = response.data.data
          ? response.data.data.map(normalizeCampaignPayload)
          : []
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
        const normalized = normalizeCampaignPayload(response.data)
        this.currentCampaign = normalized
        return normalized
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
        const normalized = normalizeCampaignPayload(response.data)
        this.campaigns.unshift(normalized)
        // Ensure the draft list stays in sync for resume dialog
        if (normalized?.status === 'draft') {
          const existingIndex = this.drafts.findIndex(draft => draft.uuid === normalized.uuid)
          if (existingIndex !== -1) {
            this.drafts.splice(existingIndex, 1, normalized)
          } else {
            this.drafts.unshift(normalized)
          }
        }
        return normalized
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create campaign'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Update campaign
    async updateCampaign(uuid, data) {
      this.error = null

      try {
        const response = await campaignService.updateCampaign(uuid, data)
        const normalized = normalizeCampaignPayload(response.data)
        const index = this.campaigns.findIndex(campaign => campaign.uuid === uuid)
        if (index !== -1) {
          this.campaigns[index] = normalized
        }
        if (this.currentCampaign?.uuid === uuid) {
          this.currentCampaign = normalized
        }
        const draftIndex = this.drafts.findIndex(draft => draft.uuid === uuid)
        if (['draft', 'pending', 'pending_review', 'generating'].includes(normalized?.status)) {
          if (draftIndex !== -1) {
            this.drafts.splice(draftIndex, 1, normalized)
          } else {
            this.drafts.unshift(normalized)
          }
        } else if (draftIndex !== -1) {
          this.drafts.splice(draftIndex, 1)
        }
        return normalized
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update campaign'
        throw error
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
        this.preview = response.data ?? null
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to generate preview'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Generate comprehensive campaign intelligence
    async generateIntelligence(data) {
      this.loading = true
      this.error = null

      try {
        const response = await campaignService.generateIntelligence(data)
        const payloadData = response.data
          ? JSON.parse(JSON.stringify(response.data))
          : null
        this.intelligence = payloadData
        this.intelligenceMeta = {
          fallback: response.fallback ?? false,
          message: response.message ?? null,
        }
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to generate intelligence'
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
      this.currentCampaign = normalizeCampaignPayload(campaign)
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


