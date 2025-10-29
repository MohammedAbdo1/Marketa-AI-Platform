import { defineStore } from 'pinia'
import createTableState from './table'
import axiosClient from '@/axios'

export const useRoleStore = defineStore('role', {
  state: () => ({
    roles: createTableState({}),
    currentRole: null,
  }),

  getters: {
    adminRoles: (state) => state.roles.data.filter(role => 
      ['admin', 'support', 'sales', 'manager'].includes(role.name)
    ),
  },

  actions: {
    async getRoles({ url = null, per_page, search, sort, direction } = {}) {
      this.roles.loading = true
      try {
        const endpoint = url || '/admin/roles'
        const params = {
          per_page: per_page || this.roles.filters.per_page || 10,
          search: search || this.roles.filters.search || '',
          sort: sort || this.roles.filters.sort || 'id',
          direction: direction || this.roles.filters.direction || 'desc',
        }
        const response = await axiosClient.get(endpoint, { params })
        this.roles.data = response.data.data
        this.roles.meta = {
          from: response.data.meta.from,
          to: response.data.meta.to,
          total: response.data.meta.total,
          current_page: response.data.meta.current_page,
          last_page: response.data.meta.last_page,
          links: response.data.meta.links || []
        }
        this.roles.loading = false
      } catch (error) {
        this.roles.loading = false
        console.error(error)
      }
    },

    async getRole(id) {
      try {
        const response = await axiosClient.get(`/admin/roles/${id}`)
        this.currentRole = response.data.data
        return response.data.data
      } catch (error) {
        throw error
      }
    },

    async createRole(data) {
      try {
        const response = await axiosClient.post('/admin/roles', data)
        return response.data.data
      } catch (error) {
        throw error
      }
    },

    async updateRole(id, data) {
      try {
        const response = await axiosClient.put(`/admin/roles/${id}`, data)
        return response.data.data
      } catch (error) {
        throw error
      }
    },

    async deleteRole(id) {
      try {
        await axiosClient.delete(`/admin/roles/${id}`)
      } catch (error) {
        throw error
      }
    },

    async syncPermissions(roleId, permissions) {
      try {
        const response = await axiosClient.post(`/admin/roles/${roleId}/permissions`, {
          permissions
        })
        return response.data.data
      } catch (error) {
        throw error
      }
    },
  },
})

