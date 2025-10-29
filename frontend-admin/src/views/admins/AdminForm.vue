<template>
  <div class="flex items-center justify-between mb-3">
    <nav aria-label="breadcrumb" class="flex">
      <ol class="breadcrumb bg-transparent p-0 m-0">
        <li class="breadcrumb-item">
          <router-link to="/admins">{{ t('admins.title') }}</router-link>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ isEdit ? t('admins.edit') : t('admins.create') }}
        </li>
      </ol>
    </nav>
    <h1 class="text-3xl font-semibold">
      {{ isEdit ? t('admins.edit') : t('admins.create') }}
    </h1>
  </div>

  <div class="bg-white p-4 rounded-lg shadow animate-fade-in-down">
    <Form @submit="saveAdmin" :showActions="true">
      <div class="row">
        <div class="col-md-6">
          <CustomInput 
            v-model="form.name" 
            :label="t('common.name')" 
            name="name" 
            required
            :errors="errors.name" 
          />
        </div>
        
        <div class="col-md-6">
          <CustomInput 
            v-model="form.email" 
            :label="t('common.email')" 
            name="email" 
            type="email"
            required
            :errors="errors.email" 
          />
        </div>

        <div class="col-md-6">
          <CustomInput 
            v-model="form.phone" 
            :label="t('common.phone')" 
            name="phone" 
            :errors="errors.phone" 
          />
        </div>

        <div class="col-md-6">
          <CustomInput 
            v-model="form.password" 
            :label="t('common.password')" 
            name="password" 
            type="password"
            :required="!isEdit"
            :errors="errors.password" 
          />
        </div>

        <div v-if="!isEdit || form.password" class="col-md-6">
          <CustomInput 
            v-model="form.password_confirmation" 
            :label="t('common.confirm_password')" 
            name="password_confirmation" 
            type="password"
            :required="!isEdit && form.password"
            :errors="errors.password_confirmation" 
          />
        </div>

        <!-- Role Selection -->
        <div class="col-md-6">
          <CustomInput 
            v-model="form.role" 
            :label="t('common.role')" 
            name="role" 
            type="select"
            required
            :selectOptions="roleOptions"
            :errors="errors.role" 
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
              {{ t('common.status') }}
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
import { useAdminStore } from '@/stores/admin'
import { useRoleStore } from '@/stores/role'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from "vue-toastification"
import axiosClient from '@/axios'

const { t } = useI18n()
const adminStore = useAdminStore()
const roleStore = useRoleStore()
const route = useRoute()
const router = useRouter()
const toast = useToast()

const loading = ref(false)
const errors = ref({})
const isEdit = computed(() => !!route.params.uuid)
const isActive = ref(false)
const roleOptions = ref([])

const DEFAULT_ADMIN = {
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  role: '',
  status: 'inactive',
}

const form = ref({ ...DEFAULT_ADMIN })

// Watch isActive checkbox and update form.status
watch(isActive, (newVal) => {
  form.value.status = newVal ? 'active' : 'inactive'
})

onMounted(async () => {
  // Load roles
  try {
    await roleStore.getRoles()
    roleOptions.value = roleStore.roles.data.map(role => ({
      key: role.name,
      text: role.name
    }))
  } catch (error) {
    console.error('Error loading roles:', error)
  }

  // Load admin data if editing
  if (isEdit.value) {
    try {
      const response = await axiosClient.get(`/admin/users/${route.params.uuid}`)
      form.value = response.data.data
      form.value.role = response.data.data.roles?.[0]?.name || ''
      isActive.value = response.data.data.status === 'active'
    } catch (error) {
      toast.error(t('common.error_loading'))
      router.push({ name: 'admins' })
    }
  }
})

async function saveAdmin() {
  errors.value = {}
  loading.value = true
  
  const data = {
    ...form.value,
    roles: [form.value.role], // Convert to array for backend
  }

  try {
    if (isEdit.value) {
      await adminStore.updateAdmin(route.params.uuid, data)
      toast.success(t('common.updated_success'))
    } else {
      await adminStore.createAdmin(data)
      toast.success(t('common.created_success'))
    }
    router.push({ name: 'admins' })
  } catch (error) {
    console.log('Error response:', error.response)
    if (error.response && error.response.data.errors) {
      errors.value = error.response.data.errors
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

