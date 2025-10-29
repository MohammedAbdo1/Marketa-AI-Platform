import { defineStore } from 'pinia'
import createTableState from './table'
import axiosClient from '@/axios'

export const usePlanStore = defineStore('plan', {
  state: () => ({
    plans: createTableState({
      status: '',
      popular: '',
    }),
    currentPlan: null,
  }),

  getters: {
    activePlans: (state) => state.plans.data.filter(plan => plan.is_active),
    inactivePlans: (state) => state.plans.data.filter(plan => !plan.is_active),
    popularPlans: (state) => state.plans.data.filter(plan => plan.is_popular),
  },

  actions: {
    async getPlans({ url = null, per_page, search, sort, direction, status, popular } = {}) {
      this.plans.loading = true
      try {
        const endpoint = url || '/admin/plans'
        const params = {
          per_page: per_page || this.plans.filters.per_page || 10,
          search: search || this.plans.filters.search || '',
          sort: sort || this.plans.filters.sort || 'sort_order',
          direction: direction || this.plans.filters.direction || 'asc',
          status: status || this.plans.filters.status || '',
          popular: popular || this.plans.filters.popular || '',
        }
        const response = await axiosClient.get(endpoint, { params })
        this.plans.data = response.data.data
        this.plans.meta = {
          from: response.data.meta.from,
          to: response.data.meta.to,
          total: response.data.meta.total,
          current_page: response.data.meta.current_page,
          last_page: response.data.meta.last_page,
          links: response.data.meta.links || []
        }
        this.plans.loading = false
      } catch (error) {
        this.plans.loading = false
        console.error(error)
      }
    },

    async getPlan(id) {
      try {
        const response = await axiosClient.get(`/admin/plans/${id}`)
        this.currentPlan = response.data.data
        return response.data.data
      } catch (error) {
        throw error
      }
    },

    async createPlan(data) {
      try {
        const response = await axiosClient.post('/admin/plans', data)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async updatePlan(id, data) {
      try {
        const response = await axiosClient.put(`/admin/plans/${id}`, data)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async deletePlan(id) {
      try {
        await axiosClient.delete(`/admin/plans/${id}`)
      } catch (error) {
        throw error
      }
    },

    async updatePlanStatus(id, status) {
      try {
        const response = await axiosClient.patch(`/admin/plans/${id}/status`, { is_active: status })
        return response.data
      } catch (error) {
        throw error
      }
    },

    async togglePopular(id, popular) {
      try {
        const response = await axiosClient.patch(`/admin/plans/${id}/popular`, { is_popular: popular })
        return response.data
      } catch (error) {
        throw error
      }
    },
  },
})