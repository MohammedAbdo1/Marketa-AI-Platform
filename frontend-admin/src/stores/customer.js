import { defineStore } from 'pinia'
import createTableState from './table'
import axiosClient from '@/axios'

export const useCustomerStore = defineStore('customer', {
  state: () => ({
    customers: createTableState({
      plan_id: '',
      subscription_status: '',
    }),
    currentCustomer: null,
  }),

  getters: {
    activeCustomers: (state) => state.customers.data.filter(c => c.status === 'active'),
    trialCustomers: (state) => state.customers.data.filter(c => 
      c.active_subscription?.status === 'trial'
    ),
    expiredCustomers: (state) => state.customers.data.filter(c => 
      c.active_subscription?.status === 'expired'
    ),
  },

  actions: {
    async getCustomers({ url = null, per_page, search, sort, direction, plan_id, subscription_status } = {}) {
      this.customers.loading = true
      try {
        const endpoint = url || '/admin/customers'
        const params = {
          per_page: per_page || this.customers.filters.per_page || 10,
          search: search || this.customers.filters.search || '',
          sort: sort || this.customers.filters.sort || 'id',
          direction: direction || this.customers.filters.direction || 'desc',
          plan_id: plan_id || this.customers.filters.plan_id || '',
          subscription_status: subscription_status || this.customers.filters.subscription_status || '',
        }
        const response = await axiosClient.get(endpoint, { params })
        this.customers.data = response.data.data
        this.customers.meta = {
          from: response.data.meta.from,
          to: response.data.meta.to,
          total: response.data.meta.total,
          current_page: response.data.meta.current_page,
          last_page: response.data.meta.last_page,
          links: response.data.meta.links || []
        }
        this.customers.loading = false
      } catch (error) {
        this.customers.loading = false
        console.error(error)
      }
    },

    async getCustomerDetails(uuid) {
      try {
        const response = await axiosClient.get(`/admin/customers/${uuid}/details`)
        this.currentCustomer = response.data.data
        return response.data.data
      } catch (error) {
        throw error
      }
    },

    async createCustomer(data) {
      try {
        const response = await axiosClient.post('/admin/customers', data)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async updateCustomer(uuid, data) {
      try {
        const response = await axiosClient.put(`/admin/customers/${uuid}`, data)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async deleteCustomer(uuid) {
      try {
        await axiosClient.delete(`/admin/customers/${uuid}`)
      } catch (error) {
        throw error
      }
    },

    async updateCustomerStatus(uuid, status) {
      try {
        const response = await axiosClient.patch(`/admin/customers/${uuid}/status`, { status })
        return response.data
      } catch (error) {
        throw error
      }
    },
  },
})

