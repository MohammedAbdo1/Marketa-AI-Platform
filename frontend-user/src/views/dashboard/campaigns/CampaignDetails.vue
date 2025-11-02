<template>
  <div class="container max-w-4xl mx-auto py-6">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">{{ campaign?.name || $t('campaigns.details.title') }}</h2>
        <p class="text-muted mb-0">
          {{ $t('campaigns.details.subtitle') }}
        </p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" @click="$router.push('/dashboard/campaigns')">
          <i class="bx bx-arrow-back"></i> {{ $t('common.back') }}
        </button>
        <button class="btn btn-primary" @click="rebuildCampaign" :disabled="loading">
          <i class="bx bx-refresh"></i> {{ $t('campaigns.details.rebuild') || 'Regenerate' }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary mb-3" role="status"></div>
      <div class="text-muted">{{ $t('common.loading') }}</div>
    </div>

    <div v-else>
      <div class="card mb-4">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <div class="text-muted">{{ $t('campaigns.fields.status') }}</div>
              <div class="fw-semibold">{{ campaign?.generation_status }}</div>
            </div>
            <div class="col-md-3">
              <div class="text-muted">{{ $t('campaigns.fields.progress') }}</div>
              <div class="fw-semibold">{{ campaign?.generation_progress }}%</div>
            </div>
            <div class="col-md-3">
              <div class="text-muted">{{ $t('campaigns.fields.duration_days') }}</div>
              <div class="fw-semibold">{{ campaign?.duration_days }}</div>
            </div>
            <div class="col-md-3">
              <div class="text-muted">{{ $t('campaigns.fields.platforms') }}</div>
              <div class="fw-semibold">{{ (campaign?.platforms || []).join(', ') }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ $t('campaigns.details.posts') }}</h4>
        <span class="badge bg-primary">{{ posts.length }}</span>
      </div>

      <div v-if="posts.length === 0" class="alert alert-info">
        {{ $t('campaigns.details.no_posts') }}
      </div>

      <div class="row g-3">
        <div v-for="post in posts" :key="post.id" class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-light text-dark">{{ post.platform }}</span>
                <span class="badge" :class="post.status === 'approved' ? 'bg-success' : 'bg-secondary'">{{ post.status }}</span>
              </div>

              <div class="mb-2" v-if="post.content_ar">
                <div class="text-muted small mb-1">AR</div>
                <div class="fw-semibold">{{ post.content_ar }}</div>
              </div>
              <div class="mb-2" v-if="post.content_en">
                <div class="text-muted small mb-1">EN</div>
                <div>{{ post.content_en }}</div>
              </div>

              <div v-if="Array.isArray(post.media_urls) && post.media_urls.length" class="mt-3">
                <div class="text-muted small mb-1">Media</div>
                <div class="row row-cols-2 g-2">
                  <div v-for="(url, idx) in post.media_urls" :key="idx" class="col">
                    <img :src="url" class="img-fluid rounded border w-100" style="height:220px;object-fit:cover;" alt="Post image" />
                  </div>
                </div>
              </div>

              <div v-if="Array.isArray(post.hashtags) && post.hashtags.length" class="mt-2">
                <div class="text-muted small mb-1">#</div>
                <div class="d-flex flex-wrap gap-1">
                  <span v-for="(tag, idx) in post.hashtags" :key="idx" class="badge bg-light text-dark">{{ tag }}</span>
                </div>
              </div>

              <!-- Editor Button -->
              <div class="mt-3 d-flex gap-2">
                <router-link 
                  :to="{ name: 'posts.edit', params: { id: post.id } }"
                  class="btn btn-sm btn-primary"
                >
                  <i class="fas fa-edit"></i> تحرير
                </router-link>
                <button 
                  v-if="post.is_composed" 
                  class="btn btn-sm btn-outline-info"
                  :title="'منشور ذكي مع ' + (post.composition_layers?.length || 0) + ' طبقة'"
                >
                  <i class="fas fa-layer-group"></i> {{ post.composition_layers?.length || 0 }} طبقات
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useCampaignStore } from '@/stores/campaign'

const route = useRoute()
const campaignStore = useCampaignStore()

const campaign = ref(null)
const posts = ref([])
const loading = ref(true)
async function rebuildCampaign() {
  try {
    loading.value = true
    const id = route.params.id || campaign.value?.uuid || campaign.value?.id
    if (!id) throw new Error('Missing campaign id/uuid')
    await campaignStore.generateCampaign(id, { rebuild: true })
    // reload details
    const data = await campaignStore.fetchCampaign(id)
    campaign.value = data?.data || data
    const rawPosts = Array.isArray(campaign.value?.posts) ? campaign.value.posts : []
    posts.value = rawPosts.map(p => ({
      ...p,
      hashtags: typeof p.hashtags === 'string' ? safeParseJsonArray(p.hashtags) : (Array.isArray(p.hashtags) ? p.hashtags : [])
    }))
  } catch (e) {
    // handled by interceptor/toast
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const uuid = route.params.uuid
    const data = await campaignStore.fetchCampaign(uuid)
    campaign.value = data?.data || data

    // Normalize hashtags if stored as JSON string
    const rawPosts = Array.isArray(campaign.value?.posts) ? campaign.value.posts : []
    posts.value = rawPosts.map(p => ({
      ...p,
      hashtags: typeof p.hashtags === 'string' ? safeParseJsonArray(p.hashtags) : (Array.isArray(p.hashtags) ? p.hashtags : [])
    }))
  } catch (e) {
    // leave default error handling to interceptors/toasts elsewhere
  } finally {
    loading.value = false
  }
})

function safeParseJsonArray(str) {
  try {
    const v = JSON.parse(str)
    return Array.isArray(v) ? v : []
  } catch (_) {
    return []
  }
}
</script>
