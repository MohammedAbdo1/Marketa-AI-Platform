<template>
  <div class="flex items-center justify-between mb-3">
    <nav aria-label="breadcrumb" class="flex">
      <ol class="breadcrumb bg-transparent p-0 m-0">
        <li class="breadcrumb-item">
          <router-link to="/roles">{{ t('roles.title') }}</router-link>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
          {{ isEdit ? t('roles.edit') : t('roles.create') }}
        </li>
      </ol>
    </nav>
    <h1 class="text-3xl font-semibold">
      {{ isEdit ? t('roles.edit') : t('roles.create') }}
    </h1>
  </div>

  <div class="bg-white p-4 rounded-lg shadow animate-fade-in-down">
    <Form @submit="saveRole" :showActions="true">
      <div class="row">
        <!-- Role Name -->
        <div class="col-md-12">
          <CustomInput 
            v-model="form.name" 
            label="name" 
            name="name" 
            required
            :errors="errors.name" 
          />
        </div>

        <!-- Permissions Section -->
        <div class="col-md-12 mt-4">
          <h4 class="mb-3">{{ t('roles.permissions') }}</h4>
          
          <div v-if="permissionStore.loading" class="text-center py-4">
            <Spinner />
          </div>

          <div v-else>
            <!-- Users Permissions -->
            <div class="card mb-3">
              <div class="card-header bg-light">
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    :id="`module-users`"
                    @change="toggleModulePermissions('users')"
                    :checked="isModuleFullySelected('users')"
                  >
                  <label class="form-check-label fw-bold" :for="`module-users`">
                    👥 {{ t('modules.users') }}
                  </label>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div 
                    v-for="permission in permissionsByModule.users" 
                    :key="permission.id"
                    class="col-md-6 mb-2"
                  >
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="checkbox" 
                        :id="`perm-${permission.id}`"
                        :value="permission.name"
                        v-model="selectedPermissions"
                      >
                      <label class="form-check-label" :for="`perm-${permission.id}`">
                        {{ t(`permissions.${permission.name}`) }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Plans Permissions -->
            <div class="card mb-3">
              <div class="card-header bg-light">
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    :id="`module-plans`"
                    @change="toggleModulePermissions('plans')"
                    :checked="isModuleFullySelected('plans')"
                  >
                  <label class="form-check-label fw-bold" :for="`module-plans`">
                    📦 {{ t('modules.plans') }}
                  </label>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div 
                    v-for="permission in permissionsByModule.plans" 
                    :key="permission.id"
                    class="col-md-6 mb-2"
                  >
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="checkbox" 
                        :id="`perm-${permission.id}`"
                        :value="permission.name"
                        v-model="selectedPermissions"
                      >
                      <label class="form-check-label" :for="`perm-${permission.id}`">
                        {{ t(`permissions.${permission.name}`) }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Organizations Permissions -->
            <div class="card mb-3">
              <div class="card-header bg-light">
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    :id="`module-organizations`"
                    @change="toggleModulePermissions('organizations')"
                    :checked="isModuleFullySelected('organizations')"
                  >
                  <label class="form-check-label fw-bold" :for="`module-organizations`">
                    🏢 {{ t('modules.organizations') }}
                  </label>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div 
                    v-for="permission in permissionsByModule.organizations" 
                    :key="permission.id"
                    class="col-md-6 mb-2"
                  >
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="checkbox" 
                        :id="`perm-${permission.id}`"
                        :value="permission.name"
                        v-model="selectedPermissions"
                      >
                      <label class="form-check-label" :for="`perm-${permission.id}`">
                        {{ t(`permissions.${permission.name}`) }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Subscriptions Permissions -->
            <div class="card mb-3">
              <div class="card-header bg-light">
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    :id="`module-subscriptions`"
                    @change="toggleModulePermissions('subscriptions')"
                    :checked="isModuleFullySelected('subscriptions')"
                  >
                  <label class="form-check-label fw-bold" :for="`module-subscriptions`">
                    💳 {{ t('modules.subscriptions') }}
                  </label>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div 
                    v-for="permission in permissionsByModule.subscriptions" 
                    :key="permission.id"
                    class="col-md-6 mb-2"
                  >
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="checkbox" 
                        :id="`perm-${permission.id}`"
                        :value="permission.name"
                        v-model="selectedPermissions"
                      >
                      <label class="form-check-label" :for="`perm-${permission.id}`">
                        {{ t(`permissions.${permission.name}`) }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Settings Permissions -->
            <div class="card mb-3">
              <div class="card-header bg-light">
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    :id="`module-settings`"
                    @change="toggleModulePermissions('settings')"
                    :checked="isModuleFullySelected('settings')"
                  >
                  <label class="form-check-label fw-bold" :for="`module-settings`">
                    ⚙️ {{ t('modules.settings') }}
                  </label>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div 
                    v-for="permission in permissionsByModule.settings" 
                    :key="permission.id"
                    class="col-md-6 mb-2"
                  >
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="checkbox" 
                        :id="`perm-${permission.id}`"
                        :value="permission.name"
                        v-model="selectedPermissions"
                      >
                      <label class="form-check-label" :for="`perm-${permission.id}`">
                        {{ t(`permissions.${permission.name}`) }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Roles Permissions -->
            <div class="card mb-3">
              <div class="card-header bg-light">
                <div class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    :id="`module-roles`"
                    @change="toggleModulePermissions('roles')"
                    :checked="isModuleFullySelected('roles')"
                  >
                  <label class="form-check-label fw-bold" :for="`module-roles`">
                    🔐 {{ t('modules.roles') }}
                  </label>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div 
                    v-for="permission in permissionsByModule.roles" 
                    :key="permission.id"
                    class="col-md-6 mb-2"
                  >
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="checkbox" 
                        :id="`perm-${permission.id}`"
                        :value="permission.name"
                        v-model="selectedPermissions"
                      >
                      <label class="form-check-label" :for="`perm-${permission.id}`">
                        {{ t(`permissions.${permission.name}`) }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Form>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from "vue"
import { Form, CustomInput } from "@/components/core"
import Spinner from '@/components/core/Spinner.vue'
import { useI18n } from 'vue-i18n'
import { useRoleStore } from '@/stores/role'
import { usePermissionStore } from '@/stores/permission'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from "vue-toastification"

const { t } = useI18n()
const roleStore = useRoleStore()
const permissionStore = usePermissionStore()
const route = useRoute()
const router = useRouter()
const toast = useToast()

const loading = ref(false)
const errors = ref({})
const isEdit = computed(() => !!route.params.id)
const selectedPermissions = ref([])

const DEFAULT_ROLE = {
  name: '',
}

const form = ref({ ...DEFAULT_ROLE })

const permissionsByModule = computed(() => permissionStore.permissionsByModule)

onMounted(async () => {
  // Load permissions
  await permissionStore.getPermissions()

  // Load role data if editing
  if (isEdit.value) {
    try {
      const role = await roleStore.getRole(route.params.id)
      form.value.name = role.name
      selectedPermissions.value = role.permissions || []
    } catch (error) {
      toast.error(t('common.error_loading'))
      router.push({ name: 'roles' })
    }
  }
})

async function saveRole() {
  errors.value = {}
  loading.value = true
  
  const data = {
    name: form.value.name,
    permissions: selectedPermissions.value,
  }

  try {
    if (isEdit.value) {
      await roleStore.updateRole(route.params.id, data)
      toast.success(t('roles.role_updated_success'))
    } else {
      await roleStore.createRole(data)
      toast.success(t('roles.role_created_success'))
    }
    router.push({ name: 'roles' })
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

function toggleModulePermissions(module) {
  const modulePermissions = permissionsByModule.value[module] || []
  const permissionNames = modulePermissions.map(p => p.name)
  
  const isFullySelected = permissionNames.every(p => selectedPermissions.value.includes(p))
  
  if (isFullySelected) {
    // Remove all module permissions
    selectedPermissions.value = selectedPermissions.value.filter(p => !permissionNames.includes(p))
  } else {
    // Add all module permissions
    permissionNames.forEach(p => {
      if (!selectedPermissions.value.includes(p)) {
        selectedPermissions.value.push(p)
      }
    })
  }
}

function isModuleFullySelected(module) {
  const modulePermissions = permissionsByModule.value[module] || []
  const permissionNames = modulePermissions.map(p => p.name)
  return permissionNames.length > 0 && permissionNames.every(p => selectedPermissions.value.includes(p))
}
</script>

