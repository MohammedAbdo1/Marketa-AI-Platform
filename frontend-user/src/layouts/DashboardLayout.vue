<template>
  <div class="dashboard-layout">
    <DashboardSidebar 
      :is-open="sidebarOpen" 
      :is-collapsed="sidebarCollapsed"
      @toggle="toggleSidebar"
      @toggle-collapse="toggleCollapse"
    />
    
    <div class="main-wrapper" :class="{ 'sidebar-open': sidebarOpen, 'sidebar-collapsed': sidebarCollapsed }">
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
import { ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import DashboardSidebar from '@/components/dashboard/DashboardSidebar.vue'
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue'

const { t } = useI18n()

// Sidebar state
const sidebarOpen = ref(window.innerWidth > 768)
const sidebarCollapsed = ref(false) // للـ Desktop collapse

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const toggleCollapse = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

// عند تغيير حجم الشاشة
const handleResize = () => {
  if (window.innerWidth > 768) {
    sidebarOpen.value = true
  } else {
    sidebarOpen.value = false
    sidebarCollapsed.value = false // reset على mobile
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background: var(--color-bg-primary);
}

.main-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  margin-left: var(--sidebar-width, 250px);
  transition: margin-left var(--transition-slow);
}

[dir="rtl"] .main-wrapper {
  margin-left: 0;
  margin-right: var(--sidebar-width, 250px);
}

/* Sidebar collapsed (64px) */
.main-wrapper.sidebar-collapsed {
  margin-left: var(--sidebar-width-collapsed, 64px);
}

[dir="rtl"] .main-wrapper.sidebar-collapsed {
  margin-left: 0;
  margin-right: var(--sidebar-width-collapsed, 64px);
}

.content {
  flex: 1;
  padding: var(--space-8) 0;
}

.dashboard-footer {
  padding: var(--space-4);
  text-align: center;
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  background: var(--color-bg-primary);
  border-top: 1px solid var(--color-border-light);
  margin-top: auto;
  position: sticky;
  bottom: 0;
  z-index: var(--z-sticky);
}

/* Tablet - Sidebar يصغر */
@media (min-width: 769px) and (max-width: 1024px) {
  .main-wrapper:not(.sidebar-collapsed) {
    margin-left: 64px;
  }
  
  [dir="rtl"] .main-wrapper:not(.sidebar-collapsed) {
    margin-left: 0;
    margin-right: 64px;
  }
}

/* Mobile - Sidebar يختفي تماماً (مثل ChatGPT) */
@media (max-width: 768px) {
  .main-wrapper,
  [dir="rtl"] .main-wrapper {
    margin-left: 0 !important;
    margin-right: 0 !important;
  }
  
  .content {
    padding: var(--space-4) 0;
  }
}
</style>

