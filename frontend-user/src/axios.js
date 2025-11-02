import axios from 'axios'
import { useAuthStore } from './stores/auth'
import router from './router'

const instance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor
instance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
instance.interceptors.response.use(
  (response) => response,
  async (error) => {
    // Handle daily limit exceeded
    if (error.response?.status === 429 && error.response?.data?.error === 'DAILY_LIMIT_EXCEEDED') {
      // Show special message for daily limit
      console.error('Daily limit exceeded:', error.response.data)
    }
    
    // Handle unauthorized
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      // Call logout and redirect to login
      // Using router.push instead of window.location to avoid page reload
      await authStore.logout()
      
      // Only redirect if not already on login page
      if (router.currentRoute.value.name !== 'login') {
        router.push({ 
          name: 'login', 
          query: { redirect: router.currentRoute.value.fullPath },
          replace: true 
        })
      }
    }
    
    return Promise.reject(error)
  }
)

export default instance

