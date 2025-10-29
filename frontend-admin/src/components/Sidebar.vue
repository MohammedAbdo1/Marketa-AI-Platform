<template>
  <div class="sidenav-menu">
    <!-- Brand Logo -->
    <router-link to="/dashboard" class="logo">
      <span class="logo-lg">
        <img src="/src/assets/images/logo.png" style="height: 100px;" alt="logo">
      </span>
      <span class="logo-sm">
        <img src="/src/assets/images/logo.png" alt="small logo">
      </span>
    </router-link>

    <!-- Sidebar Hover Menu Toggle Button -->
    <button class="button-sm-hover">
      <i class="ri-circle-line align-middle"></i>
    </button>

    <!-- Sidebar Menu Toggle Button -->
    <button class="sidenav-toggle-button">
      <i class="ri-menu-5-line fs-20"></i>
    </button>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-fullsidebar">
      <i class="ti ti-x align-middle"></i>
    </button>

    <div data-simplebar>
      <!-- Sidenav Menu -->
      <ul class="side-nav">
        <!-- Dashboard -->
        <li class="side-nav-item">
          <router-link to="/dashboard" class="side-nav-link">
            <span class="menu-icon"><i class="ti ti-layout-dashboard"></i></span>
            <span class="menu-text">{{ t('dashboard.title') }}</span>
          </router-link>
        </li>

        <!-- Platform Management -->
        <li class="side-nav-title text-white">{{ t('sidebar.platform_management') }}</li>

        <!-- إدارة المدراء -->
        <li class="side-nav-item">
          <router-link to="/admins" class="side-nav-link">
            <span class="menu-icon"><i class="ti ti-user-shield"></i></span>
            <span class="menu-text">{{ t('admins.title') }}</span>
          </router-link>
        </li>

        <!-- إدارة الأدوار -->
        <li class="side-nav-item">
          <router-link to="/roles" class="side-nav-link">
            <span class="menu-icon"><i class="ti ti-shield-lock"></i></span>
            <span class="menu-text">{{ t('roles.title') }}</span>
          </router-link>
        </li>

        <!-- Content Management -->
        <li class="side-nav-title text-white">{{ t('cms.title') }}</li>

        <!-- إدارة الصفحات -->
        <li class="side-nav-item">
          <router-link to="/admin.cms/pages" class="side-nav-link">
            <span class="menu-icon"><i class="ti ti-file-text"></i></span>
            <span class="menu-text">{{ t('cms.pages') }}</span>
          </router-link>
        </li>

        <!-- إدارة الأقسام -->
        <li class="side-nav-item">
          <router-link to="/admin.cms/sections" class="side-nav-link">
            <span class="menu-icon"><i class="ti ti-layout"></i></span>
            <span class="menu-text">{{ t('cms.sections') }}</span>
          </router-link>
        </li>

        <!-- آراء العملاء -->
        <li class="side-nav-item">
          <router-link to="/admin.testimonials" class="side-nav-link">
            <span class="menu-icon"><i class="ti ti-quote"></i></span>
            <span class="menu-text">{{ t('testimonials.title') }}</span>
          </router-link>
        </li>

        <!-- الأسئلة الشائعة -->
        <li class="side-nav-item">
          <router-link to="/admin.faqs" class="side-nav-link">
            <span class="menu-icon"><i class="ti ti-help"></i></span>
            <span class="menu-text">{{ t('faqs.title') }}</span>
          </router-link>
        </li>

        <!-- Customer Management -->
        <li class="side-nav-title text-white">{{ t('sidebar.customer_management') }}</li>

        <!-- العملاء -->
        <li class="side-nav-item">
          <router-link to="/customers" class="side-nav-link">
            <span class="menu-icon"><i class="ti ti-users"></i></span>
            <span class="menu-text">{{ t('customers.title') }}</span>
          </router-link>
        </li>

        <!-- المؤسسات -->
        <li class="side-nav-item">
          <router-link to="/organizations" class="side-nav-link">
            <span class="menu-icon"><i class="ti ti-building"></i></span>
            <span class="menu-text">{{ t('organizations.title') }}</span>
          </router-link>
        </li>

        <!-- Subscription Management -->
        <li class="side-nav-title text-white">{{ t('sidebar.subscription_management') }}</li>

        <!-- الباقات -->
        <li class="side-nav-item">
          <router-link to="/plans" class="side-nav-link">
            <span class="menu-icon"><i class="ti ti-package"></i></span>
            <span class="menu-text">{{ t('plans.title') }}</span>
          </router-link>
        </li>

        <!-- الإعدادات -->
        <li class="side-nav-item has-submenu">
          <a data-bs-toggle="collapse" href="#sidebarsetting" class="side-nav-link">
            <span class="menu-icon"><i class="ti ti-settings"></i></span>
            <span class="menu-text">{{ t('settings.title') }}</span>
            <span class="menu-arrow"></span>
          </a>
          <div class="collapse" id="sidebarsetting">
            <ul class="sub-menu">
              <li class="side-nav-item">
                <router-link to="/settings" class="side-nav-link">
                  <span class="menu-icon"><i class="ti ti-tools"></i></span>
                  <span class="menu-text">{{ t('settings.title') }}</span>
                </router-link>
              </li>
            </ul>
          </div>
        </li>
      </ul>
    </div>

    <div class="clearfix"></div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<style>
/* Sidebar specific styles */
</style>
