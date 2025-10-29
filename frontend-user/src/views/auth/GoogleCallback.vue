<template>
  <div class="auth-card text-center">
    <div class="spinner-border text-primary mb-3"></div>
    <p>{{ $t('common.loading') }}</p>
    <p class="text-muted">Processing Google authentication...</p>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

onMounted(async () => {
  const token = route.query.token
  const error = route.query.error

  if (error) {
    toast.error('Google authentication failed')
    router.push('/auth/login')
    return
  }

  if (!token) {
    toast.error('No token received from Google')
    router.push('/auth/login')
    return
  }

  try {
    // Save token
    authStore.token = token
    authStore.isAuthenticated = true
    localStorage.setItem('token', token)

    // Fetch user data
    await authStore.fetchUser()

    toast.success('Successfully logged in with Google!')
    router.push('/dashboard')
  } catch (err) {
    toast.error('Failed to authenticate')
    router.push('/auth/login')
  }
})
</script>

<style scoped>
.auth-card {
  background: white;
  border-radius: 16px;
  padding: 4rem 3rem;
  max-width: 450px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
</style>

