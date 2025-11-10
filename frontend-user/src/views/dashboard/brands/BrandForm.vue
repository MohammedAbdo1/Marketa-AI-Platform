<template>
  <div class="brand-form-page">
    <div class="card card-flat mb-6">
      <div class="card-body d-flex flex-column gap-2">
        <div class="d-flex items-center gap-3 text-secondary text-sm">
          <router-link class="text-secondary" :to="{ name: 'brands' }">
            لوحة العلامات
          </router-link>
          <i class="bx bx-chevron-left text-xs"></i>
          <span class="text-primary">
            {{ isEdit ? 'تعديل العلامة' : 'إنشاء علامة جديدة' }}
          </span>
        </div>
        <h1 class="text-2xl font-semibold text-primary">
          {{ isEdit ? 'تحديث الهوية البصرية' : 'تعريف هوية بصرية جديدة' }}
        </h1>
        <p class="text-secondary text-sm">
          أضف تفاصيل العلامة المرئية لضمان اتساق المحتوى بين أعضاء الفريق وقنوات النشر المختلفة.
        </p>
      </div>
    </div>

    <form class="card brand-form" @submit.prevent="handleSubmit">
      <div class="card-body d-flex flex-column gap-6">
        <section class="form-section">
          <div class="section-header d-flex items-center justify-between">
            <div>
              <h2 class="text-md font-semibold text-primary">معلومات عامة</h2>
              <p class="text-secondary text-xs mt-1">
                الاسم والشعار والوصف المرئي العام للعلامة.
              </p>
            </div>
            <label class="d-flex items-center gap-2 text-sm">
              <input
                type="checkbox"
                class="form-check-input"
                v-model="form.is_default"
                :disabled="isDefaultLocked"
              />
              علامة افتراضية
            </label>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label form-label-required">اسم العلامة</label>
              <input
                class="form-control"
                type="text"
                required
                v-model="form.name"
                placeholder="مثال: هوية منصة ماركيتا"
              />
            </div>

            <div class="form-group">
              <label class="form-label">الشعار المختصر</label>
              <input
                class="form-control"
                type="text"
                v-model="form.tagline"
                placeholder="عبارة قصيرة تعبر عن العلامة"
              />
            </div>

            <div class="form-group">
              <label class="form-label">الحالة</label>
              <select class="form-control" v-model="form.status">
                <option value="active">مفعّلة</option>
                <option value="inactive">مؤرشفة</option>
                <option value="archived">غير مستخدمة</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">نبرة الصوت</label>
              <input
                class="form-control"
                type="text"
                v-model="form.brand_voice"
                placeholder="رسمية، ودودة، مرحة ..."
              />
            </div>
          </div>
        </section>

        <section class="form-section">
          <div class="section-header">
            <h2 class="text-md font-semibold text-primary">ألوان وخطوط العلامة</h2>
            <p class="text-secondary text-xs mt-1">
              ساعد فريق التصميم على استخدام القيم الصحيحة في كافة اللمسات البصرية.
            </p>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">اللون الأساسي</label>
              <input
                class="form-control"
                type="text"
                v-model="form.primary_color"
                placeholder="#0B6E99"
              />
            </div>

            <div class="form-group">
              <label class="form-label">اللون الثانوي</label>
              <input
                class="form-control"
                type="text"
                v-model="form.secondary_color"
                placeholder="#0F7B6C"
              />
            </div>

            <div class="form-group">
              <label class="form-label">لون الإبراز</label>
              <input
                class="form-control"
                type="text"
                v-model="form.accent_color"
                placeholder="#D9730D"
              />
            </div>

            <div class="form-group">
              <label class="form-label">الخط العربي</label>
              <input
                class="form-control"
                type="text"
                v-model="form.font_arabic"
                placeholder="مثال: Cairo, DIN Next"
              />
            </div>

            <div class="form-group">
              <label class="form-label">الخط اللاتيني</label>
              <input
                class="form-control"
                type="text"
                v-model="form.font_english"
                placeholder="مثال: Inter, Open Sans"
              />
            </div>

            <div class="form-group">
              <label class="form-label">الكلمات المفتاحية</label>
              <input
                class="form-control"
                type="text"
                v-model="form.keywords"
                placeholder="فاخر، بنكي، رقمي ..."
              />
              <span class="form-hint text-xs">افصل بين الكلمات بفاصلة</span>
            </div>
          </div>
        </section>

        <section class="form-section">
          <div class="section-header d-flex items-center justify-between">
            <div>
              <h2 class="text-md font-semibold text-primary">إرشادات الاستخدام</h2>
              <p class="text-secondary text-xs mt-1">
                ساعد أعضاء الفريق على فهم كيفية الظهور باسم العلامة في مختلف السياقات.
              </p>
            </div>
            <button class="btn btn-ghost btn-sm" type="button" @click="addGuideline">
              <i class="bx bx-plus me-1"></i>
              إضافة إرشاد
            </button>
          </div>

          <div class="guidelines-grid" v-if="form.usage_guidelines.length">
            <div
              v-for="(guideline, index) in form.usage_guidelines"
              :key="index"
              class="card card-flat guideline-card"
            >
              <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex items-center justify-between gap-2">
                  <span class="text-xs text-secondary">إرشاد {{ index + 1 }}</span>
                  <button
                    class="btn btn-ghost btn-icon btn-xs"
                    type="button"
                    @click="removeGuideline(index)"
                    v-if="form.usage_guidelines.length > 1"
                  >
                    <i class="bx bx-x"></i>
                  </button>
                </div>
                <input
                  class="form-control"
                  type="text"
                  v-model="guideline.title"
                  placeholder="عنوان الإرشاد"
                />
                <textarea
                  class="form-control"
                  rows="3"
                  v-model="guideline.description"
                  placeholder="شرح مختصر وطريقة الاستخدام"
                ></textarea>
              </div>
            </div>
          </div>
        </section>
      </div>

      <div class="card-footer d-flex items-center justify-between">
        <button class="btn btn-ghost" type="button" @click="handleCancel">
          إلغاء
        </button>
        <div class="d-flex items-center gap-2">
          <button class="btn btn-secondary" type="button" @click="handleSaveDraft" :disabled="isSaving">
            حفظ كمسودة
          </button>
          <button class="btn btn-primary" type="submit" :disabled="isSaving">
            {{ isEdit ? 'تحديث العلامة' : 'إنشاء العلامة' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useBrandStore } from '@/stores/brand'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const brandStore = useBrandStore()

const brandId = computed(() => route.query.id ? Number(route.query.id) : null)
const isEdit = computed(() => Boolean(brandId.value))
const isSaving = computed(() => brandStore.saving)
const isDefaultLocked = computed(() => brandStore.defaultBrand && brandStore.defaultBrand.id === brandId.value)

const form = reactive({
  name: '',
  tagline: '',
  status: 'active',
  is_default: false,
  brand_voice: '',
  primary_color: '',
  secondary_color: '',
  accent_color: '',
  font_arabic: '',
  font_english: '',
  keywords: '',
  usage_guidelines: [
    {
      title: '',
      description: '',
    },
  ],
})

const populateForm = (brand) => {
  if (!brand) return

  form.name = brand.name || ''
  form.tagline = brand.tagline || ''
  form.status = brand.status || 'active'
  form.is_default = Boolean(brand.is_default)
  form.brand_voice = brand.brand_voice || ''
  form.primary_color = brand.primary_color || ''
  form.secondary_color = brand.secondary_color || ''
  form.accent_color = brand.accent_color || ''
  form.font_arabic = brand.font_arabic || ''
  form.font_english = brand.font_english || ''
  form.keywords = (brand.keywords || []).join(', ')
  form.usage_guidelines =
    (brand.usage_guidelines && brand.usage_guidelines.length
      ? brand.usage_guidelines.map((item) => ({
          title: item.title || '',
          description: item.description || '',
        }))
      : [{ title: '', description: '' }])
}

const handleSubmit = async () => {
  const payload = buildPayload()
  try {
    if (isEdit.value) {
      await brandStore.updateBrand(brandId.value, payload)
      toast.success('تم تحديث العلامة بنجاح')
    } else {
      await brandStore.createBrand(payload)
      toast.success('تم إنشاء العلامة بنجاح')
    }

    await brandStore.fetchBrands()
    router.push({ name: 'brands' })
  } catch (error) {
    toast.error(error.response?.data?.message || 'تعذّر حفظ العلامة')
  }
}

const handleSaveDraft = async () => {
  try {
    if (isEdit.value) {
      await handleSubmit()
      return
    }
    const payload = buildPayload()
    payload.status = 'inactive'
    await brandStore.createBrand(payload)
    toast.success('تم حفظ العلامة كمسودة')
    router.push({ name: 'brands' })
  } catch (error) {
    toast.error(error.response?.data?.message || 'تعذّر حفظ العلامة')
  }
}

const handleCancel = () => {
  router.back()
}

const addGuideline = () => {
  form.usage_guidelines.push({ title: '', description: '' })
}

const removeGuideline = (index) => {
  if (form.usage_guidelines.length === 1) return
  form.usage_guidelines.splice(index, 1)
}

const buildPayload = () => {
  const keywords = form.keywords
    ? form.keywords
        .split(',')
        .map((keyword) => keyword.trim())
        .filter(Boolean)
    : []

  const usageGuidelines = form.usage_guidelines
    .map((item) => ({
      title: item.title?.trim(),
      description: item.description?.trim(),
    }))
    .filter((item) => item.title || item.description)

  return {
    name: form.name,
    tagline: form.tagline,
    status: form.status,
    is_default: form.is_default,
    brand_voice: form.brand_voice,
    primary_color: form.primary_color,
    secondary_color: form.secondary_color,
    accent_color: form.accent_color,
    font_arabic: form.font_arabic,
    font_english: form.font_english,
    keywords,
    usage_guidelines: usageGuidelines,
  }
}

onMounted(async () => {
  if (isEdit.value) {
    try {
      const brand = await brandStore.fetchBrand(brandId.value)
      populateForm(brand)
    } catch (error) {
      toast.error(error.response?.data?.message || 'تعذّر تحميل العلامة')
      router.push({ name: 'brands' })
    }
  }
})
</script>

<style scoped>
.brand-form-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.brand-form {
  border-radius: 16px;
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.section-header {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.form-grid {
  display: grid;
  gap: 1.25rem;
}

@media (min-width: 768px) {
  .form-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.guidelines-grid {
  display: grid;
  gap: 1rem;
}

@media (min-width: 768px) {
  .guidelines-grid {
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  }
}

.guideline-card {
  border-radius: 14px;
}
</style>
