import axiosClient from '@/axios'

export const permissionService = {
  async getPermissions() {
    const response = await axiosClient.get('/admin/permissions')
    return response.data
  },
}

export default permissionService

