import { defineStore } from 'pinia'
import createTableState from './table'
import axiosClient from '@/axios'

export const useOrganizationStore = defineStore('organization', {
  state: () => ({
    organizations: createTableState({
      status: '',
    }),
    currentOrganization: null,
  }),

  getters: {
    activeOrganizations: (state) => state.organizations.data.filter(org => org.status === 'active'),
    suspendedOrganizations: (state) => state.organizations.data.filter(org => org.status === 'suspended'),
    trialOrganizations: (state) => state.organizations.data.filter(org => 
      org.trial_ends_at && new Date(org.trial_ends_at) > new Date()
    ),
  },

  actions: {
    async getOrganizations({ url = null, per_page, search, sort, direction, status } = {}) {
      this.organizations.loading = true
      try {
        const endpoint = url || '/admin/organizations'
        const params = {
          per_page: per_page || this.organizations.filters.per_page || 10,
          search: search || this.organizations.filters.search || '',
          sort: sort || this.organizations.filters.sort || 'id',
          direction: direction || this.organizations.filters.direction || 'desc',
          status: status || this.organizations.filters.status || '',
        }
        const response = await axiosClient.get(endpoint, { params })
        this.organizations.data = response.data.data
        this.organizations.meta = {
          from: response.data.meta.from,
          to: response.data.meta.to,
          total: response.data.meta.total,
          current_page: response.data.meta.current_page,
          last_page: response.data.meta.last_page,
          links: response.data.meta.links || []
        }
        this.organizations.loading = false
      } catch (error) {
        this.organizations.loading = false
        console.error(error)
      }
    },

    async getOrganization(uuid) {
      try {
        const response = await axiosClient.get(`/admin/organizations/${uuid}`)
        this.currentOrganization = response.data.data
        return response.data.data
      } catch (error) {
        throw error
      }
    },

    async createOrganization(data) {
      try {
        const response = await axiosClient.post('/admin/organizations', data)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async updateOrganization(uuid, data) {
      try {
        const response = await axiosClient.put(`/admin/organizations/${uuid}`, data)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async deleteOrganization(uuid) {
      try {
        await axiosClient.delete(`/admin/organizations/${uuid}`)
      } catch (error) {
        throw error
      }
    },

    async updateOrganizationStatus(uuid, status) {
      try {
        const response = await axiosClient.patch(`/admin/organizations/${uuid}/status`, { status })
        return response.data
      } catch (error) {
        throw error
      }
    },
  },
})