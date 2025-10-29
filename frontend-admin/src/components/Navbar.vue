<template>
  <div class="topbar">
    <div class="navbar-custom">
      <div class="topbar-left">
        <!-- Logo -->
        <div class="logo">
          <span class="logo-lg">
            <img src="/src/assets/images/logo.png" alt="logo" height="50">
          </span>
          <span class="logo-sm">
            <img src="/src/assets/images/logo.png" alt="small logo" height="30">
          </span>
        </div>
      </div>

      <div class="topbar-right">
        <!-- Language Switcher -->
        <div class="dropdown d-none d-lg-inline-block">
          <LanguageSwitcher />
        </div>

        <!-- User Info -->
        <div class="dropdown">
          <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <span class="d-none d-md-inline-block">{{ auth.user?.name || 'Admin' }}</span>
            <i class="mdi mdi-chevron-down"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="#">
              <i class="mdi mdi-account me-2"></i>
              {{ t('auth.profile') }}
            </a>
            <a class="dropdown-item" href="#">
              <i class="mdi mdi-settings me-2"></i>
              {{ t('common.settings') }}
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#" @click="handleLogout">
              <i class="mdi mdi-logout me-2"></i>
              {{ t('auth.logout') }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LanguageSwitcher from './LanguageSwitcher.vue'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.topbar {
  background: #fff;
  border-bottom: 1px solid #e9ecef;
  padding: 0 20px;
  height: 70px;
  display: flex;
  align-items: center;
}

.navbar-custom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.topbar-left .logo img {
  max-height: 50px;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.dropdown-toggle {
  border: none;
  background: none;
  color: #495057;
  text-decoration: none;
}

.dropdown-toggle:hover {
  color: #007bff;
}
</style>