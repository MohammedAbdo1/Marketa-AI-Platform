<template>
  <div class="wizard-step">
    <div class="step-header mb-4">
      <h3>{{ $t('campaigns.wizard.step2.title') }}</h3>
      <p class="text-muted">{{ $t('campaigns.wizard.step2.subtitle') }}</p>
    </div>

    <form @submit.prevent="handleNext">
      <div class="row">
        <!-- Campaign Goal -->
        <div class="col-12 mb-4">
          <label class="form-label">{{ $t('campaigns.wizard.step2.campaignGoal') }} *</label>
          <div class="goal-options">
            <div 
              v-for="goal in campaignGoals" 
              :key="goal.value"
              class="goal-option"
              :class="{ 'selected': localData.campaign_goal === goal.value }"
              @click="selectGoal(goal.value)"
            >
              <div class="goal-icon">
                <i :class="goal.icon"></i>
              </div>
              <div class="goal-content">
                <h6>{{ goal.label }}</h6>
                <p>{{ goal.description }}</p>
              </div>
            </div>
          </div>
          <div v-if="errors.campaign_goal" class="invalid-feedback">
            {{ errors.campaign_goal }}
          </div>
        </div>

        <!-- Target Audience -->
        <div class="col-12 mb-4">
          <label class="form-label">{{ $t('campaigns.wizard.step2.targetAudience') }} *</label>
          <div class="row">
            <!-- Age Range -->
            <div class="col-md-6 mb-3">
              <label class="form-label small">{{ $t('campaigns.wizard.step2.ageRange') }}</label>
              <select v-model="localData.target_audience.age_range" class="form-select">
                <option value="">{{ $t('common.selectOption') }}</option>
                <option value="18-24">18-24</option>
                <option value="25-34">25-34</option>
                <option value="35-44">35-44</option>
                <option value="45-54">45-54</option>
                <option value="55-64">55-64</option>
                <option value="65+">65+</option>
                <option value="all">All Ages</option>
              </select>
            </div>

            <!-- Gender -->
            <div class="col-md-6 mb-3">
              <label class="form-label small">{{ $t('campaigns.wizard.step2.gender') }}</label>
              <select v-model="localData.target_audience.gender" class="form-select">
                <option value="">{{ $t('common.selectOption') }}</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="all">All Genders</option>
              </select>
            </div>

            <!-- Interests -->
            <div class="col-12 mb-3">
              <label class="form-label small">{{ $t('campaigns.wizard.step2.interests') }}</label>
              <div class="interests-grid">
                <div 
                  v-for="interest in interests" 
                  :key="interest"
                  class="interest-tag"
                  :class="{ 'selected': localData.target_audience.interests?.includes(interest) }"
                  @click="toggleInterest(interest)"
                >
                  {{ interest }}
                </div>
              </div>
            </div>

            <!-- Location -->
            <div class="col-12 mb-3">
              <label class="form-label small">{{ $t('campaigns.wizard.step2.location') }}</label>
              <input 
                v-model="localData.target_audience.location"
                type="text"
                class="form-control"
                :placeholder="$t('campaigns.wizard.step2.locationPlaceholder')"
              />
            </div>
          </div>
        </div>

        <!-- Duration -->
        <div class="col-12 mb-4">
          <label class="form-label">{{ $t('campaigns.wizard.step2.duration') }} *</label>
          <div class="duration-options">
            <div 
              v-for="duration in durationOptions" 
              :key="duration.weeks"
              class="duration-option"
              :class="{ 'selected': localData.duration_weeks === duration.weeks }"
              @click="selectDuration(duration.weeks)"
            >
              <div class="duration-value">{{ duration.weeks }}</div>
              <div class="duration-label">{{ duration.label }}</div>
            </div>
          </div>
          <div v-if="errors.duration_weeks" class="invalid-feedback">
            {{ errors.duration_weeks }}
          </div>
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
          <button 
            type="submit" 
            class="btn btn-primary"
            :disabled="!isValid"
          >
            {{ $t('common.next') }} <i class="bx bx-right-arrow-alt ms-1"></i>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  wizardData: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:wizardData', 'next', 'back'])

// Local data
const localData = ref({ 
  ...props.wizardData,
  target_audience: {
    age_range: '',
    gender: '',
    interests: [],
    location: '',
    ...props.wizardData.target_audience
  }
})
const errors = ref({})

// Campaign goals
const campaignGoals = [
  {
    value: 'awareness',
    label: 'Brand Awareness',
    description: 'Increase brand recognition and visibility',
    icon: 'bx bx-trending-up'
  },
  {
    value: 'sales',
    label: 'Sales & Revenue',
    description: 'Drive direct sales and conversions',
    icon: 'bx bx-cart'
  },
  {
    value: 'engagement',
    label: 'Engagement',
    description: 'Increase likes, comments, and shares',
    icon: 'bx bx-heart'
  },
  {
    value: 'traffic',
    label: 'Website Traffic',
    description: 'Drive traffic to your website',
    icon: 'bx bx-link'
  },
  {
    value: 'leads',
    label: 'Lead Generation',
    description: 'Capture leads and contact information',
    icon: 'bx bx-user-plus'
  }
]

// Interests
const interests = [
  'Technology', 'Fashion', 'Food', 'Travel', 'Fitness', 'Music',
  'Movies', 'Sports', 'Art', 'Books', 'Gaming', 'Photography',
  'Cooking', 'Beauty', 'Health', 'Business', 'Education'
]

// Duration options
const durationOptions = [
  { weeks: 1, label: '1 Week' },
  { weeks: 2, label: '2 Weeks' },
  { weeks: 4, label: '1 Month' },
  { weeks: 8, label: '2 Months' },
  { weeks: 12, label: '3 Months' }
]

// Computed
const isValid = computed(() => {
  return localData.value.campaign_goal && 
         localData.value.duration_weeks
})

// Watch for changes
watch(localData, (newData) => {
  emit('update:wizardData', newData)
}, { deep: true })

// Methods
const selectGoal = (goal) => {
  localData.value.campaign_goal = goal
  errors.value.campaign_goal = null
}

const selectDuration = (weeks) => {
  localData.value.duration_weeks = weeks
  errors.value.duration_weeks = null
}

const toggleInterest = (interest) => {
  if (!localData.value.target_audience.interests) {
    localData.value.target_audience.interests = []
  }
  
  const index = localData.value.target_audience.interests.indexOf(interest)
  if (index > -1) {
    localData.value.target_audience.interests.splice(index, 1)
  } else {
    localData.value.target_audience.interests.push(interest)
  }
}

const validateForm = () => {
  errors.value = {}
  
  if (!localData.value.campaign_goal) {
    errors.value.campaign_goal = 'Campaign goal is required'
  }
  
  if (!localData.value.duration_weeks) {
    errors.value.duration_weeks = 'Duration is required'
  }
  
  return Object.keys(errors.value).length === 0
}

const handleNext = () => {
  if (validateForm()) {
    emit('next')
  }
}

const handleBack = () => {
  emit('back')
}
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

.goal-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.goal-option {
  border: 2px solid #e9ecef;
  border-radius: 12px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.goal-option:hover {
  border-color: #007bff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
}

.goal-option.selected {
  border-color: #007bff;
  background: linear-gradient(135deg, #f8f9ff, #e3f2fd);
}

.goal-icon {
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

.goal-content h6 {
  margin: 0 0 0.25rem 0;
  font-weight: 600;
  color: #2c3e50;
}

.goal-content p {
  margin: 0;
  font-size: 0.875rem;
  color: #6c757d;
}

.duration-options {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.duration-option {
  border: 2px solid #e9ecef;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
  min-width: 100px;
}

.duration-option:hover {
  border-color: #007bff;
  transform: translateY(-2px);
}

.duration-option.selected {
  border-color: #007bff;
  background: linear-gradient(135deg, #f8f9ff, #e3f2fd);
}

.duration-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #007bff;
  margin-bottom: 0.25rem;
}

.duration-label {
  font-size: 0.875rem;
  color: #6c757d;
}

.interests-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.interest-tag {
  padding: 0.5rem 1rem;
  border: 1px solid #dee2e6;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.875rem;
  background: white;
}

.interest-tag:hover {
  border-color: #007bff;
  color: #007bff;
}

.interest-tag.selected {
  background: #007bff;
  border-color: #007bff;
  color: white;
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

.btn-outline-secondary {
  border-color: #6c757d;
  color: #6c757d;
}

.btn-outline-secondary:hover {
  background: #6c757d;
  border-color: #6c757d;
}

@media (max-width: 768px) {
  .goal-options {
    grid-template-columns: 1fr;
  }
  
  .duration-options {
    justify-content: center;
  }
  
  .btn {
    width: 100%;
  }
}
</style>
