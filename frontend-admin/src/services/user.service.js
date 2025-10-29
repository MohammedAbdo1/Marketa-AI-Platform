import axiosClient from '@/axios'

export const userService = {
  async getUsers(params = {}) {
    const response = await axiosClient.get('/admin/users', { params })
    return response.data
  },

  async getUser(uuid) {
    const response = await axiosClient.get(`/admin/users/${uuid}`)
    return response.data
  },

  async createUser(data) {
    const response = await axiosClient.post('/admin/users', data)
    return response.data
  },

  async updateUser(uuid, data) {
    const response = await axiosClient.put(`/admin/users/${uuid}`, data)
    return response.data
  },

  async deleteUser(uuid) {
    const response = await axiosClient.delete(`/admin/users/${uuid}`)
    return response.data
  },

  async updateUserStatus(uuid, status) {
    const response = await axiosClient.patch(`/admin/users/${uuid}/status`, { status })
    return response.data
  },
}

export default userService
