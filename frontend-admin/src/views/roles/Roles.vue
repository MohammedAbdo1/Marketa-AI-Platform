<template>
  <div class="flex items-center justify-between mb-3">
    <h1 class="text-3xl font-semibold">{{ t('roles.title') }}</h1>
    <router-link 
      v-if="can('add_role')" 
      to="/roles/create" 
      class="btn btn-primary"
    >
      {{ t('roles.create_role') }}
    </router-link>
  </div>
  <RoleTable />
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import RoleTable from './RoleTable.vue';

const { t } = useI18n();
const authStore = useAuthStore();
const can = computed(() => authStore.can);
</script>

