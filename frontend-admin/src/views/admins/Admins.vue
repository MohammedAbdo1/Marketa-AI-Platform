<template>
  <div class="flex items-center justify-between mb-3">
    <h1 class="text-3xl font-semibold">{{ t('admins.title') }}</h1>
    <router-link 
      v-if="can('create_user')" 
      to="/admins/create" 
      class="btn btn-primary"
    >
      {{ t('admins.create_admin') }}
    </router-link>
  </div>
  <AdminTable />
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import AdminTable from './AdminTable.vue';

const { t } = useI18n();
const authStore = useAuthStore();
const can = computed(() => authStore.can);
</script>

