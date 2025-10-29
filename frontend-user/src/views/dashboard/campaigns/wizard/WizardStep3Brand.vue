<template>
  <div class="wizard-step">
    <div class="step-header mb-4">
      <h3>{{ $t('campaigns.wizard.step3.title') }}</h3>
      <p class="text-muted">{{ $t('campaigns.wizard.step3.subtitle') }}</p>
    </div>

    <form @submit.prevent="handleNext">
      <div class="row">
        <!-- Brand Selection -->
        <div class="col-12 mb-4">
          <label class="form-label">{{ $t('campaigns.wizard.step3.brandSelection') }}</label>
          
          <!-- Existing Brands -->
          <div v-if="brands.length > 0" class="brand-options mb-3">
            <div 
              v-for="brand in brands" 
              :key="brand.id"
              class="brand-option"
              :class="{ 'selected': localData.brand_id === brand.id }"
              @click="selectBrand(brand)"
            >
              <div class="brand-logo">
                <img v-if="brand.logo_url" :src="brand.logo_url" :alt="brand.name" />
                <div v-else class="brand-placeholder">
                  <i class="bx bx-image"></i>
                </div>
              </div>
              <div class="brand-info">
                <h6>{{ brand.name }}</h6>
                <div class="brand-colors">
                  <span 
                    v-if="brand.primary_color"
                    class="color-dot" 
                    :style="{ backgroundColor: brand.primary_color }"
                  ></span>
                  <span 
                    v-if="brand.secondary_color"
                    class="color-dot" 
                    :style="{ backgroundColor: brand.secondary_color }"
                  ></span>
                  <span 
                    v-if="brand.accent_color"
                    class="color-dot" 
                    :style="{ backgroundColor: brand.accent_color }"
                  ></span>
                </div>
              </div>
            </div>
          </div>

          <!-- No Brand Options -->
          <div class="no-brand-options">
            <div class="row">
              <!-- AI Color Suggestion -->
              <div class="col-md-4 mb-3">
                <div 
                  class="brand-option ai-option"
                  :class="{ 'selected': localData.brand_selection === 'ai' }"
                  @click="selectAIColors"
                >
                  <div class="brand-icon">
                    <i class="bx bx-palette"></i>
                  </div>
                  <div class="brand-info">
                    <h6>{{ $t('campaigns.wizard.step3.aiColors') }}</h6>
                    <p>{{ $t('campaigns.wizard.step3.aiColorsDesc') }}</p>
                  </div>
                </div>
              </div>

              <!-- Manual Colors -->
              <div class="col-md-4 mb-3">
                <div 
                  class="brand-option manual-option"
                  :class="{ 'selected': localData.brand_selection === 'manual' }"
                  @click="selectManualColors"
                >
                  <div class="brand-icon">
                    <i class="bx bx-edit"></i>
                  </div>
                  <div class="brand-info">
                    <h6>{{ $t('campaigns.wizard.step3.manualColors') }}</h6>
                    <p>{{ $t('campaigns.wizard.step3.manualColorsDesc') }}</p>
                  </div>
                </div>
              </div>

              <!-- Create New Brand -->
              <div class="col-md-4 mb-3">
                <div 
                  class="brand-option create-option"
                  :class="{ 'selected': localData.brand_selection === 'create' }"
                  @click="selectCreateBrand"
                >
                  <div class="brand-icon">
                    <i class="bx bx-plus"></i>
                  </div>
                  <div class="brand-info">
                    <h6>{{ $t('campaigns.wizard.step3.createBrand') }}</h6>
                    <p>{{ $t('campaigns.wizard.step3.createBrandDesc') }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- AI Color Suggestions -->
        <div v-if="localData.brand_selection === 'ai' && aiColorPalettes.length > 0" class="col-12 mb-4">
          <label class="form-label">{{ $t('campaigns.wizard.step3.chooseColors') }}</label>
          <div class="color-palettes">
            <div 
              v-for="(palette, index) in aiColorPalettes" 
              :key="index"
              class="color-palette"
              :class="{ 'selected': localData.selected_palette_index === index }"
              @click="selectColorPalette(index)"
            >
              <div class="palette-colors">
                <div 
                  class="color-swatch" 
                  :style="{ backgroundColor: palette.primary_color }"
                ></div>
                <div 
                  class="color-swatch" 
                  :style="{ backgroundColor: palette.secondary_color }"
                ></div>
                <div 
                  class="color-swatch" 
                  :style="{ backgroundColor: palette.accent_color }"
                ></div>
              </div>
              <div class="palette-info">
                <h6>{{ palette.name }}</h6>
                <p>{{ palette.reasoning }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Manual Color Picker -->
        <div v-if="localData.brand_selection === 'manual'" class="col-12 mb-4">
          <label class="form-label">{{ $t('campaigns.wizard.step3.chooseColors') }}</label>
          <div class="manual-colors">
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label small">{{ $t('campaigns.wizard.step3.primaryColor') }}</label>
                <div class="color-input-group">
                  <input 
                    v-model="localData.manual_colors.primary"
                    type="color"
                    class="form-control color-picker"
                  />
                  <input 
                    v-model="localData.manual_colors.primary"
                    type="text"
                    class="form-control color-text"
                    placeholder="#000000"
                  />
                </div>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label small">{{ $t('campaigns.wizard.step3.secondaryColor') }}</label>
                <div class="color-input-group">
                  <input 
                    v-model="localData.manual_colors.secondary"
                    type="color"
                    class="form-control color-picker"
                  />
                  <input 
                    v-model="localData.manual_colors.secondary"
                    type="text"
                    class="form-control color-text"
                    placeholder="#000000"
                  />
                </div>
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label small">{{ $t('campaigns.wizard.step3.accentColor') }}</label>
                <div class="color-input-group">
                  <input 
                    v-model="localData.manual_colors.accent"
                    type="color"
                    class="form-control color-picker"
                  />
                  <input 
                    v-model="localData.manual_colors.accent"
                    type="text"
                    class="form-control color-text"
                    placeholder="#000000"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Platforms -->
        <div class="col-12 mb-4">
          <label class="form-label">{{ $t('campaigns.wizard.step3.platforms') }} *</label>
          <div class="platform-options">
            <div 
              v-for="platform in platforms" 
              :key="platform.value"
              class="platform-option"
              :class="{ 'selected': localData.platforms?.includes(platform.value) }"
              @click="togglePlatform(platform.value)"
            >
              <div class="platform-icon">
                <i :class="platform.icon"></i>
              </div>
              <div class="platform-info">
                <h6>{{ platform.label }}</h6>
                <p>{{ platform.description }}</p>
              </div>
            </div>
          </div>
          <div v-if="errors.platforms" class="invalid-feedback">
            {{ errors.platforms }}
          </div>
        </div>

        <!-- Posts per Week -->
        <div class="col-12 mb-4">
          <label class="form-label">{{ $t('campaigns.wizard.step3.postsPerWeek') }} *</label>
          <div class="posts-slider">
            <input 
              v-model="localData.posts_per_week"
              type="range"
              class="form-range"
              min="1"
              max="7"
              step="1"
            />
            <div class="slider-labels">
              <span>1 {{ $t('common.post') }}</span>
              <span class="current-value">{{ localData.posts_per_week }} {{ $t('common.posts') }}</span>
              <span>7 {{ $t('common.posts') }}</span>
            </div>
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
import { ref, computed, watch, onMounted } from 'vue'
import { useBrandStore } from '@/stores/brand'
import { useCampaignStore } from '@/stores/campaign'

const props = defineProps({
  wizardData: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:wizardData', 'next', 'back'])

const brandStore = useBrandStore()
const campaignStore = useCampaignStore()

// Local data
const localData = ref({ 
  ...props.wizardData,
  brand_selection: null,
  selected_palette_index: null,
  manual_colors: {
    primary: '#007bff',
    secondary: '#6c757d',
    accent: '#28a745'
  }
})
const errors = ref({})
const aiColorPalettes = ref([])
const loading = ref(false)

// Platforms
const platforms = [
  {
    value: 'instagram',
    label: 'Instagram',
    description: 'Visual content and stories',
    icon: 'bx bxl-instagram'
  },
  {
    value: 'facebook',
    label: 'Facebook',
    description: 'Posts and community building',
    icon: 'bx bxl-facebook'
  },
  {
    value: 'twitter',
    label: 'Twitter',
    description: 'Quick updates and news',
    icon: 'bx bxl-twitter'
  },
  {
    value: 'linkedin',
    label: 'LinkedIn',
    description: 'Professional networking',
    icon: 'bx bxl-linkedin'
  }
]

// Computed
const brands = computed(() => brandStore.brands)
const isValid = computed(() => {
  return localData.value.platforms?.length > 0 && 
         localData.value.posts_per_week > 0
})

// Watch for changes
watch(localData, (newData) => {
  emit('update:wizardData', newData)
}, { deep: true })

// Methods
const selectBrand = (brand) => {
  localData.value.brand_id = brand.id
  localData.value.brand_selection = 'existing'
  localData.value.brand_colors = {
    primary_color: brand.primary_color,
    secondary_color: brand.secondary_color,
    accent_color: brand.accent_color
  }
  localData.value.brand_voice = brand.brand_voice
}

const selectAIColors = async () => {
  localData.value.brand_selection = 'ai'
  localData.value.brand_id = null
  
  if (props.wizardData.description) {
    loading.value = true
    try {
      const response = await campaignStore.suggestColors(props.wizardData.description)
      aiColorPalettes.value = response.color_palettes || []
    } catch (error) {
      console.error('Failed to get AI color suggestions:', error)
    } finally {
      loading.value = false
    }
  }
}

const selectManualColors = () => {
  localData.value.brand_selection = 'manual'
  localData.value.brand_id = null
}

const selectCreateBrand = () => {
  localData.value.brand_selection = 'create'
  localData.value.brand_id = null
}

const selectColorPalette = (index) => {
  localData.value.selected_palette_index = index
  const palette = aiColorPalettes.value[index]
  localData.value.brand_colors = {
    primary_color: palette.primary_color,
    secondary_color: palette.secondary_color,
    accent_color: palette.accent_color
  }
}

const togglePlatform = (platform) => {
  if (!localData.value.platforms) {
    localData.value.platforms = []
  }
  
  const index = localData.value.platforms.indexOf(platform)
  if (index > -1) {
    localData.value.platforms.splice(index, 1)
  } else {
    localData.value.platforms.push(platform)
  }
}

const validateForm = () => {
  errors.value = {}
  
  if (!localData.value.platforms?.length) {
    errors.value.platforms = 'Please select at least one platform'
  }
  
  if (!localData.value.posts_per_week) {
    errors.value.posts_per_week = 'Posts per week is required'
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

// Lifecycle
onMounted(async () => {
  await brandStore.fetchBrands()
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

.brand-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.brand-option {
  border: 2px solid #e9ecef;
  border-radius: 12px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.brand-option:hover {
  border-color: #007bff;
  transform: translateY(-2px);
}

.brand-option.selected {
  border-color: #007bff;
  background: linear-gradient(135deg, #f8f9ff, #e3f2fd);
}

.brand-logo {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
}

.brand-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.brand-placeholder {
  color: #6c757d;
  font-size: 1.5rem;
}

.brand-info h6 {
  margin: 0 0 0.25rem 0;
  font-weight: 600;
}

.brand-colors {
  display: flex;
  gap: 0.25rem;
}

.color-dot {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 0 0 1px #dee2e6;
}

.no-brand-options .brand-option {
  text-align: center;
  flex-direction: column;
  padding: 1.5rem;
}

.brand-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #007bff, #0056b3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.ai-option .brand-icon {
  background: linear-gradient(135deg, #28a745, #1e7e34);
}

.manual-option .brand-icon {
  background: linear-gradient(135deg, #ffc107, #e0a800);
}

.create-option .brand-icon {
  background: linear-gradient(135deg, #6f42c1, #5a32a3);
}

.color-palettes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.color-palette {
  border: 2px solid #e9ecef;
  border-radius: 12px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.color-palette:hover {
  border-color: #007bff;
  transform: translateY(-2px);
}

.color-palette.selected {
  border-color: #007bff;
  background: linear-gradient(135deg, #f8f9ff, #e3f2fd);
}

.palette-colors {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.color-swatch {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 2px solid white;
  box-shadow: 0 0 0 1px #dee2e6;
}

.manual-colors .color-input-group {
  display: flex;
  gap: 0.5rem;
}

.color-picker {
  width: 60px;
  height: 38px;
  padding: 0;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.color-text {
  flex: 1;
}

.platform-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.platform-option {
  border: 2px solid #e9ecef;
  border-radius: 12px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
}

.platform-option:hover {
  border-color: #007bff;
  transform: translateY(-2px);
}

.platform-option.selected {
  border-color: #007bff;
  background: linear-gradient(135deg, #f8f9ff, #e3f2fd);
}

.platform-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #007bff, #0056b3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  margin: 0 auto 0.5rem auto;
}

.platform-info h6 {
  margin: 0 0 0.25rem 0;
  font-weight: 600;
}

.platform-info p {
  margin: 0;
  font-size: 0.875rem;
  color: #6c757d;
}

.posts-slider {
  max-width: 400px;
  margin: 0 auto;
}

.slider-labels {
  display: flex;
  justify-content: space-between;
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #6c757d;
}

.current-value {
  font-weight: 600;
  color: #007bff;
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
  .brand-options,
  .platform-options {
    grid-template-columns: 1fr;
  }
  
  .color-palettes {
    grid-template-columns: 1fr;
  }
  
  .btn {
    width: 100%;
  }
}
</style>
