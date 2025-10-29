<template>
  <div class="auth-card text-center">
    <div class="verification-icon">
      <i class="bx bx-envelope"></i>
    </div>
    
    <h2 class="mb-3">{{ $t('auth.verify_email') }}</h2>
    <p class="text-muted mb-4">
      تم إرسال رابط التحقق إلى بريدك الإلكتروني
      <strong v-if="email">{{ email }}</strong>
    </p>
    
    <p class="text-muted mb-4">
      يرجى التحقق من بريدك الإلكتروني والنقر على رابط التفعيل لإكمال عملية التسجيل.
    </p>
    
    <div class="alert alert-info">
      <small>
        <i class="bx bx-info-circle me-1"></i>
        لم تستلم الرسالة؟ تحقق من مجلد البريد المزعج (Spam)
      </small>
    </div>
    
    <button 
      @click="resendEmail" 
      class="btn btn-outline w-100 mb-3"
      :disabled="resending || countdown > 0"
    >
      <span v-if="resending" class="spinner-border spinner-border-sm me-2"></span>
      {{ countdown > 0 ? `إعادة الإرسال بعد ${countdown}ث` : 'إعادة إرسال الرابط' }}
    </button>
    
    <div class="auth-links">
      <router-link to="/auth/login">{{ $t('auth.back_to_login') }}</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useI18n } from 'vue-i18n'
import axios from '@/axios'

const route = useRoute()
const toast = useToast()
const { t } = useI18n()

const email = ref(route.query.email)
const resending = ref(false)
const countdown = ref(0)
let countdownInterval = null

const resendEmail = async () => {
  if (!email.value) {
    toast.error('Email address is required')
    return
  }
  
  resending.value = true
  
  try {
    await axios.post('/resend-verification', { email: email.value })
    toast.success('تم إرسال رابط التحقق مجدداً')
    
    // Start countdown (60 seconds)
    countdown.value = 60
    countdownInterval = setInterval(() => {
      countdown.value--
      if (countdown.value <= 0) {
        clearInterval(countdownInterval)
      }
    }, 1000)
  } catch (error) {
    toast.error(error.response?.data?.message || 'فشل إرسال الرابط. حاول مرة أخرى')
  } finally {
    resending.value = false
  }
}

onUnmounted(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
})
</script>

<style scoped>
.auth-card {
  background: white;
  border-radius: 16px;
  padding: 3rem;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.verification-icon {
  width: 100px;
  height: 100px;
  margin: 0 auto 2rem;
  background: #2383E2;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 3rem;
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

