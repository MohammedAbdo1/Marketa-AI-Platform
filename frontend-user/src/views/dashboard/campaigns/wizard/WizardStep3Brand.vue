<template>
  <div class="wizard-step">
    <div class="step-header mb-4">
      <h3>{{ $t('campaigns.wizard.step3.title') }}</h3>
      <p class="text-muted">{{ $t('campaigns.wizard.step3.subtitle') }}</p>
    </div>

    <form @submit.prevent="handleNext">
      <div class="d-flex flex-column gap-4">
        <section class="brand-section">
          <label class="form-label">{{ $t('campaigns.wizard.step3.brandSelection') }}</label>

          <div v-if="brands.length" class="brand-grid mb-3">
            <button
              v-for="brand in brands"
              :key="brand.id"
              type="button"
              class="card card-interactive brand-tile"
              :class="{ 'is-selected': localData.brand_id === brand.id }"
              @click="selectBrand(brand)"
            >
              <div class="card-body d-flex flex-column gap-2 items-start">
                <div class="d-flex items-center gap-3 w-100">
                  <div class="brand-token">
                    <i class="bx bx-store-alt text-secondary" v-if="!brand.logo_url"></i>
                    <img v-else :src="brand.logo_url" :alt="brand.name" />
                  </div>
                  <div class="d-flex flex-column gap-1">
                    <span class="text-sm font-semibold text-primary">{{ brand.name }}</span>
                    <span class="text-xs text-secondary" v-if="brand.tagline">{{ brand.tagline }}</span>
                  </div>
                </div>
                <div class="d-flex flex-wrap gap-1 text-2xs text-secondary">
                  <span v-if="brand.primary_color" class="badge badge-ghost">أساسي: {{ brand.primary_color }}</span>
                  <span v-if="brand.secondary_color" class="badge badge-ghost">ثانوي: {{ brand.secondary_color }}</span>
                  <span v-if="brand.accent_color" class="badge badge-ghost">إبراز: {{ brand.accent_color }}</span>
                </div>
                <div class="d-flex flex-wrap gap-2 text-2xs text-secondary">
                  <span v-if="brand.brand_voice">نبرة: {{ brand.brand_voice }}</span>
                  <span v-if="brand.font_arabic">خط عربي: {{ brand.font_arabic }}</span>
                </div>
                <span class="badge badge-success text-2xs" v-if="brand.is_default">العلامة الأساسية</span>
              </div>
            </button>
          </div>

          <div class="brand-helpers">
            <button
              type="button"
              class="card helper-card"
              :class="{ 'is-selected': localData.brand_selection === 'ai' }"
              @click="selectAIColors"
            >
              <div class="card-body d-flex items-start gap-3">
                <div class="helper-icon text-brand">
                  <i class="bx bx-palette"></i>
                </div>
                <div class="d-flex flex-column gap-1 text-start">
                  <span class="text-sm font-semibold text-primary">
                    {{ $t('campaigns.wizard.step3.aiColors') }}
                  </span>
                  <span class="text-xs text-secondary">
                    {{ $t('campaigns.wizard.step3.aiColorsDesc') }}
                  </span>
                </div>
              </div>
            </button>

            <button
              type="button"
              class="card helper-card"
              :class="{ 'is-selected': localData.brand_selection === 'manual' }"
              @click="selectManualColors"
            >
              <div class="card-body d-flex items-start gap-3">
                <div class="helper-icon text-secondary">
                  <i class="bx bx-edit"></i>
                </div>
                <div class="d-flex flex-column gap-1 text-start">
                  <span class="text-sm font-semibold text-primary">
                    {{ $t('campaigns.wizard.step3.manualColors') }}
                  </span>
                  <span class="text-xs text-secondary">
                    {{ $t('campaigns.wizard.step3.manualColorsDesc') }}
                  </span>
                </div>
              </div>
            </button>

            <button
              type="button"
              class="card helper-card"
              :class="{ 'is-selected': localData.brand_selection === 'create' }"
              @click="selectCreateBrand"
            >
              <div class="card-body d-flex items-start gap-3">
                <div class="helper-icon text-success">
                  <i class="bx bx-plus"></i>
                </div>
                <div class="d-flex flex-column gap-1 text-start">
                  <span class="text-sm font-semibold text-primary">
                    {{ $t('campaigns.wizard.step3.createBrand') }}
                  </span>
                  <span class="text-xs text-secondary">
                    {{ $t('campaigns.wizard.step3.createBrandDesc') }}
                  </span>
                </div>
              </div>
            </button>
          </div>
        </section>

        <section
          v-if="localData.brand_selection === 'ai' && aiColorPalettes.length"
          class="ai-section"
        >
          <label class="form-label">{{ $t('campaigns.wizard.step3.chooseColors') }}</label>
          <div class="palette-grid">
            <button
              v-for="(palette, index) in aiColorPalettes"
              :key="index"
              type="button"
              class="card palette-card"
              :class="{ 'is-selected': localData.selected_palette_index === index }"
              @click="selectColorPalette(index)"
            >
              <div class="card-body d-flex flex-column gap-2 items-start">
                <span class="text-sm font-semibold text-primary">{{ palette.name }}</span>
                <div class="d-flex flex-wrap gap-1 text-2xs text-secondary">
                  <span class="badge badge-ghost">أساسي: {{ palette.primary_color }}</span>
                  <span class="badge badge-ghost">ثانوي: {{ palette.secondary_color }}</span>
                  <span class="badge badge-ghost">إبراز: {{ palette.accent_color }}</span>
                </div>
                <p class="text-xs text-secondary text-start">
                  {{ palette.reasoning }}
                </p>
              </div>
            </button>
          </div>
        </section>

        <section v-if="localData.brand_selection === 'manual'" class="manual-section">
          <label class="form-label">{{ $t('campaigns.wizard.step3.chooseColors') }}</label>
          <div class="manual-grid">
            <div class="form-group">
              <label class="form-label small">{{ $t('campaigns.wizard.step3.primaryColor') }}</label>
              <div class="color-input-group">
                <input
                  class="form-control color-picker"
                  type="color"
                  v-model="localData.manual_colors.primary"
                />
                <input
                  class="form-control"
                  type="text"
                  v-model="localData.manual_colors.primary"
                  placeholder="#000000"
                />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label small">{{ $t('campaigns.wizard.step3.secondaryColor') }}</label>
              <div class="color-input-group">
                <input
                  class="form-control color-picker"
                  type="color"
                  v-model="localData.manual_colors.secondary"
                />
                <input
                  class="form-control"
                  type="text"
                  v-model="localData.manual_colors.secondary"
                  placeholder="#000000"
                />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label small">{{ $t('campaigns.wizard.step3.accentColor') }}</label>
              <div class="color-input-group">
                <input
                  class="form-control color-picker"
                  type="color"
                  v-model="localData.manual_colors.accent"
                />
                <input
                  class="form-control"
                  type="text"
                  v-model="localData.manual_colors.accent"
                  placeholder="#000000"
                />
              </div>
            </div>
          </div>
        </section>

        <section>
          <label class="form-label">{{ $t('campaigns.wizard.step3.platforms') }} *</label>
          <div class="platform-options">
            <button
              v-for="platform in platforms"
              :key="platform.value"
              type="button"
              class="platform-option"
              :class="{ 'is-selected': localData.platforms?.includes(platform.value) }"
              @click="togglePlatform(platform.value)"
            >
              <div class="platform-icon">
                <i :class="platform.icon"></i>
              </div>
              <div class="platform-info">
                <span class="text-sm font-semibold text-primary">{{ platform.label }}</span>
                <span class="text-xs text-secondary">{{ platform.description }}</span>
              </div>
            </button>
          </div>
          <div v-if="errors.platforms" class="invalid-feedback">
            {{ errors.platforms }}
          </div>
        </section>

        <section>
          <label class="form-label">{{ $t('campaigns.wizard.step3.postsPerWeek') }} *</label>
          <div class="posts-slider">
            <input
              v-model.number="localData.posts_per_week"
              type="range"
              class="form-range"
              min="1"
              max="7"
              step="1"
            />
            <div class="slider-labels">
              <span>1 {{ $t('common.post') }}</span>
              <span class="current-value">
                {{ localData.posts_per_week }} {{ $t('common.posts') }}
              </span>
              <span>7 {{ $t('common.posts') }}</span>
            </div>
          </div>
        </section>
      </div>

      <div class="step-actions mt-4">
        <div class="d-flex justify-content-between">
          <button type="button" class="btn btn-outline-secondary" @click="handleBack">
            <i class="bx bx-left-arrow-alt me-1"></i> {{ $t('common.back') }}
          </button>
          <button type="submit" class="btn btn-primary" :disabled="!isValid">
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
    required: true,
  },
})

const emit = defineEmits(['update:wizardData', 'next', 'back'])

const brandStore = useBrandStore()
const campaignStore = useCampaignStore()

const localData = ref({
  ...props.wizardData,
  brand_selection: props.wizardData.brand_selection || null,
  selected_palette_index: props.wizardData.selected_palette_index ?? null,
  manual_colors: {
    primary: props.wizardData.manual_colors?.primary || '#0B6E99',
    secondary: props.wizardData.manual_colors?.secondary || '#0F7B6C',
    accent: props.wizardData.manual_colors?.accent || '#D9730D',
  },
})

localData.value.platforms = Array.isArray(localData.value.platforms)
  ? [...localData.value.platforms]
  : []
localData.value.posts_per_week = Number(localData.value.posts_per_week || 3)

const errors = ref({})
const aiColorPalettes = ref([])
const loadingPalettes = ref(false)

const platforms = [
  {
    value: 'instagram',
    label: 'Instagram',
    description: 'منشورات مرئية وقصص تفاعلية',
    icon: 'bx bxl-instagram',
  },
  {
    value: 'facebook',
    label: 'Facebook',
    description: 'مجتمع ومحتوى متعدد الصيغ',
    icon: 'bx bxl-facebook',
  },
  {
    value: 'twitter',
    label: 'X (Twitter)',
    description: 'تحديثات سريعة وأخبار',
    icon: 'bx bxl-twitter',
  },
  {
    value: 'linkedin',
    label: 'LinkedIn',
    description: 'شبكات احترافية ومحتوى أعمال',
    icon: 'bx bxl-linkedin',
  },
]

const brands = computed(() => brandStore.sortedBrands || [])

const isValid = computed(() => {
  return (
    Array.isArray(localData.value.platforms) &&
    localData.value.platforms.length > 0 &&
    Number(localData.value.posts_per_week) > 0
  )
})

watch(
  localData,
  (value) => {
    emit('update:wizardData', {
      ...value,
      manual_colors: { ...value.manual_colors },
    })
  },
  { deep: true },
)

const selectBrand = (brand) => {
  localData.value.brand_id = brand.id
  localData.value.brand_selection = 'existing'
  localData.value.brand_colors = {
    primary_color: brand.primary_color,
    secondary_color: brand.secondary_color,
    accent_color: brand.accent_color,
  }
  localData.value.brand_voice = brand.brand_voice
}

const selectAIColors = async () => {
  localData.value.brand_selection = 'ai'
  localData.value.brand_id = null

  if (!props.wizardData.description) {
    aiColorPalettes.value = []
    return
  }

  loadingPalettes.value = true
  try {
    const response = await campaignStore.suggestColors(props.wizardData.description)
    aiColorPalettes.value = response.color_palettes || []
  } catch (error) {
    console.error('Failed to fetch AI palettes', error)
    aiColorPalettes.value = []
  } finally {
    loadingPalettes.value = false
  }
}

const selectManualColors = () => {
  localData.value.brand_selection = 'manual'
  localData.value.brand_id = null
}

const selectCreateBrand = () => {
  localData.value.brand_selection = 'create'
  localData.value.brand_id = null
  window.open('/dashboard/brands/create', '_blank')
}

const selectColorPalette = (index) => {
  localData.value.selected_palette_index = index
  const palette = aiColorPalettes.value[index]
  if (!palette) return

  localData.value.brand_colors = {
    primary_color: palette.primary_color,
    secondary_color: palette.secondary_color,
    accent_color: palette.accent_color,
  }
}

const togglePlatform = (platform) => {
  const list = localData.value.platforms || []
  const index = list.indexOf(platform)
  if (index > -1) {
    list.splice(index, 1)
  } else {
    list.push(platform)
  }
  localData.value.platforms = [...list]
}

const validateForm = () => {
  errors.value = {}

  if (!localData.value.platforms || localData.value.platforms.length === 0) {
    errors.value.platforms = 'يرجى اختيار منصة نشر واحدة على الأقل'
  }

  if (!localData.value.posts_per_week) {
    errors.value.posts_per_week = 'عدد المنشورات الأسبوعية مطلوب'
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

onMounted(async () => {
  if (!brandStore.brands.length) {
    await brandStore.fetchBrands()
  }
})
</script>

<style scoped>
.wizard-step {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.step-header {
  text-align: center;
}

.brand-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.brand-grid {
  display: grid;
  gap: 1rem;
}

@media (min-width: 768px) {
  .brand-grid {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  }
}

.brand-tile {
  border: 1px solid var(--color-bg-tertiary);
  border-radius: 14px;
  text-align: start;
  transition: transform 0.2s ease, border-color 0.2s ease;
  cursor: pointer;
}

.brand-tile:hover {
  border-color: var(--color-brand-primary);
  transform: translateY(-2px);
}

.brand-tile.is-selected {
  border-color: var(--color-brand-primary);
  box-shadow: 0 0 0 2px rgba(11, 110, 153, 0.2);
}

.brand-token {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background-color: var(--color-bg-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.brand-token img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.brand-helpers {
  display: grid;
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .brand-helpers {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  }
}

.helper-card {
  border-radius: 14px;
  transition: transform 0.2s ease, border-color 0.2s ease;
  cursor: pointer;
}

.helper-card:hover {
  transform: translateY(-2px);
  border-color: var(--color-brand-primary);
}

.helper-card.is-selected {
  border-color: var(--color-brand-primary);
  box-shadow: 0 0 0 2px rgba(11, 110, 153, 0.2);
}

.helper-icon {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background-color: var(--color-bg-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.ai-section,
.manual-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.palette-grid {
  display: grid;
  gap: 1rem;
}

@media (min-width: 768px) {
  .palette-grid {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  }
}

.palette-card {
  border-radius: 12px;
  transition: transform 0.2s ease, border-color 0.2s ease;
  cursor: pointer;
}

.palette-card:hover {
  border-color: var(--color-brand-primary);
  transform: translateY(-2px);
}

.palette-card.is-selected {
  border-color: var(--color-brand-primary);
  box-shadow: 0 0 0 2px rgba(11, 110, 153, 0.2);
}

.manual-grid {
  display: grid;
  gap: 1rem;
}

@media (min-width: 768px) {
  .manual-grid {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  }
}

.color-input-group {
  display: flex;
  gap: 0.5rem;
}

.color-picker {
  width: 60px;
  height: 40px;
  border-radius: 10px;
  border: 1px solid var(--color-bg-tertiary);
  cursor: pointer;
}

.platform-options {
  display: grid;
  gap: 1rem;
}

@media (min-width: 768px) {
  .platform-options {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}

.platform-option {
  border: 1px solid var(--color-bg-tertiary);
  border-radius: 14px;
  text-align: start;
  padding: 1rem;
  display: flex;
  gap: 1rem;
  align-items: center;
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.platform-option:hover {
  border-color: var(--color-brand-primary);
  transform: translateY(-2px);
}

.platform-option.is-selected {
  border-color: var(--color-brand-primary);
  box-shadow: 0 0 0 2px rgba(11, 110, 153, 0.15);
}

.platform-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background-color: var(--color-bg-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}

.platform-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.posts-slider {
  padding: 1rem;
  border-radius: 14px;
  background-color: var(--color-bg-secondary);
}

.slider-labels {
  display: flex;
  justify-content: space-between;
  margin-top: 0.5rem;
  color: var(--color-text-secondary);
  font-size: 0.85rem;
}

.slider-labels .current-value {
  font-weight: 600;
  color: var(--color-brand-primary);
}

.step-actions .btn {
  min-width: 140px;
}
</style>
