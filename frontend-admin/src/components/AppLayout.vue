<template>
  <div class="wrapper" :dir="direction">
    <!-- Sidebar -->
    <Sidebar />
    
    <!-- Topbar -->
    <Navbar />
    
    <!-- Main Content -->
    <div class="page-content">
      <div class="page-container">
        <div class="mb-4 mt-3">
          <!-- <h4 class="page-title fs-20 fw-semibold mb-0">{{ pageTitle }}</h4> -->
        </div>
        <!-- Page Content -->
        <router-view></router-view>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import Sidebar from "./Sidebar.vue"
import Navbar from "./Navbar.vue"

const { locale } = useI18n()

const direction = computed(() => locale.value === 'ar' ? 'rtl' : 'ltr')

// Props
const props = defineProps({
  pageTitle: {
    type: String,
    default: 'Dashboard'
  }
})
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
