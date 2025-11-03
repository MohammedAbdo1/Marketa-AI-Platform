<template>
  <div class="auth-card">
    <h2 class="auth-title">{{ $t('auth.login') }}</h2>
    
    <form @submit.prevent="handleLogin" class="auth-form">
      <div class="form-group">
        <label class="form-label">{{ $t('auth.email') }}</label>
        <input 
          v-model="form.email" 
          type="email" 
          class="form-control" 
          required
          autocomplete="email"
        />
      </div>
      
      <div class="form-group">
        <label class="form-label">{{ $t('auth.password') }}</label>
        <input 
          v-model="form.password" 
          type="password" 
          class="form-control form-control-sm" 
          required
          autocomplete="current-password"
        />
      </div>
      
      <div class="form-check mb-3">
        <input 
          v-model="form.remember" 
          type="checkbox" 
          class="form-check-input" 
          id="remember"
        />
        <label class="form-check-label" for="remember">
          {{ $t('auth.remember_me') }}
        </label>
      </div>
      
      <div v-if="error" class="alert alert-danger">{{ error }}</div>
      
      <button type="submit" class="btn btn-primary w-100" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
        {{ $t('auth.login') }}
      </button>
      
      <div class="divider">أو</div>
      
      <button type="button" class="btn btn-outline w-100" @click="loginWithGoogle">
        <i class="bx bxl-google me-2"></i>
        {{ $t('auth.continue_with_google') }}
      </button>
      
      <div class="auth-links">
        <router-link to="/auth/forgot-password">{{ $t('auth.forgot_password') }}</router-link>
        <span>·</span>
        <router-link to="/auth/register">{{ $t('auth.dont_have_account') }}</router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const authStore = useAuthStore()
const router = useRouter()
const toast = useToast()

const form = ref({
  email: '',
  password: '',
  remember: false
})

const loading = ref(false)
const error = ref(null)

const handleLogin = async () => {
  loading.value = true
  error.value = null
  
  try {
    await authStore.login(form.value)
    toast.success('Login successful!')
    router.push('/dashboard')
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed'
    toast.error(error.value)
  } finally {
    loading.value = false
  }
}

const loginWithGoogle = () => {
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  const baseUrl = apiUrl.replace('/api', '')
  window.location.href = baseUrl + '/api/auth/google'
}
</script>

<style scoped>
.auth-card {
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-2xl);
  padding: var(--space-12);
  max-width: 450px;
  width: 100%;
  box-shadow: var(--shadow-lg);
  animation: fadeInUp var(--duration-slow) var(--ease-out);
}

.auth-title {
  margin-bottom: var(--space-8);
  color: var(--color-text-primary);
  font-weight: var(--font-bold);
  font-size: var(--text-3xl);
}

.auth-form {
  width: 100%;
}

.divider {
  text-align: center;
  margin: var(--space-6) 0;
  color: var(--color-text-secondary);
  position: relative;
  font-size: var(--text-sm);
}

.divider::before,
.divider::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 40%;
  height: 1px;
  background: var(--color-border-light);
}

.divider::before {
  left: 0;
}

.divider::after {
  right: 0;
}

.auth-links {
  margin-top: var(--space-6);
  text-align: center;
  display: flex;
  justify-content: center;
  gap: var(--space-2);
  font-size: var(--text-sm);
}

.auth-links a {
  color: var(--color-brand-primary);
  text-decoration: none;
  font-weight: var(--font-medium);
}

.auth-links a:hover {
  text-decoration: underline;
}
</style>

