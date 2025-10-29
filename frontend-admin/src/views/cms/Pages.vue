<template>
  <div class="container-fluid">
    <PageHeader :title="$t('cms.pages')" />
    
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <PageTable 
              :pages="pages"
              :loading="loading"
              @edit="handleEdit"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Page Modal -->
    <PageFormModal
      v-if="showModal"
      :page="selectedPage"
      @close="showModal = false"
      @saved="handleSaved"
    />
  </div>
</template>

<script>
import { useCmsStore } from '@/stores/cms'
import { computed, ref, onMounted } from 'vue'
import PageHeader from '@/components/PageHeader.vue'
import PageTable from './PageTable.vue'
import PageFormModal from './PageFormModal.vue'

export default {
  name: 'Pages',
  components: {
    PageHeader,
    PageTable,
    PageFormModal
  },
  setup() {
    const cmsStore = useCmsStore()
    const showModal = ref(false)
    const selectedPage = ref(null)

    const pages = computed(() => cmsStore.pages)
    const loading = computed(() => cmsStore.loading)

    onMounted(() => {
      cmsStore.fetchPages()
    })

    const handleEdit = (page) => {
      selectedPage.value = page
      showModal.value = true
    }

    const handleSaved = () => {
      showModal.value = false
      selectedPage.value = null
      cmsStore.fetchPages()
    }

    return {
      pages,
      loading,
      showModal,
      selectedPage,
      handleEdit,
      handleSaved
    }
  }
}
</script>

