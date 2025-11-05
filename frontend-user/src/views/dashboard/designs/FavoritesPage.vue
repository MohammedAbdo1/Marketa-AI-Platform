<template>
  <div class="favorites-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>⭐ {{ $t('designs.favorites') }}</h1>
        <p class="text-muted">{{ $t('designs.subtitle') }}</p>
      </div>
      <button class="btn btn-ghost" @click="createSection">
        <i class="bx bx-plus"></i>
        {{ $t('designs.create_section') }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-indicator">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">{{ $t('common.loading') }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="sections.length === 0 && unsectionedDesigns.length === 0" class="empty-state">
      <i class="bx bx-star display-1"></i>
      <h4>{{ $t('designs.empty.title') }}</h4>
      <p>{{ $t('designs.empty.subtitle') }}</p>
    </div>

    <!-- Sections with Drag & Drop -->
    <div v-else>
      <draggable 
        v-model="sections" 
        @end="handleSectionsReorder"
        handle=".section-handle"
        :animation="200"
        item-key="uuid"
      >
        <template #item="{ element: section }">
          <div class="favorite-section">
            <div class="section-header">
              <i class="bx bx-grip-vertical section-handle"></i>
              <span class="section-emoji">{{ section.emoji }}</span>
              
              <div style="flex: 1;">
                <InlineEditableName 
                  v-model="section.name"
                  @save="(newName) => updateSectionName(section.uuid, newName)"
                />
              </div>

              <div class="section-actions">
                <button class="btn-icon" @click="openSectionMenu(section)">
                  <i class="bx bx-dots-horizontal-rounded"></i>
                </button>
                <button class="section-collapse-btn" @click="toggleSection(section.uuid)">
                  <i :class="collapsedSections.has(section.uuid) ? 'bx bx-chevron-down' : 'bx bx-chevron-up'"></i>
                </button>
              </div>
            </div>

            <!-- Designs in section with drag-drop -->
            <draggable
              v-show="!collapsedSections.has(section.uuid)"
              v-model="section.designs"
              @end="handleDesignsReorder(section)"
              @add="onDesignDropped($event, section)"
              group="designs"
              :animation="200"
              item-key="uuid"
              class="section-designs"
            >
              <template #item="{ element: design }">
                <DesignCard
                  :design="design"
                  :show-unfavorite="true"
                  @edit="editDesign"
                  @duplicate="duplicateDesign"
                  @delete="confirmDelete"
                  @unfavorite="removeFromFavorites"
                />
              </template>
            </draggable>
          </div>
        </template>
      </draggable>

      <!-- Unsectioned Favorites -->
      <div v-if="unsectionedDesigns.length > 0" class="unsectioned-favorites">
        <h3>📌 {{ $t('designs.unsectioned') }}</h3>
        <draggable
          v-model="unsectionedDesigns"
          group="designs"
          @add="onDesignDropped($event, null)"
          :animation="200"
          item-key="uuid"
          class="section-designs"
        >
          <template #item="{ element: design }">
            <DesignCard
              :design="design"
              :show-unfavorite="true"
              @edit="editDesign"
              @duplicate="duplicateDesign"
              @delete="confirmDelete"
              @unfavorite="removeFromFavorites"
            />
          </template>
        </draggable>
      </div>
    </div>

    <!-- Section Context Menu -->
    <Teleport to="body">
      <div 
        v-if="sectionMenuOpen && selectedSection"
        ref="sectionMenuRef"
        class="context-menu"
        :style="sectionMenuStyle"
        @click.stop
      >
        <button class="context-menu-item" @click="renameSectionAction">
          <i class="bx bx-pencil"></i>
          <span>{{ $t('designs.rename_section') }}</span>
        </button>
        <div class="context-menu-divider"></div>
        <button class="context-menu-item danger" @click="deleteSectionAction">
          <i class="bx bx-trash"></i>
          <span>{{ $t('designs.delete_section') }}</span>
        </button>
      </div>
    </Teleport>

    <!-- Delete Confirmation Modal -->
    <div 
      v-if="deleteModalVisible" 
      class="modal show d-block" 
      tabindex="-1"
      @click.self="cancelDelete"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $t('designs.move_to_trash') }}</h5>
            <button type="button" class="btn-close" @click="cancelDelete"></button>
          </div>
          <div class="modal-body">
            <p>{{ $t('designs.delete.confirm_message') }}</p>
            <p v-if="designToDelete" class="text-muted">
              <strong>{{ designToDelete.title || 'Untitled Design' }}</strong>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cancelDelete">
              {{ $t('common.cancel') }}
            </button>
            <button type="button" class="btn btn-danger" @click="moveToTrash" :disabled="deleting">
              <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
              {{ $t('designs.move_to_trash') }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="deleteModalVisible" class="modal-backdrop show"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useFavoritesStore } from '@/stores/favorites'
import { useDesignStore } from '@/stores/design'
import { useI18n } from 'vue-i18n'
import draggable from 'vuedraggable'
import DesignCard from './DesignCard.vue'
import InlineEditableName from '@/components/designs/InlineEditableName.vue'

const router = useRouter()
const favoritesStore = useFavoritesStore()
const designStore = useDesignStore()
const { t } = useI18n()

// State
const collapsedSections = ref(new Set())
const sectionMenuOpen = ref(false)
const selectedSection = ref(null)
const sectionMenuStyle = ref({ top: '0px', left: '0px' })
const sectionMenuRef = ref(null)
const deleteModalVisible = ref(false)
const designToDelete = ref(null)
const deleting = ref(false)

// Computed
const sections = computed({
  get: () => favoritesStore.sections || [],
  set: (value) => {
    favoritesStore.sections = value
  }
})

const unsectionedDesigns = computed({
  get: () => favoritesStore.unsectionedFavorites || [],
  set: (value) => {
    favoritesStore.unsectionedFavorites = value
  }
})

const loading = computed(() => favoritesStore.loading)

// Methods
const createSection = async () => {
  try {
    const newSection = await favoritesStore.createSection()
    
    // Auto-select name for editing
    await nextTick()
    // TODO: Trigger inline edit automatically
  } catch (error) {
    console.error('Failed to create section:', error)
  }
}

const updateSectionName = async (uuid, newName) => {
  try {
    await favoritesStore.updateSection(uuid, { name: newName })
  } catch (error) {
    console.error('Failed to update section name:', error)
  }
}

const toggleSection = (uuid) => {
  if (collapsedSections.value.has(uuid)) {
    collapsedSections.value.delete(uuid)
  } else {
    collapsedSections.value.add(uuid)
  }
}

const openSectionMenu = (section) => {
  selectedSection.value = section
  sectionMenuOpen.value = true
  // TODO: Position menu correctly
  sectionMenuStyle.value = { top: '100px', right: '100px' }
}

const renameSectionAction = () => {
  sectionMenuOpen.value = false
  // Inline edit already handles this
}

const deleteSectionAction = async () => {
  if (!selectedSection.value) return
  
  try {
    await favoritesStore.deleteSection(selectedSection.value.uuid)
    sectionMenuOpen.value = false
    selectedSection.value = null
  } catch (error) {
    console.error('Failed to delete section:', error)
  }
}

const handleSectionsReorder = async () => {
  try {
    const orderedUuids = sections.value.map(s => s.uuid)
    await favoritesStore.reorderSections(orderedUuids)
  } catch (error) {
    console.error('Failed to reorder sections:', error)
  }
}

const handleDesignsReorder = async (section) => {
  try {
    const orderedIds = section.designs.map(d => d.id)
    await favoritesStore.reorderDesigns(section.uuid, orderedIds)
  } catch (error) {
    console.error('Failed to reorder designs:', error)
  }
}

const onDesignDropped = async (event, targetSection) => {
  try {
    const designId = event.item.dataset.designId
    const sectionId = targetSection?.id || null
    
    await favoritesStore.moveFavorite(parseInt(designId), sectionId)
  } catch (error) {
    console.error('Failed to move design:', error)
  }
}

const editDesign = (design) => {
  window.open(`/editor/${design.uuid}`, '_blank')
}

const duplicateDesign = async (design) => {
  try {
    await designStore.duplicateDesign(design.uuid)
    console.log('Design duplicated successfully')
  } catch (error) {
    console.error('Failed to duplicate design:', error)
  }
}

const confirmDelete = (design) => {
  designToDelete.value = design
  deleteModalVisible.value = true
}

const cancelDelete = () => {
  designToDelete.value = null
  deleteModalVisible.value = false
}

const moveToTrash = async () => {
  if (!designToDelete.value) return
  
  deleting.value = true
  try {
    await designStore.moveToTrash(designToDelete.value.uuid)
    
    // Remove from favorites
    await favoritesStore.removeFromFavorites(designToDelete.value.id)
    
    cancelDelete()
  } catch (error) {
    console.error('Failed to move to trash:', error)
  } finally {
    deleting.value = false
  }
}

const removeFromFavorites = async (design) => {
  try {
    await favoritesStore.removeFromFavorites(design.id)
  } catch (error) {
    console.error('Failed to remove from favorites:', error)
  }
}

const handleClickOutside = (event) => {
  if (sectionMenuRef.value && !sectionMenuRef.value.contains(event.target)) {
    sectionMenuOpen.value = false
  }
}

// Lifecycle
onMounted(async () => {
  await favoritesStore.fetchFavorites()
  
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* All styles are in design-cards.css */

/* Additional section menu styles */
.context-menu {
  position: fixed;
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-dropdown);
  min-width: 200px;
  padding: var(--space-2);
  z-index: 1000;
}

.context-menu-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-2) var(--space-3);
  border: none;
  background: transparent;
  color: var(--color-text-primary);
  font-size: var(--text-sm);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition-fast);
  text-align: start;
}

.context-menu-item:hover {
  background: var(--color-bg-hover);
}

.context-menu-item.danger {
  color: var(--color-error);
}

.context-menu-item.danger:hover {
  background: var(--color-error-bg);
}

.context-menu-divider {
  height: 1px;
  background: var(--color-border-light);
  margin: var(--space-2) 0;
}

[dir="rtl"] .context-menu-item {
  text-align: right;
}
</style>

