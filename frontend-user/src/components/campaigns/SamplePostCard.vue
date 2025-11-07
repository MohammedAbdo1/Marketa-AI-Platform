<template>
  <div class="sample-post-card">
    <!-- Post Image with Text Overlays -->
    <div class="post-image-container">
      <img 
        :src="post.base_image_url || post.image_url || '/placeholder-image.png'" 
        :alt="getPostContent()"
        class="post-image"
      />
      
      <!-- Text Overlays Preview -->
      <div 
        v-for="(layer, idx) in getTextLayers()" 
        :key="idx"
        class="text-overlay"
        :style="getLayerStyle(layer)"
      >
        {{ layer.content }}
      </div>

      <!-- Platform Badge -->
      <div class="platform-badge">
        <i :class="getPlatformIcon(post.platform)"></i>
        {{ post.platform }}
      </div>
    </div>

    <!-- Post Info -->
    <div class="post-info">
      <!-- Content -->
      <div class="post-content">
        <p>{{ getPostContent() }}</p>
      </div>

      <!-- Hashtags -->
      <div v-if="getHashtags().length > 0" class="post-hashtags">
        <span v-for="tag in getHashtags()" :key="tag" class="hashtag">
          {{ tag }}
        </span>
      </div>

      <!-- Post Meta -->
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

    <!-- Content Brief (Expandable) -->
    <details v-if="post.content_brief" class="content-brief">
      <summary class="brief-summary">
        <i class="bx bx-info-circle"></i>
        {{ $t('campaigns.content_brief') }}
      </summary>
      
      <div class="brief-content">
        <!-- Instructions -->
        <div v-if="post.content_brief.instructions" class="brief-section">
          <h6 class="brief-title">
            <i class="bx bx-list-ul"></i>
            {{ $t('campaigns.instructions') }}
          </h6>
          <p class="brief-text">{{ post.content_brief.instructions.overview }}</p>
        </div>

        <!-- Filming Guide -->
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

        <!-- Editing Tips -->
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

        <!-- Engagement Tips -->
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

    <!-- Expected Results -->
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

const props = defineProps({
  post: { type: Object, required: true }
})

const { t } = useI18n()

const getPostContent = () => {
  if (!props.post.content) return ''
  
  // If content is object with multiple languages
  if (typeof props.post.content === 'object') {
    const primary = props.post.primary_language || 'ar'
    return props.post.content[primary] || Object.values(props.post.content)[0] || ''
  }
  
  return props.post.content
}

const getHashtags = () => {
  if (!props.post.hashtags) return []
  
  // If hashtags is object with multiple languages
  if (typeof props.post.hashtags === 'object' && !Array.isArray(props.post.hashtags)) {
    const primary = props.post.primary_language || 'ar'
    return props.post.hashtags[primary] || Object.values(props.post.hashtags)[0] || []
  }
  
  if (Array.isArray(props.post.hashtags)) {
    return props.post.hashtags
  }
  
  return []
}

const getTextLayers = () => {
  if (!props.post.composition_layers?.layers) return []
  return props.post.composition_layers.layers.filter(layer => layer.type === 'text')
}

const getLayerStyle = (layer) => {
  if (!layer.position || !layer.style) return {}
  
  return {
    position: 'absolute',
    left: `${layer.position.x}px`,
    top: `${layer.position.y}px`,
    fontSize: `${layer.style.size}px`,
    color: layer.style.color || '#FFFFFF',
    fontWeight: layer.style.weight || 'bold',
    textAlign: layer.style.align || 'center',
    textShadow: layer.style.shadow ? '2px 2px 4px rgba(0,0,0,0.5)' : 'none',
    fontFamily: layer.style.font || 'inherit',
    transform: 'translate(-50%, -50%)'
  }
}

const getPlatformIcon = (platform) => {
  const iconMap = {
    'instagram': 'bx bxl-instagram',
    'facebook': 'bx bxl-facebook',
    'twitter': 'bx bxl-twitter',
    'x': 'bx bxl-twitter',
    'tiktok': 'bx bxl-tiktok',
    'linkedin': 'bx bxl-linkedin'
  }
  return iconMap[platform?.toLowerCase()] || 'bx bx-share-alt'
}

const formatKey = (key) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
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

/* Post Image */
.post-image-container {
  position: relative;
  width: 100%;
  aspect-ratio: 1;
  overflow: hidden;
  background: var(--color-bg-secondary);
}

.post-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.text-overlay {
  position: absolute;
  pointer-events: none;
  white-space: nowrap;
  max-width: 90%;
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

/* Post Info */
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

/* Content Brief */
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

/* Expected Results */
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

/* Responsive */
@media (max-width: 768px) {
  .results-grid {
    grid-template-columns: 1fr;
  }
}
</style>

