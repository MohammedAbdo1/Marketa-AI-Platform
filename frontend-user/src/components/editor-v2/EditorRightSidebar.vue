<template>
  <div class="editor-right-sidebar" :class="{ collapsed: isCollapsed }">
    <!-- Collapse Toggle -->
    <button class="collapse-toggle" @click="isCollapsed = !isCollapsed">
      <i :class="isCollapsed ? 'bx bx-chevron-left' : 'bx bx-chevron-right'"></i>
    </button>

    <div v-if="!isCollapsed" class="sidebar-content">
      <!-- Header -->
      <div class="sidebar-header">
        <div class="brand-logo">
          <h4>مركتة</h4>
        </div>
        <button class="btn-icon" @click="toggleMenu">
          <i class='bx bx-menu'></i>
        </button>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <button class="btn-primary-full" @click="createNew">
          <i class='bx bx-plus'></i>
          {{ $t('editor.create_new') }}
        </button>
      </div>

      <!-- Starred -->
      <div class="sidebar-section">
        <div class="section-header">
          <i class='bx bx-star text-warning'></i>
          <span>{{ $t('editor.starred') }}</span>
        </div>
      </div>

      <!-- Home -->
      <div class="sidebar-section">
        <div class="section-header">
          <i class='bx bx-home'></i>
          <span>{{ $t('editor.home') }}</span>
        </div>
      </div>

      <!-- Recent Designs -->
      <div class="sidebar-section">
        <h6 class="section-title">{{ $t('editor.recent_designs') }}</h6>
        <div class="recent-designs-list">
          <div
            v-for="design in recentDesigns"
            :key="design.uuid"
            class="recent-design-item"
            @click="$emit('design-select', design)"
          >
            <div class="design-thumbnail">
              <img 
                v-if="design.thumbnail_url"
                :src="design.thumbnail_url"
                :alt="design.title"
              />
              <div v-else class="thumbnail-placeholder">
                <i class='bx bx-image'></i>
              </div>
            </div>
            <div class="design-details">
              <p class="design-name">{{ design.title || 'Untitled' }}</p>
              <p class="design-type">{{ formatDesignType(design.design_type) }}</p>
            </div>
          </div>
        </div>
        <button class="btn-view-all" @click="viewAllDesigns">
          {{ $t('editor.view_all') }}
        </button>
      </div>

      <!-- Bottom Navigation -->
      <div class="bottom-nav">
        <button class="nav-item">
          <i class='bx bx-briefcase'></i>
        </button>
        <button class="nav-item">
          <i class='bx bx-folder'></i>
        </button>
        <button class="nav-item">
          <i class='bx bx-layout'></i>
        </button>
        <button class="nav-item">
          <i class='bx bx-crown'></i>
        </button>
        <button class="nav-item">
          <i class='bx bx-dots-horizontal-rounded'></i>
        </button>
        <div class="nav-divider"></div>
        <button class="nav-item" :class="{ 'has-badge': true }">
          <i class='bx bx-bell'></i>
          <span class="badge">1</span>
        </button>
        <button class="nav-item">
          <i class='bx bx-user-circle'></i>
        </button>
        <div class="nav-divider"></div>
        <button class="nav-item">
          <i class='bx bx-trash'></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

const props = defineProps({
  recentDesigns: {
    type: Array,
    default: () => []
  }
})

defineEmits(['design-select'])

const { t } = useI18n()
const router = useRouter()

const isCollapsed = ref(false)

const toggleMenu = () => {
  console.log('Toggle menu')
}

const createNew = () => {
  router.push('/dashboard/designs')
}

const viewAllDesigns = () => {
  router.push('/dashboard/designs')
}

const formatDesignType = (type) => {
  const types = {
    social_post: 'منشور سوشيال',
    story: 'ستوري',
    presentation: 'عرض تقديمي',
    banner: 'بانر'
  }
  return types[type] || type
}
</script>

<style scoped>
.editor-right-sidebar {
  width: 280px;
  background: white;
  border-left: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  position: relative;
  transition: width 0.3s ease;
  overflow: hidden;
}

.editor-right-sidebar.collapsed {
  width: 0;
  border: none;
}

.collapse-toggle {
  position: absolute;
  left: -12px;
  top: 50%;
  transform: translateY(-50%);
  width: 24px;
  height: 48px;
  border-radius: 6px 0 0 6px;
  border: 1px solid #e2e8f0;
  border-right: none;
  background: white;
  color: #64748b;
  font-size: 1rem;
  cursor: pointer;
  z-index: 10;
  transition: all 0.2s ease;
}

.collapse-toggle:hover {
  background: #f8f9fa;
  color: #667eea;
}

.sidebar-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.sidebar-header {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand-logo h4 {
  margin: 0;
  color: #667eea;
  font-weight: 700;
}

.btn-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 1.3rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-icon:hover {
  background: #f8f9fa;
}

.quick-actions {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.btn-primary-full {
  width: 100%;
  padding: 0.75rem;
  border-radius: 8px;
  border: none;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-primary-full:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.sidebar-section {
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.95rem;
  color: #2d3748;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  transition: background 0.2s ease;
}

.section-header:hover {
  background: #f8f9fa;
}

.section-header i {
  font-size: 1.2rem;
}

.section-title {
  margin: 0 0 0.75rem 0;
  font-size: 0.85rem;
  font-weight: 600;
  color: #64748b;
}

.recent-designs-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.recent-design-item {
  display: flex;
  gap: 0.75rem;
  padding: 0.5rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.recent-design-item:hover {
  background: #f8f9fa;
}

.design-thumbnail {
  width: 48px;
  height: 48px;
  border-radius: 6px;
  overflow: hidden;
  background: #f8f9fa;
  flex-shrink: 0;
}

.design-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumbnail-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #cbd5e1;
  font-size: 1.5rem;
}

.design-details {
  flex: 1;
  overflow: hidden;
}

.design-name {
  margin: 0;
  font-size: 0.9rem;
  color: #2d3748;
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.design-type {
  margin: 0.25rem 0 0 0;
  font-size: 0.75rem;
  color: #94a3b8;
}

.btn-view-all {
  width: 100%;
  padding: 0.5rem;
  margin-top: 0.75rem;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #667eea;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-view-all:hover {
  background: #f8f9fa;
  border-color: #667eea;
}

.bottom-nav {
  margin-top: auto;
  padding: 1rem;
  border-top: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-item {
  width: 100%;
  height: 40px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 1.3rem;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-item:hover {
  background: #f8f9fa;
  color: #667eea;
}

.nav-item.has-badge .badge {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #e53e3e;
  color: white;
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.nav-divider {
  height: 1px;
  background: #e2e8f0;
  margin: 0.5rem 0;
}
</style>

