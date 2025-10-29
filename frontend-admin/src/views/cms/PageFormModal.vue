<template>
  <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ $t('common.edit') }} - {{ page?.slug }}</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <form @submit.prevent="handleSubmit">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('cms.title_arabic') }}</label>
                <input 
                  v-model="form.title_ar" 
                  type="text" 
                  class="form-control"
                  required
                />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('cms.title_english') }}</label>
                <input 
                  v-model="form.title_en" 
                  type="text" 
                  class="form-control"
                  required
                />
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('cms.meta_description') }} ({{ $t('common.name') }})</label>
                <textarea 
                  v-model="form.meta_description_ar" 
                  class="form-control"
                  rows="3"
                ></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('cms.meta_description') }} (English)</label>
                <textarea 
                  v-model="form.meta_description_en" 
                  class="form-control"
                  rows="3"
                ></textarea>
              </div>
            </div>

            <div class="form-check form-switch">
              <input 
                v-model="form.is_active" 
                class="form-check-input" 
                type="checkbox" 
                id="pageActive"
              />
              <label class="form-check-label" for="pageActive">
                {{ $t('common.active') }}
              </label>
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
  name: 'PageFormModal',
  props: {
    page: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const cmsStore = useCmsStore()
    const toast = useToast()
    const saving = ref(false)

    const form = ref({
      title_ar: '',
      title_en: '',
      meta_description_ar: '',
      meta_description_en: '',
      is_active: true
    })

    watch(() => props.page, (newPage) => {
      if (newPage) {
        form.value = {
          title_ar: newPage.title_ar || '',
          title_en: newPage.title_en || '',
          meta_description_ar: newPage.meta_description_ar || '',
          meta_description_en: newPage.meta_description_en || '',
          is_active: newPage.is_active
        }
      }
    }, { immediate: true })

    const handleSubmit = async () => {
      saving.value = true
      try {
        await cmsStore.updatePage(props.page.id, form.value)
        toast.success('Page updated successfully')
        emit('saved')
      } catch (error) {
        toast.error(error.message || 'Error updating page')
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

