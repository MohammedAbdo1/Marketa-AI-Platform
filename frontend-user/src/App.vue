<template>
  <div :dir="currentDir">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted, computed, watch } from 'vue'
import { useAuthStore } from './stores/auth'
import { useI18n } from 'vue-i18n'

const authStore = useAuthStore()
const { locale } = useI18n()

// Compute text direction based on locale
const currentDir = computed(() => locale.value === 'ar' ? 'rtl' : 'ltr')

// Watch locale changes and update HTML dir attribute
watch(locale, (newLocale) => {
  document.documentElement.setAttribute('dir', newLocale === 'ar' ? 'rtl' : 'ltr')
  document.documentElement.setAttribute('lang', newLocale)
}, { immediate: true })

onMounted(async () => {
  // Set initial direction
  document.documentElement.setAttribute('dir', locale.value === 'ar' ? 'rtl' : 'ltr')
  document.documentElement.setAttribute('lang', locale.value)
  
  // Fetch user if token exists and not logging out
  if (authStore.token && !authStore.isLoggingOut) {
    try {
      await authStore.fetchUser()
    } catch (error) {
      // Error is already handled by axios interceptor
      // Just log it here for debugging
      console.debug('Failed to fetch user on mount:', error.message)
    }
  }
})
</script>

<style>
/* Global app styles are managed by design-system/index.css */
</style>
