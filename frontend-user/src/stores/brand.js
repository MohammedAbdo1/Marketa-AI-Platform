import { defineStore } from 'pinia'
import { brandService } from '@/services/brandService'

const initialState = () => ({
  brands: [],
  currentBrand: null,
  loading: false,
  assetsLoading: false,
  saving: false,
  error: null,
  filters: {
    search: '',
    status: 'all',
  },
})

const getResponseData = (response) => response?.data ?? response

export const useBrandStore = defineStore('brand', {
  state: () => initialState(),

  getters: {
    hasBrands: (state) => state.brands.length > 0,

    defaultBrand: (state) => state.brands.find((brand) => brand.is_default),

    sortedBrands: (state) => {
      const cloned = [...state.brands]
      return cloned.sort((a, b) => {
        if (a.is_default && !b.is_default) return -1
        if (!a.is_default && b.is_default) return 1
        return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' })
      })
    },

    filteredBrands() {
      const search = this.filters.search.trim().toLowerCase()
      const status = this.filters.status

      return this.sortedBrands.filter((brand) => {
        const matchesSearch =
          !search ||
          brand.name.toLowerCase().includes(search) ||
          (brand.tagline && brand.tagline.toLowerCase().includes(search)) ||
          (brand.keywords || [])
            .join(' ')
            .toLowerCase()
            .includes(search)

        const matchesStatus =
          status === 'all' ||
          (status === 'active' && brand.status === 'active') ||
          (status === 'inactive' && brand.status !== 'active')

        return matchesSearch && matchesStatus
      })
    },
  },

  actions: {
    setFilters(partial) {
      this.filters = { ...this.filters, ...partial }
    },

    resetState() {
      Object.assign(this, initialState())
    },

    async fetchBrands(params = {}) {
      this.loading = true
      this.error = null

      try {
        const response = await brandService.getBrands(params)
        this.brands = getResponseData(response) || []

        if (this.currentBrand) {
          const updated = this.brands.find((brand) => brand.id === this.currentBrand.id)
          if (updated) {
            this.currentBrand = updated
          }
        } else if (!this.currentBrand && this.brands.length > 0) {
          this.currentBrand = this.defaultBrand || this.brands[0]
        }

        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر تحميل العلامات التجارية'
        this.brands = []
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchBrand(id) {
      this.loading = true
      this.error = null

      try {
        const response = await brandService.getBrand(id)
        const payload = getResponseData(response)
        this.updateBrandState(payload)
        this.currentBrand = payload
        return payload
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر تحميل العلامة'
        throw error
      } finally {
        this.loading = false
      }
    },

    async refreshCurrentBrand() {
      if (!this.currentBrand?.id) return null
      return this.fetchBrand(this.currentBrand.id)
    },

    async createBrand(payload) {
      this.saving = true
      this.error = null

      try {
        const response = await brandService.createBrand(payload)
        const created = getResponseData(response)
        this.updateBrandState(created, { preferPrepend: true })
        this.currentBrand = created
        return created
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر إنشاء العلامة'
        throw error
      } finally {
        this.saving = false
      }
    },

    async updateBrand(id, payload) {
      this.saving = true
      this.error = null

      try {
        const response = await brandService.updateBrand(id, payload)
        const updated = getResponseData(response)
        this.updateBrandState(updated)
        if (this.currentBrand?.id === id) {
          this.currentBrand = updated
        }
        return updated
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر تحديث العلامة'
        throw error
      } finally {
        this.saving = false
      }
    },

    async deleteBrand(id) {
      this.saving = true
      this.error = null

      try {
        await brandService.deleteBrand(id)
        this.brands = this.brands.filter((brand) => brand.id !== id)

        if (this.currentBrand?.id === id) {
          this.currentBrand = this.defaultBrand || this.brands[0] || null
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر حذف العلامة'
        throw error
      } finally {
        this.saving = false
      }
    },

    async uploadLogo(id, file, label = null) {
      this.saving = true
      this.error = null

      try {
        const response = await brandService.uploadLogo(id, file, label)
        const payload = getResponseData(response)
        if (payload?.brand) {
          this.updateBrandState(payload.brand)
          if (this.currentBrand?.id === payload.brand.id) {
            this.currentBrand = payload.brand
          }
        }
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر رفع الشعار'
        throw error
      } finally {
        this.saving = false
      }
    },

    async setDefaultBrand(id) {
      await this.updateBrand(id, { is_default: true })
    },

    async fetchBrandAssets(brandId) {
      this.assetsLoading = true
      this.error = null

      try {
        const response = await brandService.listAssets(brandId)
        const assets = getResponseData(response) || []
        this.applyAssetsToBrand(brandId, assets)
        return assets
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر تحميل أصول العلامة'
        throw error
      } finally {
        this.assetsLoading = false
      }
    },

    async createAsset(brandId, payload) {
      this.saving = true
      this.error = null

      try {
        const response = await brandService.createAsset(brandId, payload)
        const data = getResponseData(response)
        const brand = data?.brand ?? await this.fetchBrand(brandId)
        this.updateBrandState(brand)
        if (this.currentBrand?.id === brandId) {
          this.currentBrand = brand
        }
        return data
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر إضافة الأصل'
        throw error
      } finally {
        this.saving = false
      }
    },

    async updateAsset(brandId, assetId, payload) {
      this.saving = true
      this.error = null

      try {
        const response = await brandService.updateAsset(brandId, assetId, payload)
        const data = getResponseData(response)
        const brand = data?.brand ?? await this.fetchBrand(brandId)
        this.updateBrandState(brand)
        if (this.currentBrand?.id === brandId) {
          this.currentBrand = brand
        }
        return data
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر تحديث الأصل'
        throw error
      } finally {
        this.saving = false
      }
    },

    async deleteAsset(brandId, assetId) {
      this.saving = true
      this.error = null

      try {
        const response = await brandService.deleteAsset(brandId, assetId)
        const brand = getResponseData(response) ?? await this.fetchBrand(brandId)
        this.updateBrandState(brand)
        if (this.currentBrand?.id === brandId) {
          this.currentBrand = brand
        }
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر حذف الأصل'
        throw error
      } finally {
        this.saving = false
      }
    },

    async markAssetPrimary(brandId, assetId) {
      this.saving = true
      this.error = null

      try {
        const response = await brandService.markAssetPrimary(brandId, assetId)
        const brand = getResponseData(response) ?? await this.fetchBrand(brandId)
        this.updateBrandState(brand)
        if (this.currentBrand?.id === brandId) {
          this.currentBrand = brand
        }
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر تحديث الأصل'
        throw error
      } finally {
        this.saving = false
      }
    },

    async reorderAssets(brandId, order) {
      this.saving = true
      this.error = null

      try {
        const response = await brandService.reorderAssets(brandId, order)
        const brand = getResponseData(response) ?? await this.fetchBrand(brandId)
        this.updateBrandState(brand)
        if (this.currentBrand?.id === brandId) {
          this.currentBrand = brand
        }
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'تعذّر إعادة ترتيب الأصول'
        throw error
      } finally {
        this.saving = false
      }
    },

    setCurrentBrand(brand) {
      this.currentBrand = brand
    },

    clearCurrentBrand() {
      this.currentBrand = null
    },

    clearError() {
      this.error = null
    },

    updateBrandState(brand, options = {}) {
      if (!brand) return

      const index = this.brands.findIndex((item) => item.id === brand.id)
      if (index !== -1) {
        this.brands.splice(index, 1, brand)
      } else if (options.preferPrepend) {
        this.brands.unshift(brand)
      } else {
        this.brands.push(brand)
      }
    },

    applyAssetsToBrand(brandId, assets) {
      const brand = this.brands.find((item) => item.id === brandId)
      if (!brand) return

      brand.assets = assets
      if (this.currentBrand?.id === brandId) {
        this.currentBrand = { ...brand, assets }
      }
    },
  },
})

