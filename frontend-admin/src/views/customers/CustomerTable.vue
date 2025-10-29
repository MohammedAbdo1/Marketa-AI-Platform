<template>
  <div class="bg-white p-4 rounded-lg shadow animate-fade-in-down">
    
    <!-- Advanced Controls: البحث، عدد الصفوف، الفلاتر -->
    <div class="row mb-2 justify-content-between">
      <!-- Left Side: Filters -->
      <div class="col-md-8">
        <div class="row">
          <!-- Filter by Plan -->
          <div class="col-md-4">
            <CustomInput
              type="select"
              v-model="filters.plan_id"
              @change="updateFilters"
              :selectOptions="planFilterOptions"
              :placeholder="t('customers.filter_by_plan')"
            />
          </div>

          <!-- Filter by Subscription Status -->
          <div class="col-md-4">
            <CustomInput
              type="select"
              v-model="filters.subscription_status"
              @change="updateFilters"
              :selectOptions="statusFilterOptions"
              :placeholder="t('customers.filter_by_status')"
            />
          </div>
        </div>
      </div>

      <!-- Right Side: Search & Per Page -->
      <div class="col-md-4">
        <div class="row">
          <div class="col-md-6">
            <CustomInput
              type="select"
              v-model="filters.per_page"
              @change="updatePerPage"
              :selectOptions="[
                { key: 5, text: '5' }, 
                { key: 10, text: '10' }, 
                { key: 20, text: '20' }, 
                { key: 50, text: '50' }
              ]"
              :placeholder="t('common.per_page')"
            />
          </div>

          <div class="col-md-6">
            <CustomInput
              v-model="filters.search"
              name="search"
              :placeholder="t('common.search')"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- الجدول -->
    <div class="table-responsive">
      <table class="table table-hover table-centered mb-0">
        <thead class="table-light">
          <tr>
            <th>
              <TableHeaderCell 
                @click="sortCustomers('id')" 
                field="id" 
                :sortField="filters.sort" 
                :sortDirection="filters.direction"
              >
                {{ t('common.id') }}
              </TableHeaderCell>
            </th>
            <th>
              <TableHeaderCell 
                @click="sortCustomers('name')" 
                field="name" 
                :sortField="filters.sort" 
                :sortDirection="filters.direction"
              >
                {{ t('customers.name') }}
              </TableHeaderCell>
            </th>
            <th>
              <TableHeaderCell 
                @click="sortCustomers('email')" 
                field="email" 
                :sortField="filters.sort" 
                :sortDirection="filters.direction"
              >
                {{ t('customers.email') }}
              </TableHeaderCell>
            </th>
            <th>
              <TableHeaderCell 
                field="organization" 
                :sortField="filters.sort" 
                :sortDirection="filters.direction"
              >
                {{ t('customers.organization') }}
              </TableHeaderCell>
            </th>
            <th>
              <TableHeaderCell 
                field="plan" 
                :sortField="filters.sort" 
                :sortDirection="filters.direction"
              >
                {{ t('customers.plan') }}
              </TableHeaderCell>
            </th>
            <th>
              <TableHeaderCell 
                field="subscription_status" 
                :sortField="filters.sort" 
                :sortDirection="filters.direction"
              >
                {{ t('customers.subscription_status') }}
              </TableHeaderCell>
            </th>
            <th>
              <TableHeaderCell 
                field="tokens_usage" 
                :sortField="filters.sort" 
                :sortDirection="filters.direction"
              >
                {{ t('customers.tokens_usage') }}
              </TableHeaderCell>
            </th>
            <th>
              <TableHeaderCell 
                @click="sortCustomers('last_login_at')" 
                field="last_login_at" 
                :sortField="filters.sort" 
                :sortDirection="filters.direction"
              >
                {{ t('customers.last_activity') }}
              </TableHeaderCell>
            </th>
            <th>
              <TableHeaderCell 
                @click="sortCustomers('status')" 
                field="status" 
                :sortField="filters.sort" 
                :sortDirection="filters.direction"
              >
                {{ t('customers.status') }}
              </TableHeaderCell>
            </th>
            <th>{{ t('common.actions') }}</th>
          </tr>
        </thead>

        <tbody v-if="customers.loading || !customers.data.length">
          <tr>
            <td colspan="10">
              <Spinner v-if="customers.loading"/>
              <p v-else class="text-center py-8 text-gray-700">
                {{ t('common.no_data') }}
              </p>
            </td>
          </tr>
        </tbody>

        <tbody v-else>
          <tr v-for="customer in customers.data" :key="customer.id">
            <td>{{ customer.id }}</td>
            <td>
              <div class="d-flex align-items-center">
                <img 
                  v-if="customer.avatar" 
                  :src="customer.avatar" 
                  alt="avatar" 
                  class="rounded-circle me-2" 
                  style="width: 32px; height: 32px;"
                />
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                     v-else
                     style="width: 32px; height: 32px; font-size: 14px;">
                  {{ customer.name?.charAt(0)?.toUpperCase() }}
                </div>
                <span>{{ customer.name }}</span>
              </div>
            </td>
            <td>{{ customer.email }}</td>
            <td>
              <span v-if="customer.organization" class="badge bg-info">
                {{ customer.organization.name }}
              </span>
              <span v-else class="text-muted">-</span>
            </td>
            <td>
              <span v-if="customer.active_subscription?.plan" class="badge bg-success">
                {{ customer.active_subscription.plan.name_en }}
              </span>
              <span v-else class="badge bg-secondary">{{ t('customers.no_plan') }}</span>
            </td>
            <td>
              <span 
                v-if="customer.active_subscription" 
                :class="getSubscriptionStatusClass(customer.active_subscription.status)"
              >
                {{ t(`customers.status_${customer.active_subscription.status}`) }}
              </span>
              <span v-else class="badge bg-secondary">{{ t('customers.no_subscription') }}</span>
            </td>
            <td>
              <div v-if="customer.active_subscription?.plan" style="min-width: 150px;">
                <div class="d-flex justify-content-between mb-1">
                  <small>{{ getTokensUsed(customer) }} / {{ customer.active_subscription.plan.tokens_limit }}</small>
                  <small>{{ getTokensPercentage(customer) }}%</small>
                </div>
                <div class="progress" style="height: 6px;">
                  <div 
                    class="progress-bar" 
                    :class="getProgressBarClass(customer)"
                    :style="{ width: getTokensPercentage(customer) + '%' }"
                  ></div>
                </div>
              </div>
              <span v-else class="text-muted">-</span>
            </td>
            <td>
              <small class="text-muted">{{ formatDate(customer.last_login_at) }}</small>
            </td>
            <td>
              <div class="form-check form-switch">
                <input 
                  class="form-check-input cursor-pointer" 
                  type="checkbox" 
                  role="switch" 
                  :id="`switch-${customer.id}`"
                  :checked="customer.status === 'active'"
                  @change="toggleStatus(customer)"
                  :disabled="!can('edit_user')"
                >
                <label class="form-check-label cursor-pointer" :for="`switch-${customer.id}`">
                  {{ customer.status === 'active' ? t('common.active') : t('common.inactive') }}
                </label>
              </div>
            </td>
            <td>
              <Menu as="div" class="relative inline-block text-left">
                <div>
                  <MenuButton class="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <EllipsisVerticalIcon class="-mr-1 h-5 w-5 text-gray-400" aria-hidden="true" />
                  </MenuButton>
                </div>

                <transition 
                  enter-active-class="transition ease-out duration-100" 
                  enter-from-class="transform opacity-0 scale-95" 
                  enter-to-class="transform opacity-100 scale-100" 
                  leave-active-class="transition ease-in duration-75" 
                  leave-from-class="transform opacity-100 scale-100" 
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <MenuItems class="absolute left-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                    <div class="py-1">
                      <MenuItem v-slot="{ active }">
                        <router-link 
                          :to="{ name: 'customers.details', params: { uuid: customer.uuid } }" 
                          :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']"
                        >
                          <i class="mdi mdi-eye me-2"></i>
                          {{ t('customers.view_details') }}
                        </router-link>
                      </MenuItem>
                      <MenuItem v-if="can('edit_user')" v-slot="{ active }">
                        <router-link 
                          :to="{ name: 'customers.edit', params: { uuid: customer.uuid } }" 
                          :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']"
                        >
                          <i class="mdi mdi-pencil me-2"></i>
                          {{ t('common.edit') }}
                        </router-link>
                      </MenuItem>
                      <MenuItem v-if="can('delete_user')" v-slot="{ active }">
                        <a 
                          @click="confirmDelete(customer.uuid)" 
                          :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm cursor-pointer']"
                        >
                          <i class="mdi mdi-delete me-2"></i>
                          {{ t('common.delete') }}
                        </a>
                      </MenuItem>
                    </div>
                  </MenuItems>
                </transition>
              </Menu>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <Pagination 
      v-if="customers.meta && customers.meta.total" 
      :meta="customers.meta" 
      @page-change="pageChange"
    />

    <!-- Confirm Delete Dialog -->
    <ConfirmDialog
      :show="showConfirm"
      :title="t('common.confirm_delete')"
      :message="t('customers.confirm_delete_message')"
      @onConfirmDialog="deleteCustomer"
      @onCancelDialog="showConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCustomerStore } from '@/stores/customer';
import { usePlanStore } from '@/stores/plan';
import { useAuthStore } from '@/stores/auth';
import Spinner from '@/components/core/Spinner.vue';
import CustomInput from '@/components/core/CustomInput.vue';
import TableHeaderCell from '@/components/Table/TableHeaderCell.vue';
import Pagination from '@/components/core/Pagination.vue';
import ConfirmDialog from '@/components/core/ConfirmDialog.vue';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { EllipsisVerticalIcon } from '@heroicons/vue/20/solid';
import { useToast } from "vue-toastification";
import debounce from 'lodash.debounce';

const { t } = useI18n();
const toast = useToast();
const authStore = useAuthStore();
const can = computed(() => authStore.can);
const customerStore = useCustomerStore();
const planStore = usePlanStore();
const customers = computed(() => customerStore.customers);
const filters = computed(() => customerStore.customers.filters);

const showConfirm = ref(false);
const deleteUuid = ref(null);

const planFilterOptions = ref([
  { key: '', text: t('customers.all_plans') }
]);

const statusFilterOptions = ref([
  { key: '', text: t('customers.all_statuses') },
  { key: 'active', text: t('customers.status_active') },
  { key: 'trial', text: t('customers.status_trial') },
  { key: 'expired', text: t('customers.status_expired') },
]);

onMounted(async () => {
  customerStore.getCustomers();
  
  // Load plans for filter
  try {
    await planStore.fetchPlans();
    planFilterOptions.value = [
      { key: '', text: t('customers.all_plans') },
      ...planStore.plans.map(plan => ({
        key: plan.id,
        text: plan.name_en
      }))
    ];
  } catch (error) {
    console.error('Error loading plans:', error);
  }
});

// Watch for search changes with debounce
const debouncedSearch = debounce(() => {
  customerStore.getCustomers({
    search: filters.value.search,
    per_page: filters.value.per_page,
    sort: filters.value.sort,
    direction: filters.value.direction,
    plan_id: filters.value.plan_id,
    subscription_status: filters.value.subscription_status,
  });
}, 500);

watch(() => filters.value.search, () => {
  debouncedSearch();
});

function updatePerPage() {
  customerStore.getCustomers({
    per_page: filters.value.per_page,
    search: filters.value.search,
    sort: filters.value.sort,
    direction: filters.value.direction,
    plan_id: filters.value.plan_id,
    subscription_status: filters.value.subscription_status,
  });
}

function updateFilters() {
  customerStore.getCustomers({
    per_page: filters.value.per_page,
    search: filters.value.search,
    sort: filters.value.sort,
    direction: filters.value.direction,
    plan_id: filters.value.plan_id,
    subscription_status: filters.value.subscription_status,
  });
}

function sortCustomers(field) {
  const newDirection = filters.value.sort === field && filters.value.direction === 'asc' ? 'desc' : 'asc';
  filters.value.sort = field;
  filters.value.direction = newDirection;
  customerStore.getCustomers({
    sort: field,
    direction: newDirection,
    per_page: filters.value.per_page,
    search: filters.value.search,
    plan_id: filters.value.plan_id,
    subscription_status: filters.value.subscription_status,
  });
}

function pageChange(url) {
  customerStore.getCustomers({ url });
}

function confirmDelete(uuid) {
  deleteUuid.value = uuid;
  showConfirm.value = true;
}

async function deleteCustomer() {
  try {
    await customerStore.deleteCustomer(deleteUuid.value);
    toast.success(t('customers.customer_deleted_success'));
    showConfirm.value = false;
    customerStore.getCustomers();
  } catch (error) {
    toast.error(t('common.error_deleting'));
  }
}

async function toggleStatus(customer) {
  try {
    const newStatus = customer.status === 'active' ? 'inactive' : 'active';
    await customerStore.updateCustomerStatus(customer.uuid, newStatus);
    toast.success(t('customers.status_updated_success'));
    customerStore.getCustomers();
  } catch (error) {
    toast.error(t('common.error_updating'));
    customerStore.getCustomers();
  }
}

function formatDate(dateString) {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString();
}

function getTokensUsed(customer) {
  // TODO: Get from actual usage data when available
  return customer.tokens_used || 0;
}

function getTokensPercentage(customer) {
  if (!customer.active_subscription?.plan?.tokens_limit) return 0;
  const used = getTokensUsed(customer);
  const limit = customer.active_subscription.plan.tokens_limit;
  return Math.min(Math.round((used / limit) * 100), 100);
}

function getProgressBarClass(customer) {
  const percentage = getTokensPercentage(customer);
  if (percentage >= 90) return 'bg-danger';
  if (percentage >= 70) return 'bg-warning';
  return 'bg-success';
}

function getSubscriptionStatusClass(status) {
  const classes = {
    active: 'badge bg-success',
    trial: 'badge bg-info',
    expired: 'badge bg-danger',
    cancelled: 'badge bg-secondary',
  };
  return classes[status] || 'badge bg-secondary';
}
</script>

