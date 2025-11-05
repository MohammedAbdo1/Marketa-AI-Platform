<template>
  <div 
    class="design-card" 
    @click="$emit('edit', design)"
    @mouseenter="showActions = true"
    @mouseleave="showActions = false"
  >
    <!-- Thumbnail - Live Canvas Preview ONLY -->
    <div class="card-thumbnail">
      <CanvasPreview
        :composition-data="design.composition_data || {}"
        :width="design.width || 1080"
        :height="design.height || 1080"
        :scale="0.25"
      />

      <!-- Card Actions (Star + Three-dots) -->
      <!-- النجمة ظاهرة دائماً للمفضلة، Three-dots تظهر عند Hover -->
      <div class="card-actions">
        <button 
          v-show="design.is_favorited || showActions || contextMenuOpen"
          class="btn-icon btn-star" 
          :class="{ active: design.is_favorited, loading: isFavoriting }"
          @click.stop="toggleFavorite"
          :aria-label="design.is_favorited ? $t('designs.unfavorite') : $t('common.favorite')"
          :disabled="isFavoriting"
        >
          <i v-if="!isFavoriting" :class="design.is_favorited ? 'bxs-star' : 'bx-star'" class="bx"></i>
          <i v-else class="bx bx-loader-alt bx-spin"></i>
        </button>
        <button 
          v-show="showActions || contextMenuOpen"
          class="btn-icon" 
          @click.stop="openContextMenu"
          :aria-label="$t('common.more')"
        >
          <i class="bx bx-dots-horizontal-rounded"></i>
        </button>
      </div>
    </div>

    <!-- Card Body -->
    <div class="card-body">
      <InlineEditableName
        v-model="design.title"
        :placeholder="$t('designs.create_first')"
        @save="updateTitle"
      />
      
      <div class="card-meta">
        <span class="design-type">{{ formatDesignType(design.design_type) }}</span>
        <span class="last-modified">
          <i class="bx bx-time-five"></i>
          {{ formatDate(design.updated_at) }}
        </span>
      </div>
    </div>

    <!-- Context Menu -->
    <DesignContextMenu
      v-if="contextMenuOpen"
      :design="design"
      :show-unfavorite="showUnfavorite"
      :position="menuPosition"
      @action="handleAction"
      @close="contextMenuOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useDesignStore } from '@/stores/design'
import { useToast } from '@/composables/useToast'
import InlineEditableName from '@/components/designs/InlineEditableName.vue'
import DesignContextMenu from '@/components/designs/DesignContextMenu.vue'
import CanvasPreview from '@/components/designs/CanvasPreview.vue'

const props = defineProps({
  design: {
    type: Object,
    required: true
  },
  showUnfavorite: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['edit', 'duplicate', 'delete', 'add-to-campaign', 'unfavorite', 'refresh'])

const { t } = useI18n()
const designStore = useDesignStore()
const { success, error } = useToast()

const showActions = ref(false)
const contextMenuOpen = ref(false)
const menuPosition = ref({ x: 0, y: 0 })
const isFavoriting = ref(false)

const toggleFavorite = async () => {
  if (props.disabled || isFavoriting.value) return
  
  // Optimistic UI Update - تحديث فوري!
  const previousState = props.design.is_favorited
  props.design.is_favorited = !previousState
  
  isFavoriting.value = true
  
  try {
    await designStore.toggleFavorite(props.design.id, previousState)
    
    // Show success notification (مثل Canva)
    if (props.design.is_favorited) {
      success(t('designs.added_to_favorites'), 1500)
    } else {
      success(t('designs.removed_from_favorites'), 1500)
    }
    
    if (props.design.is_favorited && props.showUnfavorite) {
      emit('unfavorite', props.design)
    }
  } catch (err) {
    // Revert optimistic update on error
    props.design.is_favorited = previousState
    error(t('designs.favorite_error'), 2000)
    console.error('Failed to toggle favorite:', err)
  } finally {
    isFavoriting.value = false
  }
}

const openContextMenu = (event) => {
  if (props.disabled) return
  
  const rect = event.target.getBoundingClientRect()
  menuPosition.value = {
    x: rect.right - 220, // 220px is menu width
    y: rect.bottom + 8
  }
  contextMenuOpen.value = true
}

const updateTitle = async (newTitle) => {
  try {
    await designStore.updateDesignTitle(props.design.uuid, newTitle)
  } catch (error) {
    console.error('Failed to update title:', error)
  }
}

const handleAction = async ({ action, design }) => {
  switch (action) {
    case 'open-new-tab':
      window.open(`/editor/${design.uuid}`, '_blank')
      break
      
    case 'details':
      // TODO: Show design details modal
      console.log('Show details for:', design)
      break
      
    case 'rename':
      // Trigger inline edit (already handled by InlineEditableName)
      break
      
    case 'duplicate':
      emit('duplicate', design)
      break
      
    case 'download':
      // TODO: Download design
      console.log('Download design:', design)
      break
      
    case 'share':
      // TODO: Share design
      console.log('Share design:', design)
      break
      
    case 'copy-link':
      // TODO: Copy link to clipboard
      const link = `${window.location.origin}/editor/${design.uuid}`
      navigator.clipboard.writeText(link)
      break
      
    case 'add-to-campaign':
      emit('add-to-campaign', design)
      break
      
    case 'unfavorite':
      await toggleFavorite()
      emit('unfavorite', design)
      break
      
    case 'move-to-trash':
      emit('delete', design)
      break
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))
  
  if (diffDays === 0) return t('common.today')
  if (diffDays === 1) return t('common.yesterday')
  if (diffDays < 7) return `${diffDays} ${t('common.days_ago')}`
  
  return date.toLocaleDateString()
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
</script>

<style scoped>
/* All styles are in design-cards.css */
</style>
