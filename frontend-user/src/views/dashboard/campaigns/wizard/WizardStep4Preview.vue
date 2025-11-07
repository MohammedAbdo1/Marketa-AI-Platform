<template>
  <div class="wizard-step-preview">
    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>{{ $t('campaigns.generating_intelligence') }}</p>
    </div>

    <!-- Intelligence Preview -->
    <div v-else-if="intelligence" class="intelligence-preview">
      <div 
        v-if="hasFallback" 
        class="fallback-banner card card-interactive"
      >
        <div class="card-body fallback-banner-body">
          <i class="bx bx-info-circle"></i>
          <div>
            <h4 class="fallback-title">{{ $t('campaigns.preview_fallback_title') }}</h4>
            <p class="fallback-message">
              {{ fallbackMessage }}
            </p>
          </div>
        </div>
      </div>

      <!-- Executive Summary -->
      <div class="summary-card card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="bx bx-bar-chart-alt-2"></i>
            {{ $t('campaigns.executive_summary') }}
          </h3>
        </div>
        <div class="card-body">
          <div class="summary-grid">
            <div class="summary-item">
              <span class="label">{{ $t('campaigns.campaign_name') }}</span>
              <span class="value">{{ intelligence.executive_summary?.campaign_name }}</span>
            </div>
            <div class="summary-item">
              <span class="label">{{ $t('campaigns.objective') }}</span>
              <span class="value">{{ intelligence.executive_summary?.objective }}</span>
            </div>
            <div class="summary-item">
              <span class="label">{{ $t('campaigns.duration') }}</span>
              <span class="value">{{ intelligence.executive_summary?.duration }}</span>
            </div>
            <div class="summary-item">
              <span class="label">{{ $t('campaigns.total_posts') }}</span>
              <span class="value">{{ intelligence.executive_summary?.total_posts }}</span>
            </div>
          </div>

          <!-- KPIs -->
          <div v-if="intelligence.executive_summary?.target_kpis" class="kpis-grid">
            <div class="kpi-card">
              <i class="bx bx-trending-up"></i>
              <div class="kpi-info">
                <span class="kpi-label">{{ $t('campaigns.reach') }}</span>
                <span class="kpi-value">{{ intelligence.executive_summary.target_kpis.reach }}</span>
              </div>
            </div>
            <div class="kpi-card">
              <i class="bx bx-heart"></i>
              <div class="kpi-info">
                <span class="kpi-label">{{ $t('campaigns.engagement_rate') }}</span>
                <span class="kpi-value">{{ intelligence.executive_summary.target_kpis.engagement_rate }}</span>
              </div>
            </div>
            <div class="kpi-card" v-if="intelligence.executive_summary.target_kpis.conversions">
              <i class="bx bx-shopping-bag"></i>
              <div class="kpi-info">
                <span class="kpi-label">{{ $t('campaigns.conversions') }}</span>
                <span class="kpi-value">{{ intelligence.executive_summary.target_kpis.conversions }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Language Analysis -->
      <div v-if="intelligence.language_analysis" class="language-analysis-card card">
        <div class="card-header">
          <h4 class="card-title">
            <i class="bx bx-globe"></i>
            {{ $t('campaigns.language_analysis') }}
          </h4>
        </div>
        <div class="card-body">
          <div class="analysis-grid">
            <div class="analysis-item">
              <span class="label">{{ $t('campaigns.detected_languages') }}</span>
              <div class="language-badges">
                <span 
                  v-for="lang in intelligence.language_analysis.detected_languages" 
                  :key="lang"
                  class="badge badge-primary"
                >
                  {{ getLanguageName(lang) }}
                </span>
              </div>
            </div>
            <div class="analysis-item">
              <span class="label">{{ $t('campaigns.audience_location') }}</span>
              <span class="value">{{ intelligence.language_analysis.audience_location }}</span>
            </div>
            <div class="analysis-item">
              <span class="label">{{ $t('campaigns.audience_age') }}</span>
              <span class="value">{{ intelligence.language_analysis.audience_age }}</span>
            </div>
            <div class="analysis-item">
              <span class="label">{{ $t('campaigns.tone') }}</span>
              <span class="value">{{ intelligence.language_analysis.tone }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Campaign Phases -->
      <div v-if="intelligence.campaign_phases" class="phases-section">
        <h3 class="section-title">
          <i class="bx bx-layer"></i>
          {{ $t('campaigns.campaign_phases') }}
        </h3>
        
        <div class="phases-accordion">
          <details 
            v-for="phase in intelligence.campaign_phases" 
            :key="phase.phase"
            class="phase-details"
            open
          >
            <summary class="phase-summary">
              <div class="phase-header">
                <span class="phase-number">{{ $t('campaigns.phase') }} {{ phase.phase }}</span>
                <span class="phase-name">{{ phase.name }}</span>
              </div>
              <i class="bx bx-chevron-down"></i>
            </summary>
            
            <div class="phase-content">
              <div class="phase-info-grid">
                <div class="info-item">
                  <span class="label">{{ $t('campaigns.duration') }}</span>
                  <span class="value">{{ phase.duration }}</span>
                </div>
                <div class="info-item">
                  <span class="label">{{ $t('campaigns.objective') }}</span>
                  <span class="value">{{ phase.objective }}</span>
                </div>
              </div>

              <div class="phase-strategy">
                <h5>{{ $t('campaigns.strategy') }}</h5>
                <p>{{ phase.strategy }}</p>
              </div>

              <div v-if="phase.content_mix" class="content-mix">
                <h5>{{ $t('campaigns.content_mix') }}</h5>
                <div class="mix-grid">
                  <div v-for="(percentage, type) in phase.content_mix" :key="type" class="mix-item">
                    <span class="mix-type">{{ type }}</span>
                    <span class="mix-percentage">{{ percentage }}</span>
                  </div>
                </div>
              </div>

              <div v-if="phase.key_messages" class="key-messages">
                <h5>{{ $t('campaigns.key_messages') }}</h5>
                <ul>
                  <li v-for="(message, idx) in phase.key_messages" :key="idx">
                    {{ message }}
                  </li>
                </ul>
              </div>
            </div>
          </details>
        </div>
      </div>

      <!-- Timeline Visualization -->
      <div v-if="intelligence.daily_calendar" class="timeline-section">
        <h3 class="section-title">
          <i class="bx bx-calendar"></i>
          {{ $t('campaigns.daily_calendar') }}
        </h3>
        <TimelineVisualization :timeline="intelligence.daily_calendar" />
      </div>

      <!-- Sample Posts -->
      <div v-if="intelligence.sample_posts && intelligence.sample_posts.length > 0" class="samples-section">
        <h3 class="section-title">
          <i class="bx bx-images"></i>
          {{ $t('campaigns.sample_posts') }}
        </h3>
        
        <div class="samples-carousel">
          <div class="samples-grid">
            <SamplePostCard 
              v-for="(post, idx) in intelligence.sample_posts.slice(0, 3)" 
              :key="idx"
              :post="post"
            />
          </div>
        </div>
      </div>

      <!-- Content Guidelines -->
      <div v-if="intelligence.content_guidelines" class="guidelines-card card">
        <div class="card-header">
          <h4 class="card-title">
            <i class="bx bx-palette"></i>
            {{ $t('campaigns.content_guidelines') }}
          </h4>
        </div>
        <div class="card-body">
          <div class="guidelines-grid">
            <div class="guideline-item">
              <span class="label">{{ $t('campaigns.visual_style') }}</span>
              <span class="value">{{ intelligence.content_guidelines.visual_style }}</span>
            </div>
            <div class="guideline-item">
              <span class="label">{{ $t('campaigns.tone_of_voice') }}</span>
              <span class="value">{{ intelligence.content_guidelines.tone_of_voice }}</span>
            </div>
            <div v-if="intelligence.content_guidelines.colors" class="guideline-item full-width">
              <span class="label">{{ $t('campaigns.color_palette') }}</span>
              <div class="color-swatches">
                <div 
                  v-for="color in intelligence.content_guidelines.colors" 
                  :key="color"
                  class="color-swatch"
                  :style="{ background: color }"
                  :title="color"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Estimated Metrics -->
      <div v-if="intelligence.estimated_metrics" class="metrics-card card">
        <div class="card-header">
          <h4 class="card-title">
            <i class="bx bx-calculator"></i>
            {{ $t('campaigns.estimated_metrics') }}
          </h4>
        </div>
        <div class="card-body">
          <div class="metrics-grid">
            <div class="metric-item">
              <i class="bx bx-group"></i>
              <div class="metric-info">
                <span class="metric-label">{{ $t('campaigns.total_reach') }}</span>
                <span class="metric-value">{{ intelligence.estimated_metrics.total_reach }}</span>
              </div>
            </div>
            <div class="metric-item">
              <i class="bx bx-pulse"></i>
              <div class="metric-info">
                <span class="metric-label">{{ $t('campaigns.engagement_rate') }}</span>
                <span class="metric-value">{{ intelligence.estimated_metrics.engagement_rate }}</span>
              </div>
            </div>
            <div class="metric-item">
              <i class="bx bx-dollar"></i>
              <div class="metric-info">
                <span class="metric-label">{{ $t('campaigns.estimated_cost') }}</span>
                <span class="metric-value">{{ intelligence.estimated_metrics.estimated_cost }}</span>
              </div>
            </div>
            <div class="metric-item">
              <i class="bx bx-time"></i>
              <div class="metric-info">
                <span class="metric-label">{{ $t('campaigns.generation_time') }}</span>
                <span class="metric-value">{{ intelligence.estimated_metrics.generation_time }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="wizard-actions">
        <button class="btn btn-secondary" @click="$emit('back')">
          <i class="bx bx-arrow-back"></i>
          {{ $t('common.back') }}
        </button>
        
        <div class="primary-actions">
          <button class="btn btn-ghost" @click="regeneratePreview">
            <i class="bx bx-refresh"></i>
            {{ $t('campaigns.regenerate_preview') }}
          </button>
          
          <button class="btn btn-primary" @click="confirmGenerate">
            <i class="bx bx-check-circle"></i>
            {{ $t('campaigns.confirm_and_generate') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Initial State -->
    <div v-else class="initial-state">
      <i class="bx bx-bulb"></i>
      <h4>{{ $t('campaigns.ready_to_preview') }}</h4>
      <p>{{ $t('campaigns.preview_description') }}</p>
      <button class="btn btn-primary" @click="generatePreview">
        <i class="bx bx-sparkles"></i>
        {{ $t('campaigns.generate_preview') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCampaignStore } from '@/stores/campaign'
import TimelineVisualization from '@/components/campaigns/TimelineVisualization.vue'
import SamplePostCard from '@/components/campaigns/SamplePostCard.vue'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  wizardData: { type: Object, required: true },
  campaignUuid: { type: String, default: null }
})

const emit = defineEmits(['back', 'generate'])

const { t } = useI18n()
const campaignStore = useCampaignStore()
const { info: toastInfo, error: toastError } = useToast()

const loading = ref(false)
const intelligence = computed(() => campaignStore.intelligence)
const hasFallback = computed(() => !!campaignStore.intelligenceMeta?.fallback)

const fallbackMessage = computed(() => {
  if (!campaignStore.intelligenceMeta?.fallback) return ''
  return (
    campaignStore.intelligenceMeta?.message ||
    t('campaigns.preview_fallback_description')
  )
})

const generatePreview = async () => {
  loading.value = true
  
  try {
    if (!props.campaignUuid) {
      throw new Error('missing-campaign-uuid')
    }

    await campaignStore.generateIntelligence({
      campaign_uuid: props.campaignUuid,
      mode: props.wizardData.mode
    })
    if (campaignStore.intelligenceMeta?.fallback) {
      toastInfo(t('campaigns.preview_fallback_toast'))
    }
  } catch (error) {
    if (error.message === 'missing-campaign-uuid') {
      toastError('يرجى إكمال الخطوات السابقة قبل إنشاء المعاينة.')
    } else {
      console.error('Failed to generate intelligence:', error)
      toastError(t('campaigns.preview_error_generic'))
    }
  } finally {
    loading.value = false
  }
}

const regeneratePreview = async () => {
  await generatePreview()
}

const confirmGenerate = () => {
  emit('generate')
}

const getLanguageName = (code) => {
  const names = {
    'ar': 'العربية',
    'en': 'English',
    'fr': 'Français',
    'it': 'Italiano',
    'es': 'Español',
    'de': 'Deutsch',
    'zh': '中文'
  }
  return names[code] || code
}

onMounted(() => {
  // Auto-generate preview if wizard data is complete and campaign exists
  if (
    props.campaignUuid &&
    props.wizardData.business_type &&
    props.wizardData.product_name &&
    props.wizardData.description
  ) {
    generatePreview()
  }
})
</script>

<style scoped>
.wizard-step-preview {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.fallback-banner-body {
  display: flex;
  gap: var(--space-3);
  align-items: flex-start;
}

.fallback-banner-body i {
  font-size: var(--text-xl);
  color: var(--color-orange-text);
  margin-top: var(--space-1);
}

.fallback-title {
  margin: 0 0 var(--space-1) 0;
  font-size: var(--text-md);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
}

.fallback-message {
  margin: 0;
  color: var(--color-text-secondary);
  font-size: var(--text-sm);
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--space-10);
  gap: var(--space-4);
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid var(--color-bg-tertiary);
  border-top-color: var(--color-brand-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Initial State */
.initial-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--space-10);
  gap: var(--space-4);
  text-align: center;
}

.initial-state i {
  font-size: 64px;
  color: var(--color-brand-primary);
}

.initial-state h4 {
  font-size: var(--text-lg);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
  margin: 0;
}

.initial-state p {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  margin: 0;
  max-width: 400px;
}

/* Intelligence Preview */
.intelligence-preview {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

/* Summary Card */
.summary-card {
  background: linear-gradient(135deg, var(--color-brand-primary), var(--color-blue-text));
  color: white;
}

.summary-card .card-title {
  color: white;
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-4);
  margin-bottom: var(--space-4);
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.summary-item .label {
  font-size: var(--text-xs);
  opacity: 0.9;
}

.summary-item .value {
  font-size: var(--text-md);
  font-weight: var(--font-semibold);
}

/* KPIs */
.kpis-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-3);
}

.kpi-card {
  background: rgba(255, 255, 255, 0.15);
  border-radius: var(--radius-md);
  padding: var(--space-3);
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.kpi-card i {
  font-size: 32px;
  opacity: 0.9;
}

.kpi-info {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.kpi-label {
  font-size: var(--text-xs);
  opacity: 0.9;
}

.kpi-value {
  font-size: var(--text-md);
  font-weight: var(--font-bold);
}

/* Language Analysis */
.language-analysis-card .analysis-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-4);
}

.analysis-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.analysis-item .label {
  font-size: var(--text-xs);
  color: var(--color-text-tertiary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.analysis-item .value {
  font-size: var(--text-sm);
  color: var(--color-text-primary);
  font-weight: var(--font-medium);
}

.language-badges {
  display: flex;
  gap: var(--space-2);
  flex-wrap: wrap;
}

/* Phases Section */
.section-title {
  font-size: var(--text-lg);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
  display: flex;
  align-items: center;
  gap: var(--space-2);
  margin: 0;
}

.phases-accordion {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.phase-details {
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.phase-summary {
  padding: var(--space-4);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--color-bg-secondary);
  transition: var(--transition-fast);
  user-select: none;
}

.phase-summary:hover {
  background: var(--color-bg-hover);
}

.phase-header {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.phase-number {
  font-size: var(--text-xs);
  font-weight: var(--font-semibold);
  color: var(--color-brand-primary);
  background: var(--color-blue-bg);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
}

.phase-name {
  font-size: var(--text-md);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
}

.phase-content {
  padding: var(--space-4);
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.phase-info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-4);
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.info-item .label {
  font-size: var(--text-xs);
  color: var(--color-text-tertiary);
}

.info-item .value {
  font-size: var(--text-sm);
  color: var(--color-text-primary);
}

.phase-strategy h5,
.content-mix h5,
.key-messages h5 {
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
  margin: 0 0 var(--space-2) 0;
}

.phase-strategy p {
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  line-height: 1.6;
  margin: 0;
}

.mix-grid {
  display: flex;
  gap: var(--space-2);
  flex-wrap: wrap;
}

.mix-item {
  background: var(--color-bg-secondary);
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-xs);
}

.mix-type {
  color: var(--color-text-secondary);
}

.mix-percentage {
  color: var(--color-brand-primary);
  font-weight: var(--font-semibold);
}

.key-messages ul {
  margin: 0;
  padding-left: var(--space-5);
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  line-height: 1.8;
}

[dir="rtl"] .key-messages ul {
  padding-left: 0;
  padding-right: var(--space-5);
}

/* Samples Section */
.samples-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: var(--space-4);
}

/* Guidelines */
.guidelines-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-4);
}

.guideline-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.guideline-item.full-width {
  grid-column: 1 / -1;
}

.color-swatches {
  display: flex;
  gap: var(--space-2);
}

.color-swatch {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-md);
  border: 2px solid var(--color-border-light);
  cursor: pointer;
  transition: var(--transition-fast);
}

.color-swatch:hover {
  transform: scale(1.1);
  box-shadow: var(--shadow-md);
}

/* Metrics Card */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--space-3);
}

.metric-item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3);
  background: var(--color-bg-secondary);
  border-radius: var(--radius-md);
}

.metric-item i {
  font-size: 32px;
  color: var(--color-brand-primary);
}

.metric-info {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.metric-label {
  font-size: 11px;
  color: var(--color-text-tertiary);
}

.metric-value {
  font-size: var(--text-md);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
}

/* Actions */
.wizard-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: var(--space-4);
  border-top: 1px solid var(--color-border-light);
}

.primary-actions {
  display: flex;
  gap: var(--space-2);
}

/* Responsive */
@media (max-width: 1024px) {
  .summary-grid,
  .analysis-grid,
  .phase-info-grid,
  .guidelines-grid {
    grid-template-columns: 1fr;
  }
  
  .kpis-grid {
    grid-template-columns: 1fr;
  }
  
  .metrics-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .samples-grid {
    grid-template-columns: 1fr;
  }
  
  .wizard-actions {
    flex-direction: column;
    align-items: stretch;
    gap: var(--space-2);
  }
  
  .primary-actions {
    flex-direction: column;
  }
  
  .metrics-grid {
    grid-template-columns: 1fr;
  }
}
</style>
