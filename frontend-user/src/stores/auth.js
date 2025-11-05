import { defineStore } from 'pinia'
import axios from '../axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('cached_user')) || null,
    token: localStorage.getItem('token') || null,
    isAuthenticated: !!localStorage.getItem('token'),
    userLastFetch: localStorage.getItem('user_last_fetch') || null,
    loading: false,
    error: null,
    isLoggingOut: false
  }),

  getters: {
    isLoggedIn: (state) => state.isAuthenticated,
    currentUser: (state) => state.user
  },

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.post('/login', credentials)
        
        this.token = response.data.token
        this.user = response.data.user
        this.isAuthenticated = true
        
        // Cache token and user data
        localStorage.setItem('token', response.data.token)
        localStorage.setItem('cached_user', JSON.stringify(this.user))
        this.userLastFetch = new Date().toISOString()
        localStorage.setItem('user_last_fetch', this.userLastFetch)
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(data) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.post('/register', data)
        
        // Save token and user data
        this.token = response.data.token
        this.user = response.data.user
        this.isAuthenticated = true
        
        localStorage.setItem('token', response.data.token)
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || error.response?.data?.error || 'Registration failed'
        console.error('Registration error in store:', error.response?.data)
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      // Prevent multiple simultaneous logout calls
      if (this.isLoggingOut) {
        return
      }
      
      this.isLoggingOut = true
      
      try {
        await axios.post('/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        this.isAuthenticated = false
        this.isLoggingOut = false
        this.userLastFetch = null
        
        // Clear all cached data
        localStorage.removeItem('token')
        localStorage.removeItem('cached_user')
        localStorage.removeItem('user_last_fetch')
      }
    },

    async fetchUser() {
      if (!this.token || this.isLoggingOut) return
      
      // Check if we have fresh cached data (within 5 minutes)
      const CACHE_TTL = 5 * 60 * 1000 // 5 minutes in milliseconds
      const now = Date.now()
      const lastFetch = this.userLastFetch ? new Date(this.userLastFetch).getTime() : 0
      const cacheAge = now - lastFetch
      
      // If cache is fresh and we have user data, use it
      if (cacheAge < CACHE_TTL && this.user) {
        console.debug('Using cached user data', { cacheAge: Math.round(cacheAge / 1000) + 's' })
        return
      }
      
      try {
        // Use lightweight /me endpoint for faster authentication checks
        const response = await axios.get('/me')
        this.user = response.data.user
        
        // Cache user data and timestamp in localStorage
        localStorage.setItem('cached_user', JSON.stringify(this.user))
        this.userLastFetch = new Date().toISOString()
        localStorage.setItem('user_last_fetch', this.userLastFetch)
        
        console.debug('Fetched fresh user data from API')
      } catch (error) {
        // Don't call logout here - let axios interceptor handle 401 errors
        // to avoid potential infinite loops
        console.error('Fetch user error:', error)
        throw error
      }
    },

    async fetchFullProfile() {
      // Use this for profile page where complete information is needed
      if (!this.token || this.isLoggingOut) return
      
      try {
        const response = await axios.get('/profile')
        this.user = response.data.user
      } catch (error) {
        console.error('Fetch full profile error:', error)
        throw error
      }
    },

    async updateProfile(data) {
      this.loading = true
      try {
        const response = await axios.put('/profile', data)
        this.user = response.data.user
        
        // Update cache with new user data
        localStorage.setItem('cached_user', JSON.stringify(this.user))
        this.userLastFetch = new Date().toISOString()
        localStorage.setItem('user_last_fetch', this.userLastFetch)
        
        return response.data
      } catch (error) {
        throw error
      } finally {
        this.loading = false
      }
    },
    
    // Helper method to manually invalidate cache
    invalidateCache() {
      localStorage.removeItem('cached_user')
      localStorage.removeItem('user_last_fetch')
      this.userLastFetch = null
    }
  }
})

