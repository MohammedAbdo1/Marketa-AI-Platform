<template>
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()

onMounted(() => {
  // Fetch user if token exists
  if (authStore.token) {
    authStore.fetchUser()
  }
})
</script>

<style>
/* Global app styles are in main.css */
</style>
