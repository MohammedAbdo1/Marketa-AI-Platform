import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Toast from 'vue-toastification'

import App from './App.vue'
import router from './router'
import i18n from './i18n'

// Bootstrap Grid Only (for layout system)
import 'bootstrap/dist/css/bootstrap-grid.min.css'

// Boxicons
import 'boxicons/css/boxicons.min.css'

// Toast CSS
import 'vue-toastification/dist/index.css'

// Design System (Main - controls everything)
import './design-system/index.css'

// Legacy CSS (for backward compatibility - will be removed gradually)
import './assets/main.css'
import './styles/editor.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(i18n)
app.use(Toast, {
  position: 'top-right',
  timeout: 3000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: 'button',
  icon: true,
  rtl: i18n.global.locale.value === 'ar'
})

app.mount('#app')
