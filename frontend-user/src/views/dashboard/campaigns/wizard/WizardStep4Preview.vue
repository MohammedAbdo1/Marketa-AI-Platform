<template>
  <div class="wizard-step">
    <div class="step-header mb-4">
      <h3>{{ $t('campaigns.wizard.step4.title') }}</h3>
      <p class="text-muted">{{ $t('campaigns.wizard.step4.subtitle') }}</p>
    </div>

    <!-- Preview Content -->
    <div v-if="preview" class="preview-content">
      <!-- Campaign Overview -->
      <div class="campaign-overview mb-4">
        <div class="row">
          <div class="col-md-6">
            <div class="overview-card">
              <div class="card-icon">
                <i class="bx bx-target-lock"></i>
              </div>
              <div class="card-content">
                <h6>{{ $t('campaigns.wizard.step4.campaignGoal') }}</h6>
                <p>{{ getGoalLabel(wizardData.campaign_goal) }}</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="overview-card">
              <div class="card-icon">
                <i class="bx bx-calendar"></i>
              </div>
              <div class="card-content">
                <h6>{{ $t('campaigns.wizard.step4.duration') }}</h6>
                <p>{{ wizardData.duration_weeks }} {{ $t('common.weeks') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Campaign Structure -->
      <div class="campaign-structure mb-4">
        <h5 class="mb-3">{{ $t('campaigns.wizard.step4.campaignStructure') }}</h5>
        
        <div class="structure-grid">
          <div class="structure-item">
            <div class="structure-value">{{ preview.total_posts }}</div>
            <div class="structure-label">{{ $t('campaigns.wizard.step4.totalPosts') }}</div>
          </div>
          <div class="structure-item">
            <div class="structure-value">{{ preview.weekly_distribution ? Object.keys(preview.weekly_distribution).length : 0 }}</div>
            <div class="structure-label">{{ $t('campaigns.wizard.step4.weeks') }}</div>
          </div>
          <div class="structure-item">
            <div class="structure-value">{{ wizardData.posts_per_week }}</div>
            <div class="structure-label">{{ $t('campaigns.wizard.step4.postsPerWeek') }}</div>
          </div>
          <div class="structure-item">
            <div class="structure-value">{{ wizardData.platforms?.length || 0 }}</div>
            <div class="structure-label">{{ $t('campaigns.wizard.step4.platforms') }}</div>
          </div>
        </div>
      </div>

      <!-- Platform Breakdown -->
      <div v-if="preview.platforms_breakdown" class="platform-breakdown mb-4">
        <h5 class="mb-3">{{ $t('campaigns.wizard.step4.platformBreakdown') }}</h5>
        <div class="platform-stats">
          <div 
            v-for="(count, platform) in preview.platforms_breakdown" 
            :key="platform"
            class="platform-stat"
          >
            <div class="platform-name">
              <i :class="getPlatformIcon(platform)"></i>
              {{ getPlatformLabel(platform) }}
            </div>
            <div class="platform-count">{{ count }} {{ $t('common.posts') }}</div>
          </div>
        </div>
      </div>

      <!-- Content Themes -->
      <div v-if="preview.content_themes?.length" class="content-themes mb-4">
        <h5 class="mb-3">{{ $t('campaigns.wizard.step4.contentThemes') }}</h5>
        <div class="themes-list">
          <span 
            v-for="theme in preview.content_themes" 
            :key="theme"
            class="theme-tag"
          >
            {{ theme }}
          </span>
        </div>
      </div>

      <!-- Suggested Topics -->
      <div v-if="preview.suggested_topics?.length" class="suggested-topics mb-4">
        <h5 class="mb-3">{{ $t('campaigns.wizard.step4.suggestedTopics') }}</h5>
        <div class="topics-list">
          <div 
            v-for="(topic, index) in preview.suggested_topics.slice(0, 5)" 
            :key="index"
            class="topic-item"
          >
            <i class="bx bx-check-circle text-success me-2"></i>
            {{ topic }}
          </div>
        </div>
      </div>

      <!-- Weekly Distribution -->
      <div v-if="preview.weekly_distribution" class="weekly-distribution mb-4">
        <h5 class="mb-3">{{ $t('campaigns.wizard.step4.weeklyDistribution') }}</h5>
        <div class="distribution-chart">
          <div 
            v-for="(count, week) in preview.weekly_distribution" 
            :key="week"
            class="week-bar"
          >
            <div class="week-label">{{ week }}</div>
            <div class="week-progress">
              <div 
                class="week-fill" 
                :style="{ width: getWeekPercentage(count) + '%' }"
              ></div>
            </div>
            <div class="week-count">{{ count }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="loading-state">
      <div class="text-center">
        <div class="spinner-border text-primary mb-3"></div>
        <h5>{{ $t('campaigns.wizard.step4.generatingPreview') }}</h5>
        <p class="text-muted">{{ $t('campaigns.wizard.step4.generatingDescription') }}</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="error-state">
      <div class="text-center">
        <i class="bx bx-error-circle text-danger mb-3" style="font-size: 3rem;"></i>
        <h5>{{ $t('campaigns.wizard.step4.previewError') }}</h5>
        <p class="text-muted">{{ $t('campaigns.wizard.step4.previewErrorDesc') }}</p>
        <button class="btn btn-outline-primary" @click="generatePreview">
          <i class="bx bx-refresh me-1"></i> {{ $t('common.tryAgain') }}
        </button>
      </div>
    </div>

    <!-- Step Actions -->
    <div class="step-actions mt-4">
      <div class="d-flex justify-content-between">
        <button 
          type="button" 
          class="btn btn-outline-secondary"
          @click="handleBack"
        >
          <i class="bx bx-left-arrow-alt me-1"></i> {{ $t('common.back') }}
        </button>
        <div>
          <button 
            v-if="!preview"
            type="button" 
            class="btn btn-outline-primary me-2"
            @click="generatePreview"
            :disabled="loading"
          >
            <i class="bx bx-refresh me-1"></i> {{ $t('campaigns.wizard.step4.generatePreview') }}
          </button>
          <button 
            v-if="preview"
            type="button" 
            class="btn btn-primary"
            @click="handleGenerate"
            :disabled="loading"
          >
            <i class="bx bx-rocket me-1"></i> {{ $t('campaigns.wizard.step4.generateCampaign') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useCampaignStore } from '@/stores/campaign'
import { useToast } from 'vue-toastification'

const props = defineProps({
  wizardData: {
    type: Object,
    required: true
  },
  preview: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:wizardData', 'generate', 'back'])
const campaignStore = useCampaignStore()
const toast = useToast()

// Local data
const localData = ref({ ...props.wizardData })

// Watch for changes
watch(localData, (newData) => {
  emit('update:wizardData', newData)
}, { deep: true })

// Methods
const getGoalLabel = (goal) => {
  const goals = {
    awareness: 'Brand Awareness',
    sales: 'Sales & Revenue',
    engagement: 'Engagement',
    traffic: 'Website Traffic',
    leads: 'Lead Generation'
  }
  return goals[goal] || goal
}

const getPlatformLabel = (platform) => {
  const platforms = {
    instagram: 'Instagram',
    facebook: 'Facebook',
    twitter: 'Twitter',
    linkedin: 'LinkedIn'
  }
  return platforms[platform] || platform
}

const getPlatformIcon = (platform) => {
  const icons = {
    instagram: 'bx bxl-instagram',
    facebook: 'bx bxl-facebook',
    twitter: 'bx bxl-twitter',
    linkedin: 'bx bxl-linkedin'
  }
  return icons[platform] || 'bx bx-globe'
}

const getWeekPercentage = (count) => {
  if (!props.preview?.total_posts) return 0
  return (count / props.preview.total_posts) * 100
}

const generatePreview = async () => {
  try {
    // Prepare data for preview generation
    const previewData = {
      business_type: props.wizardData.business_type,
      product_name: props.wizardData.product_name,
      description: props.wizardData.description,
      campaign_goal: props.wizardData.campaign_goal,
      target_audience: props.wizardData.target_audience,
      platforms: props.wizardData.platforms,
      duration_weeks: props.wizardData.duration_weeks,
      posts_per_week: props.wizardData.posts_per_week,
      brand_colors: props.wizardData.brand_colors,
      brand_voice: props.wizardData.brand_voice,
      mode: props.wizardData.mode || 'quick'
    }
    
    await campaignStore.generatePreview(previewData)
    toast.success('Preview generated successfully!')
  } catch (error) {
    toast.error(error.message || 'Failed to generate preview')
  }
}

const handleGenerate = () => {
  emit('generate')
}

const handleBack = () => {
  emit('back')
}

// Lifecycle
onMounted(() => {
  // Auto-generate preview when component mounts
  if (!props.preview && !props.loading) {
    generatePreview()
  }
})
</script>

<style scoped>
.wizard-step {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.step-header {
  text-align: center;
  margin-bottom: 2rem;
}

.preview-content {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
}

.overview-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #007bff, #0056b3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
}

.card-content h6 {
  margin: 0 0 0.25rem 0;
  font-weight: 600;
  color: #2c3e50;
}

.card-content p {
  margin: 0;
  color: #6c757d;
  font-size: 0.875rem;
}

.structure-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.structure-item {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.structure-value {
  font-size: 2rem;
  font-weight: 700;
  color: #007bff;
  margin-bottom: 0.5rem;
}

.structure-label {
  font-size: 0.875rem;
  color: #6c757d;
  font-weight: 500;
}

.platform-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.platform-stat {
  background: white;
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.platform-name {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
}

.platform-name i {
  font-size: 1.25rem;
  color: #007bff;
}

.platform-count {
  font-weight: 600;
  color: #007bff;
}

.themes-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.theme-tag {
  background: #007bff;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
}

.topics-list {
  display: grid;
  gap: 0.5rem;
}

.topic-item {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.distribution-chart {
  display: grid;
  gap: 1rem;
}

.week-bar {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.week-label {
  min-width: 80px;
  font-weight: 500;
  color: #495057;
}

.week-progress {
  flex: 1;
  height: 20px;
  background: #e9ecef;
  border-radius: 10px;
  overflow: hidden;
  position: relative;
}

.week-fill {
  height: 100%;
  background: linear-gradient(90deg, #007bff, #0056b3);
  border-radius: 10px;
  transition: width 0.3s ease;
}

.week-count {
  min-width: 40px;
  text-align: center;
  font-weight: 600;
  color: #007bff;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 3rem 1rem;
}

.step-actions {
  border-top: 1px solid #e9ecef;
  padding-top: 1.5rem;
}

.btn {
  border-radius: 8px;
  padding: 0.75rem 2rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #007bff, #0056b3);
  border: none;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #0056b3, #004085);
  transform: translateY(-1px);
}

.btn-outline-primary {
  border-color: #007bff;
  color: #007bff;
}

.btn-outline-primary:hover {
  background: #007bff;
  border-color: #007bff;
}

.btn-outline-secondary {
  border-color: #6c757d;
  color: #6c757d;
}

.btn-outline-secondary:hover {
  background: #6c757d;
  border-color: #6c757d;
}

@media (max-width: 768px) {
  .preview-content {
    padding: 1rem;
  }
  
  .structure-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .platform-stats {
    grid-template-columns: 1fr;
  }
  
  .btn {
    width: 100%;
  }
}
</style>
