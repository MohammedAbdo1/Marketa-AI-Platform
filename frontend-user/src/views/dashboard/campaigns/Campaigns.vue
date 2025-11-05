<template>
  <div class="campaigns-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">{{ $t('sidebar.campaigns') }}</h1>
        <p class="page-subtitle">{{ $t('campaigns.subtitle') }}</p>
      </div>
      <button class="btn btn-primary" @click="$router.push('/dashboard/campaigns/create')">
        <i class="bx bx-plus"></i>
        {{ $t('campaigns.create_new') }}
      </button>
    </div>

    <!-- Filters & Actions Bar -->
    <div class="filters-bar">
      <!-- Search -->
      <div class="search-box">
        <i class="bx bx-search"></i>
        <input 
          v-model="query" 
          type="text" 
          class="search-input" 
          :placeholder="$t('campaigns.search_placeholder')"
        />
      </div>

      <div class="filters-actions">
        <!-- Sort Dropdown -->
        <SortDropdown 
          v-model="sortBy"
          :options="sortOptions"
        />

        <!-- View Toggle (Grid/List) -->
        <ViewToggle 
          v-model="viewMode"
          :show-label="false"
        />

        <!-- Refresh Button -->
        <button class="btn-icon-secondary" @click="refresh" :aria-label="$t('common.refresh')">
          <i class="bx bx-refresh"></i>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="campaigns-content">
      <!-- Loading State -->
      <LoadingSpinner 
        v-if="campaignStore.loading"
        size="lg"
        :message="$t('campaigns.loading_campaigns')"
      />
      
      <!-- Grid View (Cards) -->
      <CampaignKanban v-else-if="viewMode === 'grid'" />
      
      <!-- List View (Table) -->
      <CampaignList v-else />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCampaignStore } from '@/stores/campaign'
import CampaignKanban from '@/components/campaigns/CampaignKanban.vue'
import CampaignList from '@/components/campaigns/CampaignList.vue'
import SortDropdown from '@/components/shared/SortDropdown.vue'
import ViewToggle from '@/components/shared/ViewToggle.vue'
import LoadingSpinner from '@/components/shared/LoadingSpinner.vue'

const { t } = useI18n()
const campaignStore = useCampaignStore()

const query = ref('')
const sortBy = ref('most_relevant')
const viewMode = ref('grid') // 'grid' or 'list' (default: grid/cards)

// Sort Options
const sortOptions = computed(() => [
  {
    value: 'most_relevant',
    label: t('common.most_relevant'),
    icon: 'bx-sparkles'
  },
  {
    value: 'newest',
    label: t('common.newest_first'),
    icon: 'bx-time-five'
  },
  {
    value: 'oldest',
    label: t('common.oldest_first'),
    icon: 'bx-time-five'
  },
  {
    value: 'name_asc',
    label: t('common.name_asc'),
    icon: 'bx-sort-a-z'
  },
  {
    value: 'name_desc',
    label: t('common.name_desc'),
    icon: 'bx-sort-z-a'
  }
])

const refresh = async () => {
  await campaignStore.fetchCampaigns({ 
    q: query.value,
    sort: sortBy.value
  })
}

// Watch for changes
watch(query, async () => {
  await refresh()
})

watch(sortBy, async () => {
  await refresh()
})

onMounted(async () => {
  await refresh()
})
</script>

<style scoped>
.campaigns-page {
  padding: var(--space-6);
  max-width: 100%;
}

/* Page Header */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: var(--space-6);
  gap: var(--space-4);
}

.header-content {
  flex: 1;
}

.page-title {
  font-size: var(--text-2xl);
  font-weight: var(--font-bold);
  color: var(--color-text-primary);
  margin: 0 0 var(--space-2) 0;
}

.page-subtitle {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  margin: 0;
}

/* Filters Bar */
.filters-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-4);
  margin-bottom: var(--space-6);
  flex-wrap: wrap;
}

.filters-actions {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

/* Search Box */
.search-box {
  position: relative;
  flex: 1;
  min-width: 280px;
  max-width: 290px;
}

.search-box i {
  position: absolute;
  top: 50%;
  right: var(--space-3);
  transform: translateY(-50%);
  color: var(--color-text-tertiary);
  font-size: var(--text-md);
  pointer-events: none;
}

[dir="rtl"] .search-box i {
  right: auto;
  left: var(--space-3);
}

.search-input {
  width: 100%;
  padding: var(--space-2) var(--space-10) var(--space-2) var(--space-3);
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  color: var(--color-text-primary);
  transition: var(--transition-fast);
}

[dir="rtl"] .search-input {
  padding: var(--space-2) var(--space-3) var(--space-2) var(--space-10);
}

.search-input:focus {
  outline: none;
  border-color: var(--color-brand-primary);
  box-shadow: 0 0 0 3px rgba(11, 110, 153, 0.1);
  background: var(--color-bg-primary);
}

/* View Toggle styles moved to ViewToggle.vue component */

/* Icon Button */
.btn-icon-secondary {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition-fast);
  color: var(--color-text-secondary);
  font-size: var(--text-md);
}

.btn-icon-secondary:hover {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
  border-color: var(--color-border);
}

/* Content */
.campaigns-content {
  min-height: 400px;
}

/* Responsive */
@media (max-width: 1024px) {
  .campaigns-page {
    padding: var(--space-4);
  }
  
  .filters-bar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-box {
    max-width: 100%;
  }
  
  .filters-actions {
    justify-content: space-between;
  }
}

@media (max-width: 768px) {
  .campaigns-page {
    padding: var(--space-3);
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .page-title {
    font-size: var(--text-xl);
  }
  
  .filters-actions {
    width: 100%;
  }
}
</style>

