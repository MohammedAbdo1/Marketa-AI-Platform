import { defineStore } from 'pinia'
import createTableState from './table'
import axiosClient from '@/axios'

export const useAdminStore = defineStore('admin', {
  state: () => ({
    admins: createTableState({}),
    currentAdmin: null,
  }),

  getters: {
    activeAdmins: (state) => state.admins.data.filter(admin => admin.status === 'active'),
  },

  actions: {
    async getAdmins({ url = null, per_page, search, sort, direction } = {}) {
      this.admins.loading = true
      try {
        const endpoint = url || '/admin/admins'
        const params = {
          per_page: per_page || this.admins.filters.per_page || 10,
          search: search || this.admins.filters.search || '',
          sort: sort || this.admins.filters.sort || 'id',
          direction: direction || this.admins.filters.direction || 'desc',
        }
        const response = await axiosClient.get(endpoint, { params })
        this.admins.data = response.data.data
        this.admins.meta = {
          from: response.data.meta.from,
          to: response.data.meta.to,
          total: response.data.meta.total,
          current_page: response.data.meta.current_page,
          last_page: response.data.meta.last_page,
          links: response.data.meta.links || []
        }
        this.admins.loading = false
      } catch (error) {
        this.admins.loading = false
        console.error('Error fetching admins:', error)
      }
    },

    async createAdmin(data) {
      try {
        const response = await axiosClient.post('/admin/users', data)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async updateAdmin(uuid, data) {
      try {
        const response = await axiosClient.put(`/admin/users/${uuid}`, data)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async deleteAdmin(uuid) {
      try {
        await axiosClient.delete(`/admin/users/${uuid}`)
      } catch (error) {
        throw error
      }
    },

    async updateAdminStatus(uuid, status) {
      try {
        const response = await axiosClient.patch(`/admin/users/${uuid}/status`, { status })
        return response.data
      } catch (error) {
        throw error
      }
    },
  },
})

