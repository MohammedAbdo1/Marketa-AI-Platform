<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <!-- Filters -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
            <option value="inactive">{{ $t('common.inactive') }}</option>
          </select>
        </div>

        <!-- Popular Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $t('plans.is_popular') }}
          </label>
          <select
            v-model="popularFilter"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            @change="applyFilters"
          >
            <option value="">{{ $t('common.all') }}</option>
            <option value="yes">{{ $t('common.yes') }}</option>
            <option value="no">{{ $t('common.no') }}</option>
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
            <th>
              <TableHeaderCell
                @click="handleSort('name_ar')"
                field="name_ar"
                :sortField="sortField"
                :sortDirection="sortDirection"
              >
                {{ $t('plans.name_ar') }}
              </TableHeaderCell>
            </th>
            <th>
              <TableHeaderCell
                @click="handleSort('name_en')"
                field="name_en"
                :sortField="sortField"
                :sortDirection="sortDirection"
              >
                {{ $t('plans.name_en') }}
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
            <th>{{ $t('plans.price_monthly') }}</th>
            <th>{{ $t('plans.price_yearly') }}</th>
            <th>{{ $t('plans.tokens_limit') }}</th>
            <th>{{ $t('plans.features_count') }}</th>
            <th>{{ $t('plans.subscribers_count') }}</th>
            <th>{{ $t('plans.is_popular') }}</th>
            <th>
              <TableHeaderCell
                @click="handleSort('is_active')"
                field="is_active"
                :sortField="sortField"
                :sortDirection="sortDirection"
              >
                {{ $t('common.status') }}
              </TableHeaderCell>
            </th>
            <th>
              <TableHeaderCell
                @click="handleSort('sort_order')"
                field="sort_order"
                :sortField="sortField"
                :sortDirection="sortDirection"
              >
                {{ $t('plans.sort_order') }}
              </TableHeaderCell>
            </th>
            <th>{{ $t('common.actions') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="plans.loading" class="bg-white dark:bg-gray-800">
            <td colspan="13" class="px-6 py-12 text-center">
              <Spinner />
            </td>
          </tr>
          <tr
            v-else-if="plans.data.length === 0"
            class="bg-white dark:bg-gray-800"
          >
            <td colspan="13" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
              {{ $t('common.no_data') }}
            </td>
          </tr>
          <tr
            v-else
            v-for="plan in plans.data"
            :key="plan.id"
            class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              {{ plan.id }}
            </td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
              {{ plan.name_ar }}
            </td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
              {{ plan.name_en }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
              {{ plan.slug }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              <span class="font-medium">{{ plan.price_monthly }} {{ $t('common.currency') }}</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              <div class="flex flex-col">
                <span class="font-medium">{{ plan.price_yearly }} {{ $t('common.currency') }}</span>
                <span v-if="plan.price_yearly && plan.price_monthly" class="text-xs text-green-600 dark:text-green-400">
                  {{ calculateSavings(plan.price_monthly, plan.price_yearly) }}% {{ $t('plans.savings') }}
                </span>
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                {{ formatNumber(plan.tokens_limit) }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                {{ plan.features_count || 0 }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                {{ plan.subscribers_count || 0 }}
              </span>
            </td>
            <td class="px-6 py-4">
              <button
                @click="togglePopular(plan)"
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors',
                  plan.is_popular
                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                ]"
              >
                <i v-if="plan.is_popular" class="ti ti-star-filled mr-1"></i>
                <i v-else class="ti ti-star mr-1"></i>
                {{ plan.is_popular ? $t('common.yes') : $t('common.no') }}
              </button>
            </td>
            <td class="px-6 py-4">
              <button
                @click="toggleStatus(plan)"
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors',
                  plan.is_active
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                ]"
              >
                {{ plan.is_active ? $t('common.active') : $t('common.inactive') }}
              </button>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
              {{ plan.sort_order }}
            </td>
            <td class="px-6 py-4 text-sm font-medium">
              <div class="flex items-center space-x-2">
                <router-link
                  :to="`/plans/${plan.id}/edit`"
                  class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  <i class="ti ti-edit"></i>
                </router-link>
                <button
                  @click="confirmDelete(plan)"
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
      v-if="plans.meta && plans.meta.last_page > 1"
      :meta="plans.meta"
      @page-change="handlePageChange"
    />

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      :show="showDeleteDialog"
      :title="$t('common.confirm_delete')"
      :message="$t('common.confirm_delete_message')"
      @confirm="deletePlan"
      @cancel="showDeleteDialog = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { usePlanStore } from '@/stores/plan'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import debounce from 'lodash.debounce'
import TableHeaderCell from '@/components/Table/TableHeaderCell.vue'
import Pagination from '@/components/core/Pagination.vue'
import Spinner from '@/components/core/Spinner.vue'
import ConfirmDialog from '@/components/core/ConfirmDialog.vue'

const { t } = useI18n()
const toast = useToast()
const planStore = usePlanStore()

// Reactive data
const searchQuery = ref('')
const statusFilter = ref('')
const popularFilter = ref('')
const perPage = ref(10)
const sortField = ref('sort_order')
const sortDirection = ref('asc')
const showDeleteDialog = ref(false)
const planToDelete = ref(null)

// Computed
const plans = computed(() => planStore.plans)

// Debounced search
const debouncedSearch = debounce(() => {
  applyFilters()
}, 500)

// Methods
const applyFilters = () => {
  planStore.getPlans({
    search: searchQuery.value,
    status: statusFilter.value,
    popular: popularFilter.value,
    per_page: perPage.value,
    sort: sortField.value,
    direction: sortDirection.value,
  })
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  popularFilter.value = ''
  perPage.value = 10
  sortField.value = 'sort_order'
  sortDirection.value = 'asc'
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
  planStore.getPlans({
    page,
    search: searchQuery.value,
    status: statusFilter.value,
    popular: popularFilter.value,
    per_page: perPage.value,
    sort: sortField.value,
    direction: sortDirection.value,
  })
}

const toggleStatus = async (plan) => {
  try {
    const newStatus = !plan.is_active
    await planStore.updatePlanStatus(plan.id, newStatus)
    toast.success(t('common.status_updated_success'))
    applyFilters()
  } catch (error) {
    toast.error(t('common.error_updating'))
  }
}

const togglePopular = async (plan) => {
  try {
    const newPopular = !plan.is_popular
    await planStore.togglePopular(plan.id, newPopular)
    toast.success(t('common.updated_success'))
    applyFilters()
  } catch (error) {
    toast.error(t('common.error_updating'))
  }
}

const confirmDelete = (plan) => {
  planToDelete.value = plan
  showDeleteDialog.value = true
}

const deletePlan = async () => {
  try {
    await planStore.deletePlan(planToDelete.value.id)
    toast.success(t('common.deleted_success'))
    showDeleteDialog.value = false
    planToDelete.value = null
    applyFilters()
  } catch (error) {
    toast.error(t('common.error_deleting'))
  }
}

const calculateSavings = (monthly, yearly) => {
  if (!monthly || !yearly) return 0
  const monthlyYearly = monthly * 12
  const savings = ((monthlyYearly - yearly) / monthlyYearly) * 100
  return Math.round(savings)
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num)
}

// Lifecycle
onMounted(() => {
  applyFilters()
})
</script>