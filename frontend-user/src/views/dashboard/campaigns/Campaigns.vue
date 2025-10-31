<template>
  <div class="campaigns-page">
    <div class="d-flex align-items-center justify-content-between">
      <h2 class="mb-0">{{ $t('campaigns') }}</h2>
      <div class="d-flex gap-2">
        <button class="btn btn-primary" @click="$router.push('/dashboard/campaigns/create')">
          <i class="bx bx-plus"></i>
          {{ $t('campaigns.wizard.title') || 'New Campaign' }}
        </button>
        <input v-model="query" type="text" class="form-control" style="width: 260px" :placeholder="$t('common.search') || 'Search'" />
        <button class="btn btn-outline-secondary" @click="refresh">Refresh</button>
      </div>
    </div>

    <div class="mt-4">
      
      <div v-if="campaignStore.loading" class="text-muted">Loading campaigns...</div>
      <CampaignKanban v-else />
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useCampaignStore } from '@/stores/campaign'
import CampaignKanban from '@/components/campaigns/CampaignKanban.vue'

const campaignStore = useCampaignStore()
const query = ref('')

const refresh = async () => {
  await campaignStore.fetchCampaigns({ q: query.value })
}

watch(query, async () => {
  await refresh()
})

onMounted(async () => {
  await refresh()
})
</script>

<style scoped>
.campaigns-page { padding: 1rem; }
</style>

