<template>
  <div class="trash-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>🗑️ {{ $t('designs.trash') }}</h1>
        <p class="text-error">{{ $t('designs.trash_warning') }}</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-indicator">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">{{ $t('common.loading') }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="trashedDesigns.length === 0" class="empty-state">
      <i class="bx bx-trash display-1"></i>
      <h4>{{ $t('designs.empty.title') }}</h4>
      <p>سلة المهملات فارغة</p>
    </div>

    <!-- Trashed Designs Grid -->
    <div v-else class="designs-grid">
      <div 
        v-for="design in trashedDesigns" 
        :key="design.uuid"
        class="trash-card"
      >
        <DesignCard :design="design" :disabled="true" />
        
        <!-- Trash Actions Overlay -->
        <div class="trash-actions">
          <button 
            class="btn btn-sm btn-secondary" 
            @click="restoreDesign(design)"
            :disabled="restoring"
          >
            <span v-if="restoring && restoringId === design.uuid" class="spinner-border spinner-border-sm me-1"></span>
            <i v-else class="bx bx-undo"></i>
            {{ $t('designs.restore') }}
          </button>
          <button 
            class="btn btn-sm btn-danger" 
            @click="confirmDeleteForever(design)"
            :disabled="deleting"
          >
            <i class="bx bx-trash"></i>
            {{ $t('designs.delete_forever') }}
          </button>
        </div>
        
        <!-- Trash Date -->
        <span class="trash-date">
          <i class="bx bx-time-five"></i>
          تم الحذف {{ formatDate(design.trashed_at) }}
        </span>
      </div>
    </div>

    <!-- Load More -->
    <div ref="loadMoreTrigger" class="loading-indicator">
      <div v-if="loadingMore" class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Delete Forever Confirmation Modal -->
    <div 
      v-if="deleteModalVisible" 
      class="modal show d-block" 
      tabindex="-1"
      @click.self="cancelDelete"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger">{{ $t('designs.delete_forever') }}</h5>
            <button type="button" class="btn-close" @click="cancelDelete"></button>
          </div>
          <div class="modal-body">
            <p class="text-danger fw-bold">⚠️ تحذير: هذا الإجراء لا يمكن التراجع عنه!</p>
            <p>سيتم حذف التصميم نهائياً ولن تتمكن من استعادته.</p>
            <p v-if="designToDelete" class="text-muted">
              <strong>{{ designToDelete.title || 'Untitled Design' }}</strong>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cancelDelete">
              {{ $t('common.cancel') }}
            </button>
            <button type="button" class="btn btn-danger" @click="deleteForever" :disabled="deleting">
              <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
              {{ $t('designs.delete_forever') }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="deleteModalVisible" class="modal-backdrop show"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useDesignStore } from '@/stores/design'
import { useI18n } from 'vue-i18n'
import DesignCard from './DesignCard.vue'

const designStore = useDesignStore()
const { t } = useI18n()

// State
const trashedDesigns = ref([])
const loading = ref(false)
const loadingMore = ref(false)
const restoring = ref(false)
const restoringId = ref(null)
const deleteModalVisible = ref(false)
const designToDelete = ref(null)
const deleting = ref(false)
const currentPage = ref(1)
const hasMore = ref(true)
const loadMoreTrigger = ref(null)
const observer = ref(null)

// Methods
const loadTrashedDesigns = async (page = 1, append = false) => {
  if (append) {
    loadingMore.value = true
  } else {
    loading.value = true
  }

  try {
    const response = await designStore.fetchTrashedDesigns(page)
    
    if (append) {
      trashedDesigns.value = [...trashedDesigns.value, ...response.data]
    } else {
      trashedDesigns.value = response.data || []
    }
    
    hasMore.value = response.current_page < response.last_page
    currentPage.value = response.current_page
  } catch (error) {
    console.error('Failed to load trashed designs:', error)
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const loadMore = async () => {
  if (loadingMore.value || !hasMore.value || loading.value) return
  await loadTrashedDesigns(currentPage.value + 1, true)
}

const setupObserver = () => {
  observer.value = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        loadMore()
      }
    },
    { threshold: 0.5 }
  )
  
  if (loadMoreTrigger.value) {
    observer.value.observe(loadMoreTrigger.value)
  }
}

const restoreDesign = async (design) => {
  restoring.value = true
  restoringId.value = design.uuid
  
  try {
    await designStore.restoreDesign(design.uuid)
    
    // Remove from trash list
    trashedDesigns.value = trashedDesigns.value.filter(d => d.uuid !== design.uuid)
  } catch (error) {
    console.error('Failed to restore design:', error)
  } finally {
    restoring.value = false
    restoringId.value = null
  }
}

const confirmDeleteForever = (design) => {
  designToDelete.value = design
  deleteModalVisible.value = true
}

const cancelDelete = () => {
  designToDelete.value = null
  deleteModalVisible.value = false
}

const deleteForever = async () => {
  if (!designToDelete.value) return
  
  deleting.value = true
  try {
    await designStore.forceDeleteDesign(designToDelete.value.uuid)
    
    // Remove from trash list
    trashedDesigns.value = trashedDesigns.value.filter(d => d.uuid !== designToDelete.value.uuid)
    
    cancelDelete()
  } catch (error) {
    console.error('Failed to delete design:', error)
  } finally {
    deleting.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
  
  if (diffHours < 1) return 'منذ دقائق'
  if (diffHours < 24) return `منذ ${diffHours} ساعة`
  if (diffDays === 1) return t('common.yesterday')
  if (diffDays < 7) return `منذ ${diffDays} أيام`
  if (diffDays < 30) return `منذ ${Math.floor(diffDays / 7)} أسابيع`
  
  return date.toLocaleDateString()
}

// Lifecycle
onMounted(async () => {
  await loadTrashedDesigns()
  
  setTimeout(() => {
    setupObserver()
  }, 100)
})

onUnmounted(() => {
  if (observer.value) {
    observer.value.disconnect()
  }
})
</script>

<style scoped>
/* All styles are in design-cards.css */
</style>

