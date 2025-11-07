<template>
  <div class="timeline-visualization">
    <h4 class="timeline-title">{{ $t('campaigns.timeline') }}</h4>

    <!-- Weeks Container -->
    <div class="weeks-container">
      <div 
        v-for="(week, weekIndex) in timeline" 
        :key="weekIndex"
        class="week-section"
      >
        <!-- Week Header -->
        <div class="week-header">
          <div class="week-info">
            <h5 class="week-name">{{ week.name || `الأسبوع ${weekIndex + 1}` }}</h5>
            <span class="week-theme">{{ week.theme }}</span>
          </div>
          <span class="week-count">{{ getTotalPosts(week) }} {{ $t('campaigns.posts') }}</span>
        </div>

        <!-- Days Grid -->
        <div class="days-grid">
          <div 
            v-for="(day, dayIndex) in week.days" 
            :key="day.day"
            class="day-card"
            :class="{ 'has-posts': day.posts && day.posts.length > 0 }"
            @click="toggleDay(weekIndex, dayIndex, day.day, week.days.length)"
          >
            <!-- Day Number -->
            <div class="day-number">{{ day.day }}</div>
            
            <!-- Day Name -->
            <div class="day-name">{{ day.day_name }}</div>
            
            <!-- Posts Count -->
            <div class="posts-count" v-if="day.posts && day.posts.length > 0">
              <i class="bx bx-images"></i>
              {{ day.posts.length }}
            </div>

            <!-- Expanded Day Details -->
            <div 
              v-if="expandedDay?.key === `${weekIndex}-${day.day}`" 
              class="day-details"
              :class="`align-${expandedDay.align}`"
            >
              <div v-for="(post, idx) in day.posts" :key="idx" class="post-preview">
                <div class="post-time">{{ post.time }}</div>
                <div class="post-platform">
                  <i :class="getPlatformIcon(post.platform)"></i>
                  {{ post.platform }}
                </div>
                <div class="post-type">{{ post.type }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  timeline: { type: Array, required: true }
})

const { t } = useI18n()
const expandedDay = ref(null)

const getTotalPosts = (week) => {
  if (!week.days) return 0
  return week.days.reduce((total, day) => {
    return total + (day.posts ? day.posts.length : 0)
  }, 0)
}

const toggleDay = (weekIndex, dayIndex, dayNum, totalDays) => {
  const key = `${weekIndex}-${dayNum}`
  if (expandedDay.value?.key === key) {
    expandedDay.value = null
    return
  }

  let align = 'center'
  if (dayIndex <= 1) {
    align = 'start'
  } else if (dayIndex >= totalDays - 2) {
    align = 'end'
  }

  expandedDay.value = { key, align }
}

const getPlatformIcon = (platform) => {
  const iconMap = {
    'instagram': 'bx bxl-instagram',
    'facebook': 'bx bxl-facebook',
    'twitter': 'bx bxl-twitter',
    'x': 'bx bxl-twitter',
    'tiktok': 'bx bxl-tiktok',
    'linkedin': 'bx bxl-linkedin',
    'youtube': 'bx bxl-youtube',
    'snapchat': 'bx bxl-snapchat'
  }
  return iconMap[platform?.toLowerCase()] || 'bx bx-share-alt'
}
</script>

<style scoped>
.timeline-visualization {
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  padding: var(--space-5);
}

.timeline-title {
  font-size: var(--text-lg);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
  margin: 0 0 var(--space-4) 0;
}

/* Weeks Container */
.weeks-container {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  overflow: visible;
}

.week-section {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  overflow: visible;
}

/* Week Header */
.week-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: var(--space-2);
  border-bottom: 2px solid var(--color-border-light);
}

.week-info {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.week-name {
  font-size: var(--text-md);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
  margin: 0;
}

.week-theme {
  font-size: var(--text-xs);
  color: var(--color-text-secondary);
  font-style: italic;
}

.week-count {
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  color: var(--color-brand-primary);
  background: var(--color-blue-bg);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
}

/* Days Grid */
.days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: var(--space-2);
  position: relative;
  overflow: visible;
}

.day-card {
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  padding: var(--space-3);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-2);
  cursor: pointer;
  transition: var(--transition-fast);
  position: relative;
}

.day-card:hover {
  border-color: var(--color-brand-primary);
  background: var(--color-bg-hover);
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.day-card.has-posts {
  border-color: var(--color-brand-primary);
  background: var(--color-bg-primary);
}

.day-number {
  font-size: var(--text-xl);
  font-weight: var(--font-bold);
  color: var(--color-text-primary);
}

.day-name {
  font-size: var(--text-xs);
  color: var(--color-text-secondary);
  text-align: center;
}

.posts-count {
  display: flex;
  align-items: center;
  gap: var(--space-1);
  font-size: var(--text-xs);
  font-weight: var(--font-semibold);
  color: var(--color-brand-primary);
  background: var(--color-blue-bg);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
}

/* Day Details */
.day-details {
  position: absolute;
  inset-inline-start: 50%;
  bottom: calc(100% + var(--space-2));
  transform: translateX(-50%);
  background: var(--color-bg-primary);
  border: 1px solid var(--color-brand-primary);
  border-radius: var(--radius-md);
  padding: var(--space-3);
  z-index: 10;
  box-shadow: var(--shadow-md);
  min-width: 220px;
  max-width: 260px;
  text-align: start;
}

.day-details.align-start {
  inset-inline-start: 0;
  inset-inline-end: auto;
  transform: none;
}

.day-details.align-end {
  inset-inline-end: 0;
  inset-inline-start: auto;
  transform: none;
}

.day-details::before {
  content: '';
  position: absolute;
  inset-inline-start: 50%;
  bottom: -10px;
  transform: translateX(-50%);
  border-width: 10px 10px 0 10px;
  border-style: solid;
  border-color: var(--color-brand-primary) transparent transparent transparent;
}

.day-details.align-start::before,
.day-details.align-start::after {
  inset-inline-start: calc(24px);
  transform: none;
}

.day-details.align-end::before,
.day-details.align-end::after {
  inset-inline-start: auto;
  inset-inline-end: calc(24px);
  transform: none;
}

.day-details::after {
  content: '';
  position: absolute;
  inset-inline-start: 50%;
  bottom: -8px;
  transform: translateX(-50%);
  border-width: 10px 10px 0 10px;
  border-style: solid;
  border-color: var(--color-bg-primary) transparent transparent transparent;
}

.post-preview {
  padding: var(--space-2);
  background: var(--color-bg-secondary);
  border-radius: var(--radius-sm);
  margin-bottom: var(--space-2);
  font-size: var(--text-xs);
}

.post-preview:last-child {
  margin-bottom: 0;
}

.post-time {
  font-weight: var(--font-semibold);
  color: var(--color-brand-primary);
  margin-bottom: var(--space-1);
}

.post-platform {
  display: flex;
  align-items: center;
  gap: var(--space-1);
  color: var(--color-text-secondary);
}

.post-type {
  color: var(--color-text-tertiary);
  font-size: 11px;
}

/* Responsive */
@media (max-width: 1024px) {
  .days-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 768px) {
  .timeline-visualization {
    padding: var(--space-4);
  }
  
  .days-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .day-card {
    padding: var(--space-2);
  }
}
</style>

