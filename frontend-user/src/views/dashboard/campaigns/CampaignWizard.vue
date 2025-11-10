<template>
  <div class="campaign-wizard">
    <!-- Draft Resume Dialog -->
    <DraftResumeDialog
      :show="showDraftDialog"
      :drafts="campaignStore.drafts"
      @close="showDraftDialog = false"
      @resume="resumeDraft"
      @discard="discardDraft"
      @startNew="startNewCampaign"
    />

    <!-- Header -->
    <div class="wizard-header mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="mb-1">{{ $t('campaigns.wizard.title') }}</h2>
          <p class="text-muted mb-0">{{ $t('campaigns.wizard.subtitle') }}</p>
        </div>
        <button 
          class="btn btn-outline-secondary"
          @click="handleCancel"
        >
          <i class="bx bx-x"></i> {{ $t('common.cancel') }}
        </button>
      </div>
    </div>

    <div v-if="isInitializing" class="wizard-loading">
      <div class="spinner-border text-primary me-2" role="status"></div>
      <span>جارٍ تحميل بيانات الحملة...</span>
    </div>
    <template v-else>
      <!-- Progress Bar -->
      <div class="wizard-progress mb-4">
        <div class="progress">
          <div 
            class="progress-bar" 
            :style="{ width: progressPercentage + '%' }"
          ></div>
        </div>
        <div class="d-flex justify-content-between mt-2">
          <span class="text-muted">{{ $t('campaigns.wizard.step') }} {{ currentStep }} {{ $t('common.of') }} {{ totalSteps }}</span>
          <span class="text-muted">{{ Math.round(progressPercentage) }}%</span>
        </div>
      </div>

      <!-- Wizard Steps -->
      <div class="wizard-content">
        <!-- Step 1: Business Basics -->
        <WizardStep1Business 
          v-if="currentStep === 1"
          v-model:wizardData="wizardData"
          @next="nextStep"
          @back="previousStep"
        />

        <!-- Step 2: Campaign Goal -->
        <WizardStep2Goal 
          v-if="currentStep === 2"
          v-model:wizardData="wizardData"
          @next="nextStep"
          @back="previousStep"
        />

        <!-- Step 3: Brand & Preferences -->
        <WizardStep3Brand 
          v-if="currentStep === 3"
          v-model:wizardData="wizardData"
          @next="nextStep"
          @back="previousStep"
        />

        <!-- Step 4: Preview & Generate -->
        <WizardStep4Preview 
          v-if="currentStep === 4"
          v-model:wizardData="wizardData"
          :preview="campaignStore.preview"
          :loading="campaignStore.loading"
          :campaign-uuid="currentCampaignUuid"
          @generate="generateCampaign"
          @back="previousStep"
        />
      </div>
    </template>

    <!-- Loading Overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner-border text-primary mb-3"></div>
        <h5>{{ $t('campaigns.wizard.generating') }}</h5>
        <p class="text-muted">{{ $t('campaigns.wizard.generatingDescription') }}</p>
        <div class="progress mt-3" style="width: 300px;">
          <div class="progress-bar progress-bar-striped progress-bar-animated" 
               :style="{ width: generationProgress + '%' }"></div>
        </div>
        <small class="text-muted mt-2">{{ generationProgress }}% {{ $t('common.complete') }}</small>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCampaignStore } from '@/stores/campaign'
import { useBrandStore } from '@/stores/brand'
import { useToast } from 'vue-toastification'
import WizardStep1Business from './wizard/WizardStep1Business.vue'
import WizardStep2Goal from './wizard/WizardStep2Goal.vue'
import WizardStep3Brand from './wizard/WizardStep3Brand.vue'
import WizardStep4Preview from './wizard/WizardStep4Preview.vue'
import DraftResumeDialog from '@/components/campaigns/DraftResumeDialog.vue'

const router = useRouter()
const route = useRoute()
const campaignStore = useCampaignStore()
const brandStore = useBrandStore()
const toast = useToast()

// Draft Management
const showDraftDialog = ref(false)
const currentCampaignUuid = ref(null)
const hasChanges = ref(false)
const autoSaveInterval = ref(null)

// Wizard state
const currentStep = ref(1)
const totalSteps = 4
const defaultWizardState = () => ({
  business_type: '',
  product_name: '',
  description: '',
  campaign_goal: '',
  target_audience: {},
  duration_weeks: 4,
  platforms: [],
  posts_per_week: 3,
  brand_id: null,
  brand_colors: null,
  brand_voice: null,
  mode: 'quick'
})
const wizardData = ref(defaultWizardState())

const loading = ref(false)
const isInitializing = ref(true)
const generationProgress = ref(0)
const generationInterval = ref(null)

// Computed
const progressPercentage = computed(() => {
  if (isInitializing.value || !currentStep.value) {
    return 0
  }
  return (currentStep.value / totalSteps) * 100
})

const clampStep = (step) => {
  const parsed = Number(step)
  if (!Number.isFinite(parsed)) {
    return 1
  }
  const rounded = Math.round(parsed)
  return Math.min(Math.max(rounded || 1, 1), totalSteps)
}

// Helpers
const buildWizardSnapshot = () => ({
  step1: {
    business_type: wizardData.value.business_type,
    product_name: wizardData.value.product_name,
    description: wizardData.value.description
  },
  step2: {
    campaign_goal: wizardData.value.campaign_goal,
    target_audience: wizardData.value.target_audience,
    duration_weeks: Number(wizardData.value.duration_weeks) || defaultWizardState().duration_weeks,
    platforms: wizardData.value.platforms,
    posts_per_week: Number(wizardData.value.posts_per_week) || defaultWizardState().posts_per_week
  },
  step3: {
    brand_id: wizardData.value.brand_id,
    brand_colors: wizardData.value.brand_colors,
    brand_voice: wizardData.value.brand_voice
  },
  step4: {
    mode: wizardData.value.mode
  }
})

const buildUpdatePayload = (step) => {
  const payload = {
    wizard_step: step,
    wizard_data: buildWizardSnapshot(),
    name: wizardData.value.product_name,
    business_type: wizardData.value.business_type,
    description: wizardData.value.description,
    goal: wizardData.value.campaign_goal || 'awareness',
    target_audience: wizardData.value.target_audience,
    platforms: wizardData.value.platforms,
    duration_weeks: Number(wizardData.value.duration_weeks) || defaultWizardState().duration_weeks,
    posts_per_week: Number(wizardData.value.posts_per_week) || defaultWizardState().posts_per_week,
    mode: wizardData.value.mode
  }

  if (wizardData.value.brand_id) {
    payload.brand_id = wizardData.value.brand_id
  }

  return payload
}

const ensureCampaignCreated = async () => {
  if (currentCampaignUuid.value) {
    return currentCampaignUuid.value
  }

  if (!wizardData.value.business_type || !wizardData.value.product_name || !wizardData.value.description) {
    toast.error('يرجى تعبئة بيانات الخطوة الأولى قبل المتابعة.')
    return null
  }

  try {
    const campaign = await campaignStore.createCampaign({
      name: wizardData.value.product_name,
      business_type: wizardData.value.business_type,
      description: wizardData.value.description,
      goal: wizardData.value.campaign_goal || 'awareness',
      wizard_step: 1,
      wizard_data: buildWizardSnapshot(),
      mode: wizardData.value.mode
    })
    currentCampaignUuid.value = campaign.uuid
    hasChanges.value = false
    return currentCampaignUuid.value
  } catch (error) {
    console.error('Failed to create campaign draft:', error)
    toast.error('تعذّر إنشاء الحملة، حاول مرة أخرى.')
    return null
  }
}

const saveWizardProgress = async (step = currentStep.value) => {
  if (!currentCampaignUuid.value) return true

  try {
    await campaignStore.updateCampaign(currentCampaignUuid.value, buildUpdatePayload(step))
    hasChanges.value = false
    return true
  } catch (error) {
    console.error('Failed to save progress:', error)
    const message = error?.response?.data?.message || 'فشل حفظ تقدم الحملة'
    toast.error(message)
    return false
  }
}

// Navigation
const nextStep = async () => {
  let saveResult = true
  if (currentStep.value === 1) {
    const uuid = await ensureCampaignCreated()
    if (!uuid) return
    saveResult = await saveWizardProgress(1)
  } else {
    saveResult = await saveWizardProgress(currentStep.value)
  }

  if (!saveResult) {
    return
  }

  if (currentStep.value < totalSteps) {
    currentStep.value++
  }
}

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

// Draft Management Methods
const loadCampaignIntoWizard = (campaign, { toastMessage = null, stepOverride = null } = {}) => {
  if (!campaign) return

  currentCampaignUuid.value = campaign.uuid

  const snapshot = campaign.wizard_data || {}
  const defaults = defaultWizardState()

  wizardData.value = {
    ...defaults,
    ...snapshot.step1,
    ...snapshot.step2,
    ...snapshot.step3,
    ...snapshot.step4
  }

  wizardData.value.business_type = snapshot.step1?.business_type ?? campaign.business_type ?? defaults.business_type
  wizardData.value.product_name = snapshot.step1?.product_name ?? campaign.name ?? defaults.product_name
  wizardData.value.description = snapshot.step1?.description ?? campaign.description ?? defaults.description
  wizardData.value.campaign_goal = snapshot.step2?.campaign_goal ?? campaign.goal ?? defaults.campaign_goal
  wizardData.value.target_audience = snapshot.step2?.target_audience ?? campaign.target_audience ?? defaults.target_audience
  wizardData.value.duration_weeks = snapshot.step2?.duration_weeks
    ?? (campaign.duration_days ? Math.max(1, Math.round(campaign.duration_days / 7)) : defaults.duration_weeks)
  wizardData.value.duration_weeks = Number(wizardData.value.duration_weeks) || defaults.duration_weeks
  wizardData.value.platforms = snapshot.step2?.platforms ?? campaign.platforms ?? defaults.platforms
  wizardData.value.posts_per_week = snapshot.step2?.posts_per_week ?? campaign.posts_per_week ?? defaults.posts_per_week
  wizardData.value.posts_per_week = Number(wizardData.value.posts_per_week) || defaults.posts_per_week
  wizardData.value.brand_id = snapshot.step3?.brand_id ?? campaign.brand_id ?? defaults.brand_id
  wizardData.value.brand_colors = snapshot.step3?.brand_colors ?? defaults.brand_colors
  wizardData.value.brand_voice = snapshot.step3?.brand_voice ?? campaign.brand_voice ?? campaign.tone_of_voice ?? defaults.brand_voice
  wizardData.value.mode = snapshot.step4?.mode ?? campaign.mode ?? defaults.mode

  currentStep.value = clampStep(stepOverride ?? campaign.wizard_step ?? 1)
  showDraftDialog.value = false
  hasChanges.value = false

  isInitializing.value = false

  if (toastMessage) {
    toast.success(toastMessage)
  }
}

const resumeDraft = (draft, { silent = false } = {}) => {
  loadCampaignIntoWizard(draft, { toastMessage: silent ? null : 'تم استئناف الحملة' })
}

const loadCampaignByUuid = async (uuid, step) => {
  isInitializing.value = true
  try {
    const campaign = await campaignStore.fetchCampaign(uuid)
    loadCampaignIntoWizard(campaign, {
      toastMessage: null,
      stepOverride: step !== undefined ? clampStep(step) : null
    })
  } catch (error) {
    console.error('Failed to load campaign by uuid:', error)
    toast.error('تعذّر تحميل الحملة المحددة')
    if (campaignStore.drafts.length > 0) {
      showDraftDialog.value = true
    } else {
      startNewCampaign()
    }
    isInitializing.value = false
  }
}

const initializeWizard = async () => {
  isInitializing.value = true
  await campaignStore.fetchDrafts()

  const routeCampaignUuid = route.query.campaign
  const routeStep = route.query.step

  if (routeCampaignUuid) {
    await loadCampaignByUuid(routeCampaignUuid, routeStep)
  } else if (!currentCampaignUuid.value && campaignStore.drafts.length > 0) {
    showDraftDialog.value = true
    isInitializing.value = false
  } else {
    startNewCampaign()
  }
}

const discardDraft = async (draft) => {
  try {
    await campaignStore.deleteCampaign(draft.uuid)
    campaignStore.drafts = campaignStore.drafts.filter(item => item.uuid !== draft.uuid)

    if (currentCampaignUuid.value === draft.uuid) {
      resetWizardState()
    }

    toast.success('تم حذف المسودة')
  } catch (error) {
    toast.error('فشل حذف المسودة')
  }
}

const resetWizardState = () => {
  wizardData.value = defaultWizardState()
  currentCampaignUuid.value = null
  currentStep.value = 1
  hasChanges.value = false
}

const startNewCampaign = () => {
  resetWizardState()
  showDraftDialog.value = false
  isInitializing.value = false
}

const handleCancel = async () => {
  if (hasChanges.value && currentCampaignUuid.value) {
    await saveWizardProgress(currentStep.value)
  }
  router.push('/dashboard/campaigns')
}

// Watch for changes
watch(wizardData, () => {
  hasChanges.value = true
}, { deep: true })

watch(
  () => [route.query.campaign, route.query.step],
  async ([campaignUuid, step], [prevCampaignUuid, prevStep]) => {
    if (campaignUuid === prevCampaignUuid && step === prevStep) {
      return
    }

    if (campaignUuid) {
      isInitializing.value = true
      if (campaignUuid === currentCampaignUuid.value) {
        if (step !== undefined) {
          currentStep.value = clampStep(step)
        }
        isInitializing.value = false
        return
      }
      await loadCampaignByUuid(campaignUuid, step)
    } else if (!currentCampaignUuid.value) {
      if (campaignStore.drafts.length > 0) {
        showDraftDialog.value = true
      } else {
        startNewCampaign()
      }
      isInitializing.value = false
    }
  }
)

const generateCampaign = async () => {
  try {
    loading.value = true

    campaignStore.initializeSocket()

    const uuid = await ensureCampaignCreated()
    if (!uuid) {
      loading.value = false
      return
    }

  const saved = await saveWizardProgress(totalSteps)
  if (!saved) {
    loading.value = false
    return
  }

    const response = await campaignStore.generateCampaign(uuid)

    if (response?.status === 'completed') {
      loading.value = false
      toast.success('تم إنشاء الحملة بنجاح!')
      router.push(`/dashboard/campaigns/${uuid}`)
      return
    }

    try {
      const quickStatus = await campaignStore.fetchGenerationStatus(uuid, 1)
      if (quickStatus?.status === 'completed') {
        loading.value = false
        toast.success('تم إنشاء الحملة بنجاح!')
        router.push(`/dashboard/campaigns/${uuid}`)
        return
      }
    } catch (error) {
      console.debug('Quick status check failed, continuing with polling.', error)
    }

    if (response?.task_id) {
      campaignStore.setCurrentTaskId(response.task_id)
    }

    startProgressPolling(uuid)
    toast.success('بدأ إنشاء الحملة!')
  } catch (error) {
    toast.error(error?.response?.data?.message || error.message || 'فشل إنشاء الحملة')
    loading.value = false
  }
}

const startProgressPolling = (campaignUuid) => {
  let pollCount = 0
  const maxPolls = 120 // Up to ~4 minutes to accommodate slower AI
  
  generationInterval.value = setInterval(async () => {
    pollCount++
    
    if (pollCount > maxPolls) {
      clearInterval(generationInterval.value)
      loading.value = false
      toast.error('Campaign generation timeout')
      return
    }
    
    try {
      const status = await campaignStore.fetchGenerationStatus(campaignUuid, 1) // Only 1 retry per poll
      if (status) {
        generationProgress.value = status.progress
        
        if (status.status === 'completed') {
          clearInterval(generationInterval.value)
          loading.value = false
          toast.success('Campaign generated successfully!')
          router.push(`/dashboard/campaigns/${campaignUuid}`)
        } else if (status.status === 'failed') {
          clearInterval(generationInterval.value)
          loading.value = false
          toast.error('Campaign generation failed')
        }
      }
    } catch (error) {
      console.error('Failed to fetch generation status:', error)
      // Don't stop polling on individual errors, let maxPolls handle it
    }
  }, 2000)
}

// Lifecycle
onMounted(async () => {
  await initializeWizard()

  // Load brands for step 3
  await brandStore.fetchBrands()
  
  // Setup auto-save interval (every 30 seconds)
  autoSaveInterval.value = setInterval(async () => {
    if (hasChanges.value && currentCampaignUuid.value) {
      await saveWizardProgress(currentStep.value)
    }
  }, 30000)
})

onUnmounted(() => {
  // Clear intervals
  if (generationInterval.value) {
    clearInterval(generationInterval.value)
  }
  if (autoSaveInterval.value) {
    clearInterval(autoSaveInterval.value)
  }
  
  // Save before leaving
  if (hasChanges.value && currentCampaignUuid.value) {
    saveWizardProgress(currentStep.value)
  }
  
  // Disconnect Socket.IO when component is unmounted
  campaignStore.disconnectSocket()
})
</script>

<style scoped>
.campaign-wizard {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
}

.wizard-header {
  border-bottom: 1px solid #e9ecef;
  padding-bottom: 1rem;
}

.wizard-progress {
  margin-bottom: 2rem;
}

.wizard-content {
  min-height: 400px;
}

.wizard-loading {
  min-height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  color: var(--color-text-secondary);
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-content {
  background: white;
  padding: 3rem;
  border-radius: 16px;
  text-align: center;
  max-width: 400px;
  width: 90%;
}

.progress {
  height: 8px;
  border-radius: 4px;
  background-color: #f8f9fa;
}

.progress-bar {
  background: linear-gradient(45deg, #007bff, #0056b3);
  border-radius: 4px;
  transition: width 0.3s ease;
}

@media (max-width: 768px) {
  .campaign-wizard {
    padding: 1rem;
  }
  
  .loading-content {
    padding: 2rem;
    margin: 1rem;
  }
}
</style>
