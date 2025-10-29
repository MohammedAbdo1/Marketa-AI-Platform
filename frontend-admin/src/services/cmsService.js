import axios from '../axios'

export default {
  // Pages
  getPages() {
    return axios.get('/admin/cms/pages')
  },

  updatePage(id, data) {
    return axios.put(`/admin/cms/pages/${id}`, data)
  },

  // Sections
  getSections() {
    return axios.get('/admin/cms/sections')
  },

  createSection(data) {
    return axios.post('/admin/cms/sections', data)
  },

  updateSection(id, data) {
    return axios.put(`/admin/cms/sections/${id}`, data)
  },

  deleteSection(id) {
    return axios.delete(`/admin/cms/sections/${id}`)
  },

  // Content
  createContent(data) {
    return axios.post('/admin/cms/content', data)
  },

  updateContent(id, data) {
    return axios.put(`/admin/cms/content/${id}`, data)
  },

  deleteContent(id) {
    return axios.delete(`/admin/cms/content/${id}`)
  }
}

