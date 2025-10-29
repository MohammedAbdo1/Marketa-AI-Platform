<template>
  <div>
    <!-- Breadcrumb -->
    <div class="flex items-center justify-between mb-3">
      <nav aria-label="breadcrumb" class="flex">
        <ol class="breadcrumb bg-transparent p-0 m-0">
          <li class="breadcrumb-item">
            <router-link to="/customers">{{ t('customers.title') }}</router-link>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            {{ customer?.name || t('customers.details') }}
          </li>
        </ol>
      </nav>
      <h1 class="text-3xl font-semibold">{{ t('customers.details') }}</h1>
    </div>

    <div v-if="loading" class="text-center py-8">
      <Spinner :text="t('common.loading')" />
    </div>

    <div v-else-if="customer" class="animate-fade-in-down">
      <div class="row">
        <!-- Customer Info Card -->
        <div class="col-xl-4">
          <div class="card">
            <div class="card-body">
              <div class="text-center">
                <div v-if="customer.avatar" class="mb-3">
                  <img 
                    :src="customer.avatar" 
                    alt="avatar" 
                    class="rounded-circle" 
                    style="width: 100px; height: 100px;"
                  />
                </div>
                <div v-else class="mb-3">
                  <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                       style="width: 100px; height: 100px; font-size: 40px;">
                    {{ customer.name?.charAt(0)?.toUpperCase() }}
                  </div>
                </div>
                <h4 class="mb-1">{{ customer.name }}</h4>
                <p class="text-muted mb-2">{{ customer.email }}</p>
                <p class="text-muted mb-3">{{ customer.phone || '-' }}</p>
                
                <div class="d-flex justify-content-center gap-2 mb-3">
                  <span 
                    class="badge" 
                    :class="customer.status === 'active' ? 'bg-success' : 'bg-secondary'"
                  >
                    {{ t(`common.${customer.status}`) }}
                  </span>
                  <span v-if="customer.organization" class="badge bg-info">
                    {{ customer.organization.name }}
                  </span>
                </div>

                <div class="text-start mt-4">
                  <p class="mb-2">
                    <i class="mdi mdi-calendar me-2"></i>
                    <strong>{{ t('customers.registration_date') }}:</strong>
                    {{ formatDate(customer.created_at) }}
                  </p>
                  <p class="mb-2">
                    <i class="mdi mdi-clock me-2"></i>
                    <strong>{{ t('customers.last_activity') }}:</strong>
                    {{ formatDate(customer.last_login_at) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Subscription & Usage -->
        <div class="col-xl-8">
          <!-- Subscription Card -->
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-3">
                <i class="mdi mdi-credit-card me-2"></i>
                {{ t('customers.subscription_info') }}
              </h5>
              
              <div v-if="customer.active_subscription">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="text-muted">{{ t('customers.current_plan') }}</label>
                    <h4 class="mb-0">{{ customer.active_subscription.plan?.name_en || '-' }}</h4>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="text-muted">{{ t('customers.subscription_status') }}</label>
                    <div>
                      <span 
                        class="badge fs-6" 
                        :class="getSubscriptionBadgeClass(customer.active_subscription.status)"
                      >
                        {{ t(`customers.status_${customer.active_subscription.status}`) }}
                      </span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="text-muted">{{ t('customers.start_date') }}</label>
                    <p class="mb-0">{{ formatDate(customer.active_subscription.start_date) }}</p>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="text-muted">{{ t('customers.end_date') }}</label>
                    <p class="mb-0">{{ formatDate(customer.active_subscription.end_date) }}</p>
                  </div>
                </div>
              </div>
              <div v-else>
                <div class="alert alert-warning">
                  <i class="mdi mdi-alert me-2"></i>
                  {{ t('customers.no_active_subscription') }}
                </div>
              </div>
            </div>
          </div>

          <!-- Usage Analytics Card -->
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-3">
                <i class="mdi mdi-chart-line me-2"></i>
                {{ t('customers.usage_analytics') }}
              </h5>
              
              <div v-if="customer.active_subscription?.plan">
                <!-- Tokens Usage -->
                <div class="mb-4">
                  <div class="d-flex justify-content-between mb-2">
                    <label class="text-muted">{{ t('customers.tokens_usage') }}</label>
                    <span class="fw-bold">
                      {{ getTokensUsed(customer) }} / {{ customer.active_subscription.plan.tokens_limit }}
                    </span>
                  </div>
                  <div class="progress" style="height: 20px;">
                    <div 
                      class="progress-bar" 
                      :class="getProgressBarClass(customer)"
                      :style="{ width: getTokensPercentage(customer) + '%' }"
                    >
                      {{ getTokensPercentage(customer) }}%
                    </div>
                  </div>
                </div>

                <!-- Statistics Row -->
                <div class="row">
                  <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded">
                      <i class="mdi mdi-bullhorn fs-2 text-primary"></i>
                      <h4 class="mt-2 mb-0">0</h4>
                      <p class="text-muted mb-0">{{ t('customers.campaigns_count') }}</p>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded">
                      <i class="mdi mdi-file-document fs-2 text-success"></i>
                      <h4 class="mt-2 mb-0">0</h4>
                      <p class="text-muted mb-0">{{ t('customers.posts_count') }}</p>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded">
                      <i class="mdi mdi-api fs-2 text-info"></i>
                      <h4 class="mt-2 mb-0">0</h4>
                      <p class="text-muted mb-0">{{ t('customers.api_calls') }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else>
                <div class="alert alert-info">
                  {{ t('customers.no_usage_data') }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Billing History & Activity Log -->
      <div class="row mt-3">
        <div class="col-xl-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-3">
                <i class="mdi mdi-receipt me-2"></i>
                {{ t('customers.billing_history') }}
              </h5>
              <div class="alert alert-info">
                {{ t('customers.no_billing_data') }}
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-6">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-3">
                <i class="mdi mdi-history me-2"></i>
                {{ t('customers.activity_log') }}
              </h5>
              <div class="alert alert-info">
                {{ t('customers.no_activity_data') }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCustomerStore } from '@/stores/customer'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from "vue-toastification"
import { Form, CustomInput } from "@/components/core"
import Spinner from '@/components/core/Spinner.vue'

const { t } = useI18n()
const customerStore = useCustomerStore()
const route = useRoute()
const router = useRouter()
const toast = useToast()

const loading = ref(false)
const customer = ref(null)

onMounted(async () => {
  loading.value = true
  try {
    customer.value = await customerStore.getCustomerDetails(route.params.uuid)
  } catch (error) {
    toast.error(t('common.error_loading'))
    router.push({ name: 'customers' })
  } finally {
    loading.value = false
  }
})

function formatDate(dateString) {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString();
}

function getTokensUsed(customer) {
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

function getSubscriptionBadgeClass(status) {
  const classes = {
    active: 'bg-success',
    trial: 'bg-info',
    expired: 'bg-danger',
    cancelled: 'bg-secondary',
  };
  return classes[status] || 'bg-secondary';
}
</script>

