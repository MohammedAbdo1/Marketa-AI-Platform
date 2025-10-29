import { defineStore } from 'pinia'
import axios from '../axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    isAuthenticated: !!localStorage.getItem('token'),
    loading: false,
    error: null
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
        
        localStorage.setItem('token', response.data.token)
        
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
      try {
        await axios.post('/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        this.isAuthenticated = false
        localStorage.removeItem('token')
      }
    },

    async fetchUser() {
      if (!this.token) return
      
      try {
        const response = await axios.get('/profile')
        this.user = response.data.user
      } catch (error) {
        this.logout()
      }
    },

    async updateProfile(data) {
      this.loading = true
      try {
        const response = await axios.put('/profile', data)
        this.user = response.data.user
        return response.data
      } catch (error) {
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})

