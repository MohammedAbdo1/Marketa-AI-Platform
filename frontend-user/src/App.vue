<template>
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()

onMounted(async () => {
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
/* Global app styles are in main.css */
</style>
