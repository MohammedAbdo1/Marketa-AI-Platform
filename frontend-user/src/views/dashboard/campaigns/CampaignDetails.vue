<template>
  <div class="campaign-details-enhanced">
    <!-- Header -->
    <div class="page-header">
      <nav class="breadcrumb">
        <button class="breadcrumb-home" @click="$router.push('/dashboard/campaigns')">
          <i class="bx bx-chevron-right"></i>
          {{ $t('campaigns.details.breadcrumb_home') }}
        </button>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">{{ campaign?.name || $t('campaigns.details.title') }}</span>
      </nav>

      <div class="header-content">
        <h1 class="page-title">{{ campaign?.name || $t('campaigns.details.title') }}</h1>
        <div class="header-meta">
          <span class="meta-item">
            <i class="bx bx-calendar"></i>
            {{ formatDateRange(campaign?.start_date, campaign?.end_date) }}
          </span>
          <span class="meta-item">
            <i class="bx bx-images"></i>
            {{ posts.length }} {{ $t('campaigns.posts') }}
          </span>
          <span class="status-badge" :class="`status-${campaign?.status}`">
            {{ campaign?.status }}
          </span>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <LoadingSpinner v-if="loading" size="lg" />

    <!-- Content -->
    <div v-else class="campaign-content">
      <!-- Tab Navigation -->
      <div class="tab-navigation card card-flat">
        <div class="tab-buttons">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            class="tab-button"
            :class="{ active: activeTab === tab.value }"
            @click="activeTab = tab.value"
          >
            <i :class="tab.icon"></i>
            <span>{{ tab.label }}</span>
          </button>
        </div>
      </div>

      <!-- Overview Tab -->
      <div v-show="activeTab === 'overview'" class="tab-panel">
        <div class="overview-grid">
          <!-- Executive Summary -->
          <div class="card overview-card" v-if="executiveSummary">
            <div class="card-header">
              <h3 class="card-title">
                <i class="bx bx-chart"></i>
                {{ $t('campaigns.executive_summary') }}
              </h3>
            </div>
            <div class="card-body">
              <p class="summary-text">{{ executiveSummary.strategy_overview }}</p>
              <div class="summary-stats">
                <div class="stat">
                  <span class="stat-label">{{ $t('campaigns.target_reach') }}</span>
                  <span class="stat-value">{{ executiveSummary.target_kpis?.reach || '—' }}</span>
                </div>
                <div class="stat">
                  <span class="stat-label">{{ $t('campaigns.engagement_rate') }}</span>
                  <span class="stat-value">{{ executiveSummary.target_kpis?.engagement_rate || '—' }}</span>
                </div>
                <div class="stat">
                  <span class="stat-label">{{ $t('campaigns.total_posts_planned') }}</span>
                  <span class="stat-value">{{ executiveSummary.total_posts || posts.length }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Campaign Meta -->
          <div class="card overview-card meta-card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="bx bx-target-lock"></i>
                {{ $t('campaigns.details.campaign_basics') }}
              </h3>
            </div>
            <div class="card-body meta-grid">
              <div class="meta-block">
                <span class="meta-label">{{ $t('campaigns.details.goal') }}</span>
                <span class="meta-value">{{ campaign?.goal || '—' }}</span>
              </div>
              <div class="meta-block">
                <span class="meta-label">{{ $t('campaigns.details.business_type') }}</span>
                <span class="meta-value">{{ campaign?.business_type || '—' }}</span>
              </div>
              <div class="meta-block">
                <span class="meta-label">{{ $t('campaigns.details.platforms') }}</span>
                <span class="meta-value">
                  <span v-for="platform in campaign?.platforms || []" :key="platform" class="platform-chip">
                    <i :class="getPlatformIcon(platform)"></i> {{ platform }}
                  </span>
                  <span v-if="!campaign?.platforms?.length">—</span>
                </span>
              </div>
              <div class="meta-block">
                <span class="meta-label">{{ $t('campaigns.details.duration') }}</span>
                <span class="meta-value">
                  {{ formatDateRange(campaign?.start_date, campaign?.end_date) || '—' }}
                </span>
              </div>
              <div class="meta-block">
                <span class="meta-label">{{ $t('campaigns.details.target_audience') }}</span>
                <span class="meta-value audience-value">{{ audienceSummary }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Phases Overview -->
        <div v-if="campaign?.campaign_strategy?.campaign_phases" class="card phases-section">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bx bx-layer"></i>
              {{ $t('campaigns.campaign_phases') }}
            </h3>
          </div>
          <div class="card-body">
            <div class="phases-grid">
              <div
                v-for="phase in campaign.campaign_strategy.campaign_phases"
                :key="phase.phase"
                class="phase-card"
              >
                <div class="phase-heading">
                  <span class="phase-number">{{ phase.phase }}</span>
                  <div>
                    <h4>{{ phase.name }}</h4>
                    <p>{{ phase.duration }}</p>
                  </div>
                </div>
                <p class="phase-objective">{{ phase.objective }}</p>
                <ul class="phase-messages" v-if="phase.key_messages && phase.key_messages.length">
                  <li v-for="message in phase.key_messages.slice(0, 3)" :key="message">{{ message }}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Timeline Calendar View -->
        <div v-if="campaign?.campaign_strategy?.daily_calendar" class="card timeline-section">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bx bx-calendar"></i>
              {{ $t('campaigns.details.schedule') }}
            </h3>
          </div>
          <div class="card-body">
            <TimelineVisualization :timeline="campaign.campaign_strategy.daily_calendar" />
          </div>
        </div>
      </div>

      <!-- Content Tab -->
      <div v-show="!loading && activeTab === 'content'" class="tab-panel">
        <div class="card posts-controls">
          <div class="card-body controls-grid">
            <div class="control">
              <label class="form-label">{{ $t('campaigns.filter.platform') }}</label>
              <select class="form-select" v-model="selectedPlatform">
                <option value="all">{{ $t('common.all') }}</option>
                <option v-for="platform in availablePlatforms" :key="platform" :value="platform">
                  {{ platform }}
                </option>
              </select>
            </div>
            <div class="control">
              <label class="form-label">{{ $t('campaigns.filter.phase') }}</label>
              <select class="form-select" v-model="selectedPhase">
                <option value="all">{{ $t('common.all') }}</option>
                <option v-for="phase in availablePhases" :key="phase" :value="phase">
                  {{ phase }}
                </option>
              </select>
            </div>
            <div class="control search-control">
              <label class="form-label">{{ $t('campaigns.filter.search') }}</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="searchTerm"
                :placeholder="$t('campaigns.filter.search_placeholder')"
              />
            </div>
            <div class="control view-control">
              <label class="form-label">{{ $t('campaigns.view_mode') }}</label>
              <div class="view-controls">
                <button 
                  class="btn-icon" 
                  :class="{ active: viewMode === 'grid' }"
                  @click="viewMode = 'grid'"
                >
                  <i class="bx bx-grid-alt"></i>
                </button>
                <button 
                  class="btn-icon"
                  :class="{ active: viewMode === 'list' }"
                  @click="viewMode = 'list'"
                >
                  <i class="bx bx-list-ul"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!filteredPosts.length" class="card empty-card">
          <div class="card-body empty-state">
            <i class="bx bx-folder-open"></i>
            <p>{{ $t('campaigns.details.no_posts_filtered') }}</p>
          </div>
        </div>

        <!-- Posts Grid View -->
        <div v-else-if="viewMode === 'grid'" class="posts-grid responsive-grid">
          <div v-for="post in filteredPosts" :key="post.id" class="post-card-wrapper">
            <div class="post-card">
              <div class="post-image">
                <CanvasPreview
                  v-if="hasComposition(post)"
                  :composition-data="getCompositionData(post)"
                  :width="getCompositionDimensions(post).width"
                  :height="getCompositionDimensions(post).height"
                  fit-parent
                />
                <img
                  v-else-if="post.media_urls && post.media_urls.length > 0"
                  :src="post.media_urls[0]"
                  :alt="getPostContent(post)"
                />
                <div v-else class="placeholder-image">
                  <i class="bx bx-image"></i>
                </div>
                <div class="platform-badge">
                  <i :class="getPlatformIcon(post.platform)"></i>
                  {{ post.platform }}
                </div>
              </div>
              <div class="post-content-section">
                <p class="post-text">{{ getPostContent(post) }}</p>
                <div class="post-hashtags" v-if="getHashtags(post).length">
                  <span 
                    v-for="tag in getHashtags(post).slice(0, 3)" 
                    :key="tag" 
                    class="hashtag"
                  >
                    {{ tag }}
                  </span>
                </div>
                <div class="post-meta">
                  <span v-if="post.phase_name" class="meta-tag">{{ post.phase_name }}</span>
                  <span class="meta-tag">{{ $t('campaigns.week') }} {{ post.week_number }}</span>
                  <span class="meta-tag">{{ $t('campaigns.day') }} {{ post.day_number }}</span>
                </div>
              </div>
              <div class="post-actions">
                <button class="btn btn-sm btn-primary" @click="editPost(post)">
                  <i class="bx bx-edit"></i>
                  {{ $t('common.edit') }}
                </button>
                <button 
                  v-if="post.content_brief"
                  class="btn btn-sm btn-ghost"
                  @click="showBrief(post)"
                >
                  <i class="bx bx-info-circle"></i>
                  {{ $t('campaigns.view_brief') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Posts List View -->
        <div v-else class="card post-list-card">
          <div class="list-item" v-for="post in filteredPosts" :key="post.id">
            <div class="list-content">
              <h4>{{ getPostContent(post).substring(0, 140) }}{{ getPostContent(post).length > 140 ? '…' : '' }}</h4>
              <div class="list-meta">
                <span><i :class="getPlatformIcon(post.platform)"></i> {{ post.platform }}</span>
                <span v-if="post.phase_name">{{ post.phase_name }}</span>
                <span>{{ $t('campaigns.week') }} {{ post.week_number }}</span>
              </div>
            </div>
            <div class="list-actions">
              <button class="btn btn-sm btn-primary" @click="editPost(post)">
                <i class="bx bx-edit"></i>
              </button>
              <button 
                v-if="post.content_brief"
                class="btn btn-sm btn-ghost"
                @click="showBrief(post)"
              >
                <i class="bx bx-info-circle"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Insights Tab -->
      <div v-show="!loading && activeTab === 'insights'" class="tab-panel">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bx bx-line-chart"></i>
              {{ $t('campaigns.details.future_insights') }}
            </h3>
          </div>
          <div class="card-body insights-placeholder">
            <p>{{ $t('campaigns.details.insights_placeholder') }}</p>
            <ul>
              <li>{{ $t('campaigns.details.future_insight_1') }}</li>
              <li>{{ $t('campaigns.details.future_insight_2') }}</li>
              <li>{{ $t('campaigns.details.future_insight_3') }}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Content Brief Modal -->
    <div v-if="selectedBrief" class="modal-backdrop" @click="selectedBrief = null">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h3 class="modal-title">{{ $t('campaigns.content_brief') }}</h3>
          <button class="modal-close" @click="selectedBrief = null">
            <i class="bx bx-x"></i>
          </button>
        </div>
        <div class="modal-body">
          <SamplePostCard :post="selectedBrief" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { useCampaignStore } from '@/stores/campaign'
import TimelineVisualization from '@/components/campaigns/TimelineVisualization.vue'
import SamplePostCard from '@/components/campaigns/SamplePostCard.vue'
import CanvasPreview from '@/components/designs/CanvasPreview.vue'
import LoadingSpinner from '@/components/shared/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()
const toast = useToast()
const campaignStore = useCampaignStore()

const loading = ref(true)
const campaign = ref(null)
const posts = ref([])
const viewMode = ref('grid')
const activeTab = ref('overview')
const selectedPlatform = ref('all')
const selectedPhase = ref('all')
const searchTerm = ref('')
const selectedBrief = ref(null)

const tabs = computed(() => [
  { value: 'overview', label: t('campaigns.tabs.overview'), icon: 'bx bx-grid' },
  { value: 'content', label: t('campaigns.tabs.content'), icon: 'bx bx-message-square-detail' },
  { value: 'insights', label: t('campaigns.tabs.insights'), icon: 'bx bx-line-chart' },
])

const executiveSummary = computed(
  () => campaign.value?.campaign_strategy?.executive_summary || null
)

const audienceSummary = computed(() => {
  const strategyAudience = campaign.value?.campaign_strategy?.audience_intelligence
  if (strategyAudience?.summary) {
    return strategyAudience.summary
  }

  if (Array.isArray(campaign.value?.target_audience)) {
    return campaign.value.target_audience.join(', ')
  }

  return (
    campaign.value?.target_audience?.summary ||
    t('campaigns.details.audience_placeholder')
  )
})

const availablePlatforms = computed(() => {
  const all = posts.value
    .map((post) => post.platform)
    .filter((platform) => !!platform)
  return Array.from(new Set(all))
})

const availablePhases = computed(() => {
  const phases = posts.value
    .map((post) => post.phase_name)
    .filter((phase) => !!phase)
  return Array.from(new Set(phases))
})

const filteredPosts = computed(() => {
  return posts.value.filter((post) => {
    const matchesPlatform =
      selectedPlatform.value === 'all' ||
      post.platform === selectedPlatform.value

    const matchesPhase =
      selectedPhase.value === 'all' ||
      post.phase_name === selectedPhase.value

    const rawContent = getPostContent(post)
    const contentText = typeof rawContent === 'string'
      ? rawContent.toLowerCase()
      : JSON.stringify(rawContent ?? '').toLowerCase()
    const hashtags = (getHashtags(post) || []).join(' ').toLowerCase()
    const needle = searchTerm.value.trim().toLowerCase()

    const matchesSearch =
      !needle ||
      contentText.includes(needle) ||
      hashtags.includes(needle) ||
      (post.phase_name || '').toLowerCase().includes(needle)

    return matchesPlatform && matchesPhase && matchesSearch
  })
})

const formatDateRange = (start, end) => {
  if (!start || !end) return ''
  try {
    const formatter = new Intl.DateTimeFormat(locale.value, {
      month: 'short',
      day: 'numeric',
    })
    return `${formatter.format(new Date(start))} - ${formatter.format(new Date(end))}`
  } catch (error) {
    console.warn('Failed to format date range', error)
    return ''
  }
}

const hasGeneratedContent = (campaign) => {
  if (!campaign) return false
  if (typeof campaign.has_generated_content === 'boolean') {
    return campaign.has_generated_content
  }

  if (typeof campaign.posts_count === 'number') {
    return campaign.posts_count > 0
  }

  const posts = Array.isArray(campaign.posts) ? campaign.posts : []
  const creativeAssets = Array.isArray(campaign.creative_assets) ? campaign.creative_assets : []
  const generatedPosts = Array.isArray(campaign.generated_posts) ? campaign.generated_posts : []
  return posts.length > 0 || creativeAssets.length > 0 || generatedPosts.length > 0
}

const isCampaignComplete = (campaign) => {
  if (!campaign) return false

  const rawStatus = (campaign.status || '').toLowerCase()
  const status = rawStatus === 'building' ? 'generating' : rawStatus
  const generationStatus = (campaign.generation_status || '').toLowerCase()
  const contentReady = hasGeneratedContent(campaign)
  const explicitComplete = campaign.is_complete === true

  if (['completed', 'active', 'ready'].includes(status)) {
    return true
  }

  if (explicitComplete && contentReady) {
    return true
  }

  if (generationStatus === 'completed' && contentReady) {
    return true
  }

  return false
}

const shouldResumeWizard = (campaign) => {
  if (!campaign) return true

  const rawStatus = (campaign.status || '').toLowerCase()
  const status = rawStatus === 'building' ? 'generating' : rawStatus
  const generationStatus = (campaign.generation_status || '').toLowerCase()

  if (['draft', 'pending', 'pending_review'].includes(status)) {
    return true
  }

  if (['pending', 'generating', 'processing'].includes(generationStatus)) {
    return true
  }

  if (status === 'generating') {
    if (generationStatus === 'completed' && isCampaignComplete(campaign)) {
      return false
    }
    return true
  }

  if (!isCampaignComplete(campaign)) {
    return true
  }

  return false
}

const redirectToWizard = (campaign) => {
  const step = Number(campaign?.wizard_step || 1)
  const safeStep = Math.min(Math.max(isNaN(step) ? 1 : step, 1), 4)
  router.replace({
    name: 'campaign-wizard',
    query: {
      campaign: campaign.uuid,
      step: safeStep,
    },
  })
}

const loadCampaign = async () => {
  try {
    loading.value = true
    const uuid = route.params.uuid
    const response = await campaignStore.fetchCampaign(uuid)
    campaign.value = response

     if (shouldResumeWizard(response)) {
       loading.value = false
       redirectToWizard(response)
       return
     }

    posts.value = Array.isArray(response?.posts) ? response.posts : []
  } catch (error) {
    console.error('Failed to load campaign:', error)
    toast.error(t('campaigns.details.load_failed'))
  } finally {
    loading.value = false
  }
}

const rebuildCampaign = async () => {
  try {
    loading.value = true
    const uuid = route.params.uuid
    await campaignStore.generateCampaign(uuid, { rebuild: true })
    await loadCampaign()
    toast.success(t('campaigns.details.rebuild_success'))
  } catch (error) {
    console.error('Failed to rebuild campaign:', error)
    toast.error(t('campaigns.details.rebuild_failed'))
  } finally {
    loading.value = false
  }
}

const editPost = (post) => {
  const targetUuid =
    post?.creative_asset_uuid || post?.creative_asset?.uuid || post?.uuid

  if (targetUuid && typeof window !== 'undefined') {
    const editorWindow = window.open(`/editor/${targetUuid}`, '_blank', 'noopener')

    if (editorWindow) {
      const handler = async (event) => {
        if (
          event.origin === window.location.origin &&
          event.data?.type === 'creative-asset:updated' &&
          event.data?.payload?.creative_asset_uuid === targetUuid
        ) {
          await loadCampaign()
          window.removeEventListener('message', handler)
        }
      }

      window.addEventListener('message', handler)

      const pollTimer = setInterval(async () => {
        if (editorWindow.closed) {
          clearInterval(pollTimer)
          window.removeEventListener('message', handler)
          await loadCampaign()
        }
      }, 1500)
    }

    return
  }

  const fallbackId = post?.uuid || post?.id
  if (fallbackId) {
    router.push({ name: 'posts.edit', params: { uuid: fallbackId } })
  } else {
    toast.error(t('campaigns.details.load_failed'))
  }
}

const showBrief = (post) => {
  selectedBrief.value = post
}

const getPostContent = (post) => {
  if (!post?.content) return ''

  if (typeof post.content === 'object' && !Array.isArray(post.content)) {
    const primary = post.primary_language || 'ar'
    const localized = post.content[primary] || Object.values(post.content)[0]
    return typeof localized === 'string'
      ? localized
      : JSON.stringify(localized ?? '')
  }

  if (Array.isArray(post.content)) {
    return post.content
      .map(item => (typeof item === 'string' ? item : JSON.stringify(item)))
      .join(' ')
  }

  return typeof post.content === 'string'
    ? post.content
    : String(post.content ?? '')
}

const getHashtags = (post) => {
  if (!post?.hashtags) return []

  if (typeof post.hashtags === 'object' && !Array.isArray(post.hashtags)) {
    const primary = post.primary_language || 'ar'
    return post.hashtags[primary] || Object.values(post.hashtags)[0] || []
  }

  if (typeof post.hashtags === 'string') {
    try {
      return JSON.parse(post.hashtags)
    } catch (error) {
      return []
    }
  }

  if (Array.isArray(post.hashtags)) {
    return post.hashtags
  }

  return []
}

const resolvePostComposition = (post) => {
  if (!post) return null
  const raw =
    post.composition_layers ??
    (Array.isArray(post.content?.composition_layers)
      ? post.content.composition_layers
      : post.content?.composition_layers)

  if (!raw) return null

  if (Array.isArray(raw)) {
    return raw
  }

  if (typeof raw === 'object') {
    if (Array.isArray(raw.layers)) {
      return raw.layers
    }
    const keys = Object.keys(raw)
    const numeric = keys.length > 0 && keys.every((key) => !Number.isNaN(Number(key)))
    if (numeric) {
      return Object.values(raw)
    }
  }

  return null
}

const hasComposition = (post) => {
  const layers = resolvePostComposition(post)
  return Array.isArray(layers) && layers.length > 0
}

const getCompositionDimensions = (post) => {
  const width =
    post.content?.dimensions?.width ||
    post.settings?.dimensions?.width ||
    post.width ||
    1080
  const height =
    post.content?.dimensions?.height ||
    post.settings?.dimensions?.height ||
    post.height ||
    1080
  return { width, height }
}

const getCompositionData = (post) => {
  const layers = resolvePostComposition(post)
  if (!Array.isArray(layers) || layers.length === 0) return null

  const { width, height } = getCompositionDimensions(post)
  return {
    layers,
    dimensions: { width, height }
  }
}

const getPlatformIcon = (platform) => {
  const iconMap = {
    instagram: 'bx bxl-instagram',
    facebook: 'bx bxl-facebook',
    twitter: 'bx bxl-twitter',
    x: 'bx bxl-twitter',
    tiktok: 'bx bxl-tiktok',
    linkedin: 'bx bxl-linkedin',
    youtube: 'bx bxl-youtube',
  }
  return iconMap[platform?.toLowerCase?.()] || 'bx bx-share-alt'
}

onMounted(loadCampaign)
</script>

<style scoped>
.campaign-details-enhanced {
  padding: var(--space-6);
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  margin-bottom: var(--space-6);
  text-align: start;
  align-items: flex-start;
}

.breadcrumb {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-sm);
  color: var(--color-text-tertiary);
  text-align: start;
}

.breadcrumb-home {
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
  border: none;
  background: none;
  color: inherit;
  cursor: pointer;
}

.breadcrumb-home:hover {
  color: var(--color-brand-primary);
}

.breadcrumb-separator {
  color: var(--color-text-tertiary);
}

.breadcrumb-current {
  color: var(--color-text-secondary);
  font-weight: var(--font-medium);
}

.header-content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: flex-start;
  text-align: start;
  width: 100%;
  gap: var(--space-3);
}

.page-title {
  font-size: var(--text-2xl);
  font-weight: var(--font-bold);
  color: var(--color-text-primary);
  margin: 0 0 var(--space-3);
}

.header-meta {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
  justify-content: flex-start;
  align-items: center;
  color: var(--color-text-secondary);
  font-size: var(--text-sm);
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
}

.status-badge {
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  background: var(--color-bg-secondary);
  text-transform: capitalize;
}

.campaign-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.tab-navigation {
  padding: var(--space-2);
}

.tab-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
}

.tab-button {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  border: none;
  background: var(--color-bg-secondary);
  color: var(--color-text-secondary);
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  cursor: pointer;
  transition: background 0.2s ease;
}

.tab-button.active {
  background: var(--color-brand-primary);
  color: #fff;
}

.tab-panel {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.overview-grid {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(0, 1.2fr);
  gap: var(--space-4);
}

.overview-card {
  height: 100%;
}

.meta-card .meta-grid {
  display: grid;
  gap: var(--space-3);
}

.meta-block {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.meta-label {
  font-size: var(--text-xs);
  color: var(--color-text-tertiary);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.meta-value {
  font-size: var(--text-sm);
  color: var(--color-text-primary);
  line-height: 1.6;
}

.platform-chip {
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
  background: var(--color-bg-secondary);
  border-radius: var(--radius-sm);
  padding: var(--space-1) var(--space-2);
  font-size: var(--text-xs);
  margin-inline-end: var(--space-1);
}

.audience-value {
  white-space: pre-line;
}

.executive-summary {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.summary-text {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  line-height: 1.6;
}

.summary-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: var(--space-3);
}

.stat {
  background: var(--color-bg-secondary);
  border-radius: var(--radius-md);
  padding: var(--space-3);
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.stat-label {
  font-size: var(--text-xs);
  color: var(--color-text-tertiary);
}

.stat-value {
  font-size: var(--text-lg);
  font-weight: var(--font-semibold);
  color: var(--color-brand-primary);
}

.phases-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: var(--space-3);
}

.phase-card {
  background: var(--color-bg-secondary);
  border-radius: var(--radius-md);
  padding: var(--space-4);
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.phase-heading {
  display: flex;
  gap: var(--space-3);
  align-items: center;
}

.phase-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--color-brand-primary);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: var(--font-semibold);
}

.phase-objective {
  margin: 0;
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  line-height: 1.6;
}

.phase-messages {
  margin: 0;
  padding-inline-start: var(--space-4);
  color: var(--color-text-secondary);
  font-size: var(--text-sm);
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.posts-controls .controls-grid {
  display: grid;
  gap: var(--space-3);
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  align-items: end;
}

.control {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.view-controls {
  display: inline-flex;
  gap: var(--space-2);
}

.view-controls .btn-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md);
  border: none;
  background: var(--color-bg-secondary);
  color: var(--color-text-secondary);
  cursor: pointer;
}

.view-controls .btn-icon.active {
  background: var(--color-brand-primary);
  color: #fff;
}

.empty-card .empty-state {
  padding: var(--space-6);
}

.responsive-grid {
  display: grid;
  gap: var(--space-4);
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
}

.post-card {
  background: var(--color-bg-primary);
  border-radius: var(--radius-lg);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 100%;
  box-shadow: var(--shadow-sm);
}

.post-card .post-image {
  position: relative;
  padding-top: 65%;
  background: var(--color-bg-secondary);
}

.post-card .post-image .canvas-preview {
  position: absolute;
  inset: 0;
}

.post-card .post-image img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.placeholder-image {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: var(--color-text-tertiary);
}

.platform-badge {
  position: absolute;
  inset-inline-end: var(--space-2);
  inset-block-end: var(--space-2);
  background: rgba(0, 0, 0, 0.65);
  color: #fff;
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  font-size: var(--text-xs);
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
}

.post-content-section {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-4);
  flex: 1;
}

.post-text {
  margin: 0;
  font-size: var(--text-sm);
  color: var(--color-text-primary);
  line-height: 1.6;
}

.post-hashtags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
}

.hashtag {
  font-size: var(--text-xs);
  background: var(--color-bg-secondary);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
}

.post-meta {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
}

.meta-tag {
  font-size: var(--text-xs);
  background: var(--color-bg-tertiary);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  color: var(--color-text-secondary);
}

.post-actions {
  display: flex;
  gap: var(--space-2);
  padding: var(--space-3) var(--space-4);
  border-top: 1px solid var(--color-bg-tertiary);
}

.post-list-card {
  padding: 0;
}

.post-list-card .list-item {
  display: flex;
  justify-content: space-between;
  gap: var(--space-3);
  padding: var(--space-4);
  border-bottom: 1px solid var(--color-bg-tertiary);
}

.post-list-card .list-item:last-child {
  border-bottom: none;
}

.list-content h4 {
  margin: 0 0 var(--space-2);
  font-size: var(--text-md);
}

.list-meta {
  display: flex;
  gap: var(--space-4);
  color: var(--color-text-secondary);
  font-size: var(--text-xs);
}

.list-actions {
  display: flex;
  gap: var(--space-2);
  align-items: flex-start;
}

.insights-placeholder {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  color: var(--color-text-secondary);
  font-size: var(--text-sm);
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: var(--space-3);
  color: var(--color-text-tertiary);
  text-align: center;
}

.empty-state i {
  font-size: 48px;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-6);
  z-index: 100;
}

.modal {
  width: min(720px, 100%);
  background: var(--color-bg-primary);
  border-radius: var(--radius-lg);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: var(--shadow-lg);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-4);
  border-bottom: 1px solid var(--color-bg-tertiary);
}

.modal-title {
  margin: 0;
  font-size: var(--text-lg);
  font-weight: var(--font-semibold);
}

.modal-body {
  padding: var(--space-4);
  max-height: 70vh;
  overflow-y: auto;
}

.modal-close {
  border: none;
  background: none;
  font-size: 24px;
  color: var(--color-text-tertiary);
  cursor: pointer;
}

@media (max-width: 1200px) {
  .overview-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 1024px) {
  .campaign-details-enhanced {
    padding: var(--space-4);
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    align-self: stretch;
    justify-content: flex-end;
  }
}

@media (max-width: 768px) {
  .tab-buttons {
    gap: var(--space-1);
  }

  .tab-button {
    flex: 1 1 auto;
    justify-content: center;
  }

  .controls-grid {
    grid-template-columns: 1fr;
  }

  .responsive-grid {
    grid-template-columns: 1fr;
  }

  .post-list-card .list-item {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

