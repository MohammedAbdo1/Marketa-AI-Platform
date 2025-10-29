import { createI18n } from 'vue-i18n'
import ar from './locales/ar.json'
import en from './locales/en.json'

// Get stored locale or default to 'ar'
const savedLocale = localStorage.getItem('locale') || 'ar'

const i18n = createI18n({
  legacy: false, // Use Composition API
  locale: savedLocale,
  fallbackLocale: 'ar',
  messages: {
    ar,
    en,
  },
})

// Update HTML attributes when locale changes
export function setLocale(locale) {
  i18n.global.locale.value = locale
  localStorage.setItem('locale', locale)
  
  // Update HTML lang and dir attributes
  document.documentElement.setAttribute('lang', locale)
  document.documentElement.setAttribute('dir', locale === 'ar' ? 'rtl' : 'ltr')
  
  // Update CSS file for RTL/LTR
  const appStyleLink = document.getElementById('app-style')
  if (appStyleLink) {
    if (locale === 'ar') {
      appStyleLink.href = '/src/assets/css/app-rtl.min.css'
    } else {
      appStyleLink.href = '/src/assets/css/app.min.css'
    }
  }
}

// Set initial locale
setLocale(savedLocale)

export default i18n
