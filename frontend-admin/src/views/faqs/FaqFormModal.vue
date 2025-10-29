<template>
  <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            {{ faq ? $t('faqs.edit') : $t('faqs.create') }}
          </h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <form @submit.prevent="handleSubmit">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('faqs.category_arabic') }}</label>
                <input v-model="form.category_ar" type="text" class="form-control" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('faqs.category_english') }}</label>
                <input v-model="form.category_en" type="text" class="form-control" />
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('faqs.question_arabic') }} *</label>
                <textarea v-model="form.question_ar" class="form-control" rows="2" required></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('faqs.question_english') }} *</label>
                <textarea v-model="form.question_en" class="form-control" rows="2" required></textarea>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('faqs.answer_arabic') }} *</label>
                <textarea v-model="form.answer_ar" class="form-control" rows="5" required></textarea>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">{{ $t('faqs.answer_english') }} *</label>
                <textarea v-model="form.answer_en" class="form-control" rows="5" required></textarea>
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
                  <input v-model="form.is_active" class="form-check-input" type="checkbox" id="faqActive" />
                  <label class="form-check-label" for="faqActive">{{ $t('common.active') }}</label>
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
import { useFaqStore } from '@/stores/faq'
import { useToast } from 'vue-toastification'

export default {
  name: 'FaqFormModal',
  props: {
    faq: Object
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const faqStore = useFaqStore()
    const toast = useToast()
    const saving = ref(false)

    const form = ref({
      category_ar: '',
      category_en: '',
      question_ar: '',
      question_en: '',
      answer_ar: '',
      answer_en: '',
      is_active: true,
      sort_order: 0
    })

    watch(() => props.faq, (newFaq) => {
      if (newFaq) {
        form.value = { ...newFaq }
      }
    }, { immediate: true })

    const handleSubmit = async () => {
      saving.value = true
      try {
        if (props.faq) {
          await faqStore.update(props.faq.id, form.value)
          toast.success('FAQ updated successfully')
        } else {
          await faqStore.create(form.value)
          toast.success('FAQ created successfully')
        }
        emit('saved')
      } catch (error) {
        toast.error(error.message || 'Error saving FAQ')
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

