<template>
  <div class="auth-card">
    <h2 class="auth-title">{{ $t('auth.forgot_password') }}</h2>
    <p class="text-center text-muted mb-4">Enter your email to receive a password reset link</p>
    
    <form @submit.prevent="handleSubmit" class="auth-form">
      <div class="form-group">
        <label class="form-label">{{ $t('auth.email') }}</label>
        <input 
          v-model="email" 
          type="email" 
          class="form-control" 
          required
          autocomplete="email"
        />
      </div>
      
      <div v-if="error" class="alert alert-danger">{{ error }}</div>
      <div v-if="success" class="alert alert-success">{{ success }}</div>
      
      <button type="submit" class="btn btn-primary w-100" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
        {{ $t('auth.send_reset_link') }}
      </button>
      
      <div class="auth-links">
        <router-link to="/auth/login">{{ $t('auth.back_to_login') }}</router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const email = ref('')
const loading = ref(false)
const error = ref(null)
const success = ref(null)

const handleSubmit = async () => {
  loading.value = true
  error.value = null
  success.value = null
  
  try {
    // TODO: Implement forgot password API
    success.value = 'Password reset link sent to your email'
  } catch (err) {
    error.value = 'Failed to send reset link'
  } finally {
    loading.value = false
  }
}
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

.auth-title {
  text-align: center;
  margin-bottom: 1rem;
  color: #2d3748;
}

.auth-links {
  margin-top: 1.5rem;
  text-align: center;
}

.auth-links a {
  color: var(--primary-color);
  text-decoration: none;
}
</style>

