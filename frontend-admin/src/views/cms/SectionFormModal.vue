<template>
  <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            {{ section ? $t('cms.edit_section') : $t('cms.create_section') }}
          </h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <form @submit.prevent="handleSubmit">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">{{ $t('cms.pages') }} *</label>
              <select v-model.number="form.page_id" class="form-select" required>
                <option :value="null">{{ $t('common.select_option') }}</option>
                <option v-for="page in pages" :key="page.id" :value="page.id">
                  {{ page.title_ar }} ({{ page.slug }})
                </option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">{{ $t('cms.section_type') }} *</label>
              <select v-model="form.section_type" class="form-select" required>
                <option value="hero">{{ $t('cms.section_types.hero') }}</option>
                <option value="features">{{ $t('cms.section_types.features') }}</option>
                <option value="testimonials">{{ $t('cms.section_types.testimonials') }}</option>
                <option value="faq">{{ $t('cms.section_types.faq') }}</option>
                <option value="cta">{{ $t('cms.section_types.cta') }}</option>
                <option value="pricing">{{ $t('cms.section_types.pricing') }}</option>
              </select>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('cms.title_arabic') }}</label>
                <input v-model="form.title_ar" type="text" class="form-control" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('cms.title_english') }}</label>
                <input v-model="form.title_en" type="text" class="form-control" />
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('cms.subtitle_arabic') }}</label>
                <textarea v-model="form.subtitle_ar" class="form-control" rows="2"></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('cms.subtitle_english') }}</label>
                <textarea v-model="form.subtitle_en" class="form-control" rows="2"></textarea>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('common.sort_order') }}</label>
                <input v-model.number="form.sort_order" type="number" class="form-control" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label d-block">&nbsp;</label>
                <div class="form-check">
                  <input v-model="form.is_active" class="form-check-input" type="checkbox" id="sectionActive" />
                  <label class="form-check-label" for="sectionActive">{{ $t('common.active') }}</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="$emit('close')">
              {{ $t('common.cancel') }}
            </button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
              {{ $t('common.save') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import { useCmsStore } from '@/stores/cms'
import { useToast } from 'vue-toastification'

export default {
  name: 'SectionFormModal',
  props: {
    section: Object,
    pages: Array
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const cmsStore = useCmsStore()
    const toast = useToast()
    const saving = ref(false)

    const form = ref({
      page_id: null,
      section_type: 'hero',
      title_ar: '',
      title_en: '',
      subtitle_ar: '',
      subtitle_en: '',
      is_active: true,
      sort_order: 0
    })

    watch(() => props.section, (newSection) => {
      if (newSection) {
        form.value = { ...newSection }
      }
    }, { immediate: true })

    const handleSubmit = async () => {
      saving.value = true
      try {
        if (props.section) {
          await cmsStore.updateSection(props.section.id, form.value)
          toast.success('Section updated successfully')
        } else {
          await cmsStore.createSection(form.value)
          toast.success('Section created successfully')
        }
        emit('saved')
      } catch (error) {
        toast.error(error.message || 'Error saving section')
      } finally {
        saving.value = false
      }
    }

    return {
      form,
      saving,
      handleSubmit
    }
  }
}
</script>

