<template>
  <div class="p-6">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
      <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
          <router-link
            to="/organizations"
            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white"
          >
            <i class="ti ti-building mr-2"></i>
            {{ $t('organizations.title') }}
          </router-link>
        </li>
        <li>
          <div class="flex items-center">
            <i class="ti ti-chevron-right text-gray-400 mx-1"></i>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
              {{ isEdit ? $t('common.edit') : $t('organizations.create_organization') }}
            </span>
          </div>
        </li>
      </ol>
    </nav>

    <Form :show-actions="true" @submit="handleSubmit" @cancel="handleCancel">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Name -->
        <CustomInput
          v-model="form.name"
          :label="$t('common.name')"
          type="text"
          :error="errors.name"
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

        <!-- Website -->
        <CustomInput
          v-model="form.website"
          :label="$t('common.website')"
          type="url"
          :error="errors.website"
        />

        <!-- Owner -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $t('organizations.owner') }}
          </label>
          <select
            v-model="form.owner_id"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            :class="{ 'border-red-500': errors.owner_id }"
          >
            <option value="">{{ $t('common.select_owner') }}</option>
            <option
              v-for="user in users"
              :key="user.id"
              :value="user.id"
            >
              {{ user.name }} ({{ user.email }})
            </option>
          </select>
          <p v-if="errors.owner_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ errors.owner_id }}
          </p>
        </div>

        <!-- Trial Ends At -->
        <CustomInput
          v-model="form.trial_ends_at"
          :label="$t('organizations.trial_ends')"
          type="datetime-local"
          :error="errors.trial_ends_at"
        />

        <!-- Status -->
        <div>
          <label class="flex items-center">
            <input
              v-model="form.status"
              type="checkbox"
              :true-value="'active'"
              :false-value="'suspended'"
              class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            />
            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
              {{ $t('common.active') }}
            </span>
          </label>
        </div>

        <!-- Logo Upload -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $t('organizations.logo') }}
          </label>
          <div class="flex items-center space-x-4">
            <div v-if="form.logo" class="flex-shrink-0">
              <img
                :src="form.logo"
                :alt="form.name"
                class="h-20 w-20 rounded-lg object-cover"
              />
            </div>
            <div class="flex-1">
              <input
                ref="logoInput"
                type="file"
                accept="image/*"
                @change="handleLogoChange"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-200"
              />
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $t('common.image_upload_help') }}
              </p>
            </div>
          </div>
        </div>

        <!-- Settings (JSON) -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $t('organizations.settings') }}
          </label>
          <textarea
            v-model="settingsJson"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            :class="{ 'border-red-500': errors.settings }"
            :placeholder="$t('organizations.settings_placeholder')"
          ></textarea>
          <p v-if="errors.settings" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ errors.settings }}
          </p>
        </div>
      </div>
    </Form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useOrganizationStore } from '@/stores/organization'
import { useUserStore } from '@/stores/user'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import Form from '@/components/core/Form.vue'
import CustomInput from '@/components/core/CustomInput.vue'

const route = useRoute()
const router = useRouter()
const { t } = useI18n()
const toast = useToast()
const organizationStore = useOrganizationStore()
const userStore = useUserStore()

// Reactive data
const form = ref({
  name: '',
  slug: '',
  website: '',
  owner_id: '',
  trial_ends_at: '',
  status: 'active',
  logo: '',
  settings: {}
})

const errors = ref({})
const settingsJson = ref('{}')
const logoInput = ref(null)

// Computed
const isEdit = computed(() => !!route.params.uuid)
const users = computed(() => userStore.users.data || [])

// Auto-generate slug from name
watch(() => form.value.name, (newName) => {
  if (!isEdit.value) {
    form.value.slug = newName
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .trim()
  }
})

// Watch settings JSON
watch(settingsJson, (newJson) => {
  try {
    form.value.settings = JSON.parse(newJson)
  } catch (e) {
    // Invalid JSON, keep current settings
  }
})

// Methods
const loadUsers = async () => {
  try {
    await userStore.getUsers({ per_page: 100 })
  } catch (error) {
    console.error('Error loading users:', error)
  }
}

const loadOrganization = async () => {
  if (!isEdit.value) return
  
  try {
    const organization = await organizationStore.getOrganization(route.params.uuid)
    form.value = {
      name: organization.name,
      slug: organization.slug,
      website: organization.website || '',
      owner_id: organization.owner?.id || '',
      trial_ends_at: organization.trial_ends_at ? new Date(organization.trial_ends_at).toISOString().slice(0, 16) : '',
      status: organization.status,
      logo: organization.logo || '',
      settings: organization.settings || {}
    }
    settingsJson.value = JSON.stringify(organization.settings || {}, null, 2)
  } catch (error) {
    toast.error(t('common.error_loading'))
    router.push('/organizations')
  }
}

const handleLogoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      form.value.logo = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const handleSubmit = async () => {
  errors.value = {}
  
  try {
    if (isEdit.value) {
      await organizationStore.updateOrganization(route.params.uuid, form.value)
      toast.success(t('common.updated_success'))
    } else {
      await organizationStore.createOrganization(form.value)
      toast.success(t('common.created_success'))
    }
    router.push('/organizations')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      toast.error(t('common.error_saving'))
    }
  }
}

const handleCancel = () => {
  router.push('/organizations')
}

// Lifecycle
onMounted(async () => {
  await loadUsers()
  await loadOrganization()
})
</script>