import axios from '../axios'

export default {
  getAll() {
    return axios.get('/admin/testimonials')
  },

  getById(id) {
    return axios.get(`/admin/testimonials/${id}`)
  },

  create(data) {
    return axios.post('/admin/testimonials', data)
  },

  update(id, data) {
    return axios.put(`/admin/testimonials/${id}`, data)
  },

  delete(id) {
    return axios.delete(`/admin/testimonials/${id}`)
  }
}

