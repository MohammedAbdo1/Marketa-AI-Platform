<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">{{ t('dashboard.title') }}</h1>
        <div class="flex items-center gap-4">
          <LanguageSwitcher />
          <span class="text-gray-600">{{ authStore.user?.name }}</span>
          <button
            @click="handleLogout"
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition"
          >
            {{ t('auth.logout') }}
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Welcome Card -->
      <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg p-8 text-white mb-8">
        <h2 class="text-3xl font-bold mb-2">مرحباً {{ authStore.user?.name }}! 👋</h2>
        <p class="text-blue-100">مرحباً بك في منصة التسويق الذكية</p>
      </div>

      <!-- Navigation Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <router-link to="/users" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">{{ t('users.title') }}</p>
              <p class="text-3xl font-bold text-gray-800 mt-2">10</p>
            </div>
            <div class="bg-blue-100 p-4 rounded-full">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
              </svg>
            </div>
          </div>
        </router-link>

        <router-link to="/plans" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">{{ t('plans.title') }}</p>
              <p class="text-3xl font-bold text-gray-800 mt-2">3</p>
            </div>
            <div class="bg-green-100 p-4 rounded-full">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
            </div>
          </div>
        </router-link>

        <router-link to="/organizations" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">{{ t('organizations.title') }}</p>
              <p class="text-3xl font-bold text-gray-800 mt-2">1</p>
            </div>
            <div class="bg-purple-100 p-4 rounded-full">
              <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
          </div>
        </router-link>

        <div class="bg-white rounded-xl shadow-md p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">{{ t('users.status') }}</p>
              <p class="text-3xl font-bold text-gray-800 mt-2">{{ authStore.user?.status }}</p>
            </div>
            <div class="bg-yellow-100 p-4 rounded-full">
              <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- User Info -->
      <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">معلومات المستخدم</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-gray-600">UUID</p>
            <p class="font-medium text-gray-800">{{ authStore.user?.uuid }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">البريد الإلكتروني</p>
            <p class="font-medium text-gray-800">{{ authStore.user?.email }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">الصلاحيات</p>
            <p class="font-medium text-gray-800">{{ authStore.user?.roles?.join(', ') }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">آخر تسجيل دخول</p>
            <p class="font-medium text-gray-800">{{ formatDate(authStore.user?.last_login_at) }}</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../stores/auth'
import LanguageSwitcher from '../components/LanguageSwitcher.vue'

const router = useRouter()
const authStore = useAuthStore()
const { t, locale } = useI18n()

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const formatDate = (dateString) => {
  if (!dateString) return locale.value === 'ar' ? 'غير متوفر' : 'Not Available'
  const date = new Date(dateString)
  return date.toLocaleDateString(locale.value === 'ar' ? 'ar-EG' : 'en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

