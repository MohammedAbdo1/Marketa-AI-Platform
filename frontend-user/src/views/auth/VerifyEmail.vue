<template>
  <div class="auth-card text-center">
    <div v-if="loading">
      <div class="spinner-border text-primary"></div>
      <p class="mt-3">Verifying your email...</p>
    </div>
    
    <div v-else-if="success">
      <i class="bx bx-check-circle text-success" style="font-size: 4rem;"></i>
      <h2 class="mt-3">Email Verified!</h2>
      <p>Your email has been successfully verified.</p>
      <router-link to="/dashboard" class="btn btn-primary mt-3">
        Go to Dashboard
      </router-link>
    </div>
    
    <div v-else>
      <i class="bx bx-error text-danger" style="font-size: 4rem;"></i>
      <h2 class="mt-3">Verification Failed</h2>
      <p>{{ error }}</p>
      <router-link to="/auth/login" class="btn btn-primary mt-3">
        Back to Login
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '@/axios'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const success = ref(false)
const error = ref(null)

onMounted(async () => {
  const { id, hash } = route.params
  
  try {
    // Build query string with all query params
    const queryString = new URLSearchParams(route.query).toString()
    
    // Call verification API
    const response = await axios.get(`/email/verify/${id}/${hash}${queryString ? '?' + queryString : ''}`)
    
    loading.value = false
    success.value = true
    
    // Redirect to dashboard after 2 seconds
    setTimeout(() => {
      router.push('/dashboard')
    }, 2000)
  } catch (err) {
    loading.value = false
    success.value = false
    error.value = err.response?.data?.message || 'Verification failed. The link may be invalid or expired.'
    console.error('Verification error:', err)
  }
})
</script>

<style scoped>
.auth-card {
  background: white;
  border-radius: 16px;
  padding: 3rem;
  max-width: 450px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
</style>

