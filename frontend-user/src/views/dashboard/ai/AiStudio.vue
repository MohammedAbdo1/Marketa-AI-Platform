<template>
  <div class="ai-studio">
    <div class="row g-0 h-100">
      <!-- Conversations Sidebar -->
      <div class="col-3 border-end conversations-sidebar">
        <div class="p-3 border-bottom">
          <button class="btn btn-primary w-100" @click="showNewConversationModal = true">
            <i class="bi bi-plus-lg me-2"></i>
            {{ $t('ai.new_conversation') }}
          </button>
        </div>

        <!-- Conversations List -->
        <div class="conversations-list">
          <div 
            v-for="conversation in conversations" 
            :key="conversation.uuid"
            class="conversation-item"
            :class="{ active: activeConversation?.uuid === conversation.uuid }"
            @click="loadConversation(conversation.uuid)"
          >
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <h6 class="mb-1 text-truncate">
                  {{ conversation.title || 'New Conversation' }}
                </h6>
                <p class="small text-muted mb-1 text-truncate">
                  {{ conversation.last_message?.content }}
                </p>
                <span class="badge bg-light text-dark">
                  {{ formatDesignType(conversation.design_type) }}
                </span>
              </div>
              <button 
                class="btn btn-sm btn-link text-muted p-0"
                @click.stop="deleteConversation(conversation.uuid)"
              >
                <i class="bi bi-trash"></i>
              </button>
            </div>
            <small class="text-muted">
              {{ formatDate(conversation.last_message_at) }}
            </small>
          </div>

          <div v-if="!conversations || conversations.length === 0" class="text-center p-4">
            <i class="bi bi-chat-dots display-4 text-muted"></i>
            <p class="mt-2 text-muted">{{ $t('ai.no_conversations') }}</p>
          </div>
        </div>
      </div>

      <!-- Main Chat Area -->
      <div class="col-9">
        <!-- No Conversation Selected -->
        <div v-if="!activeConversation" class="d-flex align-items-center justify-content-center h-100">
          <div class="text-center">
            <i class="bi bi-stars display-1 text-primary"></i>
            <h3 class="mt-3">{{ $t('ai.welcome_title') }}</h3>
            <p class="text-muted">{{ $t('ai.welcome_subtitle') }}</p>
            <button class="btn btn-primary mt-3" @click="showNewConversationModal = true">
              <i class="bi bi-plus-lg me-2"></i>
              {{ $t('ai.start_conversation') }}
            </button>
          </div>
        </div>

        <!-- Active Conversation -->
        <div v-else class="d-flex flex-column h-100">
          <!-- Header -->
          <div class="chat-header border-bottom p-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="mb-0">{{ activeConversation.title || 'New Conversation' }}</h5>
                <small class="text-muted">{{ formatDesignType(activeConversation.design_type) }}</small>
              </div>
              <div>
                <button class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Messages Area -->
          <div class="messages-area flex-grow-1 p-3" ref="messagesContainer">
            <div 
              v-for="message in (messages || [])" 
              :key="message.uuid"
              class="message"
              :class="[message.role, { 'has-designs': message.generated_designs?.length > 0 }]"
            >
              <div class="message-content">
                <div class="message-avatar">
                  <i v-if="message.role === 'user'" class="bi bi-person-circle"></i>
                  <i v-else class="bi bi-stars"></i>
                </div>
                <div class="message-bubble">
                  <p class="mb-0">{{ message.content }}</p>
                  
                  <!-- Loading State -->
                  <DesignLoadingCards
                    v-if="message.role === 'assistant' && conversationStore.isGenerating && (!message.generated_designs || message.generated_designs.length === 0)"
                    :count="1"
                    :status-message="$t('ai.refining_details')"
                    :show-labels="false"
                    gradient-type="purple-blue"
                    class="mt-3"
                  />

                  <!-- Generated Designs (Canva Style) -->
                  <GeneratedDesignsGrid
                    v-else-if="message.generated_designs?.length > 0"
                    :designs="message.generated_designs"
                    :suggestions="isLatestAssistant(message) ? message.suggestions : []"
                    :show-suggestions="isLatestAssistant(message)"
                    @design-click="previewDesign"
                    @suggestion-click="sendMessage"
                    @feedback="handleDesignFeedback"
                    @add-to-campaign="handleAddToCampaign"
                    class="mt-3"
                  />
                </div>
              </div>
              <small class="text-muted ms-5">{{ formatTime(message.created_at) }}</small>
            </div>

            <!-- Loading Message -->
            <div v-if="isSending" class="message assistant">
              <div class="message-content">
                <div class="message-avatar">
                  <i class="bi bi-stars"></i>
                </div>
                <div class="message-bubble">
                  <div class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Input Area -->
          <div class="chat-input border-top p-3">
            <div class="input-group">
              <textarea 
                v-model="messageInput"
                class="form-control"
                :placeholder="$t('ai.message_placeholder')"
                rows="2"
                @keypress.enter.exact.prevent="handleSendMessage"
                :disabled="isSending"
              ></textarea>
              <button 
                class="btn btn-primary"
                @click="handleSendMessage"
                :disabled="!messageInput.trim() || isSending"
              >
                <i class="bi bi-send-fill"></i>
              </button>
            </div>
            <small class="text-muted">
              {{ $t('ai.input_hint') }}
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- New Conversation Modal -->
    <div 
      v-if="showNewConversationModal" 
      class="modal show d-block" 
      tabindex="-1"
      @click.self="showNewConversationModal = false"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('ai.new_conversation_title') }}</h5>
            <button type="button" class="btn-close" @click="showNewConversationModal = false"></button>
          </div>
          <div class="modal-body">
            <label class="form-label">{{ $t('ai.select_design_type') }}</label>
            <div class="row g-2">
              <div class="col-6">
                <div 
                  class="design-type-card" 
                  :class="{ selected: newConversation.designType === 'social_post' }"
                  @click="newConversation.designType = 'social_post'"
                >
                  <i class="bi bi-images"></i>
                  <span>{{ $t('designs.types.social_post') }}</span>
                </div>
              </div>
              <div class="col-6">
                <div 
                  class="design-type-card"
                  :class="{ selected: newConversation.designType === 'story' }"
                  @click="newConversation.designType = 'story'"
                >
                  <i class="bi bi-phone"></i>
                  <span>{{ $t('designs.types.story') }}</span>
                </div>
              </div>
              <div class="col-6">
                <div 
                  class="design-type-card"
                  :class="{ selected: newConversation.designType === 'presentation' }"
                  @click="newConversation.designType = 'presentation'"
                >
                  <i class="bi bi-easel"></i>
                  <span>{{ $t('designs.types.presentation') }}</span>
                </div>
              </div>
              <div class="col-6">
                <div 
                  class="design-type-card"
                  :class="{ selected: newConversation.designType === 'banner' }"
                  @click="newConversation.designType = 'banner'"
                >
                  <i class="bi bi-card-image"></i>
                  <span>{{ $t('designs.types.banner') }}</span>
                </div>
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label">{{ $t('ai.initial_message_label') }}</label>
              <textarea 
                v-model="newConversation.initialMessage"
                class="form-control"
                rows="3"
                :placeholder="$t('ai.initial_message_placeholder')"
              ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showNewConversationModal = false">
              {{ $t('common.cancel') }}
            </button>
            <button 
              type="button" 
              class="btn btn-primary"
              @click="createConversation"
              :disabled="!newConversation.designType"
            >
              {{ $t('ai.start') }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showNewConversationModal" class="modal-backdrop show"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAiConversationStore } from '@/stores/aiConversation'
import { useI18n } from 'vue-i18n'
import DesignLoadingCards from '@/components/shared/DesignLoadingCards.vue'
import GeneratedDesignsGrid from '@/components/shared/GeneratedDesignsGrid.vue'

const router = useRouter()
const conversationStore = useAiConversationStore()
const { t } = useI18n()

// State
const showNewConversationModal = ref(false)
const messageInput = ref('')
const messagesContainer = ref(null)
const newConversation = ref({
  designType: 'social_post',
  initialMessage: ''
})

// Computed
const conversations = computed(() => conversationStore.conversations || [])
const activeConversation = computed(() => conversationStore.activeConversation)
const messages = computed(() => conversationStore.messages || [])
const isSending = computed(() => conversationStore.isSending)

// Methods
const loadConversation = async (uuid) => {
  try {
    await conversationStore.loadConversation(uuid)
    scrollToBottom()
  } catch (err) {
    console.error('Failed to load conversation:', err)
  }
}

const createConversation = async () => {
  try {
    await conversationStore.createConversation(
      newConversation.value.designType,
      newConversation.value.initialMessage || null
    )
    showNewConversationModal.value = false
    newConversation.value = {
      designType: 'social_post',
      initialMessage: ''
    }
    scrollToBottom()
  } catch (err) {
    console.error('Failed to create conversation:', err)
  }
}

const handleSendMessage = async () => {
  if (!messageInput.value.trim() || isSending.value) return
  
  const content = messageInput.value.trim()
  messageInput.value = ''
  
  try {
    await conversationStore.sendMessage(content)
    scrollToBottom()
  } catch (err) {
    console.error('Failed to send message:', err)
    messageInput.value = content // Restore message on error
  }
}

const sendMessage = async (content) => {
  // Send directly without populating input (as per user request)
  if (!content.trim()) return
  
  try {
    await conversationStore.sendMessage(content.trim())
    scrollToBottom()
  } catch (err) {
    console.error('Failed to send message:', err)
  }
}

const deleteConversation = async (uuid) => {
  if (!confirm(t('ai.confirm_delete'))) return
  
  try {
    await conversationStore.deleteConversation(uuid)
  } catch (err) {
    console.error('Failed to delete conversation:', err)
  }
}

const previewDesign = (design) => {
  // TODO: Show design preview modal
  console.log('Preview design:', design)
}

const openInEditor = (designUuid) => {
  router.push({ name: 'design-edit', params: { uuid: designUuid } })
}

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const isLatestAssistant = (message) => {
  const lastAssistant = [...messages.value].reverse().find(m => m.role === 'assistant')
  return lastAssistant?.uuid === message.uuid
}

// Formatting
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const formatDesignType = (type) => {
  const types = {
    social_post: t('designs.types.social_post'),
    story: t('designs.types.story'),
    presentation: t('designs.types.presentation'),
    banner: t('designs.types.banner'),
    custom: t('designs.types.custom')
  }
  return types[type] || type
}

// Design interactions
const handleDesignFeedback = ({ design, type }) => {
  console.log('Design feedback:', design, type)
  // TODO: Send feedback to backend
}

const handleAddToCampaign = (design) => {
  console.log('Add to campaign:', design)
  // TODO: Show campaign selection modal
}

// Lifecycle
onMounted(async () => {
  try {
    await conversationStore.fetchConversations()
  } catch (err) {
    console.error('Failed to fetch conversations:', err)
  }
})

watch(messages, () => {
  scrollToBottom()
})
</script>

<style scoped>
.ai-studio {
  height: calc(100vh - 160px);
  margin-bottom: 0;
}

/* Conversations Sidebar */
.conversations-sidebar {
  background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
  overflow-y: auto;
  box-shadow: 2px 0 10px rgba(0,0,0,0.05);
}

.conversations-list {
  max-height: calc(100vh - 180px);
  overflow-y: auto;
}

.conversation-item {
  padding: 1rem;
  cursor: pointer;
  border-bottom: 1px solid #dee2e6;
  transition: background 0.2s;
}

.conversation-item:hover {
  background: #e9ecef;
}

.conversation-item.active {
  background: white;
  border-right: 3px solid #0d6efd;
}

/* Chat Area */
.chat-header {
  background: white;
}

.messages-area {
  overflow-y: auto;
  background: #f8f9fa;
}

.message {
  margin-bottom: 1.5rem;
}

.message-content {
  display: flex;
  gap: 0.75rem;
}

.message-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #0d6efd;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.message.user .message-avatar {
  background: #6c757d;
}

.message-bubble {
  background: white;
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  max-width: 70%;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message.user .message-content {
  flex-direction: row-reverse;
  justify-content: flex-end;
}

.message.user .message-bubble {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

/* Designs Grid - Like Canva */
.designs-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-top: 1rem;
  padding: 1rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  border-radius: 1rem;
}

.design-preview {
  position: relative;
  aspect-ratio: 1;
  border-radius: 1rem;
  overflow: hidden;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.design-preview:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  border-color: #0d6efd;
}

.design-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.design-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  opacity: 0;
  transition: opacity 0.2s;
}

.design-preview:hover .design-overlay {
  opacity: 1;
}

/* Suggestions - Like Canva */
.suggestions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 1rem;
  padding: 0.75rem;
  background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
  border-radius: 0.75rem;
}

.suggestions .btn {
  border-radius: 2rem;
  padding: 0.5rem 1rem;
  font-size: 0.85rem;
  background: white;
  border: 1.5px solid #dee2e6;
  transition: all 0.2s;
}

.suggestions .btn:hover {
  border-color: #0d6efd;
  background: #f0f7ff;
  transform: scale(1.05);
}

/* Typing Indicator */
.typing-indicator {
  display: flex;
  gap: 0.25rem;
}

.typing-indicator span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #6c757d;
  animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 60%, 100% {
    transform: translateY(0);
  }
  30% {
    transform: translateY(-10px);
  }
}

/* Input Area */
.chat-input textarea {
  resize: none;
  border-radius: 1rem 0 0 1rem;
}

.chat-input .btn {
  border-radius: 0 1rem 1rem 0;
}

/* Design Type Cards */
.design-type-card {
  padding: 1.5rem;
  border: 2px solid #dee2e6;
  border-radius: 0.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.design-type-card:hover {
  border-color: #0d6efd;
  background: #f8f9fa;
}

.design-type-card.selected {
  border-color: #0d6efd;
  background: #e7f1ff;
}

.design-type-card i {
  font-size: 2rem;
  display: block;
  margin-bottom: 0.5rem;
}
</style>

