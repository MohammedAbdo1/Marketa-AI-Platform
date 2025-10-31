<template>
  <div
    class="card mb-2 shadow-sm border-0"
    draggable="true"
    @dragstart="onDragStart"
    @click="openDetails"
    style="cursor: pointer;"
  >
    <div class="card-body p-3">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="mb-0 text-truncate" :title="campaign.name">{{ campaign.name }}</h6>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-light text-dark text-capitalize">{{ campaign.status }}</span>
          <div class="dropdown" @click.stop>
            <button class="btn btn-sm btn-link text-muted px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bx bx-dots-horizontal-rounded"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <button class="dropdown-item text-danger" @click.stop.prevent="onDelete">Delete</button>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2 mb-2" v-if="campaign.brand">
        <span class="badge bg-primary-subtle text-primary">{{ campaign.brand.name }}</span>
      </div>

      <div class="small text-muted mb-2">
        <span v-if="campaign.start_date && campaign.end_date">
          {{ formatDate(campaign.start_date) }} → {{ formatDate(campaign.end_date) }}
        </span>
      </div>

      <div v-if="campaign.generation_status === 'generating'" class="mt-1">
        <div class="d-flex justify-content-between small mb-1">
          <span>Generating</span>
          <span>{{ campaign.generation_progress || 0 }}%</span>
        </div>
        <div class="progress" style="height: 6px;">
          <div class="progress-bar" role="progressbar" :style="{ width: (campaign.generation_progress || 0) + '%' }" />
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-2">
        <button class="btn btn-sm btn-outline-secondary" @click.stop="openDetails">View</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useCampaignStore } from '@/stores/campaign'

const props = defineProps({
  campaign: { type: Object, required: true }
})

const router = useRouter()
const campaignStore = useCampaignStore()

const onDragStart = (e) => {
  e.dataTransfer.setData('text/plain', props.campaign.uuid)
}

const openDetails = () => {
  router.push(`/dashboard/campaigns/${props.campaign.uuid}`)
}

const formatDate = (d) => new Date(d).toLocaleDateString()

async function onDelete() {
  try {
    // Simple confirm; can be replaced with a nicer modal later
    if (confirm('Are you sure you want to delete this campaign?')) {
      await campaignStore.deleteCampaign(props.campaign.uuid)
    }
  } catch (_) {
    // handled by store/toast elsewhere
  }
}
</script>

<style scoped>
</style>


