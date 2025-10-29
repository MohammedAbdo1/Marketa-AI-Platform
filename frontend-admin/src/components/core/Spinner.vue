<template>
  <div class="flex flex-col items-center" :class="class">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">{{ t('common.loading') }}</span>
            </div>
    <div v-if="text" class="mt-2">{{text}}</div>
  </div>
</template>

<script setup>

const {text} = defineProps({
  text: {
    type: String,
    default: 'Please Wait...'
  },
  class: {
    type: String,
    default: 'py-8'
  }
})
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>

<style scoped>

</style>
