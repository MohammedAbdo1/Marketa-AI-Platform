import axiosClient from '@/axios'

export const roleService = {
  async getRoles(params = {}) {
    const response = await axiosClient.get('/admin/roles', { params })
    return response.data
  },

  async getRole(id) {
    const response = await axiosClient.get(`/admin/roles/${id}`)
    return response.data
  },

  async createRole(data) {
    const response = await axiosClient.post('/admin/roles', data)
    return response.data
  },

  async updateRole(id, data) {
    const response = await axiosClient.put(`/admin/roles/${id}`, data)
    return response.data
  },

  async deleteRole(id) {
    const response = await axiosClient.delete(`/admin/roles/${id}`)
    return response.data
  },

  async syncPermissions(roleId, permissions) {
    const response = await axiosClient.post(`/admin/roles/${roleId}/permissions`, {
      permissions
    })
    return response.data
  },
}

export default roleService

