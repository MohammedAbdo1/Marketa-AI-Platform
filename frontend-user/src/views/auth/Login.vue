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
  background: white;
  border-radius: 16px;
  padding: 3rem;
  max-width: 450px;
  width: 100%;
  /* box-shadow: 0 20px 60px rgba(0,0,0,0.3); */
}
.auth-title {
  margin-bottom: 2rem;
  color: #2d3748;
}
.auth-form {
  width: 100%;
}

.divider {
  text-align: center;
  margin: 1.5rem 0;
  color: #718096;
  position: relative;
}

.divider::before,
.divider::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 40%;
  height: 1px;
  background: #e2e8f0;
}

.divider::before {
  left: 0;
}

.divider::after {
  right: 0;
}

.auth-links {
  margin-top: 1.5rem;
  text-align: center;
  display: flex;
  justify-content: center;
  gap: 0.5rem;
}

.auth-links a {
  color: var(--primary-color);
  text-decoration: none;
}

.auth-links a:hover {
  text-decoration: underline;
}
</style>

