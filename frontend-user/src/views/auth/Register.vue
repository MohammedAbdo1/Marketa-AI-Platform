<template>
  <div class="auth-card">
    <h2 class="auth-title">{{ $t('auth.register') }}</h2>
    
    <form @submit.prevent="handleRegister" class="auth-form">
      <div class="form-group">
        <label class="form-label">{{ $t('auth.name') }}</label>
        <input 
          v-model="form.name" 
          type="text" 
          class="form-control" 
          required
          autocomplete="name"
        />
      </div>
      
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
          class="form-control" 
          required
          autocomplete="new-password"
          minlength="6"
        />
      </div>
      
      <div class="form-group">
        <label class="form-label">{{ $t('auth.confirm_password') }}</label>
        <input 
          v-model="form.password_confirmation" 
          type="password" 
          class="form-control" 
          required
          autocomplete="new-password"
        />
      </div>
      
      <div class="form-check mb-3">
        <input 
          v-model="form.terms" 
          type="checkbox" 
          class="form-check-input" 
          id="terms"
          required
        />
        <label class="form-check-label" for="terms">
          {{ $t('auth.terms_agree') }}
        </label>
      </div>
      
      <div v-if="error" class="alert alert-danger">{{ error }}</div>
      
      <button type="submit" class="btn btn-primary w-100" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
        {{ $t('auth.register') }}
      </button>
      
      <div class="divider">أو</div>
      
      <button type="button" class="btn btn-outline w-100" @click="registerWithGoogle">
        <i class="bx bxl-google me-2"></i>
        {{ $t('auth.continue_with_google') }}
      </button>
      
      <div class="auth-links">
        <router-link to="/auth/login">{{ $t('auth.already_have_account') }}</router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useI18n } from 'vue-i18n'

const authStore = useAuthStore()
const router = useRouter()
const toast = useToast()
const { t } = useI18n()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false
})

const loading = ref(false)
const error = ref(null)

const handleRegister = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match'
    toast.error('Passwords do not match')
    return
  }
  
  loading.value = true
  error.value = null
  
  try {
    const response = await authStore.register(form.value)
    
    // Show success message
    toast.success(t('auth.email_verification_sent'))
    
    // Redirect to email verification notice page
    router.push({ 
      name: 'verify-email-notice', 
      query: { email: form.value.email } 
    })
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.error || 'Registration failed'
    toast.error(error.value)
    console.error('Registration error:', err)
  } finally {
    loading.value = false
  }
}

const registerWithGoogle = () => {
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
  max-width: 500px;
  width: 100%;
  box-shadow: var(--shadow-lg);
  animation: fadeInUp var(--duration-slow) var(--ease-out);
}

.auth-title {
  text-align: center;
  margin-bottom: var(--space-8);
  color: var(--color-text-primary);
  font-weight: var(--font-bold);
  font-size: var(--text-3xl);
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
  margin-top: 1.5rem;
  text-align: center;
}

.auth-links a {
  color: var(--primary-color);
  text-decoration: none;
}

.auth-links a:hover {
  text-decoration: underline;
}
</style>

