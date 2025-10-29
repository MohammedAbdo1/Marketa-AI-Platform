<script setup>
import { RouterView } from 'vue-router'
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'


const route = useRoute()
const { locale } = useI18n()

const layout = computed(() => {
  return route.meta.layout || 'app'
})

const direction = computed(() => locale.value === 'ar' ? 'rtl' : 'ltr')
</script>

<template>
  <div :dir="direction">
      <RouterView />
  </div>
</template>
