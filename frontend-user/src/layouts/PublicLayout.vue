<template>
  <div class="public-layout">
    <PublicHeader />
    
    <main class="main-content">
      <router-view />
    </main>
    
    <PublicFooter />
  </div>
</template>

<script setup>
import PublicHeader from '@/components/public/PublicHeader.vue'
import PublicFooter from '@/components/public/PublicFooter.vue'
</script>

<style scoped>
.public-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--color-bg-primary);
}

.main-content {
  flex: 1;
}
</style>

