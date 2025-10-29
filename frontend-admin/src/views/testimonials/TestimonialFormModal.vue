<template>
  <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            {{ testimonial ? $t('testimonials.edit') : $t('testimonials.create') }}
          </h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <form @submit.prevent="handleSubmit">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('testimonials.name_arabic') }} *</label>
                <input v-model="form.name_ar" type="text" class="form-control" required />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('testimonials.name_english') }} *</label>
                <input v-model="form.name_en" type="text" class="form-control" required />
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('testimonials.position_arabic') }}</label>
                <input v-model="form.position_ar" type="text" class="form-control" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('testimonials.position_english') }}</label>
                <input v-model="form.position_en" type="text" class="form-control" />
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('testimonials.company_arabic') }}</label>
                <input v-model="form.company_ar" type="text" class="form-control" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('testimonials.company_english') }}</label>
                <input v-model="form.company_en" type="text" class="form-control" />
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('testimonials.content_arabic') }} *</label>
                <textarea v-model="form.content_ar" class="form-control" rows="4" required></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('testimonials.content_english') }} *</label>
                <textarea v-model="form.content_en" class="form-control" rows="4" required></textarea>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label">{{ $t('testimonials.avatar_url') }}</label>
                <input v-model="form.avatar_url" type="url" class="form-control" />
              </div>
              <div class="col-md-2 mb-3">
                <label class="form-label">{{ $t('testimonials.rating') }}</label>
                <select v-model.number="form.rating" class="form-select">
                  <option :value="1">1</option>
                  <option :value="2">2</option>
                  <option :value="3">3</option>
                  <option :value="4">4</option>
                  <option :value="5">5</option>
                </select>
              </div>
              <div class="col-md-3 mb-3">
                <label class="form-label">{{ $t('common.sort_order') }}</label>
                <input v-model.number="form.sort_order" type="number" class="form-control" />
              </div>
              <div class="col-md-3 mb-3">
                <label class="form-label d-block">&nbsp;</label>
                <div class="form-check form-check-inline">
                  <input v-model="form.is_featured" class="form-check-input" type="checkbox" id="featured" />
                  <label class="form-check-label" for="featured">{{ $t('testimonials.is_featured') }}</label>
                </div>
                <div class="form-check form-check-inline">
                  <input v-model="form.is_active" class="form-check-input" type="checkbox" id="active" />
                  <label class="form-check-label" for="active">{{ $t('common.active') }}</label>
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
import { useTestimonialStore } from '@/stores/testimonial'
import { useToast } from 'vue-toastification'

export default {
  name: 'TestimonialFormModal',
  props: {
    testimonial: Object
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const testimonialStore = useTestimonialStore()
    const toast = useToast()
    const saving = ref(false)

    const form = ref({
      name_ar: '',
      name_en: '',
      position_ar: '',
      position_en: '',
      company_ar: '',
      company_en: '',
      content_ar: '',
      content_en: '',
      avatar_url: '',
      rating: 5,
      is_featured: false,
      is_active: true,
      sort_order: 0
    })

    watch(() => props.testimonial, (newTestimonial) => {
      if (newTestimonial) {
        form.value = { ...newTestimonial }
      }
    }, { immediate: true })

    const handleSubmit = async () => {
      saving.value = true
      try {
        if (props.testimonial) {
          await testimonialStore.update(props.testimonial.id, form.value)
          toast.success('Testimonial updated successfully')
        } else {
          await testimonialStore.create(form.value)
          toast.success('Testimonial created successfully')
        }
        emit('saved')
      } catch (error) {
        toast.error(error.message || 'Error saving testimonial')
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

