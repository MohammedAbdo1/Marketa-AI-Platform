<template>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
    <div class="col" v-for="c in campaigns" :key="c.uuid">
      <CampaignCard :campaign="c" />
    </div>
    <div v-if="campaigns.length === 0" class="text-muted small py-3 text-center w-100">
      No campaigns
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCampaignStore } from '@/stores/campaign'
import CampaignCard from './CampaignCard.vue'

const campaignStore = useCampaignStore()

const campaigns = computed(() => campaignStore.campaigns)
</script>

<style scoped>
</style>


