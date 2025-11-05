<template>
  <aside class="dashboard-sidebar" :class="{ open: isOpen, collapsed: isCollapsed }">
    <div class="sidebar-header">
      <router-link to="/" class="logo" v-show="!isCollapsed">
        <h3>{{ $t('app.name') }}</h3>
      </router-link>
      
      <!-- زر Collapse (Desktop) / Close (Mobile) -->
      <button class="btn-toggle-sidebar" @click="handleToggle" :title="isCollapsed ? 'فتح القائمة' : 'إغلاق القائمة'">
        <i class="bx" :class="getToggleIcon"></i>
      </button>
    </div>
    
    <nav class="sidebar-nav">
      <router-link to="/dashboard" class="nav-item" exact :title="$t('sidebar.dashboard')">
        <i class="bx bx-home"></i>
        <span>{{ $t('sidebar.dashboard') }}</span>
      </router-link>
      
      <router-link to="/dashboard/campaigns" class="nav-item" :title="$t('sidebar.campaigns')">
        <i class="bx bx-bullseye"></i>
        <span>{{ $t('sidebar.campaigns') }}</span>
      </router-link>
      
      <router-link to="/dashboard/designs" class="nav-item" :title="$t('sidebar.designs')">
        <i class="bx bx-layer"></i>
        <span>{{ $t('sidebar.designs') }}</span>
      </router-link>

      <router-link to="/dashboard/designs/favorites" class="nav-item" :title="$t('designs.favorites')">
        <i class="bx bx-star"></i>
        <span>{{ $t('designs.favorites') }}</span>
      </router-link>
      
      <router-link to="/dashboard/ai" class="nav-item" :title="$t('sidebar.ai_studio')">
        <i class="bx bx-sparkle"></i>
        <span>{{ $t('sidebar.ai_studio') }}</span>
      </router-link>
      
      <router-link to="/dashboard/brands" class="nav-item" :title="$t('sidebar.brands')">
        <i class="bx bx-palette"></i>
        <span>{{ $t('sidebar.brands') }}</span>
      </router-link>
      
      <router-link to="/dashboard/usage" class="nav-item" :title="$t('sidebar.usage')">
        <i class="bx bx-bar-chart"></i>
        <span>{{ $t('sidebar.usage') }}</span>
      </router-link>
      
      <div class="nav-divider"></div>
      
      <router-link to="/dashboard/profile" class="nav-item" :title="$t('sidebar.profile')">
        <i class="bx bx-user"></i>
        <span>{{ $t('sidebar.profile') }}</span>
      </router-link>
      
      <router-link to="/dashboard/settings" class="nav-item" :title="$t('sidebar.settings')">
        <i class="bx bx-cog"></i>
        <span>{{ $t('sidebar.settings') }}</span>
      </router-link>
      
      <div class="nav-divider"></div>
      
      <router-link to="/dashboard/trash" class="nav-item" :title="$t('sidebar.trash', 'سلة المهملات')">
        <i class="bx bx-trash"></i>
        <span>{{ $t('sidebar.trash', 'سلة المهملات') }}</span>
      </router-link>
      
      <div class="nav-divider"></div>
      
      <router-link to="/pricing" class="nav-item upgrade" :title="$t('sidebar.upgrade')">
        <i class="bx bx-star"></i>
        <span>{{ $t('sidebar.upgrade') }}</span>
      </router-link>
    </nav>
    
    <!-- User Menu -->
    <div class="sidebar-footer">
      <div class="user-menu" @click="toggleUserMenu" :class="{ active: isUserMenuOpen }">
        <div class="user-info">
          <div class="user-avatar">{{ userInitial }}</div>
          <div class="user-details" v-show="!isCollapsed">
            <span class="user-name">{{ userName }}</span>
            <span class="user-email">{{ userEmail }}</span>
          </div>
        </div>
        <i class="bx bx-chevron-up" v-show="!isCollapsed"></i>
      </div>
      
      <!-- Dropdown Menu -->
      <div class="user-dropdown" v-if="isUserMenuOpen" :class="{ collapsed: isCollapsed }" @click.stop>
        <div class="dropdown-header">
          <span class="dropdown-email">{{ userEmail }}</span>
        </div>
        
        <router-link to="/pricing" class="dropdown-item" @click="closeUserMenu">
          <i class="bx bx-star"></i>
          <span>{{ $t('userMenu.upgrade') }}</span>
        </router-link>
        
        <router-link to="/dashboard/personalization" class="dropdown-item" @click="closeUserMenu">
          <i class="bx bx-palette"></i>
          <span>{{ $t('userMenu.personalization') }}</span>
        </router-link>
        
        <router-link to="/dashboard/settings" class="dropdown-item" @click="closeUserMenu">
          <i class="bx bx-cog"></i>
          <span>{{ $t('userMenu.settings') }}</span>
        </router-link>
        
        <div class="dropdown-divider"></div>
        
        <router-link to="/help" class="dropdown-item" @click="closeUserMenu">
          <i class="bx bx-help-circle"></i>
          <span>{{ $t('userMenu.help') }}</span>
          <i class="bx bx-chevron-left arrow"></i>
        </router-link>
        
        <button class="dropdown-item logout" @click="handleLogout">
          <i class="bx bx-log-out"></i>
          <span>{{ $t('userMenu.logout') }}</span>
        </button>
      </div>
    </div>
  </aside>
  
  <!-- Mobile Overlay - خلف الـ Sidebar -->
  <div 
    v-if="isOpen" 
    class="sidebar-overlay" 
    @click="$emit('toggle')"
  ></div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: true
  },
  isCollapsed: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['toggle', 'toggle-collapse'])

const { t, locale } = useI18n()
const router = useRouter()
const authStore = useAuthStore()

// User Menu State
const isUserMenuOpen = ref(false)

// User Data - ديناميكية من authStore
const user = computed(() => authStore.user)
const userName = computed(() => user.value?.name || 'محمد عبده')
const userEmail = computed(() => user.value?.email || 'm.alshamirie@gmail.com')
const userInitial = computed(() => {
  const name = userName.value
  return name ? name.charAt(0).toUpperCase() : 'م'
})

const toggleUserMenu = (event) => {
  event.stopPropagation()
  isUserMenuOpen.value = !isUserMenuOpen.value
}

const closeUserMenu = () => {
  isUserMenuOpen.value = false
}

const handleLogout = async () => {
  await authStore.logout()
  closeUserMenu()
  router.push('/auth/login')
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  const dropdown = event.target.closest('.user-dropdown')
  const userMenu = event.target.closest('.user-menu')
  
  if (!dropdown && !userMenu && isUserMenuOpen.value) {
    closeUserMenu()
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

// على Mobile: close, على Desktop: collapse
const handleToggle = () => {
  if (window.innerWidth <= 768) {
    emit('toggle')
  } else {
    emit('toggle-collapse')
  }
}

// أيقونة التبديل حسب اللغة والحالة
const getToggleIcon = computed(() => {
  const isRTL = locale.value === 'ar'
  
  // على Mobile: دائماً X
  if (window.innerWidth <= 768) {
    return 'bx-x'
  }
  
  // على Desktop: أيقونة sidebar أفضل
  if (isRTL) {
    // للعربية (RTL)
    return props.isCollapsed ? 'bx-chevrons-left' : 'bx-chevrons-right'
  } else {
    // للإنجليزية (LTR)
    return props.isCollapsed ? 'bx-chevrons-right' : 'bx-chevrons-left'
  }
})
</script>

<style scoped>
/* الأنماط الأساسية من design-system/components/sidebars.css */

/* Override للتأكد من عمل exact-active فقط */
.nav-item.router-link-active:not(.router-link-exact-active) {
  background: transparent;
  color: var(--color-sidebar-text);
  font-weight: var(--font-medium);
}

.sidebar-nav {
  padding: var(--space-3);
}

/* ========== User Menu ========== */
.sidebar-footer {
  position: relative;
  margin-top: auto;
  padding: var(--space-3);
  border-top: 1px solid var(--color-border-light);
}

.user-menu {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-3);
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition-all);
}

.user-menu:hover {
  background: var(--color-sidebar-hover);
}

.user-menu.active {
  background: var(--color-sidebar-hover);
}

.user-info {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  flex: 1;
  min-width: 0;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--color-text-tertiary);
  color: var(--color-bg-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  flex-shrink: 0;
}

.user-details {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
  flex: 1;
}

.user-name {
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  color: var(--color-text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-email {
  font-size: var(--text-xs);
  color: var(--color-text-tertiary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-menu > .bx-chevron-up {
  font-size: 18px;
  color: var(--color-text-tertiary);
  transition: transform var(--duration-normal);
  flex-shrink: 0;
}

.user-menu.active > .bx-chevron-up {
  transform: rotate(180deg);
}

/* ========== User Dropdown ========== */
.user-dropdown {
  position: absolute;
  bottom: 100%;
  left: var(--space-3);
  right: var(--space-3);
  margin-bottom: var(--space-2);
  background: var(--color-bg-primary);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-dropdown);
  padding: var(--space-2);
  z-index: var(--z-dropdown);
  animation: slideUp var(--duration-fast) var(--ease-out);
}

[dir="rtl"] .user-dropdown {
  right: var(--space-3);
  left: var(--space-3);
}

/* عند الـ collapsed، يكون الـ dropdown أوسع */
.user-dropdown.collapsed {
  left: calc(100% + var(--space-2));
  right: auto;
  width: 240px;
}

[dir="rtl"] .user-dropdown.collapsed {
  right: calc(100% + var(--space-2));
  left: auto;
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
  width: 100%;
  padding: var(--space-2) var(--space-3);
  font-size: var(--text-sm);
  color: var(--color-text-primary);
  text-decoration: none;
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition-all);
  border: none;
  background: transparent;
  text-align: right;
  font-family: var(--font-primary);
}

[dir="ltr"] .dropdown-item {
  text-align: left;
}

.dropdown-item:hover {
  background: var(--color-sidebar-hover);
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

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .user-dropdown.collapsed {
    left: var(--space-3);
    right: var(--space-3);
    width: auto;
  }
  
  [dir="rtl"] .user-dropdown.collapsed {
    right: var(--space-3);
    left: var(--space-3);
  }
}
</style>

