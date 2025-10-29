<template>
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">{{ t('lang.locale_example') }}</h5>
      
      <!-- Display Current Locale Info -->
      <div class="mb-3">
        <p><strong>Current Locale:</strong> {{ currentLocale }}</p>
        <p><strong>Direction:</strong> {{ direction }}</p>
        <p><strong>Is RTL:</strong> {{ isRTL ? 'Yes' : 'No' }}</p>
      </div>

      <!-- Example: Direction-aware spacing -->
      <div :class="isRTL ? 'me-3' : 'ms-3'" class="p-3 bg-light">
        <p>This box has direction-aware margin</p>
      </div>

      <!-- Example: Conditional rendering based on locale -->
      <div class="mt-3">
        <p v-if="currentLocale === 'ar'">
          هذا النص يظهر فقط باللغة العربية
        </p>
        <p v-else>
          This text appears only in English
        </p>
      </div>

      <!-- Example: Using translations with i18n -->
      <div class="mt-3">
        <p>{{ t('lang.welcome') }}</p>
      </div>

      <!-- Example: Manual locale change (already handled by Navbar) -->
      <div class="mt-3">
        <button @click="switchLanguage" class="btn btn-primary">
          {{ isRTL ? 'Switch to English' : 'التبديل إلى العربية' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useLocaleStore } from '@/stores/locale'
import { useI18n } from 'vue-i18n'

const localeStore = useLocaleStore()
const { t } = useI18n()

// Access locale information
const currentLocale = computed(() => localeStore.currentLocale)
const direction = computed(() => localeStore.direction)
const isRTL = computed(() => localeStore.isRTL)

// Function to switch language
const switchLanguage = async () => {
  const newLocale = isRTL.value ? 'en' : 'ar'
  await localeStore.setLocale(newLocale)
  window.location.reload()
}
</script>

<style scoped>
/* Example of direction-specific styles */
[dir="rtl"] .custom-spacing {
  padding-right: 20px;
}

[dir="ltr"] .custom-spacing {
  padding-left: 20px;
}
</style>

