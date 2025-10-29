import axios from "axios"
import { useAuthStore } from "@/stores/auth"
import router from "@/router"

const axiosClient = axios.create({
  baseURL: (import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000") + "/api",
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor - add auth token
axiosClient.interceptors.request.use(config => {
  const auth = useAuthStore()
  if (auth.user?.token) {
    config.headers.Authorization = `Bearer ${auth.user.token}`
  }
  return config
})

// Response interceptor - handle errors
axiosClient.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      const auth = useAuthStore()
      // Only logout if it's not already a logout request to prevent infinite loop
      if (!error.config.url.includes('/admin/logout')) {
        auth.logout()
        router.push({ name: "login" })
      }
    }
    return Promise.reject(error)
  }
)

export default axiosClient
