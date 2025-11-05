<template>
  <div class="campaigns-list">
    <!-- Table Header -->
    <div class="list-header">
      <div class="list-col col-name">
        <span>{{ $t('campaigns.name') }}</span>
        <i class="bx bx-sort-alt-2"></i>
      </div>
      <div class="list-col col-status">
        {{ $t('campaigns.status') }}
      </div>
      <div class="list-col col-posts">
        {{ $t('campaigns.posts') }}
      </div>
      <div class="list-col col-date">
        <span>{{ $t('common.date_modified') }}</span>
        <i class="bx bx-sort-alt-2"></i>
      </div>
      <div class="list-col col-actions"></div>
    </div>

    <!-- Empty State -->
    <div v-if="campaigns.length === 0" class="empty-state">
      <i class="bx bx-folder-open"></i>
      <p>{{ $t('campaigns.no_campaigns') }}</p>
    </div>

    <!-- Table Rows -->
    <div v-else class="list-body">
      <div 
        v-for="campaign in campaigns" 
        :key="campaign.uuid"
        class="list-row"
        @click="openCampaign(campaign)"
      >
        <!-- Name with Thumbnail -->
        <div class="list-col col-name">
          <div class="campaign-thumbnail">
            <i class="bx bx-file-blank"></i>
          </div>
          <div class="campaign-info">
            <h4 class="campaign-name">{{ campaign.name || 'Untitled Campaign' }}</h4>
            <span class="campaign-type">{{ $t('labels.campaigns') }}</span>
          </div>
        </div>

        <!-- Status -->
        <div class="list-col col-status">
          <span 
            class="status-badge"
            :class="`status-${campaign.status || 'draft'}`"
          >
            {{ getStatusLabel(campaign.status) }}
          </span>
        </div>

        <!-- Posts Count -->
        <div class="list-col col-posts">
          <span class="posts-count">
            <i class="bx bx-images"></i>
            {{ campaign.generated_posts?.length || 0 }}
          </span>
        </div>

        <!-- Modified Date -->
        <div class="list-col col-date">
          <span class="date-text">{{ formatDate(campaign.updated_at) }}</span>
        </div>

        <!-- Actions -->
        <div class="list-col col-actions" @click.stop>
          <button 
            class="btn-more"
            @click="openMenu(campaign)"
            :aria-label="$t('common.more')"
          >
            <i class="bx bx-dots-horizontal-rounded"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useCampaignStore } from '@/stores/campaign'

const router = useRouter()
const { t } = useI18n()
const campaignStore = useCampaignStore()

const campaigns = computed(() => campaignStore.campaigns)

const openCampaign = (campaign) => {
  router.push(`/dashboard/campaigns/${campaign.uuid}`)
}

const openMenu = (campaign) => {
  // TODO: Open context menu
  console.log('Open menu for:', campaign)
}

const getStatusLabel = (status) => {
  const statusMap = {
    draft: t('campaigns.status_draft') || 'Draft',
    active: t('campaigns.status_active') || 'Active',
    completed: t('campaigns.status_completed') || 'Completed',
    paused: t('campaigns.status_paused') || 'Paused'
  }
  return statusMap[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  
  const date = new Date(dateString)
  const now = new Date()
  const diffInDays = Math.floor((now - date) / (1000 * 60 * 60 * 24))
  
  if (diffInDays === 0) return t('common.today')
  if (diffInDays === 1) return t('common.yesterday')
  if (diffInDays < 7) return `${diffInDays} ${t('common.days_ago')}`
  
  return date.toLocaleDateString('ar-SA', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}
</script>

<style scoped>
.campaigns-list {
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

/* Header */
.list-header {
  display: flex;
  align-items: center;
  padding: var(--space-3) var(--space-4);
  background: var(--color-bg-secondary);
  border-bottom: 1px solid var(--color-border-light);
  font-size: var(--text-xs);
  font-weight: var(--font-semibold);
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.list-header .list-col {
  display: flex;
  align-items: center;
  gap: var(--space-1);
}

.list-header i {
  font-size: var(--text-sm);
  color: var(--color-text-tertiary);
}

/* Columns */
.list-col {
  padding: 0 var(--space-2);
}

.col-name {
  flex: 1;
  min-width: 250px;
}

.col-status {
  width: 120px;
}

.col-posts {
  width: 100px;
  text-align: center;
}

.col-date {
  width: 150px;
}

.col-actions {
  width: 60px;
  text-align: center;
}

/* Body */
.list-body {
  display: flex;
  flex-direction: column;
}

/* Row */
.list-row {
  display: flex;
  align-items: center;
  padding: var(--space-3) var(--space-4);
  border-bottom: 1px solid var(--color-border-light);
  cursor: pointer;
  transition: var(--transition-fast);
}

.list-row:last-child {
  border-bottom: none;
}

.list-row:hover {
  background: var(--color-bg-hover);
}

/* Campaign Info */
.col-name {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.campaign-thumbnail {
  width: 40px;
  height: 40px;
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.campaign-thumbnail i {
  font-size: var(--text-xl);
  color: var(--color-text-tertiary);
}

.campaign-info {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  min-width: 0;
}

.campaign-name {
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.campaign-type {
  font-size: var(--text-xs);
  color: var(--color-text-tertiary);
}

/* Status Badge */
.status-badge {
  display: inline-flex;
  align-items: center;
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  font-size: var(--text-xs);
  font-weight: var(--font-medium);
}

.status-draft {
  background: var(--color-gray-bg);
  color: var(--color-gray-text);
}

.status-active {
  background: var(--color-green-bg);
  color: var(--color-green-text);
}

.status-completed {
  background: var(--color-blue-bg);
  color: var(--color-blue-text);
}

.status-paused {
  background: var(--color-orange-bg);
  color: var(--color-orange-text);
}

/* Posts Count */
.posts-count {
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
}

.posts-count i {
  font-size: var(--text-md);
}

/* Date */
.date-text {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
}

/* Actions Button */
.btn-more {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition-fast);
  color: var(--color-text-tertiary);
}

.btn-more:hover {
  background: var(--color-bg-secondary);
  color: var(--color-text-primary);
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--space-10) var(--space-4);
  color: var(--color-text-tertiary);
}

.empty-state i {
  font-size: 48px;
  margin-bottom: var(--space-4);
  opacity: 0.5;
}

.empty-state p {
  font-size: var(--text-sm);
  margin: 0;
}

/* Responsive */
@media (max-width: 1024px) {
  .col-posts,
  .col-date {
    display: none;
  }
  
  .col-status {
    width: 100px;
  }
}

@media (max-width: 768px) {
  .list-header {
    display: none;
  }
  
  .list-row {
    flex-wrap: wrap;
    padding: var(--space-3);
  }
  
  .col-name {
    flex: 1;
    min-width: auto;
  }
  
  .col-status {
    width: auto;
    margin-left: 52px; /* thumbnail + gap */
  }
  
  .col-actions {
    width: auto;
    margin-left: auto;
  }
}
</style>

