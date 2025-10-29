import axios from '../axios'

export default {
  getAll() {
    return axios.get('/admin/faqs')
  },

  getById(id) {
    return axios.get(`/admin/faqs/${id}`)
  },

  create(data) {
    return axios.post('/admin/faqs', data)
  },

  update(id, data) {
    return axios.put(`/admin/faqs/${id}`, data)
  },

  delete(id) {
    return axios.delete(`/admin/faqs/${id}`)
  }
}

