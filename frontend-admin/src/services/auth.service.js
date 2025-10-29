import axiosClient from '@/axios'

export const authService = {
  async login(credentials) {
    const response = await axiosClient.post('/admin/login', credentials)
    return response.data
  },

  async logout() {
    const response = await axiosClient.post('/admin/logout')
    return response.data
  },

  async getMe() {
    const response = await axiosClient.get('/admin/me')
    return response.data
  },

  async getProfile() {
    const response = await axiosClient.get('/admin/profile')
    return response.data
  },

  async updateProfile(data) {
    const response = await axiosClient.put('/admin/profile', data)
    return response.data
  },
}

export default authService

