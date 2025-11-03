<template>
  <div class="dashboard-home">
    <div class="welcome-header">
      <h1>{{ $t('dashboard.welcome') }}, {{ user?.name }}! 👋</h1>
      <p>{{ $t('dashboard.overview') }}</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="bx bx-bullseye"></i>
        </div>
        <div class="stat-info">
          <h3>{{ stats.totalCampaigns }}</h3>
          <p>{{ $t('dashboard.total_campaigns') }}</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="bx bx-rocket"></i>
        </div>
        <div class="stat-info">
          <h3>{{ stats.activeCampaigns }}</h3>
          <p>{{ $t('dashboard.active_campaigns') }}</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="bx bx-file"></i>
        </div>
        <div class="stat-info">
          <h3>{{ stats.postsCreated }}</h3>
          <p>{{ $t('dashboard.posts_created') }}</p>
        </div>
      </div>
      
      <div class="stat-card usage-card">
        <div class="stat-icon">
          <i class="bx bx-bar-chart"></i>
        </div>
        <div class="stat-info">
          <h3>{{ stats.remaining }}/{{ stats.limit }}</h3>
          <p>{{ $t('dashboard.requests_remaining') }}</p>
          <div class="progress mt-2">
            <div 
              class="progress-bar" 
              :style="{ width: usagePercentage + '%' }"
              :class="{ 'bg-danger': usagePercentage > 80 }"
            ></div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions-section">
      <h3>{{ $t('dashboard.quick_actions') }}</h3>
      <div class="actions-grid">
        <router-link to="/dashboard/campaigns/create" class="action-card">
          <i class="bx bx-plus-circle"></i>
          <span>{{ $t('dashboard.create_campaign') }}</span>
        </router-link>
        
        <router-link to="/dashboard/brands" class="action-card">
          <i class="bx bx-palette"></i>
          <span>{{ $t('dashboard.manage_brands') }}</span>
        </router-link>
        
        <router-link to="/pricing" class="action-card">
          <i class="bx bx-star"></i>
          <span>{{ $t('dashboard.view_plans') }}</span>
        </router-link>
      </div>
    </div>
    
    <!-- Recent Campaigns -->
    <div class="recent-section">
      <h3>{{ $t('dashboard.recent_campaigns') }}</h3>
      <div class="empty-state" v-if="recentCampaigns.length === 0">
        <i class="bx bx-bullseye"></i>
        <p>{{ $t('common.no_data') }}</p>
        <router-link to="/dashboard/campaigns/create" class="btn btn-primary">
          {{ $t('dashboard.create_campaign') }}
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const user = computed(() => authStore.user)

const stats = ref({
  totalCampaigns: 0,
  activeCampaigns: 0,
  postsCreated: 0,
  remaining: 15,
  limit: 20
})

const usagePercentage = computed(() => 
  ((stats.value.limit - stats.value.remaining) / stats.value.limit) * 100
)

const recentCampaigns = ref([])

onMounted(async () => {
  // TODO: Load real stats from API
  // await loadDashboardStats()
})
</script>

<style scoped>
.dashboard-home {
  animation: fadeIn var(--duration-slow) var(--ease-out);
}

.welcome-header {
  margin-bottom: var(--space-8);
}

.welcome-header h1 {
  font-size: var(--text-4xl);
  font-weight: var(--font-bold);
  margin-bottom: var(--space-2);
  color: var(--color-text-primary);
}

.welcome-header p {
  color: var(--color-text-secondary);
  font-size: var(--text-base);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--space-6);
  margin-bottom: var(--space-12);
}

.stat-card {
  background: var(--color-bg-primary);
  padding: var(--space-6);
  border-radius: var(--radius-card);
  display: flex;
  align-items: center;
  gap: var(--space-4);
  box-shadow: var(--shadow-card);
  border: 1px solid var(--color-border-light);
  transition: var(--transition-all);
}

.stat-card:hover {
  box-shadow: var(--shadow-card-hover);
  transform: translateY(-2px);
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: var(--radius-lg);
  background: var(--color-brand-primary-light);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-brand-primary);
  font-size: var(--text-3xl);
  flex-shrink: 0;
}

.stat-info h3 {
  font-size: var(--text-3xl);
  font-weight: var(--font-bold);
  margin-bottom: var(--space-1);
  color: var(--color-text-primary);
}

.stat-info p {
  color: var(--color-text-secondary);
  font-size: var(--text-sm);
  margin: 0;
}

.quick-actions-section,
.recent-section {
  margin-bottom: var(--space-12);
}

.quick-actions-section h3,
.recent-section h3 {
  margin-bottom: var(--space-6);
  font-size: var(--text-2xl);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--space-4);
}

.action-card {
  background: var(--color-bg-primary);
  padding: var(--space-8);
  border-radius: var(--radius-card);
  text-align: center;
  text-decoration: none;
  color: var(--color-text-primary);
  transition: var(--transition-all);
  box-shadow: var(--shadow-card);
  border: 1px solid var(--color-border-light);
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-card-hover);
  color: var(--color-brand-primary);
}

.action-card i {
  font-size: 3rem;
  margin-bottom: var(--space-4);
  display: block;
  color: var(--color-brand-primary);
}

.empty-state {
  background: var(--color-bg-secondary);
  padding: var(--space-16) var(--space-8);
  border-radius: var(--radius-card);
  text-align: center;
  border: 1px dashed var(--color-border-medium);
}

.empty-state i {
  font-size: 4rem;
  color: var(--color-text-tertiary);
  margin-bottom: var(--space-4);
}

.empty-state p {
  color: var(--color-text-secondary);
  font-size: var(--text-sm);
  margin-bottom: var(--space-6);
}
</style>

