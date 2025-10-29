import axiosClient from '@/axios'

export const organizationService = {
  async getOrganizations(params = {}) {
    const response = await axiosClient.get('/admin/organizations', { params })
    return response.data
  },

  async getOrganization(uuid) {
    const response = await axiosClient.get(`/admin/organizations/${uuid}`)
    return response.data
  },

  async createOrganization(data) {
    const response = await axiosClient.post('/admin/organizations', data)
    return response.data
  },

  async updateOrganization(uuid, data) {
    const response = await axiosClient.put(`/admin/organizations/${uuid}`, data)
    return response.data
  },

  async deleteOrganization(uuid) {
    const response = await axiosClient.delete(`/admin/organizations/${uuid}`)
    return response.data
  },

  async updateOrganizationStatus(uuid, status) {
    const response = await axiosClient.patch(`/admin/organizations/${uuid}/status`, { status })
    return response.data
  },
}