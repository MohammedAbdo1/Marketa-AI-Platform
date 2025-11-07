<template>
  <div
    class="campaign-card"
    @click="openDetails"
    @mouseenter="showActions = true"
    @mouseleave="showActions = false"
  >
    <!-- Card Header -->
    <div class="card-header">
      <div class="status-badge" :class="`status-${campaign.status || 'draft'}`">
        {{ getStatusLabel(campaign.status) }}
      </div>
      
      <!-- Three Dots Menu -->
      <button 
        v-show="showActions || menuOpen"
        class="btn-menu"
        @click.stop="toggleMenu"
        :aria-label="$t('common.more')"
      >
        <i class="bx bx-dots-horizontal-rounded"></i>
      </button>

      <!-- Context Menu -->
      <div v-if="menuOpen" class="context-menu" @click.stop>
        <button class="menu-item" @click="openDetails">
          <i class="bx bx-show"></i>
          <span>{{ $t('campaigns.view_details') }}</span>
        </button>
        <button class="menu-item" @click="editCampaign">
          <i class="bx bx-edit"></i>
          <span>{{ $t('common.edit') }}</span>
        </button>
        <button class="menu-item" @click="duplicateCampaign">
          <i class="bx bx-copy"></i>
          <span>{{ $t('common.duplicate') }}</span>
        </button>
        <div class="menu-divider"></div>
        <button class="menu-item menu-item-danger" @click="confirmDelete">
          <i class="bx bx-trash"></i>
          <span>{{ $t('common.delete') }}</span>
        </button>
      </div>
    </div>

    <!-- Card Body -->
    <div class="card-body">
      <!-- Campaign Name -->
      <h3 class="campaign-name">{{ campaign.name || $t('campaigns.untitled') }}</h3>

      <!-- Brand Badge (if exists) -->
      <div v-if="campaign.brand" class="brand-badge">
        <i class="bx bx-briefcase"></i>
        <span>{{ campaign.brand.name }}</span>
      </div>

      <!-- Date Range -->
      <div class="campaign-dates">
        <i class="bx bx-calendar"></i>
        <span v-if="campaign.start_date && campaign.end_date">
          {{ formatDate(campaign.start_date) }} → {{ formatDate(campaign.end_date) }}
        </span>
        <span v-else class="text-muted">{{ $t('campaigns.no_dates') }}</span>
      </div>

      <!-- Target Platforms -->
      <div v-if="displayPlatforms.length > 0" class="platforms">
        <div 
          v-for="platform in displayPlatforms" 
          :key="platform"
          class="platform-icon"
          :class="`platform-${platform.toLowerCase()}`"
          :title="getPlatformName(platform)"
        >
          <i :class="getPlatformIcon(platform)" class="platform-logo"></i>
        </div>
      </div>

      <!-- Posts Count -->
      <div class="posts-info">
        <i class="bx bx-images"></i>
        <span>{{ getPostsCount() }} {{ $t('campaigns.posts') }}</span>
      </div>

      <!-- Language Info (if AI analyzed) -->
      <div v-if="campaign.ai_analysis?.detected_languages" class="language-info">
        <i class="bx bx-globe"></i>
        <span>{{ getLanguagesText() }}</span>
      </div>

      <!-- Generation Progress (if generating) -->
      <div v-if="campaign.generation_status === 'generating'" class="generation-progress">
        <div class="progress-header">
          <span class="progress-label">{{ $t('campaigns.generating') }}</span>
          <span class="progress-value">{{ campaign.generation_progress || 0 }}%</span>
        </div>
        <div class="progress-bar-container">
          <div 
            class="progress-bar-fill" 
            :style="{ width: (campaign.generation_progress || 0) + '%' }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useCampaignStore } from '@/stores/campaign'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'

const props = defineProps({
  campaign: { type: Object, required: true }
})

const router = useRouter()
const { t } = useI18n()
const campaignStore = useCampaignStore()
const { success, error } = useToast()
const { showConfirm } = useConfirm()

const showActions = ref(false)
const menuOpen = ref(false)

// Platforms to display (مع fallback للبيانات القديمة)
const displayPlatforms = computed(() => {
  if (props.campaign.platforms && props.campaign.platforms.length > 0) {
    return props.campaign.platforms
  }
  // Fallback: عرض platforms افتراضية بناءً على generated_posts
  const posts = props.campaign.generated_posts || []
  const uniquePlatforms = [...new Set(posts.map(p => p.platform).filter(Boolean))]
  
  // إذا لا توجد posts، نعرض platforms شائعة افتراضية
  return uniquePlatforms.length > 0 
    ? uniquePlatforms 
    : ['instagram', 'facebook', 'x']  // ← افتراضي للعرض فقط
})

const openDetails = () => {
  router.push(`/dashboard/campaigns/${props.campaign.uuid}`)
}

const editCampaign = () => {
  router.push(`/dashboard/campaigns/${props.campaign.uuid}/edit`)
  menuOpen.value = false
}

const duplicateCampaign = async () => {
  try {
    await campaignStore.duplicateCampaign(props.campaign.uuid)
    success(t('campaigns.duplicated_success'))
  } catch (err) {
    error(t('campaigns.duplicate_error'))
  }
  menuOpen.value = false
}

const confirmDelete = () => {
  menuOpen.value = false
  
  showConfirm({
    title: t('campaigns.delete_title'),
    message: t('campaigns.delete_confirm'),
    description: t('campaigns.delete_warning'),
    confirmText: t('common.delete'),
    cancelText: t('common.cancel'),
    dangerMode: true,
    onConfirm: async () => {
      try {
        await campaignStore.deleteCampaign(props.campaign.uuid)
        success(t('campaigns.deleted_success'))
      } catch (err) {
        error(t('campaigns.delete_error'))
        throw err // Re-throw to keep loading state
      }
    }
  })
}

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value
}

const handleClickOutside = (event) => {
  if (menuOpen.value && !event.target.closest('.campaign-card')) {
    menuOpen.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('ar-SA', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}

const getStatusLabel = (status) => {
  const statusMap = {
    draft: t('campaigns.status_draft'),
    active: t('campaigns.status_active'),
    completed: t('campaigns.status_completed'),
    paused: t('campaigns.status_paused')
  }
  return statusMap[status] || status
}

const getPlatformIcon = (platform) => {
  const iconMap = {
    'instagram': 'fa-brands fa-instagram',
    'facebook': 'fa-brands fa-facebook',
    'twitter': 'fa-brands fa-twitter',
    'x': 'fa-brands fa-x-twitter', // X logo
    'tiktok': 'fa-brands fa-tiktok',
    'linkedin': 'fa-brands fa-linkedin',
    'youtube': 'fa-brands fa-youtube',
    'snapchat': 'fa-brands fa-snapchat',
    'pinterest': 'fa-brands fa-pinterest'
  }
  return iconMap[platform.toLowerCase()] || 'bx-share-alt'
}

const getPlatformName = (platform) => {
  const nameMap = {
    'instagram': 'Instagram',
    'facebook': 'Facebook',
    'twitter': 'Twitter',
    'x': 'X',
    'tiktok': 'TikTok',
    'linkedin': 'LinkedIn',
    'youtube': 'YouTube',
    'snapchat': 'Snapchat',
    'pinterest': 'Pinterest'
  }
  return nameMap[platform.toLowerCase()] || platform
}

const getPostsCount = () => {
  return props.campaign.posts?.length || props.campaign.generated_posts?.length || 0
}

const getLanguagesText = () => {
  const langs = props.campaign.ai_analysis?.detected_languages || []
  const names = {
    'ar': 'العربية',
    'en': 'English',
    'fr': 'Français',
    'it': 'Italiano',
    'es': 'Español'
  }
  return langs.map(l => names[l] || l).join(', ')
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.campaign-card {
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  padding: var(--space-4);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); /* Smooth and natural */
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  height: 100%;
}

.campaign-card:hover {
  border-color: rgba(55, 53, 47, 0.16); /* Notion-style subtle border */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Soft shadow */
  transform: translateY(-2px);
}

/* Card Header */
.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-2);
  position: relative;
}

/* Status Badge */
.status-badge {
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  font-size: var(--text-xs);
  font-weight: var(--font-semibold);
  text-transform: capitalize;
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

/* Menu Button */
.btn-menu {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition-fast);
  color: var(--color-text-secondary);
  font-size: var(--text-md);
}

.btn-menu:hover {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
  border-color: var(--color-border);
}

/* Context Menu */
.context-menu {
  position: absolute;
  top: calc(100% + 4px);
  right: 0;
  min-width: 180px;
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-dropdown);
  padding: var(--space-1);
  z-index: 100;
}

[dir="rtl"] .context-menu {
  right: auto;
  left: 0;
}

.menu-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-3);
  background: transparent;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition-fast);
  text-align: start;
  font-size: var(--text-sm);
  color: var(--color-text-primary);
}

.menu-item:hover {
  background: var(--color-bg-hover);
}

.menu-item i {
  font-size: var(--text-md);
}

.menu-item-danger {
  color: var(--color-error);
}

.menu-item-danger:hover {
  background: var(--color-error-bg);
}

.menu-divider {
  height: 1px;
  background: var(--color-border-light);
  margin: var(--space-1) 0;
}

/* Card Body */
.card-body {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  flex: 1;
}

/* Campaign Name */
.campaign-name {
  font-size: var(--text-md);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
  margin: 0;
  line-height: 1.4;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* Brand Badge */
.brand-badge {
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
  padding: var(--space-1) var(--space-2);
  background: var(--color-blue-bg);
  color: var(--color-blue-text);
  border-radius: var(--radius-sm);
  font-size: var(--text-xs);
  font-weight: var(--font-medium);
  width: fit-content;
}

.brand-badge i {
  font-size: var(--text-sm);
}

/* Campaign Dates */
.campaign-dates {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-xs);
  color: var(--color-text-secondary);
}

.campaign-dates i {
  font-size: var(--text-sm);
  color: var(--color-text-tertiary);
}

/* Target Platforms */
.platforms {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  flex-wrap: wrap;
}

.platform-icon {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-sm);
  transition: var(--transition-fast);
}

.platform-logo {
  font-size: 16px !important;
}

/* Platform Colors */
.platform-instagram {
  background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
  color: white;
}

.platform-facebook {
  background: #1877F2;
  color: white;
}

.platform-twitter,
.platform-x {
  background: #000000;
  color: white;
}

.platform-tiktok {
  background: #000000;
  color: white;
}

.platform-linkedin {
  background: #0A66C2;
  color: white;
}

.platform-youtube {
  background: #FF0000;
  color: white;
}

.platform-snapchat {
  background: #FFFC00;
  color: #000000;
}

.platform-pinterest {
  background: #E60023;
  color: white;
}

.platform-icon:hover {
  transform: scale(1.1);
}

/* Posts Info */
.posts-info {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  padding: var(--space-2) var(--space-3);
  background: var(--color-bg-secondary);
  border-radius: var(--radius-md);
  width: fit-content;
}

.posts-info i {
  font-size: var(--text-md);
  color: var(--color-brand-primary);
}

/* Language Info */
.language-info {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  padding: var(--space-2) var(--space-3);
  background: var(--color-bg-secondary);
  border-radius: var(--radius-md);
  width: fit-content;
}

.language-info i {
  font-size: var(--text-md);
  color: var(--color-purple-text);
}

/* Generation Progress */
.generation-progress {
  margin-top: auto;
  padding-top: var(--space-2);
  border-top: 1px solid var(--color-border-light);
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-2);
  font-size: var(--text-xs);
}

.progress-label {
  color: var(--color-text-secondary);
  font-weight: var(--font-medium);
}

.progress-value {
  color: var(--color-brand-primary);
  font-weight: var(--font-semibold);
}

.progress-bar-container {
  height: 6px;
  background: var(--color-bg-secondary);
  border-radius: var(--radius-full);
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--color-brand-primary), var(--color-blue-text));
  border-radius: var(--radius-full);
  transition: width 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
  .campaign-card {
    padding: var(--space-3);
  }
  
  .campaign-name {
    font-size: var(--text-sm);
  }
  
  .platforms {
    gap: var(--space-1);
  }
  
  .platform-icon {
    width: 24px;
    height: 24px;
    font-size: var(--text-sm);
  }
}
</style>


