<template>
  <div class="container-fluid">
    <PageHeader 
      :title="$t('cms.sections')" 
      :button-text="$t('cms.create_section')"
      @button-click="showSectionModal = true"
    />
    
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <!-- Filter by Page -->
            <div class="mb-3">
              <label class="form-label">{{ $t('cms.pages') }}</label>
              <select v-model="selectedPageId" class="form-select">
                <option :value="null">{{ $t('common.all') }}</option>
                <option v-for="page in pages" :key="page.id" :value="page.id">
                  {{ page.title_ar }} ({{ page.slug }})
                </option>
              </select>
            </div>

            <SectionTable 
              :sections="filteredSections"
              :loading="loading"
              @edit="handleEditSection"
              @delete="handleDeleteSection"
              @add-content="handleAddContent"
              @edit-content="handleEditContent"
              @delete-content="handleDeleteContent"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Section Form Modal -->
    <SectionFormModal
      v-if="showSectionModal"
      :section="selectedSection"
      :pages="pages"
      @close="closeSectionModal"
      @saved="handleSectionSaved"
    />

    <!-- Content Form Modal -->
    <ContentFormModal
      v-if="showContentModal"
      :content="selectedContent"
      :section-id="selectedSectionForContent"
      @close="closeContentModal"
      @saved="handleContentSaved"
    />
  </div>
</template>

<script>
import { useCmsStore } from '@/stores/cms'
import { computed, ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import PageHeader from '@/components/PageHeader.vue'
import SectionTable from './SectionTable.vue'
import SectionFormModal from './SectionFormModal.vue'
import ContentFormModal from './ContentFormModal.vue'

export default {
  name: 'Sections',
  components: {
    PageHeader,
    SectionTable,
    SectionFormModal,
    ContentFormModal
  },
  setup() {
    const cmsStore = useCmsStore()
    const toast = useToast()
    
    const showSectionModal = ref(false)
    const showContentModal = ref(false)
    const selectedSection = ref(null)
    const selectedContent = ref(null)
    const selectedSectionForContent = ref(null)
    const selectedPageId = ref(null)

    const pages = computed(() => cmsStore.pages)
    const sections = computed(() => cmsStore.sections)
    const loading = computed(() => cmsStore.loading)

    const filteredSections = computed(() => {
      if (!selectedPageId.value) return sections.value
      return sections.value.filter(s => s.page_id === selectedPageId.value)
    })

    onMounted(async () => {
      try {
        await cmsStore.fetchPages()
        console.log('Pages loaded:', cmsStore.pages)
        await cmsStore.fetchSections()
        console.log('Sections loaded:', cmsStore.sections)
      } catch (error) {
        console.error('Error loading data:', error)
        toast.error('Error loading data: ' + (error.response?.data?.message || error.message))
      }
    })

    const handleEditSection = (section) => {
      selectedSection.value = section
      showSectionModal.value = true
    }

    const closeSectionModal = () => {
      showSectionModal.value = false
      selectedSection.value = null
    }

    const handleSectionSaved = () => {
      closeSectionModal()
      cmsStore.fetchSections()
    }

    const handleDeleteSection = async (id) => {
      if (confirm('Are you sure you want to delete this section?')) {
        try {
          await cmsStore.deleteSection(id)
          toast.success('Section deleted successfully')
        } catch (error) {
          toast.error(error.message)
        }
      }
    }

    const handleAddContent = (sectionId) => {
      selectedSectionForContent.value = sectionId
      selectedContent.value = null
      showContentModal.value = true
    }

    const closeContentModal = () => {
      showContentModal.value = false
      selectedContent.value = null
      selectedSectionForContent.value = null
    }

    const handleContentSaved = () => {
      closeContentModal()
      cmsStore.fetchSections()
    }

    const handleEditContent = (content) => {
      selectedContent.value = content
      selectedSectionForContent.value = content.section_id
      showContentModal.value = true
    }

    const handleDeleteContent = async (id) => {
      if (confirm('Are you sure you want to delete this content?')) {
        try {
          await cmsStore.deleteContent(id)
          toast.success('Content deleted successfully')
        } catch (error) {
          toast.error(error.message)
        }
      }
    }

    return {
      pages,
      sections,
      filteredSections,
      loading,
      showSectionModal,
      showContentModal,
      selectedSection,
      selectedContent,
      selectedSectionForContent,
      selectedPageId,
      handleEditSection,
      closeSectionModal,
      handleSectionSaved,
      handleDeleteSection,
      handleAddContent,
      handleEditContent,
      handleDeleteContent,
      closeContentModal,
      handleContentSaved
    }
  }
}
</script>

