import { defineStore } from 'pinia'
import axiosClient from '@/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    permissions: JSON.parse(localStorage.getItem('permissions')) || [],
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => state.user !== null && state.user.token !== null,
    isLoggedIn: (state) => state.user !== null && state.user.token !== null,
    can: (state) => (permission) => state.permissions.includes(permission),
  },

  actions: {
    setToken(token) {
      if (this.user) {
        this.user.token = token
        localStorage.setItem('user', JSON.stringify(this.user))
      }
    },

    setUser(user) {
      this.user = user
      localStorage.setItem('user', JSON.stringify(user))
    },

    setPermissions(permissions) {
      this.permissions = permissions
      localStorage.setItem('permissions', JSON.stringify(permissions))
    },

    async login(credentials) {
      try {
        this.loading = true
        this.error = null

        const res = await axiosClient.post('/admin/login', credentials)
        
        this.user = res.data.user
        this.user.token = res.data.token // Add token to user object
        this.permissions = res.data.user.permissions || []
        
        // Save to localStorage
        localStorage.setItem('user', JSON.stringify(this.user))
        localStorage.setItem('permissions', JSON.stringify(this.permissions))

        return res.data
      } catch (err) {
        this.error = err.response?.data?.message || 'فشل تسجيل الدخول'
        throw err
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await axiosClient.post('/admin/logout')
      } catch (error) {
        // Ignore logout errors - we still want to clear the local state
        console.log('Logout request failed, but clearing local state')
      }
      
      this.user = null
      this.permissions = []
      
      // Clear from localStorage
      localStorage.removeItem('user')
      localStorage.removeItem('permissions')
      sessionStorage.removeItem('TOKEN')
    },

    async fetchUser() {
      try {
        const response = await axiosClient.get('/admin/me')
        this.user = response.data.user
        return response.data.user
      } catch (err) {
        await this.logout()
        throw err
      }
    },

    initFromStorage() {
      const storedUser = localStorage.getItem('user')
      const storedPermissions = localStorage.getItem('permissions')

      if (storedUser) {
        this.user = JSON.parse(storedUser)
      }
      if (storedPermissions) {
        this.permissions = JSON.parse(storedPermissions)
      }
    },
    
  },
})

