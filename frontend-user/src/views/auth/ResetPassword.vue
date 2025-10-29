<template>
  <div class="auth-card">
    <h2 class="auth-title">{{ $t('auth.reset_password') }}</h2>
    <p class="text-center text-muted mb-4">Enter your new password</p>
    
    <form @submit.prevent="handleSubmit" class="auth-form">
      <div class="form-group">
        <label class="form-label">{{ $t('auth.password') }}</label>
        <input v-model="form.password" type="password" class="form-control" required />
      </div>
      
      <div class="form-group">
        <label class="form-label">{{ $t('auth.confirm_password') }}</label>
        <input v-model="form.password_confirmation" type="password" class="form-control" required />
      </div>
      
      <button type="submit" class="btn btn-primary w-100">
        {{ $t('auth.reset_password') }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const form = ref({
  password: '',
  password_confirmation: '',
  token: route.params.token
})

const handleSubmit = () => {
  // TODO: Implement reset password
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
}
</style>

