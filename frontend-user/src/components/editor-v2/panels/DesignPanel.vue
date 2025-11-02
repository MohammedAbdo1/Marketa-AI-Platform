<template>
  <div class="design-panel">
    <!-- Panel Header -->
    <div class="panel-header">
      <h5 class="panel-title">{{ $t('editor.panels.design') }}</h5>
      <button class="btn-close" @click="$emit('close-panel')">
        <i class='bx bx-x'></i>
      </button>
    </div>

    <!-- Search -->
    <div class="panel-search">
      <i class='bx bx-search'></i>
      <input 
        v-model="searchQuery"
        type="text"
        :placeholder="$t('editor.search_templates')"
        class="search-input"
      />
    </div>

    <!-- Categories -->
    <div class="panel-section">
      <h6 class="section-title">{{ $t('editor.categories') }}</h6>
      <div class="categories-grid">
        <button
          v-for="category in categories"
          :key="category.id"
          class="category-chip"
          :class="{ active: selectedCategory === category.id }"
          @click="selectedCategory = category.id"
        >
          {{ category.name }}
        </button>
      </div>
    </div>

    <!-- Templates Grid -->
    <div class="panel-section">
      <h6 class="section-title">{{ $t('editor.templates') }}</h6>
      <div class="templates-grid">
        <div
          v-for="template in filteredTemplates"
          :key="template.id"
          class="template-card"
          @click="applyTemplate(template)"
        >
          <div class="template-preview">
            <img 
              v-if="template.thumbnail_url"
              :src="template.thumbnail_url"
              :alt="template.title"
            />
            <div v-else class="template-placeholder">
              <i class='bx bx-image'></i>
            </div>
          </div>
          <div class="template-info">
            <p class="template-title">{{ template.title }}</p>
            <span v-if="template.is_pro" class="pro-badge">
              <i class='bx bx-crown'></i> Pro
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useDesignStore } from '@/stores/design'

const emit = defineEmits(['add-element', 'close-panel'])

const { t } = useI18n()

const searchQuery = ref('')
const selectedCategory = ref('all')

const categories = [
  { id: 'all', name: 'الكل' },
  { id: 'social', name: 'سوشيال ميديا' },
  { id: 'story', name: 'ستوري' },
  { id: 'presentation', name: 'عروض تقديمية' },
  { id: 'banner', name: 'بانرات' }
]

const templates = ref([
  {
    id: 1,
    title: 'Instagram Post',
    category: 'social',
    thumbnail_url: null,
    is_pro: false
  },
  {
    id: 2,
    title: 'Story Template',
    category: 'story',
    thumbnail_url: null,
    is_pro: true
  }
])

const filteredTemplates = computed(() => {
  let filtered = templates.value

  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(t => t.category === selectedCategory.value)
  }

  if (searchQuery.value) {
    filtered = filtered.filter(t => 
      t.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  return filtered
})

const applyTemplate = async (template) => {
  try {
    // Get design store
    const designStore = useDesignStore()
    
    // Create new design based on template
    const newDesign = await designStore.createDesign({
      title: `تصميم ${template.title} (${Date.now()})`,
      design_type: template.category,
      source_type: 'manual',
      width: 1080,
      height: 1080,
      composition_data: {
        layers: [],
        dimensions: { width: 1080, height: 1080 }
      }
    })
    
    // Open in editor (new tab)
    window.open(`/editor/${newDesign.uuid}`, '_blank')
    
    // Close panel
    emit('close-panel')
  } catch (error) {
    console.error('Failed to create design from template:', error)
  }
}
</script>

<style scoped>
.design-panel {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: white;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.panel-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
}

.btn-close {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:hover {
  background: #f8f9fa;
  color: #2d3748;
}

.panel-search {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.panel-search i {
  font-size: 1.2rem;
  color: #94a3b8;
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 0.95rem;
  color: #2d3748;
}

.search-input::placeholder {
  color: #94a3b8;
}

.panel-section {
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.section-title {
  margin: 0 0 0.75rem 0;
  font-size: 0.85rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.categories-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.category-chip {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #64748b;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.category-chip:hover {
  border-color: #667eea;
  color: #667eea;
}

.category-chip.active {
  background: #667eea;
  color: white;
  border-color: #667eea;
}

.templates-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.template-card {
  cursor: pointer;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.2s ease;
}

.template-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.template-preview {
  width: 100%;
  aspect-ratio: 1 / 1;
  background: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
}

.template-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.template-placeholder {
  font-size: 2rem;
  color: #cbd5e1;
}

.template-info {
  padding: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: white;
}

.template-title {
  margin: 0;
  font-size: 0.85rem;
  color: #2d3748;
  font-weight: 500;
}

.pro-badge {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: #f59e0b;
  font-weight: 600;
}
</style>

