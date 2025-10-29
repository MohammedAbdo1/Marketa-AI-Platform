<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <!-- Filters -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $t('common.search') }}
          </label>
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="$t('common.search_placeholder')"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            @input="debouncedSearch"
          />
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $t('common.status') }}
          </label>
          <select
            v-model="statusFilter"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            @change="applyFilters"
          >
            <option value="">{{ $t('common.all') }}</option>
            <option value="active">{{ $t('common.active') }}</option>
            <option value="suspended">{{ $t('organizations.suspended') }}</option>
          </select>
        </div>

        <!-- Per Page -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $t('common.per_page') }}
          </label>
          <select
            v-model="perPage"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            @change="applyFilters"
          >
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>

        <!-- Actions -->
        <div class="flex items-end">
          <button
            @click="resetFilters"
            class="w-full px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
          >
            {{ $t('common.reset') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th>
              <TableHeaderCell
                @click="handleSort('id')"
                field="id"
                :sortField="sortField"
                :sortDirection="sortDirection"
              >
                ID
              </TableHeaderCell>
            </th>
            <th>{{ $t('organizations.logo') }}</th>
            <th>
              <TableHeaderCell
                @click="handleSort('name')"
                field="name"
                :sortField="sortField"
                :sortDirection="sortDirection"
              >
                {{ $t('common.name') }}
              </TableHeaderCell>
            </th>
            <th>
              <TableHeaderCell
                @click="handleSort('slug')"
                field="slug"
                :sortField="sortField"
                :sortDirection="sortDirection"
              >
                {{ $t('common.slug') }}
              </TableHeaderCell>
            </th>
            <th>{{ $t('organizations.owner') }}</th>
            <th>{{ $t('organizations.users_count') }}</th>
            <th>{{ $t('organizations.subscriptions_count') }}</th>
            <th>
              <TableHeaderCell
                @click="handleSort('status')"
                field="status"
                :sortField="sortField"
                :sortDirection="sortDirection"
              >
                {{ $t('common.status') }}
              </TableHeaderCell>
            </th>
            <th>{{ $t('organizations.trial_ends') }}</th>
            <th>
              <TableHeaderCell
                @click="handleSort('created_at')"
                field="created_at"
                :sortField="sortField"
                :sortDirection="sortDirection"
              >
                {{ $t('common.created_at') }}
              </TableHeaderCell>
            </th>
            <th>{{ $t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="organizations.loading" class="bg-white dark:bg-gray-800">
            <td colspan="10" class="px-6 py-12 text-center">
              <Spinner />
            </td>
          </tr>
          <tr
            v-else-if="organizations.data.length === 0"
            class="bg-white dark:bg-gray-800"
          >
            <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
              {{ $t('common.no_data') }}
            </td>
          </tr>
          <tr
            v-else
            v-for="organization in organizations.data"
            :key="organization.id"
            class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              {{ organization.id }}
            </td>
            <td class="px-6 py-4">
              <div class="flex-shrink-0 h-10 w-10">
                <img
                  v-if="organization.logo"
                  :src="organization.logo"
                  :alt="organization.name"
                  class="h-10 w-10 rounded-full object-cover"
                />
                <div
                  v-else
                  class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center"
                >
                  <i class="ti ti-building text-gray-400"></i>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
              {{ organization.name }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
              {{ organization.slug }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              {{ organization.owner?.name || '-' }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                {{ organization.users_count || 0 }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                {{ organization.active_subscriptions_count || 0 }}
              </span>
            </td>
            <td class="px-6 py-4">
              <button
                @click="toggleStatus(organization)"
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors',
                  organization.status === 'active'
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                ]"
              >
                {{ organization.status === 'active' ? $t('common.active') : $t('organizations.suspended') }}
              </button>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
              {{ organization.trial_ends_at ? formatDate(organization.trial_ends_at) : '-' }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
              {{ formatDate(organization.created_at) }}
            </td>
            <td class="px-6 py-4 text-sm font-medium">
              <div class="flex items-center space-x-2">
                <router-link
                  :to="`/organizations/${organization.uuid}/edit`"
                  class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  <i class="ti ti-edit"></i>
                </router-link>
                <button
                  @click="confirmDelete(organization)"
                  class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                >
                  <i class="ti ti-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <Pagination
      v-if="organizations.meta && organizations.meta.last_page > 1"
      :meta="organizations.meta"
      @page-change="handlePageChange"
    />

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      :show="showDeleteDialog"
      :title="$t('common.confirm_delete')"
      :message="$t('common.confirm_delete_message')"
      @confirm="deleteOrganization"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useOrganizationStore } from '@/stores/organization'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import debounce from 'lodash.debounce'
import TableHeaderCell from '@/components/Table/TableHeaderCell.vue'
import Pagination from '@/components/core/Pagination.vue'
import Spinner from '@/components/core/Spinner.vue'
import ConfirmDialog from '@/components/core/ConfirmDialog.vue'

const { t } = useI18n()
const toast = useToast()
const organizationStore = useOrganizationStore()

// Reactive data
const searchQuery = ref('')
const statusFilter = ref('')
const perPage = ref(10)
const sortField = ref('id')
const sortDirection = ref('desc')
const showDeleteDialog = ref(false)
const organizationToDelete = ref(null)

// Computed
const organizations = computed(() => organizationStore.organizations)

// Debounced search
const debouncedSearch = debounce(() => {
  applyFilters()
}, 500)

// Methods
const applyFilters = () => {
  organizationStore.getOrganizations({
    search: searchQuery.value,
    status: statusFilter.value,
    per_page: perPage.value,
    sort: sortField.value,
    direction: sortDirection.value,
  })
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  perPage.value = 10
  sortField.value = 'id'
  sortDirection.value = 'desc'
  applyFilters()
}

const handleSort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'desc'
  }
  applyFilters()
}

const handlePageChange = (page) => {
  organizationStore.getOrganizations({
    page,
    search: searchQuery.value,
    status: statusFilter.value,
    per_page: perPage.value,
    sort: sortField.value,
    direction: sortDirection.value,
  })
}

const toggleStatus = async (organization) => {
  try {
    const newStatus = organization.status === 'active' ? 'suspended' : 'active'
    await organizationStore.updateOrganizationStatus(organization.uuid, newStatus)
    toast.success(t('common.status_updated_success'))
    applyFilters()
  } catch (error) {
    toast.error(t('common.error_updating'))
  }
}

const confirmDelete = (organization) => {
  organizationToDelete.value = organization
  showDeleteDialog.value = true
}

const deleteOrganization = async () => {
  try {
    await organizationStore.deleteOrganization(organizationToDelete.value.uuid)
    toast.success(t('common.deleted_success'))
    showDeleteDialog.value = false
    organizationToDelete.value = null
    applyFilters()
  } catch (error) {
    toast.error(t('common.error_deleting'))
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

// Lifecycle
onMounted(() => {
  applyFilters()
})
</script>