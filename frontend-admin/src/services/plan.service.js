import axiosClient from '@/axios'

export const planService = {
  async getPlans(params = {}) {
    const response = await axiosClient.get('/admin/plans', { params })
    return response.data
  },

  async getPlan(id) {
    const response = await axiosClient.get(`/admin/plans/${id}`)
    return response.data
  },

  async createPlan(data) {
    const response = await axiosClient.post('/admin/plans', data)
    return response.data
  },

  async updatePlan(id, data) {
    const response = await axiosClient.put(`/admin/plans/${id}`, data)
    return response.data
  },

  async deletePlan(id) {
    const response = await axiosClient.delete(`/admin/plans/${id}`)
    return response.data
  },

  async updatePlanStatus(id, status) {
    const response = await axiosClient.patch(`/admin/plans/${id}/status`, { is_active: status })
    return response.data
  },

  async togglePopular(id, popular) {
    const response = await axiosClient.patch(`/admin/plans/${id}/popular`, { is_popular: popular })
    return response.data
  },
}