<template>
  <header class="dashboard-header">
    <div class="header-left">
      <button @click="$emit('toggle-sidebar')" class="btn-toggle-sidebar">
        <i class="bx bx-menu"></i>
      </button>
    </div>
    
    <div class="header-right">
      <!-- Usage Bar -->
      <div class="usage-indicator">
        <div class="usage-text">
          <small>{{ remaining }}/{{ limit }} {{ $t('dashboard.requests_remaining') }}</small>
        </div>
        <div class="usage-bar">
          <div 
            class="usage-fill" 
            :style="{ width: usagePercentage + '%' }"
            :class="{ 'warning': usagePercentage > 80 }"
          ></div>
        </div>
      </div>
      
      <!-- Language Switcher -->
      <button @click="toggleLanguage" class="btn-icon">
        <i class="bx bx-globe"></i>
      </button>
      
      <!-- User Menu -->
      <div class="user-menu" @click="showDropdown = !showDropdown">
        <div class="user-avatar">
          <i class="bx bx-user"></i>
        </div>
        <span class="user-name">{{ user?.name }}</span>
        <i class="bx bx-chevron-down"></i>
        
        <div class="dropdown-menu" v-if="showDropdown">
          <router-link to="/dashboard/profile" class="dropdown-item">
            <i class="bx bx-user"></i> {{ $t('sidebar.profile') }}
          </router-link>
          <router-link to="/dashboard/usage" class="dropdown-item">
            <i class="bx bx-bar-chart"></i> {{ $t('sidebar.usage') }}
          </router-link>
          <div class="dropdown-divider"></div>
          <button @click="handleLogout" class="dropdown-item">
            <i class="bx bx-log-out"></i> {{ $t('nav.logout', 'Logout') }}
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { setLocale } from '@/i18n'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const { locale: currentLocale } = useI18n()
const authStore = useAuthStore()
const router = useRouter()

const showDropdown = ref(false)
const user = computed(() => authStore.user)

// Mock data - will be replaced with real data
const remaining = ref(15)
const limit = ref(20)
const usagePercentage = computed(() => ((limit.value - remaining.value) / limit.value) * 100)

const toggleLanguage = () => {
  const newLocale = currentLocale.value === 'ar' ? 'en' : 'ar'
  setLocale(newLocale)
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/auth/login')
}
</script>

<style scoped>
.dashboard-header {
  background: white;
  padding: 1rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #e9ecef;
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-left, .header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.btn-toggle-sidebar {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
}

.usage-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.usage-bar {
  width: 120px;
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
}

.usage-fill {
  height: 100%;
  background: #2383E2;
  transition: width 0.3s;
}

.usage-fill.warning {
  background: #ff6b6b;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.5rem;
}

.user-menu {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s;
}

.user-menu:hover {
  background: #f8f9fa;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #2383E2;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  min-width: 200px;
  margin-top: 0.5rem;
  padding: 0.5rem 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  text-decoration: none;
  color: inherit;
  transition: background 0.3s;
  border: none;
  background: none;
  width: 100%;
  text-align: start;
  cursor: pointer;
}

.dropdown-item:hover {
  background: #f8f9fa;
}

.dropdown-divider {
  height: 1px;
  background: #dee2e6;
  margin: 0.5rem 0;
}

/* Mobile */
@media (max-width: 768px) {
  .usage-indicator {
    display: none;
  }
  
  .user-name {
    display: none;
  }
}
</style>

