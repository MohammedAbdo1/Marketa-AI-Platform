<template>
  <div class="editor-left-sidebar">
    <!-- Quick Add Button -->
    <button 
      class="sidebar-btn add-btn"
      @click="$emit('panel-change', 'add')"
      :class="{ active: activePanel === 'add' }"
      title="إضافة"
    >
      <i class='bx bx-plus'></i>
    </button>

    <div class="sidebar-divider"></div>

    <!-- Design/Templates -->
    <button 
      class="sidebar-btn"
      @click="$emit('panel-change', 'design')"
      :class="{ active: activePanel === 'design' }"
      :title="$t('editor.panels.design')"
    >
      <i class='bx bx-layout'></i>
      <span class="sidebar-label">{{ $t('editor.panels.design') }}</span>
    </button>

    <!-- Elements -->
    <button 
      class="sidebar-btn"
      @click="$emit('panel-change', 'elements')"
      :class="{ active: activePanel === 'elements' }"
      :title="$t('editor.panels.elements')"
    >
      <i class='bx bx-shape-square'></i>
      <span class="sidebar-label">{{ $t('editor.panels.elements') }}</span>
    </button>

    <!-- Text -->
    <button 
      class="sidebar-btn"
      @click="$emit('panel-change', 'text')"
      :class="{ active: activePanel === 'text' }"
      :title="$t('editor.panels.text')"
    >
      <i class='bx bx-text'></i>
      <span class="sidebar-label">{{ $t('editor.panels.text') }}</span>
    </button>

    <!-- Uploads -->
    <button 
      class="sidebar-btn"
      @click="$emit('panel-change', 'uploads')"
      :class="{ active: activePanel === 'uploads' }"
      :title="$t('editor.panels.uploads')"
    >
      <i class='bx bx-cloud-upload'></i>
      <span class="sidebar-label">{{ $t('editor.panels.uploads') }}</span>
    </button>

    <!-- Brand Kit (Pro) -->
    <button 
      class="sidebar-btn pro-feature"
      @click="$emit('panel-change', 'brand')"
      :class="{ active: activePanel === 'brand' }"
      :title="$t('editor.panels.brand')"
    >
      <i class='bx bx-crown'></i>
      <span class="sidebar-label">{{ $t('editor.panels.brand') }}</span>
    </button>

    <div class="sidebar-divider"></div>

    <!-- Tools -->
    <button 
      class="sidebar-btn"
      @click="$emit('panel-change', 'tools')"
      :class="{ active: activePanel === 'tools' }"
      :title="$t('editor.panels.tools')"
    >
      <i class='bx bx-brush'></i>
      <span class="sidebar-label">{{ $t('editor.panels.tools') }}</span>
    </button>

    <!-- Projects -->
    <button 
      class="sidebar-btn"
      @click="$emit('panel-change', 'projects')"
      :class="{ active: activePanel === 'projects' }"
      :title="$t('editor.panels.projects')"
    >
      <i class='bx bx-folder'></i>
      <span class="sidebar-label">{{ $t('editor.panels.projects') }}</span>
    </button>

    <!-- Apps -->
    <button 
      class="sidebar-btn"
      @click="$emit('panel-change', 'apps')"
      :class="{ active: activePanel === 'apps' }"
      :title="$t('editor.panels.apps')"
    >
      <i class='bx bx-grid-alt'></i>
      <span class="sidebar-label">{{ $t('editor.panels.apps') }}</span>
    </button>

    <!-- More -->
    <button 
      class="sidebar-btn"
      @click="$emit('panel-change', 'more')"
      :class="{ active: activePanel === 'more' }"
      :title="$t('editor.panels.more')"
    >
      <i class='bx bx-dots-horizontal-rounded'></i>
      <span class="sidebar-label">{{ $t('editor.panels.more') }}</span>
    </button>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
  activePanel: String
})

defineEmits(['panel-change'])

const { t } = useI18n()
</script>

<style scoped>
.editor-left-sidebar {
  width: 70px;
  background: white;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem 0;
  gap: 0.5rem;
  overflow-y: auto;
  z-index: 200;
}

.sidebar-btn {
  width: 54px;
  height: 54px;
  border-radius: 12px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  position: relative;
}

.sidebar-btn:hover {
  background: #f8f9fa;
  color: #667eea;
  transform: scale(1.05);
}

.sidebar-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.sidebar-btn.add-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  width: 54px;
  height: 54px;
}

.sidebar-btn.add-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
}

.sidebar-btn.pro-feature i {
  color: #f59e0b;
}

.sidebar-label {
  font-size: 0.65rem;
  font-weight: 500;
  text-align: center;
  line-height: 1;
  max-width: 50px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.sidebar-divider {
  width: 40px;
  height: 1px;
  background: #e2e8f0;
  margin: 0.5rem 0;
}

/* Responsive */
@media (max-width: 768px) {
  .editor-left-sidebar {
    width: 60px;
  }

  .sidebar-btn {
    width: 48px;
    height: 48px;
  }

  .sidebar-label {
    display: none;
  }
}
</style>

