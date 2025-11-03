<template>
  <div class="design-card card h-100" @click="$emit('edit', design)">
    <!-- Thumbnail -->
    <div class="card-img-wrapper position-relative">
      <img 
        v-if="design.thumbnail_url || design.export_url" 
        :src="design.thumbnail_url || design.export_url" 
        class="card-img-top" 
        :alt="design.title || 'Design'"
        @error="onImageError"
      >
      <div v-else class="card-img-placeholder">
        <i class="bi bi-image display-4 text-muted"></i>
      </div>
      
      <!-- Badges -->
      <div class="position-absolute top-0 start-0 p-2">
        <span v-if="design.is_template" class="badge bg-warning">
          <i class="bi bi-bookmark-fill"></i>
          Template
        </span>
        <span 
          v-if="design.source_type === 'ai'" 
          class="badge bg-primary ms-1"
        >
          <i class="bi bi-stars"></i>
          AI
        </span>
      </div>

      <!-- Actions Dropdown -->
      <div class="position-absolute top-0 end-0 p-2">
        <div class="dropdown">
          <button 
            class="btn btn-sm btn-light rounded-circle" 
            type="button" 
            data-bs-toggle="dropdown"
            @click.stop
          >
            <i class="bi bi-three-dots-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="#" @click.prevent.stop="$emit('edit', design)">
                <i class="bi bi-pencil me-2"></i>
                {{ $t('common.edit') }}
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="#" @click.prevent.stop="$emit('duplicate', design)">
                <i class="bi bi-files me-2"></i>
                {{ $t('common.duplicate') }}
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="#" @click.prevent.stop="$emit('add-to-campaign', design)">
                <i class="bi bi-plus-circle me-2"></i>
                {{ $t('designs.add_to_campaign') }}
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger" href="#" @click.prevent.stop="$emit('delete', design)">
                <i class="bi bi-trash me-2"></i>
                {{ $t('common.delete') }}
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Card Body -->
    <div class="card-body">
      <h6 class="card-title text-truncate">
        {{ design.title || 'Untitled Design' }}
      </h6>
      
      <!-- Meta info -->
      <div class="card-meta small text-muted d-flex justify-content-between">
        <span>
          <i class="bi bi-calendar me-1"></i>
          {{ formatDate(design.created_at) }}
        </span>
        <span v-if="design.used_count > 0">
          <i class="bi bi-arrow-repeat me-1"></i>
          {{ design.used_count }}
        </span>
      </div>

      <!-- Design type -->
      <div class="mt-2">
        <span class="badge bg-light text-dark">
          {{ formatDesignType(design.design_type) }}
        </span>
        <span v-if="design.width && design.height" class="badge bg-light text-dark ms-1">
          {{ design.width }}×{{ design.height }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  design: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['edit', 'duplicate', 'delete', 'add-to-campaign'])
const { t } = useI18n()

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))
  
  if (diffDays === 0) return t('common.today')
  if (diffDays === 1) return t('common.yesterday')
  if (diffDays < 7) return `${diffDays} ${t('common.days_ago')}`
  
  return date.toLocaleDateString()
}

const formatDesignType = (type) => {
  const types = {
    social_post: t('designs.types.social_post'),
    story: t('designs.types.story'),
    presentation: t('designs.types.presentation'),
    banner: t('designs.types.banner'),
    custom: t('designs.types.custom')
  }
  return types[type] || type
}

const onImageError = (event) => {
  event.target.style.display = 'none'
  event.target.parentElement.querySelector('.card-img-placeholder')?.classList.remove('d-none')
}
</script>

<style scoped>
.design-card {
  transition: var(--transition-all);
  cursor: pointer;
  border: 1px solid var(--color-border-light);
}

.design-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-card-hover);
}

.card-img-wrapper {
  aspect-ratio: 1;
  overflow: hidden;
  background: var(--color-bg-secondary);
}

.card-img-top {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-img-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-bg-secondary);
  color: var(--color-text-tertiary);
}

.card-title {
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  margin-bottom: var(--space-2);
  color: var(--color-text-primary);
}

.card-meta {
  font-size: var(--text-xs);
  color: var(--color-text-secondary);
}

.badge {
  font-size: var(--text-xs);
  padding: var(--space-1) var(--space-2);
}

.dropdown-toggle::after {
  display: none;
}
</style>

