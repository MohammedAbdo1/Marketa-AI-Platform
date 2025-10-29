<template>
  <div>
    <!-- Language Switcher -->
    <div class="absolute top-4 right-4 z-10">
      <LanguageSwitcher />
    </div>

    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ t('auth.welcome_back') }}</h1>
      <p class="text-gray-600">{{ t('auth.login_message') }}</p>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-6">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
          {{ t('auth.email') }}
        </label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
          placeholder="admin@admin.com"
        />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
          {{ t('auth.password') }}
        </label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
          placeholder="••••••"
        />
      </div>

      <div v-if="authStore.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        {{ authStore.error }}
      </div>

      <button
        type="submit"
        :disabled="authStore.loading"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="!authStore.loading">{{ t('auth.login') }}</span>
        <span v-else>{{ t('common.loading') }}</span>
      </button>
    </form>

    <div class="mt-6 text-center text-sm text-gray-600">
      <p>{{ locale === 'ar' ? 'البيانات الافتراضية للاختبار:' : 'Default credentials:' }}</p>
      <p class="mt-1"><strong>{{ locale === 'ar' ? 'البريد:' : 'Email:' }}</strong> admin@admin.com</p>
      <p><strong>{{ locale === 'ar' ? 'كلمة المرور:' : 'Password:' }}</strong> 123456</p>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'

const router = useRouter()
const authStore = useAuthStore()
const { t, locale } = useI18n()

const form = reactive({
  email: '',
  password: '',
})

const handleLogin = async () => {
  try {
    await authStore.login(form)
    router.push('/dashboard')
  } catch (error) {
    console.error('Login failed:', error)
  }
}
</script>
