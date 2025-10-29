<template>
  <div class="container-fluid">
    <PageHeader 
      :title="$t('faqs.title')" 
      :button-text="$t('faqs.create')"
      @button-click="showModal = true"
    />
    
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <FaqTable 
              :faqs="faqs"
              :loading="loading"
              @edit="handleEdit"
              @delete="handleDelete"
            />
          </div>
        </div>
      </div>
    </div>

    <FaqFormModal
      v-if="showModal"
      :faq="selectedFaq"
      @close="closeModal"
      @saved="handleSaved"
    />
  </div>
</template>

<script>
import { useFaqStore } from '@/stores/faq'
import { computed, ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import PageHeader from '@/components/PageHeader.vue'
import FaqTable from './FaqTable.vue'
import FaqFormModal from './FaqFormModal.vue'

export default {
  name: 'Faqs',
  components: {
    PageHeader,
    FaqTable,
    FaqFormModal
  },
  setup() {
    const faqStore = useFaqStore()
    const toast = useToast()
    const showModal = ref(false)
    const selectedFaq = ref(null)

    const faqs = computed(() => faqStore.faqs)
    const loading = computed(() => faqStore.loading)

    onMounted(() => {
      faqStore.fetchAll()
    })

    const handleEdit = (faq) => {
      selectedFaq.value = faq
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      selectedFaq.value = null
    }

    const handleSaved = () => {
      closeModal()
      faqStore.fetchAll()
    }

    const handleDelete = async (id) => {
      if (confirm('Are you sure?')) {
        try {
          await faqStore.delete(id)
          toast.success('FAQ deleted successfully')
        } catch (error) {
          toast.error(error.message)
        }
      }
    }

    return {
      faqs,
      loading,
      showModal,
      selectedFaq,
      handleEdit,
      closeModal,
      handleSaved,
      handleDelete
    }
  }
}
</script>

