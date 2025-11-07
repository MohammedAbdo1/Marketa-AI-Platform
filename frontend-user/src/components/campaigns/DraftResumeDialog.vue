<template>
  <div v-if="show" class="modal-backdrop" @click="close">
    <div class="modal modal-lg" @click.stop>
      <!-- Header -->
      <div class="modal-header">
        <div class="d-flex items-center gap-3">
          <i class="bx bx-history text-brand" style="font-size: 24px;"></i>
          <h3 class="modal-title">{{ $t('campaigns.draft_found_title') }}</h3>
        </div>
        <button class="modal-close" @click="close">
          <i class="bx bx-x"></i>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <p class="text-secondary mb-4">
          {{ $t('campaigns.draft_found_message') }}
        </p>

        <!-- Drafts List -->
        <div class="drafts-list">
          <div 
            v-for="draft in drafts" 
            :key="draft.uuid"
            class="draft-card"
          >
            <!-- Draft Info -->
            <div class="draft-info">
              <h4 class="draft-name">{{ draft.name }}</h4>
              <div class="draft-meta">
                <span class="meta-item">
                  <i class="bx bx-briefcase"></i>
                  {{ draft.business_type }}
                </span>
                <span class="meta-item">
                  <i class="bx bx-time"></i>
                  {{ formatDate(draft.updated_at) }}
                </span>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="draft-progress">
              <div class="progress-header">
                <span class="progress-label">
                  {{ $t('campaigns.wizard_step') }}: {{ draft.wizard_step }}/4
                </span>
                <span class="progress-percentage">
                  {{ Math.round((draft.wizard_step / 4) * 100) }}%
                </span>
              </div>
              <div class="progress-bar-container">
                <div 
                  class="progress-bar-fill"
                  :style="{ width: ((draft.wizard_step / 4) * 100) + '%' }"
                ></div>
              </div>
            </div>

            <!-- Actions -->
            <div class="draft-actions">
              <button 
                class="btn btn-primary btn-sm"
                @click="resume(draft)"
              >
                <i class="bx bx-play"></i>
                {{ $t('campaigns.continue_draft') }}
              </button>
              <button 
                class="btn btn-ghost btn-sm"
                @click="confirmDiscard(draft)"
              >
                <i class="bx bx-trash"></i>
                {{ $t('campaigns.discard_draft') }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <button class="btn btn-secondary" @click="startNew">
          <i class="bx bx-plus"></i>
          {{ $t('campaigns.start_new_campaign') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useCampaignStore } from '@/stores/campaign'

const props = defineProps({
  show: { type: Boolean, required: true },
  drafts: { type: Array, default: () => [] }
})

const emit = defineEmits(['close', 'resume', 'discard', 'startNew'])

const router = useRouter()
const { t } = useI18n()
const campaignStore = useCampaignStore()

const close = () => {
  emit('close')
}

const resume = (draft) => {
  emit('resume', draft)
}

const confirmDiscard = (draft) => {
  if (confirm(t('campaigns.confirm_discard_draft'))) {
    emit('discard', draft)
  }
}

const startNew = () => {
  emit('startNew')
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  
  const date = new Date(dateString)
  const now = new Date()
  const diffInMinutes = Math.floor((now - date) / (1000 * 60))
  
  if (diffInMinutes < 1) return t('common.just_now')
  if (diffInMinutes < 60) return `${diffInMinutes} ${t('common.minutes_ago')}`
  
  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) return `${diffInHours} ${t('common.hours_ago')}`
  
  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays === 1) return t('common.yesterday')
  if (diffInDays < 7) return `${diffInDays} ${t('common.days_ago')}`
  
  return date.toLocaleDateString(t('locale'), { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal {
  background: var(--color-bg-primary);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  padding: var(--space-5);
  border-bottom: 1px solid var(--color-border-light);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-title {
  font-size: var(--text-lg);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
  margin: 0;
}

.modal-close {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition-fast);
  color: var(--color-text-tertiary);
  font-size: var(--text-xl);
}

.modal-close:hover {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
}

.modal-body {
  padding: var(--space-5);
  overflow-y: auto;
  flex: 1;
}

.modal-footer {
  padding: var(--space-4) var(--space-5);
  border-top: 1px solid var(--color-border-light);
  display: flex;
  justify-content: center;
}

/* Drafts List */
.drafts-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.draft-card {
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  padding: var(--space-4);
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  transition: var(--transition-fast);
}

.draft-card:hover {
  border-color: var(--color-border);
  box-shadow: var(--shadow-sm);
}

/* Draft Info */
.draft-info {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.draft-name {
  font-size: var(--text-md);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
  margin: 0;
}

.draft-meta {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  font-size: var(--text-xs);
  color: var(--color-text-secondary);
}

.meta-item {
  display: flex;
  align-items: center;
  gap: var(--space-1);
}

.meta-item i {
  font-size: var(--text-sm);
}

/* Progress */
.draft-progress {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: var(--text-xs);
}

.progress-label {
  color: var(--color-text-secondary);
  font-weight: var(--font-medium);
}

.progress-percentage {
  color: var(--color-brand-primary);
  font-weight: var(--font-semibold);
}

.progress-bar-container {
  height: 6px;
  background: var(--color-bg-tertiary);
  border-radius: var(--radius-full);
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--color-brand-primary), var(--color-blue-text));
  border-radius: var(--radius-full);
  transition: width 0.3s ease;
}

/* Actions */
.draft-actions {
  display: flex;
  gap: var(--space-2);
}

.draft-actions .btn {
  flex: 1;
}

/* Responsive */
@media (max-width: 768px) {
  .modal {
    width: 95%;
    max-height: 90vh;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: var(--space-4);
  }
  
  .draft-actions {
    flex-direction: column;
  }
}
</style>

