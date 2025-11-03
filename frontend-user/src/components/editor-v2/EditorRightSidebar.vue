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
  background: var(--color-bg-primary);
  border-left: 1px solid var(--color-border-light);
  display: flex;
  flex-direction: column;
  position: relative;
  transition: width var(--transition-slow);
  overflow: hidden;
}

[dir="rtl"] .editor-right-sidebar {
  border-left: none;
  border-right: 1px solid var(--color-border-light);
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
  border-radius: var(--radius-md) 0 0 var(--radius-md);
  border: 1px solid var(--color-border-light);
  border-right: none;
  background: var(--color-bg-primary);
  color: var(--color-text-secondary);
  font-size: var(--text-base);
  cursor: pointer;
  z-index: 10;
  transition: var(--transition-fast);
}

.collapse-toggle:hover {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
}

[dir="rtl"] .collapse-toggle {
  left: auto;
  right: -12px;
  border-radius: 0 var(--radius-md) var(--radius-md) 0;
  border-right: 1px solid var(--color-border-light);
  border-left: none;
}

.sidebar-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.sidebar-header {
  padding: var(--space-4);
  border-bottom: 1px solid var(--color-border-light);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand-logo h4 {
  margin: 0;
  color: var(--color-text-primary);
  font-weight: var(--font-bold);
  font-size: var(--text-lg);
}

.btn-icon {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-md);
  border: none;
  background: transparent;
  color: var(--color-text-secondary);
  font-size: var(--text-xl);
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-icon:hover {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
}

.quick-actions {
  padding: var(--space-4);
  border-bottom: 1px solid var(--color-border-light);
}

.btn-primary-full {
  width: 100%;
  padding: var(--space-3);
  border-radius: var(--radius-md);
  border: none;
  background: var(--color-brand-primary);
  color: var(--color-bg-primary);
  font-weight: var(--font-semibold);
  font-size: var(--text-sm);
  cursor: pointer;
  transition: var(--transition-fast);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
}

.btn-primary-full:hover {
  background: var(--color-brand-primary-hover);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.sidebar-section {
  padding: var(--space-4);
  border-bottom: 1px solid var(--color-border-light);
}

.section-header {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  font-size: var(--text-sm);
  color: var(--color-text-primary);
  cursor: pointer;
  padding: var(--space-2);
  border-radius: var(--radius-md);
  transition: var(--transition-fast);
  font-weight: var(--font-medium);
}

.section-header:hover {
  background: var(--color-bg-hover);
}

.section-header i {
  font-size: var(--text-lg);
}

.section-title {
  margin: 0 0 var(--space-3) 0;
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  color: var(--color-text-secondary);
}

.recent-designs-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.recent-design-item {
  display: flex;
  gap: var(--space-3);
  padding: var(--space-2);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition-fast);
}

.recent-design-item:hover {
  background: var(--color-bg-hover);
}

.design-thumbnail {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--color-bg-secondary);
  flex-shrink: 0;
  border: 1px solid var(--color-border-light);
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
  color: var(--color-text-tertiary);
  font-size: var(--text-2xl);
}

.design-details {
  flex: 1;
  overflow: hidden;
}

.design-name {
  margin: 0;
  font-size: var(--text-sm);
  color: var(--color-text-primary);
  font-weight: var(--font-medium);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.design-type {
  margin: var(--space-1) 0 0 0;
  font-size: var(--text-xs);
  color: var(--color-text-tertiary);
}

.btn-view-all {
  width: 100%;
  padding: var(--space-2);
  margin-top: var(--space-3);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-medium);
  background: var(--color-bg-primary);
  color: var(--color-brand-primary);
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  cursor: pointer;
  transition: var(--transition-fast);
}

.btn-view-all:hover {
  background: var(--color-bg-hover);
  border-color: var(--color-brand-primary);
}

.bottom-nav {
  margin-top: auto;
  padding: var(--space-4);
  border-top: 1px solid var(--color-border-light);
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.nav-item {
  width: 100%;
  height: 36px;
  border-radius: var(--radius-md);
  border: none;
  background: transparent;
  color: var(--color-text-secondary);
  font-size: var(--text-xl);
  cursor: pointer;
  transition: var(--transition-fast);
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-item:hover {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
}

.nav-item.has-badge .badge {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 16px;
  height: 16px;
  border-radius: var(--radius-full);
  background: var(--color-error);
  color: var(--color-bg-primary);
  font-size: var(--text-xs);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: var(--font-semibold);
}

[dir="rtl"] .nav-item.has-badge .badge {
  right: auto;
  left: 6px;
}

.nav-divider {
  height: 1px;
  background: var(--color-border-light);
  margin: var(--space-2) 0;
}
</style>

