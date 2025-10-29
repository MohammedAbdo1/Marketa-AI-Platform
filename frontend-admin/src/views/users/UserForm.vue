<template>
  <div class="flex items-center justify-between mb-3">
    <nav aria-label="breadcrumb" class="flex">
      <ol class="breadcrumb bg-transparent p-0 m-0">
        <li class="breadcrumb-item">
          <router-link to="/users">{{ t('users.title') }}</router-link>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ isEdit ? t('users.edit') : t('users.create') }}
        </li>
      </ol>
    </nav>
    <h1 class="text-3xl font-semibold">
      {{ isEdit ? t('users.edit') : t('users.create') }}
    </h1>
  </div>

  <div class="bg-white p-4 rounded-lg shadow animate-fade-in-down">
    <Form @submit="saveUser" :showActions="true">
      <div class="row">
        <div class="col-md-6">
          <CustomInput 
            v-model="form.name" 
            label="name" 
            name="name" 
            required
            :errors="errors.name" 
          />
        </div>
        
        <div class="col-md-6">
          <CustomInput 
            v-model="form.email" 
            label="email" 
            name="email" 
            type="email"
            required
            :errors="errors.email" 
          />
        </div>

        <div class="col-md-6">
          <CustomInput 
            v-model="form.phone" 
            label="phone" 
            name="phone" 
            :errors="errors.phone" 
          />
        </div>

        <div class="col-md-6">
          <CustomInput 
            v-model="form.password" 
            label="password" 
            name="password" 
            type="password"
            :required="!isEdit"
            :errors="errors.password" 
          />
        </div>

        <div v-if="!isEdit || form.password" class="col-md-6">
          <CustomInput 
            v-model="form.password_confirmation" 
            label="password_confirmation" 
            name="password_confirmation" 
            type="password"
            :required="!isEdit && form.password"
            :errors="errors.password_confirmation" 
          />
        </div>

        <div class="col-md-6 mt-2">
          <div class="form-check form-switch">
            <input 
              class="form-check-input" 
              type="checkbox" 
              role="switch" 
              id="switchStatusDefault" 
              v-model="isActive"
            >
            <label class="form-check-label" for="switchStatusDefault">
              {{ t('users.status') }}
            </label>
          </div>
        </div>
      </div>
    </Form>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, watch } from "vue"
import { Form, CustomInput } from "@/components/core"
import { useI18n } from 'vue-i18n'
import { useUserStore } from '@/stores/user'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from "vue-toastification"
import axiosClient from '@/axios'

const { t } = useI18n()
const userStore = useUserStore()
const route = useRoute()
const router = useRouter()
const toast = useToast()

const loading = ref(false)
const errors = ref({})
const isEdit = computed(() => !!route.params.uuid)
const isActive = ref(false)

const DEFAULT_USER = {
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  status: 'inactive',
}

const form = ref({ ...DEFAULT_USER })

// Watch isActive checkbox and update form.status
watch(isActive, (newVal) => {
  form.value.status = newVal ? 'active' : 'inactive'
})

onMounted(async () => {
  // Load user data if editing
  if (isEdit.value) {
    try {
      const response = await axiosClient.get(`/admin/users/${route.params.uuid}`)
      form.value = response.data.data
      isActive.value = response.data.data.status === 'active'
    } catch (error) {
      toast.error(t('common.error_loading'))
      router.push({ name: 'users' })
    }
  }
})

async function saveUser() {
  errors.value = {}
  loading.value = true
  try {
    if (isEdit.value) {
      await userStore.updateUser(route.params.uuid, form.value)
      toast.success(t('users.user_updated_success'))
    } else {
      await userStore.createUser(form.value)
      toast.success(t('users.user_created_success'))
    }
    router.push({ name: 'users' })
  } catch (error) {
    console.log('Error response:', error.response)
    if (error.response && error.response.data.errors) {
      errors.value = error.response.data.errors
      console.log('Validation errors:', errors.value)
    } else if (error.response && error.response.data.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error(t('common.error_saving'))
    }
  } finally {
    loading.value = false
  }
}
</script>
