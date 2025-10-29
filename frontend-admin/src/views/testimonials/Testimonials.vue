<template>
  <div class="container-fluid">
    <PageHeader 
      :title="$t('testimonials.title')" 
      :button-text="$t('testimonials.create')"
      @button-click="showModal = true"
    />
    
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <TestimonialTable 
              :testimonials="testimonials"
              :loading="loading"
              @edit="handleEdit"
              @delete="handleDelete"
            />
          </div>
        </div>
      </div>
    </div>

    <TestimonialFormModal
      v-if="showModal"
      :testimonial="selectedTestimonial"
      @close="closeModal"
      @saved="handleSaved"
    />
  </div>
</template>

<script>
import { useTestimonialStore } from '@/stores/testimonial'
import { computed, ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import PageHeader from '@/components/PageHeader.vue'
import TestimonialTable from './TestimonialTable.vue'
import TestimonialFormModal from './TestimonialFormModal.vue'

export default {
  name: 'Testimonials',
  components: {
    PageHeader,
    TestimonialTable,
    TestimonialFormModal
  },
  setup() {
    const testimonialStore = useTestimonialStore()
    const toast = useToast()
    const showModal = ref(false)
    const selectedTestimonial = ref(null)

    const testimonials = computed(() => testimonialStore.testimonials)
    const loading = computed(() => testimonialStore.loading)

    onMounted(() => {
      testimonialStore.fetchAll()
    })

    const handleEdit = (testimonial) => {
      selectedTestimonial.value = testimonial
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      selectedTestimonial.value = null
    }

    const handleSaved = () => {
      closeModal()
      testimonialStore.fetchAll()
    }

    const handleDelete = async (id) => {
      if (confirm('Are you sure?')) {
        try {
          await testimonialStore.delete(id)
          toast.success('Testimonial deleted successfully')
        } catch (error) {
          toast.error(error.message)
        }
      }
    }

    return {
      testimonials,
      loading,
      showModal,
      selectedTestimonial,
      handleEdit,
      closeModal,
      handleSaved,
      handleDelete
    }
  }
}
</script>

