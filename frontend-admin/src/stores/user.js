import { defineStore } from 'pinia'
import userService from '@/services/user.service'
import createTableState from './table'
import axiosClient from '@/axios'

export const useUserStore = defineStore('user', {
  state: () => ({
    users: createTableState({}),
    currentUser: null,
  }),

  getters: {
    activeUsers: (state) => state.users.data.filter(user => user.status === 'active'),
    admins: (state) => state.users.data.filter(user => user.roles?.includes('admin')),
    customers: (state) => state.users.data.filter(user => user.roles?.includes('user')),
  },

  actions: {
    async getUsers({ url = null, per_page, search, sort, direction } = {}) {
      this.users.loading = true
      try {
        const endpoint = url || '/admin/users'
        const params = {
          per_page: per_page || this.users.filters.per_page || 10,
          search: search || this.users.filters.search || '',
          sort: sort || this.users.filters.sort || 'id',
          direction: direction || this.users.filters.direction || 'desc',
        }
        const response = await axiosClient.get(endpoint, { params })
        this.users.data = response.data.data
        this.users.meta = {
          from: response.data.meta.from,
          to: response.data.meta.to,
          total: response.data.meta.total,
          current_page: response.data.meta.current_page,
          last_page: response.data.meta.last_page,
          links: response.data.meta.links || []
        }
        this.users.loading = false
      } catch (error) {
        this.users.loading = false
        console.error(error)
      }
    },

    async createUser(data) {
      try {
        const response = await userService.createUser(data)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async updateUser(uuid, data) {
      try {
        const response = await userService.updateUser(uuid, data)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async deleteUser(uuid) {
      try {
        await userService.deleteUser(uuid)
      } catch (error) {
        throw error
      }
    },

    async updateUserStatus(uuid, status) {
      try {
        const response = await axiosClient.patch(`/admin/users/${uuid}/status`, { status })
        return response.data
      } catch (error) {
        throw error
      }
    },
  },
})
