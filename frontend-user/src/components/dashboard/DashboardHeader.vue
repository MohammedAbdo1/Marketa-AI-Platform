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
      
      <!-- User Menu Wrapper -->
      <div class="user-menu-wrapper">
        <!-- User Menu -->
        <div class="user-menu" @click="toggleDropdown" :class="{ active: showDropdown }">
          <div class="user-avatar">{{ userInitial }}</div>
          <span class="user-name">{{ userName }}</span>
          <i class="bx bx-chevron-down"></i>
        </div>
        
        <!-- Dropdown Menu -->
        <Transition name="fade">
          <div v-if="showDropdown" class="dropdown-menu" @click.stop>
            <div class="dropdown-header">
              <span class="dropdown-email">{{ userEmail }}</span>
            </div>
          
          <router-link to="/pricing" class="dropdown-item" @click="closeDropdown">
            <i class="bx bx-star"></i>
            <span>{{ $t('userMenu.upgrade') }}</span>
          </router-link>
          
          <router-link to="/dashboard/personalization" class="dropdown-item" @click="closeDropdown">
            <i class="bx bx-palette"></i>
            <span>{{ $t('userMenu.personalization') }}</span>
          </router-link>
          
          <router-link to="/dashboard/settings" class="dropdown-item" @click="closeDropdown">
            <i class="bx bx-cog"></i>
            <span>{{ $t('userMenu.settings') }}</span>
          </router-link>
          
          <div class="dropdown-divider"></div>
          
          <router-link to="/help" class="dropdown-item" @click="closeDropdown">
            <i class="bx bx-help-circle"></i>
            <span>{{ $t('userMenu.help') }}</span>
            <i class="bx bx-chevron-left arrow"></i>
          </router-link>
          
          <button @click="handleLogout" class="dropdown-item logout">
            <i class="bx bx-log-out"></i>
            <span>{{ $t('userMenu.logout') }}</span>
          </button>
          </div>
        </Transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import { setLocale } from '@/i18n'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const { locale: currentLocale } = useI18n()
const authStore = useAuthStore()
const router = useRouter()

const showDropdown = ref(false)

// User Data - ديناميكية من authStore
const user = computed(() => authStore.user)
const userName = computed(() => user.value?.name)
const userEmail = computed(() => user.value?.email)
const userInitial = computed(() => {
  const name = userName.value
  return name ? name.charAt(0).toUpperCase() : ''
})

// Mock data - will be replaced with real data
const remaining = ref(15)
const limit = ref(20)
const usagePercentage = computed(() => ((limit.value - remaining.value) / limit.value) * 100)

const toggleLanguage = () => {
  const newLocale = currentLocale.value === 'ar' ? 'en' : 'ar'
  setLocale(newLocale)
}

const toggleDropdown = (event) => {
  if (event) event.stopPropagation()
  showDropdown.value = !showDropdown.value
  
  // Force re-render للتأكد من تحديث DOM
  nextTick(() => {
    const dropdown = document.querySelector('.dropdown-menu')
    if (dropdown && showDropdown.value) {
      dropdown.style.display = 'block'
    }
  })
}

const closeDropdown = () => {
  showDropdown.value = false
}

const handleLogout = async () => {
  await authStore.logout()
  closeDropdown()
  router.push('/auth/login')
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  const userMenuWrapper = event.target.closest('.user-menu-wrapper')
  
  if (!userMenuWrapper && showDropdown.value) {
    closeDropdown()
  }
}

onMounted(() => {
  // تأخير إضافة الـ listener قليلاً
  setTimeout(() => {
    document.addEventListener('click', handleClickOutside)
  }, 100)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.dashboard-header {
  background: var(--color-bg-primary);
  padding: var(--space-4) var(--space-8);
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--color-border-light);
  position: sticky;
  top: 0;
  z-index: var(--z-sticky);
  height: var(--header-height, 64px);
}

.header-left, .header-right {
  display: flex;
  align-items: center;
  gap: var(--space-4);
}

.header-right {
  position: relative;
}

.btn-toggle-sidebar {
  background: none;
  border: none;
  font-size: var(--text-2xl);
  color: var(--color-text-secondary);
  cursor: pointer;
  padding: var(--space-2);
  border-radius: var(--radius-md);
  transition: var(--transition-fast);
}

.btn-toggle-sidebar:hover {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
}

.usage-indicator {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.usage-text {
  font-size: var(--text-xs);
  color: var(--color-text-secondary);
}

.usage-bar {
  width: 120px;
  height: 6px;
  background: var(--color-bg-tertiary);
  border-radius: var(--radius-full);
  overflow: hidden;
}

.usage-fill {
  height: 100%;
  background: var(--color-brand-primary);
  transition: width var(--transition-slow);
}

.usage-fill.warning {
  background: var(--color-error);
}

.btn-icon {
  background: none;
  border: none;
  font-size: var(--text-xl);
  color: var(--color-text-secondary);
  cursor: pointer;
  padding: var(--space-2);
  border-radius: var(--radius-md);
  transition: var(--transition-fast);
}

.btn-icon:hover {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
}

.user-menu-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.user-menu {
  position: relative;
  display: flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition-fast);
  font-size: var(--text-sm);
}

.user-menu:hover {
  background: var(--color-bg-hover);
}

.user-menu.active {
  background: var(--color-bg-hover);
}

.user-menu .bx-chevron-down {
  font-size: 16px;
  color: var(--color-text-tertiary);
  transition: transform var(--duration-normal);
}

.user-menu.active .bx-chevron-down {
  transform: rotate(180deg);
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-full);
  background: var(--color-text-tertiary);
  color: var(--color-bg-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  flex-shrink: 0;
}

.user-name {
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  color: var(--color-text-primary);
  max-width: 150px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: var(--color-bg-primary);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-dropdown);
  min-width: 240px;
  padding: var(--space-2);
  z-index: 9999;
  border: 1px solid var(--color-border-light);
}

[dir="rtl"] .dropdown-menu {
  right: auto;
  left: 0;
}

.dropdown-header {
  padding: var(--space-3);
  border-bottom: 1px solid var(--color-border-light);
  margin-bottom: var(--space-1);
}

.dropdown-email {
  font-size: var(--text-xs);
  color: var(--color-text-tertiary);
  display: block;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-2) var(--space-3);
  text-decoration: none;
  color: var(--color-text-primary);
  transition: var(--transition-fast);
  border: none;
  background: none;
  width: 100%;
  text-align: right;
  cursor: pointer;
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  font-family: var(--font-primary);
}

[dir="ltr"] .dropdown-item {
  text-align: left;
}

.dropdown-item:hover {
  background: var(--color-bg-hover);
}

.dropdown-item i {
  font-size: 18px;
  color: var(--color-text-secondary);
}

.dropdown-item .arrow {
  margin-right: auto;
  font-size: 16px;
}

[dir="rtl"] .dropdown-item .arrow {
  margin-right: 0;
  margin-left: auto;
  transform: rotate(180deg);
}

.dropdown-item.logout {
  color: var(--color-error);
}

.dropdown-item.logout:hover {
  background: var(--color-error-bg);
}

.dropdown-item.logout i {
  color: var(--color-error);
}

.dropdown-divider {
  height: 1px;
  background: var(--color-border-light);
  margin: var(--space-2) 0;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Tablet */
@media (max-width: 1024px) {
  .user-name {
    max-width: 100px;
  }
}

/* Mobile - مثل ChatGPT تماماً */
@media (max-width: 768px) {
  .dashboard-header {
    padding: var(--space-3);
    height: 56px;
  }
  
  .header-left,
  .header-right {
    gap: var(--space-2);
  }
  
  .usage-indicator {
    display: none;
  }
  
  .user-name {
    display: none;
  }
  
  .user-avatar {
    width: 28px;
    height: 28px;
  }
}
</style>

