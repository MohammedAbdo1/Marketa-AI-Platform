<template>
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex align-items-center justify-content-between">
        <h4 class="mb-0">{{ title }}</h4>
        <button 
          v-if="buttonText"
          @click="$emit('button-click')"
          class="btn btn-primary"
        >
          <i class="bx bx-plus me-1"></i>
          {{ buttonText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PageHeader',
  props: {
    title: {
      type: String,
      required: true
    },
    buttonText: {
      type: String,
      default: null
    }
  },
  emits: ['button-click']
}
</script>

