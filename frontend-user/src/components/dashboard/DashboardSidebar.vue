<template>
  <aside class="dashboard-sidebar" :class="{ open: isOpen }">
    <div class="sidebar-header">
      <router-link to="/" class="logo">
        <h3>{{ $t('app.name') }}</h3>
      </router-link>
    </div>
    
    <nav class="sidebar-nav">
      <router-link to="/dashboard" class="nav-item" exact-active-class="active">
        <i class="bx bx-home"></i>
        <span>{{ $t('sidebar.dashboard') }}</span>
      </router-link>
      
      <router-link to="/dashboard/campaigns" class="nav-item">
        <i class="bx bx-bullseye"></i>
        <span>{{ $t('sidebar.campaigns') }}</span>
      </router-link>
      
      <router-link to="/dashboard/designs" class="nav-item">
        <i class="bx bx-layer"></i>
        <span>{{ $t('sidebar.designs') }}</span>
      </router-link>
      
      <router-link to="/dashboard/ai" class="nav-item">
        <i class="bx bx-sparkle"></i>
        <span>{{ $t('sidebar.ai_studio') }}</span>
      </router-link>
      
      <router-link to="/dashboard/brands" class="nav-item">
        <i class="bx bx-palette"></i>
        <span>{{ $t('sidebar.brands') }}</span>
      </router-link>
      
      <router-link to="/dashboard/usage" class="nav-item">
        <i class="bx bx-bar-chart"></i>
        <span>{{ $t('sidebar.usage') }}</span>
      </router-link>
      
      <div class="nav-divider"></div>
      
      <router-link to="/dashboard/profile" class="nav-item">
        <i class="bx bx-user"></i>
        <span>{{ $t('sidebar.profile') }}</span>
      </router-link>
      
      <router-link to="/dashboard/settings" class="nav-item">
        <i class="bx bx-cog"></i>
        <span>{{ $t('sidebar.settings') }}</span>
      </router-link>
      
      <div class="nav-divider"></div>
      
      <router-link to="/pricing" class="nav-item upgrade">
        <i class="bx bx-star"></i>
        <span>{{ $t('sidebar.upgrade') }}</span>
      </router-link>
    </nav>
  </aside>
  
  <!-- Mobile Overlay -->
  <div 
    v-if="isOpen" 
    class="sidebar-overlay" 
    @click="$emit('toggle')"
  ></div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
  isOpen: {
    type: Boolean,
    default: true
  }
})

defineEmits(['toggle'])

const { t } = useI18n()
</script>

<style scoped>
.dashboard-sidebar {
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  width: 250px;
  background: white;
  border-right: 1px solid #e9ecef;
  z-index: 1001;
  display: flex;
  flex-direction: column;
  transition: transform 0.3s;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
}

.sidebar-header .logo {
  text-decoration: none;
  color: #2383E2;
  font-weight: bold;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  text-decoration: none;
  color: #495057;
  border-radius: 8px;
  margin-bottom: 0.25rem;
  transition: all 0.3s;
  font-weight: 500;
}

.nav-item:hover {
  background: #f8f9fa;
  color: #2383E2;
}

.nav-item.active,
.nav-item.router-link-active {
  background: #2383E2;
  color: white;
}

.nav-item i {
  font-size: 1.25rem;
}

.nav-item.upgrade {
  background: #2383E2;
  color: white;
}

.nav-item.upgrade:hover {
  opacity: 0.9;
}

.nav-divider {
  height: 1px;
  background: #e9ecef;
  margin: 1rem 0;
}

.sidebar-overlay {
  display: none;
}

/* Mobile */
@media (max-width: 768px) {
  .dashboard-sidebar {
    transform: translateX(-100%);
  }
  
  .dashboard-sidebar.open {
    transform: translateX(0);
  }
  
  .sidebar-overlay {
    display: block;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
  }
}
</style>

