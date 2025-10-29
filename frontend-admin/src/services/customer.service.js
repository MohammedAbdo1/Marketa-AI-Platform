import axiosClient from '@/axios'

export const customerService = {
  async getCustomers(params = {}) {
    const response = await axiosClient.get('/admin/customers', { params })
    return response.data
  },

  async getCustomer(uuid) {
    const response = await axiosClient.get(`/admin/customers/${uuid}`)
    return response.data
  },

  async getCustomerDetails(uuid) {
    const response = await axiosClient.get(`/admin/customers/${uuid}/details`)
    return response.data
  },

  async createCustomer(data) {
    const response = await axiosClient.post('/admin/customers', data)
    return response.data
  },

  async updateCustomer(uuid, data) {
    const response = await axiosClient.put(`/admin/customers/${uuid}`, data)
    return response.data
  },

  async deleteCustomer(uuid) {
    const response = await axiosClient.delete(`/admin/customers/${uuid}`)
    return response.data
  },

  async updateCustomerStatus(uuid, status) {
    const response = await axiosClient.patch(`/admin/customers/${uuid}/status`, { status })
    return response.data
  },
}

export default customerService

