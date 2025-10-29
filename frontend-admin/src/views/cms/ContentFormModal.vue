<template>
  <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            {{ content ? $t('cms.edit_content') : $t('cms.create_content') }}
          </h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <form @submit.prevent="handleSubmit">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">{{ $t('cms.content_type') }} *</label>
              <select v-model="form.content_type" class="form-select" required>
                <option value="text">{{ $t('cms.content_types.text') }}</option>
                <option value="image">{{ $t('cms.content_types.image') }}</option>
                <option value="icon">{{ $t('cms.content_types.icon') }}</option>
                <option value="video">{{ $t('cms.content_types.video') }}</option>
                <option value="list_item">{{ $t('cms.content_types.list_item') }}</option>
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
                <label class="form-label">{{ $t('cms.description_arabic') }}</label>
                <textarea v-model="form.description_ar" class="form-control" rows="4"></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('cms.description_english') }}</label>
                <textarea v-model="form.description_en" class="form-control" rows="4"></textarea>
              </div>
            </div>

            <!-- Media Fields -->
            <div class="row" v-if="['image', 'icon', 'video'].includes(form.content_type)">
              <div class="col-md-4 mb-3" v-if="form.content_type === 'image'">
                <label class="form-label">{{ $t('cms.image_url') }}</label>
                <input v-model="form.image_url" type="url" class="form-control" />
              </div>
              <div class="col-md-4 mb-3" v-if="form.content_type === 'icon'">
                <label class="form-label">{{ $t('cms.icon_class') }}</label>
                <input v-model="form.icon_class" type="text" class="form-control" placeholder="ti ti-star" />
              </div>
              <div class="col-md-4 mb-3" v-if="form.content_type === 'video'">
                <label class="form-label">{{ $t('cms.video_url') }}</label>
                <input v-model="form.video_url" type="url" class="form-control" />
              </div>
            </div>

            <!-- Button Fields -->
            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label">{{ $t('cms.button_text_arabic') }}</label>
                <input v-model="form.button_text_ar" type="text" class="form-control" />
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">{{ $t('cms.button_text_english') }}</label>
                <input v-model="form.button_text_en" type="text" class="form-control" />
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">{{ $t('cms.button_url') }}</label>
                <input v-model="form.button_url" type="url" class="form-control" />
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
                  <input v-model="form.is_active" class="form-check-input" type="checkbox" id="contentActive" />
                  <label class="form-check-label" for="contentActive">{{ $t('common.active') }}</label>
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
  name: 'ContentFormModal',
  props: {
    content: Object,
    sectionId: Number
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const cmsStore = useCmsStore()
    const toast = useToast()
    const saving = ref(false)

    const form = ref({
      section_id: props.sectionId,
      content_type: 'text',
      title_ar: '',
      title_en: '',
      description_ar: '',
      description_en: '',
      image_url: '',
      icon_class: '',
      video_url: '',
      button_text_ar: '',
      button_text_en: '',
      button_url: '',
      is_active: true,
      sort_order: 0
    })

    watch(() => props.content, (newContent) => {
      if (newContent) {
        form.value = { ...newContent }
      }
    }, { immediate: true })

    watch(() => props.sectionId, (newId) => {
      if (newId) {
        form.value.section_id = newId
      }
    }, { immediate: true })

    const handleSubmit = async () => {
      saving.value = true
      try {
        if (props.content) {
          await cmsStore.updateContent(props.content.id, form.value)
          toast.success('Content updated successfully')
        } else {
          await cmsStore.createContent(form.value)
          toast.success('Content created successfully')
        }
        emit('saved')
      } catch (error) {
        toast.error(error.message || 'Error saving content')
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

