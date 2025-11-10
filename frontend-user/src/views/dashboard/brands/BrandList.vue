<template>
  <div class="brands-dashboard">
    <div class="card card-flat brand-header">
      <div class="card-body d-flex flex-column gap-4">
        <div class="d-flex flex-column gap-2">
          <h1 class="text-2xl font-semibold text-primary">
            دليل الهوية البصرية
          </h1>
          <p class="text-secondary text-sm">
            نظم شعاراتك، ألوانك، خطوطك وكل موارد الهوية في مكان واحد مع دعم كامل للغة العربية.
          </p>
        </div>

        <div class="d-flex flex-column gap-3">
          <div class="d-flex flex-column flex-col-mobile gap-2 brand-toolbar">
            <div class="d-flex flex-column flex-col-mobile gap-2 flex-grow">
              <input
                class="form-control form-control-lg"
                :placeholder="'ابحث عن علامة أو كلمة مفتاحية'"
                v-model="filters.search"
                @input="applyFilters"
              />
            </div>

            <div class="d-flex flex-row flex-col-mobile gap-2 brand-actions">
              <select
                class="form-control form-control-lg min-w-44"
                v-model="filters.status"
                @change="applyFilters"
              >
                <option value="all">كل العلامات</option>
                <option value="active">مفعّلة</option>
                <option value="inactive">مؤرشفة</option>
              </select>
              <button
                class="btn btn-secondary btn-icon"
                type="button"
                @click="refreshBrands"
                :disabled="isLoading"
              >
                <i class="bx bx-refresh"></i>
              </button>
              <button
                class="btn btn-primary"
                type="button"
                @click="goToCreate"
              >
                <i class="bx bx-plus me-1"></i>
                أنشئ علامة جديدة
              </button>
            </div>
          </div>

          <div class="d-grid stats-grid">
            <div class="card stat-card">
              <div class="card-body d-flex items-center justify-between">
                <div class="d-flex flex-column gap-1">
                  <span class="text-secondary text-xs">إجمالي العلامات</span>
                  <span class="text-xl font-semibold">{{ metrics.total }}</span>
                </div>
                <i class="bx bx-layer text-brand icon-circle"></i>
              </div>
            </div>
            <div class="card stat-card">
              <div class="card-body d-flex items-center justify-between">
                <div class="d-flex flex-column gap-1">
                  <span class="text-secondary text-xs">العلامات المفعّلة</span>
                  <span class="text-xl font-semibold">{{ metrics.active }}</span>
                </div>
                <i class="bx bx-bulb text-success icon-circle"></i>
              </div>
            </div>
            <div class="card stat-card">
              <div class="card-body d-flex items-center justify-between">
                <div class="d-flex flex-column gap-1">
                  <span class="text-secondary text-xs">إجمالي الأصول</span>
                  <span class="text-xl font-semibold">{{ metrics.assets }}</span>
                </div>
                <i class="bx bx-folder text-info icon-circle"></i>
              </div>
            </div>
            <div class="card stat-card">
              <div class="card-body d-flex items-center justify-between">
                <div class="d-flex flex-column gap-1">
                  <span class="text-secondary text-xs">العلامة الافتراضية</span>
                  <span class="text-xl font-semibold truncate">{{ defaultBrandName }}</span>
                </div>
                <i class="bx bx-star text-warning icon-circle"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="brand-layout">
      <div class="card brand-list">
        <div class="card-header d-flex items-center justify-between">
          <h2 class="card-title text-md font-semibold text-primary">
            مكتبة العلامات
          </h2>
          <span class="badge badge-secondary">{{ filteredBrands.length }} علامة</span>
        </div>
        <div class="card-body">
          <div v-if="isLoading" class="empty-state">
            <div class="d-flex flex-column items-center gap-2 text-secondary">
              <i class="bx bx-loader-alt bx-spin text-2xl"></i>
              <span>جاري تحميل العلامات...</span>
            </div>
          </div>

          <div v-else-if="filteredBrands.length === 0" class="empty-state">
            <div class="card card-flat">
              <div class="card-body d-flex flex-column items-center gap-3 text-center">
                <i class="bx bx-palette text-3xl text-secondary"></i>
                <div>
                  <h3 class="text-md font-semibold text-primary">لا توجد علامات بعد</h3>
                  <p class="text-secondary text-sm mt-1">
                    ابدأ بإنشاء أول هوية بصرية لك أو عدّل عوامل البحث.
                  </p>
                </div>
                <button class="btn btn-primary" type="button" @click="goToCreate">
                  أنشئ أول علامة
                </button>
              </div>
            </div>
          </div>

          <div v-else class="brand-grid">
            <button
              v-for="brand in filteredBrands"
              :key="brand.id"
              type="button"
              class="card brand-tile"
              :class="{
                'is-selected': currentBrand && currentBrand.id === brand.id,
                'is-default': brand.is_default
              }"
              @click="handleSelectBrand(brand)"
            >
              <div class="card-body d-flex flex-column gap-3 items-start">
                <div class="d-flex items-center gap-3 w-100">
                  <div class="brand-avatar">
                    <span v-if="brand.logo_url" class="brand-logo-wrapper">
                      <img :src="brand.logo_url" :alt="brand.name" />
                    </span>
                    <span v-else class="brand-initial">
                      {{ brand.name?.charAt(0)?.toUpperCase() ?? 'B' }}
                    </span>
                  </div>
                  <div class="d-flex flex-column gap-1">
                    <span class="text-md font-semibold text-primary">{{ brand.name }}</span>
                    <span class="text-secondary text-xs truncate" v-if="brand.tagline">
                      {{ brand.tagline }}
                    </span>
                  </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                  <span class="badge badge-ghost" v-if="brand.status === 'active'">مفعّلة</span>
                  <span class="badge badge-warning" v-else>مؤرشفة</span>
                  <span class="badge badge-primary" v-if="brand.is_default">العلامة الأساسية</span>
                </div>

                <div class="d-flex flex-column gap-2 w-100">
                  <div class="d-flex items-center justify-between">
                    <span class="text-xs text-secondary">الأصول</span>
                    <span class="text-xs text-primary font-medium">
                      {{ (brand.assets && brand.assets.length) || 0 }}
                    </span>
                  </div>
                  <div class="d-flex items-center justify-between">
                    <span class="text-xs text-secondary">نبرة الصوت</span>
                    <span class="text-xs text-brand font-medium truncate">
                      {{ brand.brand_voice || 'لم يحدد' }}
                    </span>
                  </div>
                </div>
              </div>
            </button>
          </div>
        </div>
      </div>

      <div class="card brand-details" v-if="currentBrand">
        <div class="card-header d-flex flex-column gap-2">
          <div class="d-flex items-start justify-between gap-3">
            <div class="d-flex flex-column gap-1">
              <h2 class="card-title text-lg font-semibold text-primary">
                {{ currentBrand.name }}
              </h2>
              <p class="text-secondary text-sm" v-if="currentBrand.tagline">
                {{ currentBrand.tagline }}
              </p>
            </div>
            <div class="d-flex items-center gap-2">
              <button
                v-if="!currentBrand.is_default"
                class="btn btn-secondary btn-sm"
                type="button"
                @click="markAsDefault"
                :disabled="isSaving"
              >
                <i class="bx bx-star me-1"></i>
                اجعلها أساسية
              </button>
              <button
                class="btn btn-ghost btn-sm"
                type="button"
                @click="goToEdit(currentBrand)"
              >
                <i class="bx bx-edit me-1"></i>
                تعديل
              </button>
              <button
                class="btn btn-ghost btn-icon btn-sm"
                type="button"
                @click="openLogoUploader"
              >
                <i class="bx bx-upload"></i>
              </button>
              <button
                class="btn btn-danger btn-icon btn-sm"
                type="button"
                @click="handleDeleteBrand"
                :disabled="isSaving"
              >
                <i class="bx bx-trash"></i>
              </button>
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2">
            <span class="badge badge-success" v-if="currentBrand.is_default">
              العلامة الأساسية
            </span>
            <span class="badge badge-ghost" v-if="currentBrand.status === 'active'">
              نشطة
            </span>
            <span class="badge badge-warning" v-else>
              غير نشطة
            </span>
          </div>
        </div>

        <div class="card-body d-flex flex-column gap-4">
          <div class="card card-flat">
            <div class="card-body d-flex flex-column gap-3">
              <h3 class="text-sm font-semibold text-primary">بيانات الهوية</h3>
              <div class="d-grid identity-grid">
                <div class="identity-item">
                  <span class="text-xs text-secondary">الألوان الأساسية</span>
                  <div class="d-flex flex-column gap-1 text-sm font-medium">
                    <span v-if="currentBrand.primary_color">أساسي: {{ currentBrand.primary_color }}</span>
                    <span v-if="currentBrand.secondary_color">ثانوي: {{ currentBrand.secondary_color }}</span>
                    <span v-if="currentBrand.accent_color">إبراز: {{ currentBrand.accent_color }}</span>
                    <span v-if="(!currentBrand.primary_color && !currentBrand.secondary_color && !currentBrand.accent_color)" class="text-secondary">
                      لم يتم تحديد الألوان
                    </span>
                  </div>
                </div>
                <div class="identity-item">
                  <span class="text-xs text-secondary">الخطوط</span>
                  <div class="d-flex flex-column gap-1 text-sm font-medium">
                    <span v-if="currentBrand.font_arabic">عربي: {{ currentBrand.font_arabic }}</span>
                    <span v-if="currentBrand.font_english">إنجليزي: {{ currentBrand.font_english }}</span>
                    <span v-if="(!currentBrand.font_arabic && !currentBrand.font_english)" class="text-secondary">
                      لم يتم تحديد الخطوط
                    </span>
                  </div>
                </div>
                <div class="identity-item">
                  <span class="text-xs text-secondary">نبرة الصوت</span>
                  <p class="text-sm text-primary">
                    {{ currentBrand.brand_voice || 'لم يتم تحديدها بعد' }}
                  </p>
                </div>
                <div class="identity-item">
                  <span class="text-xs text-secondary">الكلمات المفتاحية</span>
                  <div class="d-flex flex-wrap gap-2">
                    <span
                      v-for="keyword in (currentBrand.keywords || [])"
                      :key="keyword"
                      class="badge badge-ghost"
                    >
                      {{ keyword }}
                    </span>
                    <span v-if="!currentBrand.keywords || currentBrand.keywords.length === 0" class="text-secondary text-sm">
                      لم يتم إضافة كلمات
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card card-flat">
            <div class="card-header d-flex items-center justify-between">
              <h3 class="text-sm font-semibold text-primary">أصول الهوية</h3>
              <button class="btn btn-primary btn-sm" type="button" @click="openAssetModal">
                <i class="bx bx-folder-plus me-1"></i>
                أصل جديد
              </button>
            </div>
            <div class="card-body">
              <div v-if="!currentBrand.assets || currentBrand.assets.length === 0" class="empty-state text-center text-secondary text-sm">
                لا توجد أصول مضافة لهذه العلامة حتى الآن.
              </div>
              <div v-else class="d-flex flex-column gap-2">
                <div
                  v-for="asset in currentBrand.assets"
                  :key="asset.id"
                  class="asset-item d-flex items-start justify-between gap-3 card card-interactive"
                >
                  <div class="card-body d-flex flex-column gap-1">
                    <div class="d-flex items-center gap-2">
                      <span class="badge badge-secondary text-xs">{{ asset.asset_type }}</span>
                      <span class="text-sm font-semibold text-primary">{{ asset.label || 'أصل بدون عنوان' }}</span>
                      <span class="badge badge-success text-xs" v-if="asset.is_primary">رئيسي</span>
                    </div>
                    <p class="text-secondary text-xs" v-if="asset.description">
                      {{ asset.description }}
                    </p>
                    <div class="d-flex flex-wrap gap-2 text-xs text-secondary">
                      <span v-if="asset.version">الإصدار: v{{ asset.version }}</span>
                      <span v-if="asset.original_filename">ملف: {{ asset.original_filename }}</span>
                      <span v-if="asset.mime_type">النوع: {{ asset.mime_type }}</span>
                      <span v-if="asset.file_size">الحجم: {{ formatSize(asset.file_size) }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                      <span
                        v-for="tag in asset.tags || []"
                        :key="tag"
                        class="badge badge-ghost text-2xs"
                      >
                        {{ tag }}
                      </span>
                    </div>
                  </div>
                  <div class="card-actions d-flex items-center gap-2">
                    <a
                      v-if="asset.url"
                      class="btn btn-ghost btn-sm"
                      :href="asset.url"
                      target="_blank"
                      rel="noopener"
                    >
                      <i class="bx bx-link-external me-1"></i>
                      فتح
                    </a>
                    <button
                      v-if="asset.asset_type === 'logo' && !asset.is_primary"
                      class="btn btn-secondary btn-sm"
                      type="button"
                      @click="handleMarkPrimary(asset)"
                      :disabled="isSaving"
                    >
                      تعيين رئيسي
                    </button>
                    <button
                      class="btn btn-danger btn-icon btn-sm"
                      type="button"
                      @click="handleDeleteAsset(asset)"
                      :disabled="isSaving"
                    >
                      <i class="bx bx-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card card-flat" v-if="currentBrand.usage_guidelines && currentBrand.usage_guidelines.length">
            <div class="card-body d-flex flex-column gap-3">
              <h3 class="text-sm font-semibold text-primary">إرشادات الاستخدام</h3>
              <ul class="guidelines-list">
                <li v-for="(guideline, index) in currentBrand.usage_guidelines" :key="index">
                  <span class="guideline-title">{{ guideline.title || `ملاحظة ${index + 1}` }}</span>
                  <p class="guideline-desc" v-if="guideline.description">
                    {{ guideline.description }}
                  </p>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Logo Uploader -->
    <input
      ref="logoInput"
      class="d-none"
      type="file"
      accept="image/*"
      @change="handleLogoSelected"
    />

    <!-- Asset Modal -->
    <div v-if="showAssetModal" class="modal-backdrop"></div>
    <div v-if="showAssetModal" class="modal modal-lg">
      <div class="modal-header">
        <h3 class="modal-title text-md font-semibold text-primary">
          إضافة أصل جديد
        </h3>
        <button class="btn-icon" type="button" aria-label="إغلاق" @click="closeAssetModal">
          <i class="bx bx-x"></i>
        </button>
      </div>
      <div class="modal-body d-flex flex-column gap-3">
        <div class="form-group">
          <label class="form-label">نوع الأصل</label>
          <select class="form-control" v-model="assetForm.asset_type">
            <option value="logo">شعار</option>
            <option value="icon">أيقونة</option>
            <option value="font">خط</option>
            <option value="palette">لوحة ألوان</option>
            <option value="guideline">إرشاد</option>
            <option value="template">قالب</option>
            <option value="document">مستند</option>
            <option value="other">مرفق آخر</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">المسمى</label>
          <input
            class="form-control"
            type="text"
            v-model="assetForm.label"
            placeholder="مثال: شعار أبيض بخلفية شفافة"
          />
        </div>

        <div class="form-group">
          <label class="form-label">الوصف</label>
          <textarea
            class="form-control"
            rows="3"
            v-model="assetForm.description"
            placeholder="متى يُستخدم هذا الأصل؟"
          ></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">الوسوم</label>
          <input
            class="form-control"
            type="text"
            v-model="assetForm.tags"
            placeholder="براند، نسخة متجهة، استخدام داخلي"
          />
          <span class="form-hint text-xs">افصل بين الوسوم بفاصلة</span>
        </div>

        <div class="form-group d-flex items-center gap-2">
          <input
            id="primary-toggle"
            type="checkbox"
            class="form-check-input"
            v-model="assetForm.is_primary"
          />
          <label class="form-check-label text-sm" for="primary-toggle">
            اجعل الأصل رئيسياً لهذا النوع
          </label>
        </div>

        <div class="form-group">
          <label class="form-label">اختر ملفاً</label>
          <input
            ref="assetInput"
            class="form-control"
            type="file"
            @change="handleAssetFile"
          />
          <span class="form-hint text-xs" v-if="assetForm.fileName">
            {{ assetForm.fileName }}
          </span>
        </div>
      </div>
      <div class="modal-footer d-flex items-center justify-between">
        <button class="btn btn-ghost" type="button" @click="closeAssetModal">
          إلغاء
        </button>
        <button class="btn btn-primary" type="button" @click="submitAsset" :disabled="isSaving || !assetFormValid">
          حفظ الأصل
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useBrandStore } from '@/stores/brand'

const brandStore = useBrandStore()
const router = useRouter()
const toast = useToast()

const filters = ref({ ...brandStore.filters })
const logoInput = ref(null)
const assetInput = ref(null)

const showAssetModal = ref(false)
const assetForm = reactive({
  asset_type: 'logo',
  label: '',
  description: '',
  file: null,
  fileName: '',
  tags: '',
  is_primary: false,
})

const filteredBrands = computed(() => brandStore.filteredBrands)
const currentBrand = computed(() => brandStore.currentBrand)
const isLoading = computed(() => brandStore.loading || brandStore.assetsLoading)
const isSaving = computed(() => brandStore.saving)

const metrics = computed(() => {
  const total = brandStore.brands.length
  const active = brandStore.brands.filter((brand) => brand.status === 'active').length
  const assets = brandStore.brands.reduce(
    (acc, brand) => acc + ((brand.assets && brand.assets.length) || 0),
    0,
  )

  return { total, active, assets }
})

const defaultBrandName = computed(() => brandStore.defaultBrand?.name || 'غير محدد')

const assetFormValid = computed(() => !!assetForm.asset_type && !!assetForm.label && (!!assetForm.file || assetForm.asset_type === 'guideline'))

const applyFilters = () => {
  brandStore.setFilters(filters.value)
}

const refreshBrands = async () => {
  try {
    await brandStore.fetchBrands()
    toast.success('تم تحديث قائمة العلامات')
  } catch (error) {
    toast.error(error.response?.data?.message || 'تعذّر تحديث العلامات')
  }
}

const goToCreate = () => {
  router.push({ name: 'brands.create' })
}

const goToEdit = (brand) => {
  router.push({ name: 'brands.create', query: { id: brand.id } })
}

const handleSelectBrand = async (brand) => {
  try {
    const result = await brandStore.fetchBrand(brand.id)
    if (!result.assets || result.assets.length === 0) {
      await brandStore.fetchBrandAssets(brand.id)
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'تعذّر فتح تفاصيل العلامة')
  }
}

const openLogoUploader = () => {
  if (!currentBrand.value) return
  logoInput.value?.click()
}

const handleLogoSelected = async (event) => {
  const files = event.target.files || []
  if (!files.length || !currentBrand.value) return

  try {
    await brandStore.uploadLogo(currentBrand.value.id, files[0])
    toast.success('تم تحديث الشعار بنجاح')
  } catch (error) {
    toast.error(error.response?.data?.message || 'تعذّر رفع الشعار')
  } finally {
    event.target.value = ''
  }
}

const openAssetModal = () => {
  if (!currentBrand.value) return
  resetAssetForm()
  showAssetModal.value = true
}

const closeAssetModal = () => {
  showAssetModal.value = false
  resetAssetForm()
}

const handleAssetFile = (event) => {
  const files = event.target.files || []
  if (!files.length) return
  assetForm.file = files[0]
  assetForm.fileName = files[0].name
}

const submitAsset = async () => {
  if (!currentBrand.value) return
  try {
    const payload = {
      asset_type: assetForm.asset_type,
      label: assetForm.label,
      description: assetForm.description,
      is_primary: assetForm.is_primary,
      tags: assetForm.tags
        ? assetForm.tags.split(',').map((tag) => tag.trim()).filter(Boolean)
        : [],
    }

    if (assetForm.file) {
      payload.file = assetForm.file
    }

    await brandStore.createAsset(currentBrand.value.id, payload)
    toast.success('تم إضافة الأصل بنجاح')
    closeAssetModal()
  } catch (error) {
    toast.error(error.response?.data?.message || 'تعذّر حفظ الأصل')
  }
}

const handleMarkPrimary = async (asset) => {
  if (!currentBrand.value) return
  try {
    await brandStore.markAssetPrimary(currentBrand.value.id, asset.id)
    toast.success('تم تعيين الأصل كرئيسي')
  } catch (error) {
    toast.error(error.response?.data?.message || 'تعذّر تحديث الأصل')
  }
}

const handleDeleteAsset = async (asset) => {
  if (!currentBrand.value) return
  const confirmed = window.confirm('سيتم حذف الأصل نهائياً، هل أنت متأكد؟')
  if (!confirmed) return

  try {
    await brandStore.deleteAsset(currentBrand.value.id, asset.id)
    toast.success('تم حذف الأصل')
  } catch (error) {
    toast.error(error.response?.data?.message || 'تعذّر حذف الأصل')
  }
}

const markAsDefault = async () => {
  if (!currentBrand.value) return
  try {
    await brandStore.setDefaultBrand(currentBrand.value.id)
    toast.success('تم تعيين العلامة كافتراضية')
  } catch (error) {
    toast.error(error.response?.data?.message || 'تعذّر تحديث العلامة')
  }
}

const handleDeleteBrand = async () => {
  if (!currentBrand.value) return
  const confirmed = window.confirm('سيتم حذف العلامة وجميع أصولها، هل أنت متأكد؟')
  if (!confirmed) return

  try {
    await brandStore.deleteBrand(currentBrand.value.id)
    toast.success('تم حذف العلامة')
  } catch (error) {
    toast.error(error.response?.data?.message || 'تعذّر حذف العلامة')
  }
}

const formatSize = (bytes) => {
  if (!bytes) return ''
  const units = ['B', 'KB', 'MB', 'GB']
  let index = 0
  let value = bytes
  while (value >= 1024 && index < units.length - 1) {
    value /= 1024
    index++
  }
  return `${value.toFixed(1)} ${units[index]}`
}

const resetAssetForm = () => {
  assetForm.asset_type = 'logo'
  assetForm.label = ''
  assetForm.description = ''
  assetForm.file = null
  assetForm.fileName = ''
  assetForm.tags = ''
  assetForm.is_primary = false
  if (assetInput.value) {
    assetInput.value.value = ''
  }
}

watch(
  () => brandStore.filters,
  (next) => {
    filters.value = { ...next }
  },
  { deep: true },
)

onMounted(async () => {
  if (!brandStore.brands.length) {
    await brandStore.fetchBrands()
  }
  if (!brandStore.currentBrand && brandStore.brands.length) {
    brandStore.setCurrentBrand(brandStore.defaultBrand || brandStore.brands[0])
  }
})
</script>

<style scoped>
.brands-dashboard {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.brand-layout {
  display: grid;
  gap: 1.5rem;
}

@media (min-width: 1024px) {
  .brand-layout {
    grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr);
  }
}

.brand-grid {
  display: grid;
  gap: 1rem;
}

@media (min-width: 768px) {
  .brand-grid {
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  }
}

.brand-tile {
  border: 1px solid var(--color-bg-tertiary);
  border-radius: 14px;
  text-align: start;
  transition: transform 0.2s ease, border-color 0.2s ease;
  cursor: pointer;
}

.brand-tile:hover {
  transform: translateY(-2px);
  border-color: var(--color-brand-primary);
}

.brand-tile.is-selected {
  border-color: var(--color-brand-primary);
  box-shadow: 0 0 0 2px rgba(11, 110, 153, 0.25);
}

.brand-tile.is-default {
  background-color: var(--color-bg-secondary);
}

.brand-avatar {
  width: 54px;
  height: 54px;
  border-radius: 14px;
  background-color: var(--color-bg-tertiary);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.brand-logo-wrapper {
  display: flex;
  width: 100%;
  height: 100%;
  align-items: center;
  justify-content: center;
}

.brand-logo-wrapper img {
  max-width: 100%;
  max-height: 100%;
}

.brand-initial {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--color-brand-primary);
}

.stats-grid {
  display: grid;
  gap: 1rem;
}

@media (min-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

.stat-card {
  border-radius: 14px;
}

.icon-circle {
  font-size: 1.5rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.identity-grid {
  display: grid;
  gap: 1.25rem;
}

@media (min-width: 768px) {
  .identity-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.identity-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.guidelines-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  list-style: none;
  padding: 0;
  margin: 0;
}

.guideline-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-primary);
}

.guideline-desc {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  margin-top: 0.25rem;
}

.asset-item {
  border-radius: 12px;
  border: 1px solid var(--color-bg-tertiary);
}

.empty-state {
  padding: 2rem 1rem;
}

.truncate {
  max-width: 160px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
