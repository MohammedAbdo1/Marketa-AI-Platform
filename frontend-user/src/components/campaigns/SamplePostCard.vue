<template>
  <div class="sample-post-card">
    <div class="post-image-container">
      <CanvasPreview
        v-if="hasComposition"
        :composition-data="compositionData"
        :width="compositionWidth"
        :height="compositionHeight"
        fit-parent
      />
      <img
        v-else
        :src="post.base_image_url || post.image_url || post.media_urls?.[0] || '/placeholder-image.png'"
        :alt="getPostContent()"
        class="post-image"
      />

      <div class="platform-badge">
        <i :class="getPlatformIcon(post.platform)"></i>
        {{ post.platform }}
      </div>
    </div>

    <div class="post-info">
      <div class="post-content">
        <p>{{ getPostContent() }}</p>
      </div>

      <div v-if="getHashtags().length > 0" class="post-hashtags">
        <span v-for="tag in getHashtags()" :key="tag" class="hashtag">
          {{ tag }}
        </span>
      </div>

      <div class="post-meta">
        <span class="meta-item">
          <i class="bx bx-calendar"></i>
          {{ post.phase || 'Week ' + post.week }}
        </span>
        <span class="meta-item">
          <i class="bx bx-time"></i>
          Day {{ post.day }}
        </span>
      </div>
    </div>

    <details v-if="post.content_brief" class="content-brief">
      <summary class="brief-summary">
        <i class="bx bx-info-circle"></i>
        {{ $t('campaigns.content_brief') }}
      </summary>

      <div class="brief-content">
        <div v-if="post.content_brief.instructions" class="brief-section">
          <h6 class="brief-title">
            <i class="bx bx-list-ul"></i>
            {{ $t('campaigns.instructions') }}
          </h6>
          <p class="brief-text">{{ post.content_brief.instructions.overview }}</p>
        </div>

        <div v-if="post.content_brief.filming" class="brief-section">
          <h6 class="brief-title">
            <i class="bx bx-camera"></i>
            {{ $t('campaigns.filming_guide') }}
          </h6>
          <ul class="brief-list">
            <li v-for="(item, key) in post.content_brief.filming" :key="key">
              <strong>{{ key }}:</strong>
              {{ Array.isArray(item) ? item.join(', ') : item }}
            </li>
          </ul>
        </div>

        <div v-if="post.content_brief.editing" class="brief-section">
          <h6 class="brief-title">
            <i class="bx bx-movie"></i>
            {{ $t('campaigns.editing_tips') }}
          </h6>
          <ul class="brief-list">
            <li v-for="(item, key) in post.content_brief.editing" :key="key">
              <strong>{{ key }}:</strong>
              {{ Array.isArray(item) ? item.join(', ') : item }}
            </li>
          </ul>
        </div>

        <div v-if="post.content_brief.engagement_tips" class="brief-section">
          <h6 class="brief-title">
            <i class="bx bx-trending-up"></i>
            {{ $t('campaigns.engagement_tips') }}
          </h6>
          <ul class="brief-list">
            <li v-for="(tip, idx) in post.content_brief.engagement_tips" :key="idx">
              {{ tip }}
            </li>
          </ul>
        </div>
      </div>
    </details>

    <div v-if="post.expected_results" class="expected-results">
      <h6 class="results-title">{{ $t('campaigns.expected_results') }}</h6>
      <div class="results-grid">
        <div
          v-for="(value, key) in post.expected_results"
          :key="key"
          class="result-item"
        >
          <span class="result-label">{{ formatKey(key) }}</span>
          <span class="result-value">{{ value }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import CanvasPreview from '@/components/designs/CanvasPreview.vue'

const props = defineProps({
  post: { type: Object, required: true }
})

const { t } = useI18n()

const getPostContent = () => {
  if (!props.post.content) return ''

  if (typeof props.post.content === 'object') {
    const primary = props.post.primary_language || 'ar'
    return props.post.content[primary] || Object.values(props.post.content)[0] || ''
  }

  return props.post.content
}

const getHashtags = () => {
  if (!props.post.hashtags) return []

  if (typeof props.post.hashtags === 'object' && !Array.isArray(props.post.hashtags)) {
    const primary = props.post.primary_language || 'ar'
    return props.post.hashtags[primary] || Object.values(props.post.hashtags)[0] || []
  }

  if (Array.isArray(props.post.hashtags)) {
    return props.post.hashtags
  }

  return []
}

const resolveCompositionSource = () => {
  if (props.post.composition_layers) {
    const raw = props.post.composition_layers
    if (Array.isArray(raw)) return raw

    const keys = Object.keys(raw)
    const isNumericCollection = keys.every((key) => !Number.isNaN(Number(key)))
    if (isNumericCollection) {
      return Object.values(raw)
    }

    return raw
  }
  if (props.post.content?.composition_layers) {
    return {
      layers: props.post.content.composition_layers,
      dimensions: props.post.content.dimensions
    }
  }
  return null
}

const hasComposition = computed(() => {
  const raw = resolveCompositionSource()
  if (!raw) return false
  if (Array.isArray(raw)) return raw.length > 0
  if (typeof raw === 'object') {
    if (Array.isArray(raw.layers)) return raw.layers.length > 0
    if (Array.isArray(raw.objects)) return raw.objects.length > 0
    if (raw.version) return true
  }
  return false
})

const compositionData = computed(() => {
  if (!hasComposition.value) return null
  const raw = resolveCompositionSource()
  if (Array.isArray(raw)) {
    return { layers: raw }
  }
  if (raw && typeof raw === 'object' && Array.isArray(raw.layers)) {
    return raw
  }
  return raw
})

const compositionWidth = computed(() => {
  const source = resolveCompositionSource()
  const dimensions = (source && !Array.isArray(source) && source.dimensions) || props.post.content?.dimensions
  if (dimensions?.width) return dimensions.width
  return props.post.width || 1080
})

const compositionHeight = computed(() => {
  const source = resolveCompositionSource()
  const dimensions = (source && !Array.isArray(source) && source.dimensions) || props.post.content?.dimensions
  if (dimensions?.height) return dimensions.height
  return props.post.height || 1080
})

const getPlatformIcon = (platform) => {
  const iconMap = {
    instagram: 'bx bxl-instagram',
    facebook: 'bx bxl-facebook',
    twitter: 'bx bxl-twitter',
    x: 'bx bxl-twitter',
    tiktok: 'bx bxl-tiktok',
    linkedin: 'bx bxl-linkedin'
  }
  return iconMap[platform?.toLowerCase?.()] || 'bx bx-share-alt'
}

const formatKey = (key) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())
}
</script>

<style scoped>
.sample-post-card {
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  overflow: hidden;
  transition: var(--transition-fast);
}

.sample-post-card:hover {
  border-color: var(--color-brand-primary);
  box-shadow: var(--shadow-md);
}

.post-image-container {
  position: relative;
  width: 100%;
  aspect-ratio: 1;
  overflow: hidden;
  background: var(--color-bg-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
}

.post-image-container .canvas-preview {
  position: absolute;
  inset: 0;
}

.post-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.platform-badge {
  position: absolute;
  top: var(--space-3);
  right: var(--space-3);
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  font-size: var(--text-xs);
  font-weight: var(--font-medium);
  display: flex;
  align-items: center;
  gap: var(--space-1);
}

[dir="rtl"] .platform-badge {
  right: auto;
  left: var(--space-3);
}

.post-info {
  padding: var(--space-4);
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.post-content p {
  font-size: var(--text-sm);
  color: var(--color-text-primary);
  line-height: 1.6;
  margin: 0;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}

.post-hashtags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-1);
}

.hashtag {
  font-size: var(--text-xs);
  color: var(--color-blue-text);
  background: var(--color-blue-bg);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
}

.post-meta {
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

.content-brief {
  border-top: 1px solid var(--color-border-light);
}

.brief-summary {
  padding: var(--space-3) var(--space-4);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  color: var(--color-text-secondary);
  transition: var(--transition-fast);
}

.brief-summary:hover {
  background: var(--color-bg-hover);
  color: var(--color-text-primary);
}

.brief-content {
  padding: var(--space-4);
  background: var(--color-bg-secondary);
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.brief-section {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.brief-title {
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  color: var(--color-text-primary);
  display: flex;
  align-items: center;
  gap: var(--space-2);
  margin: 0;
}

.brief-text {
  font-size: var(--text-xs);
  color: var(--color-text-secondary);
  line-height: 1.6;
  margin: 0;
}

.brief-list {
  margin: 0;
  padding-left: var(--space-4);
  font-size: var(--text-xs);
  color: var(--color-text-secondary);
  line-height: 1.6;
}

[dir="rtl"] .brief-list {
  padding-left: 0;
  padding-right: var(--space-4);
}

.brief-list li {
  margin-bottom: var(--space-1);
}

.expected-results {
  padding: var(--space-4);
  background: var(--color-green-bg);
  border-top: 1px solid var(--color-border-light);
}

.results-title {
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  color: var(--color-green-text);
  margin: 0 0 var(--space-3) 0;
}

.results-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-2);
}

.result-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.result-label {
  font-size: 11px;
  color: var(--color-text-tertiary);
  text-transform: capitalize;
}

.result-value {
  font-size: var(--text-sm);
  font-weight: var(--font-semibold);
  color: var(--color-green-text);
}

@media (max-width: 768px) {
  .results-grid {
    grid-template-columns: 1fr;
  }
}
</style>

