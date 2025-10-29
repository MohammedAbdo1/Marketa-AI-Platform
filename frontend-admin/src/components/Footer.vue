<template>
  <footer class="footer">
    <div class="page-container">
      <div class="text-center">
        {{ $t('all_rights') }}
        <a href="http://oriteche.com/" target="_blank">
          {{ $t('oritechsoft') }}
        </a>
        &copy;
        <!-- {{ currentYear }} -->
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
/* Footer specific styles if needed */
</style>
