<template>
  <div class="designs-list">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">{{ $t('designs.title') }}</h2>
        <p class="text-muted">{{ $t('designs.subtitle') }}</p>
      </div>
      <div>
        <button class="btn btn-primary" @click="createNew">
          <i class="bi bi-plus-lg me-2"></i>
          {{ $t('designs.create_new') }}
        </button>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <!-- Filter tabs -->
          <div class="col-12">
            <ul class="nav nav-pills">
              <li class="nav-item">
                <a 
                  class="nav-link" 
                  :class="{ active: activeFilter === 'all' }"
                  href="#" 
                  @click.prevent="setFilter('all')"
                >
                  <i class="bi bi-grid me-1"></i>
                  {{ $t('designs.filters.all') }}
                </a>
              </li>
              <li class="nav-item">
                <a 
                  class="nav-link" 
                  :class="{ active: activeFilter === 'ai' }"
                  href="#" 
                  @click.prevent="setFilter('ai')"
                >
                  <i class="bi bi-stars me-1"></i>
                  {{ $t('designs.filters.ai') }}
                </a>
              </li>
              <li class="nav-item">
                <a 
                  class="nav-link" 
                  :class="{ active: activeFilter === 'manual' }"
                  href="#" 
                  @click.prevent="setFilter('manual')"
                >
                  <i class="bi bi-pencil me-1"></i>
                  {{ $t('designs.filters.manual') }}
                </a>
              </li>
              <li class="nav-item">
                <a 
                  class="nav-link" 
                  :class="{ active: activeFilter === 'campaign' }"
                  href="#" 
                  @click.prevent="setFilter('campaign')"
                >
                  <i class="bi bi-megaphone me-1"></i>
                  {{ $t('designs.filters.campaigns') }}
                </a>
              </li>
              <li class="nav-item">
                <a 
                  class="nav-link" 
                  :class="{ active: activeFilter === 'templates' }"
                  href="#" 
                  @click.prevent="setFilter('templates')"
                >
                  <i class="bi bi-bookmark me-1"></i>
                  {{ $t('designs.filters.templates') }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Search -->
          <div class="col-md-8">
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-search"></i>
              </span>
              <input 
                v-model="searchQuery" 
                type="text" 
                class="form-control" 
                :placeholder="$t('designs.search_placeholder')"
                @input="debouncedSearch"
              >
              <button 
                v-if="searchQuery" 
                class="btn btn-outline-secondary" 
                type="button"
                @click="clearSearch"
              >
                <i class="bi bi-x"></i>
              </button>
            </div>
          </div>

          <!-- Sort -->
          <div class="col-md-4">
            <select v-model="sortBy" class="form-select" @change="loadDesigns">
              <option value="created_at">{{ $t('designs.sort.newest') }}</option>
              <option value="updated_at">{{ $t('designs.sort.recently_updated') }}</option>
              <option value="title">{{ $t('designs.sort.name') }}</option>
              <option value="used_count">{{ $t('designs.sort.most_used') }}</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && (!designs || designs.length === 0)" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">{{ $t('common.loading') }}</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="alert alert-danger">
      <i class="bi bi-exclamation-triangle me-2"></i>
      {{ error }}
    </div>

    <!-- Empty State -->
    <div v-else-if="!designs || designs.length === 0" class="text-center py-5">
      <i class="bi bi-layers display-1 text-muted"></i>
      <h4 class="mt-3">{{ $t('designs.empty.title') }}</h4>
      <p class="text-muted">{{ $t('designs.empty.subtitle') }}</p>
      <button class="btn btn-primary mt-3" @click="createNew">
        <i class="bi bi-plus-lg me-2"></i>
        {{ $t('designs.create_first') }}
      </button>
    </div>

    <!-- Designs Grid -->
    <div v-else-if="designs && designs.length > 0" class="row g-4">
      <div 
        v-for="design in designs" 
        :key="design.uuid" 
        class="col-12 col-sm-6 col-md-4 col-lg-3"
      >
        <DesignCard 
          :design="design" 
          @edit="editDesign"
          @duplicate="duplicateDesign"
          @delete="confirmDelete"
          @add-to-campaign="addToCampaign"
        />
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="d-flex justify-content-center mt-4">
      <nav>
        <ul class="pagination">
          <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
            <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page - 1)">
              <i class="bi bi-chevron-left"></i>
            </a>
          </li>
          <li 
            v-for="page in paginationPages" 
            :key="page" 
            class="page-item"
            :class="{ active: page === pagination.current_page }"
          >
            <a class="page-link" href="#" @click.prevent="goToPage(page)">
              {{ page }}
            </a>
          </li>
          <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
            <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page + 1)">
              <i class="bi bi-chevron-right"></i>
            </a>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Delete Confirmation Modal -->
    <div 
      v-if="deleteModalVisible" 
      class="modal show d-block" 
      tabindex="-1"
      @click.self="cancelDelete"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('designs.delete.confirm_title') }}</h5>
            <button type="button" class="btn-close" @click="cancelDelete"></button>
          </div>
          <div class="modal-body">
            <p>{{ $t('designs.delete.confirm_message') }}</p>
            <p v-if="designToDelete" class="text-muted">
              <strong>{{ designToDelete.title || 'Untitled Design' }}</strong>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cancelDelete">
              {{ $t('common.cancel') }}
            </button>
            <button type="button" class="btn btn-danger" @click="deleteDesign" :disabled="deleting">
              <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
              {{ $t('common.delete') }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="deleteModalVisible" class="modal-backdrop show"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useDesignStore } from '@/stores/design'
import { useI18n } from 'vue-i18n'
import DesignCard from './DesignCard.vue'

const router = useRouter()
const designStore = useDesignStore()
const { t } = useI18n()

// State
const activeFilter = ref('all')
const searchQuery = ref('')
const sortBy = ref('created_at')
const deleteModalVisible = ref(false)
const designToDelete = ref(null)
const deleting = ref(false)

// Computed
const designs = computed(() => designStore.designs || [])
const loading = computed(() => designStore.loading)
const error = computed(() => designStore.error)
const pagination = computed(() => designStore.pagination || { current_page: 1, last_page: 1, per_page: 20, total: 0 })

const paginationPages = computed(() => {
  const pages = []
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  
  // Always show first page
  pages.push(1)
  
  // Show pages around current
  for (let i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) {
    if (!pages.includes(i)) pages.push(i)
  }
  
  // Show last page
  if (last > 1 && !pages.includes(last)) pages.push(last)
  
  return pages
})

// Methods
const setFilter = (filter) => {
  activeFilter.value = filter
  
  // Update store filters based on selection
  if (filter === 'all') {
    designStore.clearFilters()
  } else if (filter === 'ai') {
    designStore.setFilters({ source_type: 'ai' })
  } else if (filter === 'manual') {
    designStore.setFilters({ source_type: 'manual' })
  } else if (filter === 'campaign') {
    // Filter by context_type in future
    designStore.setFilters({ source_type: null })
  } else if (filter === 'templates') {
    designStore.setFilters({ is_template: true })
  }
  
  loadDesigns()
}

const clearSearch = () => {
  searchQuery.value = ''
  designStore.setFilters({ search: '' })
  loadDesigns()
}

let searchTimeout
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    designStore.setFilters({ search: searchQuery.value })
    loadDesigns()
  }, 500)
}

const loadDesigns = async (page = 1) => {
  try {
    await designStore.fetchDesigns(page)
  } catch (err) {
    console.error('Failed to load designs:', err)
  }
}

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadDesigns(page)
  }
}

const createNew = () => {
  router.push({ name: 'ai-studio' })
}

const editDesign = (design) => {
  // Open editor in new tab directly
  window.open(`/editor/${design.uuid}`, '_blank')
}

const duplicateDesign = async (design) => {
  try {
    await designStore.duplicateDesign(design.uuid)
    // Show success message
    console.log('Design duplicated successfully')
  } catch (err) {
    console.error('Failed to duplicate design:', err)
  }
}

const confirmDelete = (design) => {
  designToDelete.value = design
  deleteModalVisible.value = true
}

const cancelDelete = () => {
  designToDelete.value = null
  deleteModalVisible.value = false
}

const deleteDesign = async () => {
  if (!designToDelete.value) return
  
  deleting.value = true
  try {
    await designStore.deleteDesign(designToDelete.value.uuid)
    cancelDelete()
  } catch (err) {
    console.error('Failed to delete design:', err)
    alert(t('designs.delete.error'))
  } finally {
    deleting.value = false
  }
}

const addToCampaign = (design) => {
  // TODO: Implement add to campaign modal
  console.log('Add to campaign:', design)
}

// Lifecycle
onMounted(() => {
  loadDesigns()
})
</script>

<style scoped>
.designs-list {
  padding: 2rem 0;
}

.nav-pills .nav-link {
  color: #6c757d;
}

.nav-pills .nav-link.active {
  background-color: #0d6efd;
  color: white;
}

.nav-pills .nav-link:hover {
  background-color: #e9ecef;
  color: #0d6efd;
}

.nav-pills .nav-link.active:hover {
  background-color: #0b5ed7;
  color: white;
}
</style>

