import { defineStore } from 'pinia'
import axios from '../axios'

export const useAiConversationStore = defineStore('aiConversation', {
  state: () => ({
    conversations: [],
    activeConversation: null,
    messages: [],
    generatedDesigns: [],
    isGenerating: false,
    isSending: false,
    suggestions: [],
    error: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 20,
      total: 0
    }
  }),

  getters: {
    /**
     * Get user messages from active conversation
     */
    userMessages: (state) => {
      return state.messages.filter(msg => msg.role === 'user')
    },

    /**
     * Get assistant messages from active conversation
     */
    assistantMessages: (state) => {
      return state.messages.filter(msg => msg.role === 'assistant')
    },

    /**
     * Get latest message
     */
    latestMessage: (state) => {
      return state.messages.length > 0 
        ? state.messages[state.messages.length - 1] 
        : null
    },

    /**
     * Check if conversation is active
     */
    hasActiveConversation: (state) => {
      return state.activeConversation !== null
    },

    /**
     * Get designs count from active conversation
     */
    designsCount: (state) => {
      return state.generatedDesigns.length
    }
  },

  actions: {
    /**
     * Fetch user's conversations
     */
    async fetchConversations(page = 1) {
      this.error = null

      try {
        const params = {
          page,
          per_page: this.pagination.per_page
        }

        const response = await axios.get('/ai/conversations', { params })
        
        this.conversations = response.data.data
        this.pagination = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total
        }

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch conversations'
        throw error
      }
    },

    /**
     * Load specific conversation
     */
    async loadConversation(uuid) {
      this.error = null
      this.isGenerating = true

      try {
        const response = await axios.get(`/ai/conversations/${uuid}`)
        
        this.activeConversation = response.data
        this.messages = response.data.messages || []
        this.generatedDesigns = response.data.designs || []
        
        // Get suggestions from last assistant message
        const lastAssistant = [...this.messages]
          .reverse()
          .find(msg => msg.role === 'assistant')
        
        this.suggestions = lastAssistant?.suggestions || []

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load conversation'
        throw error
      } finally {
        this.isGenerating = false
      }
    },

    /**
     * Create new conversation
     */
    async createConversation(designType, initialMessage = null) {
      this.error = null
      this.isGenerating = true

      try {
        const response = await axios.post('/ai/conversations', {
          design_type: designType,
          initial_message: initialMessage
        })

        this.activeConversation = response.data.conversation
        this.messages = response.data.conversation.messages || []
        this.generatedDesigns = []
        this.suggestions = response.data.conversation.messages?.[0]?.suggestions || []

        // Add to conversations list
        this.conversations.unshift(response.data.conversation)

        return response.data.conversation
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create conversation'
        throw error
      } finally {
        this.isGenerating = false
      }
    },

    /**
     * Send message to active conversation
     */
    async sendMessage(content) {
      if (!this.activeConversation) {
        throw new Error('No active conversation')
      }

      this.error = null
      this.isSending = true

      try {
        const response = await axios.post(
          `/ai/conversations/${this.activeConversation.uuid}/messages`,
          { content }
        )

        // Add user and assistant messages
        if (response.data.user_message) {
          this.messages.push(response.data.user_message)
        }
        
        if (response.data.assistant_message) {
          this.messages.push(response.data.assistant_message)
          
          // Update suggestions
          this.suggestions = response.data.assistant_message.suggestions || []
          
          // Update generated designs
          if (response.data.assistant_message.generated_designs?.length > 0) {
            this.generatedDesigns.push(...response.data.assistant_message.generated_designs)
          }
        }

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to send message'
        throw error
      } finally {
        this.isSending = false
      }
    },

    /**
     * Delete conversation
     */
    async deleteConversation(uuid) {
      this.error = null

      try {
        await axios.delete(`/ai/conversations/${uuid}`)
        
        // Remove from list
        this.conversations = this.conversations.filter(c => c.uuid !== uuid)
        
        // Clear active if deleted
        if (this.activeConversation?.uuid === uuid) {
          this.clearActiveConversation()
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete conversation'
        throw error
      }
    },

    /**
     * Update conversation title
     */
    async updateConversationTitle(uuid, title) {
      this.error = null

      try {
        const response = await axios.put(`/ai/conversations/${uuid}`, { title })
        
        // Update in list
        const index = this.conversations.findIndex(c => c.uuid === uuid)
        if (index !== -1) {
          this.conversations[index].title = title
        }
        
        // Update active if same
        if (this.activeConversation?.uuid === uuid) {
          this.activeConversation.title = title
        }

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update conversation'
        throw error
      }
    },

    /**
     * Open design in editor
     */
    async openDesignInEditor(designUuid) {
      try {
        // Import design store
        const { useDesignStore } = await import('./design')
        const designStore = useDesignStore()
        
        // Load design in editor
        await designStore.loadInEditor(designUuid)
        
        return true
      } catch (error) {
        this.error = 'Failed to open design in editor'
        throw error
      }
    },

    /**
     * Add design to campaign
     */
    async addDesignToCampaign(designUuid, campaignUuid, platform) {
      this.error = null

      try {
        const response = await axios.post(
          `/campaigns/${campaignUuid}/designs`,
          {
            design_uuid: designUuid,
            platform
          }
        )

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to add design to campaign'
        throw error
      }
    },

    /**
     * Clear active conversation
     */
    clearActiveConversation() {
      this.activeConversation = null
      this.messages = []
      this.generatedDesigns = []
      this.suggestions = []
    },

    /**
     * Clear error
     */
    clearError() {
      this.error = null
    }
  }
})

