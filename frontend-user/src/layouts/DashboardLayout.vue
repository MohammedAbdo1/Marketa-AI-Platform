<template>
  <div class="dashboard-layout">
    <DashboardSidebar :is-open="sidebarOpen" @toggle="toggleSidebar" />
    
    <div class="main-wrapper" :class="{ 'sidebar-open': sidebarOpen }">
      <DashboardHeader @toggle-sidebar="toggleSidebar" />
      
      <main class="content">
        <div class="container-fluid">
          <router-view />
        </div>
      </main>
      
      <footer class="dashboard-footer">
        <p>&copy; 2025 {{ $t('app.name') }}. {{ $t('footer.all_rights_reserved') }}</p>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import DashboardSidebar from '@/components/dashboard/DashboardSidebar.vue'
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue'

const { t } = useI18n()
const sidebarOpen = ref(true)

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background: #f8f9fa;
}

.main-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  margin-left: 250px;
  transition: margin-left 0.3s;
}

.main-wrapper.sidebar-open {
  margin-left: 250px;
}

.content {
  flex: 1;
  padding: 2rem 0;
}

.dashboard-footer {
  padding: 1rem;
  text-align: center;
  font-size: 0.875rem;
  color: #6c757d;
  background: white;
  border-top: 1px solid #dee2e6;
  margin-top: auto;
  position: sticky;
  bottom: 0;
  z-index: 10;
}

/* Mobile */
@media (max-width: 768px) {
  .main-wrapper {
    margin-left: 0;
  }
}
</style>

