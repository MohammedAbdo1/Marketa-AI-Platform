import { defineStore } from 'pinia'
import axiosClient from '@/axios'

export const usePermissionStore = defineStore('permission', {
  state: () => ({
    permissions: {},
    loading: false,
    error: null,
  }),

  getters: {
    permissionsByModule: (state) => state.permissions,
    allPermissions: (state) => {
      const all = []
      Object.keys(state.permissions).forEach(module => {
        all.push(...state.permissions[module])
      })
      return all
    },
  },

  actions: {
    async getPermissions() {
      this.loading = true
      try {
        const response = await axiosClient.get('/admin/permissions')
        this.permissions = response.data.data
        this.loading = false
      } catch (error) {
        this.loading = false
        this.error = error.response?.data?.message || 'Failed to load permissions'
        console.error(error)
      }
    },
  },
})

