<template>
  <div class="generated-designs-grid">
    <!-- Designs Container -->
    <div class="designs-container">
      <div 
        v-for="(design, index) in designs" 
        :key="design.uuid || index"
        class="design-item"
        :class="{ 'design-selected': selectedDesign?.uuid === design.uuid }"
      >
        <!-- Design Preview Card -->
        <div class="design-card" @click="selectDesign(design)">
          <!-- Image -->
          <div class="design-image-wrapper">
            <img 
              v-if="design.thumbnail_url || design.export_url"
              :src="design.thumbnail_url || design.export_url" 
              :alt="design.title || `Design ${index + 1}`"
              class="design-image"
              @error="handleImageError"
            >
            <div v-else class="design-placeholder">
              <i class='bx bx-image-add'></i>
            </div>

            <!-- Hover Overlay -->
            <div class="design-overlay">
              <div class="overlay-actions">
                <button 
                  class="btn btn-light btn-sm"
                  @click.stop="openInEditor(design.uuid)"
                  :title="$t('designs.open_in_editor')"
                >
                  <i class='bx bx-edit-alt'></i>
                </button>
                <button 
                  class="btn btn-light btn-sm"
                  @click.stop="downloadDesign(design)"
                  :title="$t('designs.download')"
                >
                  <i class='bx bx-download'></i>
                </button>
                <button 
                  class="btn btn-light btn-sm"
                  @click.stop="duplicateDesign(design.uuid)"
                  :title="$t('designs.duplicate')"
                >
                  <i class='bx bx-copy'></i>
                </button>
              </div>
            </div>

            <!-- Selection Indicator -->
            <div v-if="selectedDesign?.uuid === design.uuid" class="selection-indicator">
              <i class='bx bx-check'></i>
            </div>
          </div>

          <!-- Design Info -->
          <div class="design-info">
            <h6 class="design-title">{{ design.title || `تصميم ${index + 1}` }}</h6>
            <p v-if="design.metadata?.provider" class="design-meta text-muted">
              <small>{{ design.metadata.provider }}</small>
            </p>
          </div>
        </div>

        <!-- Actions Footer (Canva Style) -->
        <div class="design-actions">
          <button 
            class="action-btn"
            @click="provideFeedback(design, 'good')"
            :title="$t('ai.good_result')"
          >
            <i class='bx bx-like'></i>
          </button>
          <button 
            class="action-btn"
            @click="provideFeedback(design, 'bad')"
            :title="$t('ai.bad_result')"
          >
            <i class='bx bx-dislike'></i>
          </button>
          <button 
            class="action-btn"
            @click="showMoreOptions(design)"
            :title="$t('common.more')"
          >
            <i class='bx bx-dots-horizontal-rounded'></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Action Buttons Below Grid (Canva Style) -->
    <div v-if="showSuggestions && suggestions.length > 0" class="suggestions-bar">
      <button 
        v-for="(suggestion, idx) in suggestions"
        :key="idx"
        class="suggestion-chip"
        @click="handleSuggestionClick(suggestion)"
      >
        <i class='bx bx-bulb'></i>
        {{ suggestion }}
      </button>
    </div>

    <!-- More Options Modal -->
    <div v-if="activeOptionsMenu" class="options-modal-backdrop" @click="activeOptionsMenu = null">
      <div class="options-modal" @click.stop>
        <button class="option-item" @click="addToCampaign(activeOptionsMenu)">
          <i class='bx bx-folder-plus'></i>
          {{ $t('ai.add_to_campaign') }}
        </button>
        <button class="option-item" @click="shareDesign(activeOptionsMenu)">
          <i class='bx bx-share-alt'></i>
          {{ $t('designs.share') }}
        </button>
        <button class="option-item danger" @click="reportDesign(activeOptionsMenu)">
          <i class='bx bx-flag'></i>
          {{ $t('ai.report_result') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useDesignStore } from '@/stores/design'

const props = defineProps({
  designs: {
    type: Array,
    required: true
  },
  suggestions: {
    type: Array,
    default: () => []
  },
  showSuggestions: {
    type: Boolean,
    default: true
  },
  selectable: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['design-click', 'suggestion-click', 'feedback', 'add-to-campaign'])

const router = useRouter()
const { t } = useI18n()
const designStore = useDesignStore()

const selectedDesign = ref(null)
const activeOptionsMenu = ref(null)

const selectDesign = (design) => {
  if (props.selectable) {
    selectedDesign.value = design
  }
  emit('design-click', design)
}

const openInEditor = (uuid) => {
  // Open editor in new tab (as per user requirement)
  window.open(`/editor/${uuid}`, '_blank')
}

const downloadDesign = async (design) => {
  try {
    const response = await fetch(design.export_url || design.thumbnail_url)
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${design.title || 'design'}.png`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Download failed:', error)
  }
}

const duplicateDesign = async (uuid) => {
  try {
    await designStore.duplicateDesign(uuid)
  } catch (error) {
    console.error('Duplicate failed:', error)
  }
}

const provideFeedback = (design, type) => {
  emit('feedback', { design, type })
}

const showMoreOptions = (design) => {
  activeOptionsMenu.value = design
}

const addToCampaign = (design) => {
  emit('add-to-campaign', design)
  activeOptionsMenu.value = null
}

const shareDesign = (design) => {
  // TODO: Implement share functionality
  console.log('Share design:', design)
  activeOptionsMenu.value = null
}

const reportDesign = (design) => {
  emit('feedback', { design, type: 'report' })
  activeOptionsMenu.value = null
}

const handleImageError = (e) => {
  e.target.src = '/placeholder-design.png'
}

const handleSuggestionClick = (suggestion) => {
  // Send suggestion directly, not just populate input
  emit('suggestion-click', suggestion)
}
</script>

<style scoped>
.generated-designs-grid {
  width: 100%;
  margin: 1.5rem 0;
}

.designs-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
  padding: 0.5rem;
}

@media (max-width: 768px) {
  .designs-container {
    grid-template-columns: 1fr;
  }
}

.design-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.design-card {
  position: relative;
  cursor: pointer;
  border-radius: 12px;
  overflow: hidden;
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.design-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

.design-selected .design-card {
  box-shadow: 0 0 0 3px #667eea;
}

.design-image-wrapper {
  position: relative;
  width: 100%;
  aspect-ratio: 1 / 1;
  overflow: hidden;
  background: #f8f9fa;
}

.design-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.design-card:hover .design-image {
  transform: scale(1.05);
}

.design-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-size: 3rem;
  opacity: 0.7;
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
  opacity: 0;
  transition: opacity 0.3s ease;
}

.design-card:hover .design-overlay {
  opacity: 1;
}

.overlay-actions {
  display: flex;
  gap: 0.5rem;
}

.overlay-actions .btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  transition: all 0.2s ease;
}

.overlay-actions .btn:hover {
  transform: scale(1.1);
}

.selection-indicator {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #667eea;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.design-info {
  padding: 0.75rem;
  background: white;
}

.design-title {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #2d3748;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.design-meta {
  margin: 0.25rem 0 0 0;
  font-size: 0.8rem;
}

.design-actions {
  display: flex;
  gap: 0.5rem;
  padding: 0.5rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.action-btn {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  color: #64748b;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-btn:hover {
  background: #f8f9fa;
  color: #667eea;
  border-color: #667eea;
}

.suggestions-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
}

.suggestion-chip {
  padding: 0.5rem 1rem;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 20px;
  color: #64748b;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.suggestion-chip:hover {
  background: #667eea;
  color: white;
  border-color: #667eea;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

.options-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.options-modal {
  background: white;
  border-radius: 12px;
  padding: 0.5rem;
  min-width: 200px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.option-item {
  width: 100%;
  padding: 0.75rem 1rem;
  border: none;
  background: transparent;
  text-align: right;
  cursor: pointer;
  transition: background 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  border-radius: 8px;
  color: #2d3748;
  font-size: 0.95rem;
}

.option-item:hover {
  background: #f8f9fa;
}

.option-item.danger {
  color: #e53e3e;
}

.option-item i {
  font-size: 1.2rem;
}
</style>

