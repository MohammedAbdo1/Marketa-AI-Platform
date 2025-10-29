<template>
  <div class="language-switcher">
    <button 
      @click="toggleLanguage"
      class="btn btn-sm btn-light"
      :title="currentLocale === 'ar' ? 'Switch to English' : 'التبديل إلى العربية'"
    >
      <i class="fas fa-globe me-1"></i>
      {{ currentLocale === 'ar' ? 'EN' : 'عربي' }}
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { setLocale } from '@/i18n'

const { locale } = useI18n()

const currentLocale = computed(() => locale.value)

const toggleLanguage = () => {
  const newLocale = locale.value === 'ar' ? 'en' : 'ar'
  setLocale(newLocale)
  
  // Reload page to apply RTL/LTR changes
  window.location.reload()
}
</script>

<style scoped>
.language-switcher {
  display: inline-block;
}

.btn {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}
</style>
