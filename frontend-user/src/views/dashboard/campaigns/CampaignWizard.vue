<template>
  <div class="campaign-wizard">
    <!-- Header -->
    <div class="wizard-header mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="mb-1">{{ $t('campaigns.wizard.title') }}</h2>
          <p class="text-muted mb-0">{{ $t('campaigns.wizard.subtitle') }}</p>
        </div>
        <button 
          class="btn btn-outline-secondary"
          @click="$router.push('/dashboard/campaigns')"
        >
          <i class="bx bx-x"></i> {{ $t('common.cancel') }}
        </button>
      </div>
    </div>

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
        @generate="generateCampaign"
        @back="previousStep"
      />
    </div>

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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCampaignStore } from '@/stores/campaign'
import { useBrandStore } from '@/stores/brand'
import { useToast } from 'vue-toastification'
import WizardStep1Business from './wizard/WizardStep1Business.vue'
import WizardStep2Goal from './wizard/WizardStep2Goal.vue'
import WizardStep3Brand from './wizard/WizardStep3Brand.vue'
import WizardStep4Preview from './wizard/WizardStep4Preview.vue'

const router = useRouter()
const campaignStore = useCampaignStore()
const brandStore = useBrandStore()
const toast = useToast()

// Wizard state
const currentStep = ref(1)
const totalSteps = 4
const wizardData = ref({
  // Step 1: Business Basics
  business_type: '',
  product_name: '',
  description: '',
  
  // Step 2: Campaign Goal
  campaign_goal: '',
  target_audience: {},
  duration_weeks: 4,
  
  // Step 3: Brand & Preferences
  brand_id: null,
  brand_colors: null,
  brand_voice: null,
  platforms: [],
  posts_per_week: 3,
  languages: ['ar', 'en'],
  
  // Step 4: Preview
  mode: 'quick'
})

const loading = ref(false)
const generationProgress = ref(0)
const generationInterval = ref(null)

// Computed
const progressPercentage = computed(() => {
  return (currentStep.value / totalSteps) * 100
})

// Methods
const nextStep = () => {
  if (currentStep.value < totalSteps) {
    currentStep.value++
  }
}

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const generateCampaign = async () => {
  try {
    loading.value = true
    
    // Initialize Socket.IO connection
    campaignStore.initializeSocket()
    
    // Create campaign first
    const campaign = await campaignStore.createCampaign({
      name: wizardData.value.product_name,
      business_type: wizardData.value.business_type,
      description: wizardData.value.description,
      goal: wizardData.value.campaign_goal,
      target_audience: wizardData.value.target_audience,
      platforms: wizardData.value.platforms,
      duration_days: wizardData.value.duration_weeks * 7,
      posts_per_week: wizardData.value.posts_per_week,
      brand_id: wizardData.value.brand_id,
      mode: wizardData.value.mode,
      languages: wizardData.value.languages
    })

    // Guard: ensure we have a valid uuid
    const createdUuid = campaign?.uuid
    if (!createdUuid) {
      throw new Error('Failed to determine created campaign uuid')
    }

    // Start generation
    const response = await campaignStore.generateCampaign(createdUuid)
    
    // If completed immediately (simple mode), navigate without polling
    if (response?.status === 'completed') {
      loading.value = false
      toast.success('Campaign generated successfully!')
      router.push(`/dashboard/campaigns/${createdUuid}`)
      return
    }

    // Fallback: quick status check in case backend completed but response lacked status
    try {
      const quickStatus = await campaignStore.fetchGenerationStatus(createdUuid, 1)
      if (quickStatus?.status === 'completed') {
        loading.value = false
        toast.success('Campaign generated successfully!')
        router.push(`/dashboard/campaigns/${createdUuid}`)
        return
      }
    } catch (_) {
      // ignore and proceed to polling
    }

    // Set task ID for Socket.IO tracking
    if (response?.task_id) {
      campaignStore.setCurrentTaskId(response.task_id)
    }
    
    // Start polling for progress (fallback)
    startProgressPolling(createdUuid)
    
    toast.success('Campaign generation started!')
    
  } catch (error) {
    toast.error(error?.response?.data?.message || error.message || 'Failed to generate campaign')
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
  // Load brands for step 3
  await brandStore.fetchBrands()
})

onUnmounted(() => {
  if (generationInterval.value) {
    clearInterval(generationInterval.value)
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