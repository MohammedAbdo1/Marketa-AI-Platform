<template>
  <div class="p-6">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
      <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
          <router-link
            to="/plans"
            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white"
          >
            <i class="ti ti-package mr-2"></i>
            {{ $t('plans.title') }}
          </router-link>
        </li>
        <li>
          <div class="flex items-center">
            <i class="ti ti-chevron-right text-gray-400 mx-1"></i>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
              {{ isEdit ? $t('common.edit') : $t('plans.create_plan') }}
            </span>
          </div>
        </li>
      </ol>
    </nav>

    <Form :show-actions="true" @submit="handleSubmit" @cancel="handleCancel">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Name AR -->
        <CustomInput
          v-model="form.name_ar"
          :label="$t('plans.name_ar')"
          type="text"
          :error="errors.name_ar"
          required
        />

        <!-- Name EN -->
        <CustomInput
          v-model="form.name_en"
          :label="$t('plans.name_en')"
          type="text"
          :error="errors.name_en"
          required
        />

        <!-- Slug -->
        <CustomInput
          v-model="form.slug"
          :label="$t('common.slug')"
          type="text"
          :error="errors.slug"
          :placeholder="$t('common.slug_placeholder')"
        />

        <!-- Sort Order -->
        <CustomInput
          v-model="form.sort_order"
          :label="$t('plans.sort_order')"
          type="number"
          :error="errors.sort_order"
        />

        <!-- Description AR -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $t('plans.description_ar') }}
          </label>
          <textarea
            v-model="form.description_ar"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            :class="{ 'border-red-500': errors.description_ar }"
          ></textarea>
          <p v-if="errors.description_ar" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ errors.description_ar }}
          </p>
        </div>

        <!-- Description EN -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $t('plans.description_en') }}
          </label>
          <textarea
            v-model="form.description_en"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            :class="{ 'border-red-500': errors.description_en }"
          ></textarea>
          <p v-if="errors.description_en" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ errors.description_en }}
          </p>
        </div>

        <!-- Price Monthly -->
        <CustomInput
          v-model="form.price_monthly"
          :label="$t('plans.price_monthly')"
          type="number"
          step="0.01"
          min="0"
          :error="errors.price_monthly"
          required
        />

        <!-- Price Yearly -->
        <CustomInput
          v-model="form.price_yearly"
          :label="$t('plans.price_yearly')"
          type="number"
          step="0.01"
          min="0"
          :error="errors.price_yearly"
          required
        />

        <!-- Tokens Limit -->
        <CustomInput
          v-model="form.tokens_limit"
          :label="$t('plans.tokens_limit')"
          type="number"
          min="0"
          :error="errors.tokens_limit"
          required
        />

        <!-- Features -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $t('plans.features') }}
          </label>
          <div v-for="(feature, index) in form.features" :key="index" class="flex items-center space-x-2 mb-2">
            <input
              v-model="form.features[index]"
              type="text"
              :placeholder="$t('plans.add_feature')"
              class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            />
            <button
              type="button"
              @click="removeFeature(index)"
              class="px-3 py-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
            >
              <i class="ti ti-trash"></i>
            </button>
          </div>
          <button
            type="button"
            @click="addFeature"
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
          >
            <i class="ti ti-plus mr-1"></i>
            {{ $t('plans.add_feature') }}
          </button>
          <p v-if="errors.features" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ errors.features }}
          </p>
        </div>

        <!-- Status Toggles -->
        <div class="md:col-span-2">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Is Active -->
            <div>
              <label class="flex items-center">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                />
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                  {{ $t('common.active') }}
                </span>
              </label>
            </div>

            <!-- Is Popular -->
            <div>
              <label class="flex items-center">
                <input
                  v-model="form.is_popular"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                />
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                  {{ $t('plans.is_popular') }}
                </span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </Form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePlanStore } from '@/stores/plan'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import Form from '@/components/core/Form.vue'
import CustomInput from '@/components/core/CustomInput.vue'

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const toast = useToast()
const planStore = usePlanStore()

// Reactive data
const form = ref({
  name_ar: '',
  name_en: '',
  slug: '',
  description_ar: '',
  description_en: '',
  price_monthly: 0,
  price_yearly: 0,
  tokens_limit: 0,
  features: [''],
  is_active: true,
  is_popular: false,
  sort_order: 0
})

const errors = ref({})

// Computed
const isEdit = computed(() => !!route.params.id)

// Auto-generate slug from name_ar
watch(() => form.value.name_ar, (newName) => {
  if (!isEdit.value) {
    form.value.slug = newName
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .trim()
  }
})

// Methods
const loadPlan = async () => {
  if (!isEdit.value) return
  
  try {
    const plan = await planStore.getPlan(route.params.id)
    form.value = {
      name_ar: plan.name_ar,
      name_en: plan.name_en,
      slug: plan.slug,
      description_ar: plan.description_ar || '',
      description_en: plan.description_en || '',
      price_monthly: plan.price_monthly,
      price_yearly: plan.price_yearly,
      tokens_limit: plan.tokens_limit,
      features: Array.isArray(plan.features) && plan.features.length > 0 ? plan.features : [''],
      is_active: plan.is_active,
      is_popular: plan.is_popular,
      sort_order: plan.sort_order
    }
  } catch (error) {
    toast.error(t('common.error_loading'))
    router.push('/plans')
  }
}

const addFeature = () => {
  form.value.features.push('')
}

const removeFeature = (index) => {
  if (form.value.features.length > 1) {
    form.value.features.splice(index, 1)
  }
}

const handleSubmit = async () => {
  errors.value = {}
  
  // Filter out empty features
  const filteredFeatures = form.value.features.filter(feature => feature.trim() !== '')
  
  try {
    const data = {
      ...form.value,
      features: filteredFeatures
    }
    
    if (isEdit.value) {
      await planStore.updatePlan(route.params.id, data)
      toast.success(t('common.updated_success'))
    } else {
      await planStore.createPlan(data)
      toast.success(t('common.created_success'))
    }
    router.push('/plans')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      toast.error(t('common.error_saving'))
    }
  }
}

const handleCancel = () => {
  router.push('/plans')
}

// Lifecycle
onMounted(async () => {
  await loadPlan()
})
</script>