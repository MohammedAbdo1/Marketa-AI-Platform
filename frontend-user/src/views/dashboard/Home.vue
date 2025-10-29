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
  animation: fadeIn 0.6s ease-out;
}

.welcome-header {
  margin-bottom: 2rem;
}

.welcome-header h1 {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.welcome-header p {
  color: #718096;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  background: #2383E2;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2rem;
}

.stat-info h3 {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.stat-info p {
  color: #718096;
  margin: 0;
}

.quick-actions-section,
.recent-section {
  margin-bottom: 3rem;
}

.quick-actions-section h3,
.recent-section h3 {
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.action-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0,0,0,0.1);
  color: var(--primary-color);
}

.action-card i {
  font-size: 3rem;
  margin-bottom: 1rem;
  display: block;
}

.empty-state {
  background: white;
  padding: 4rem 2rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.empty-state i {
  font-size: 4rem;
  color: #cbd5e0;
  margin-bottom: 1rem;
}

.empty-state p {
  color: #718096;
  margin-bottom: 1.5rem;
}
</style>

