<template>
  <div class="flex items-center justify-between mb-3">
    <h1 class="text-3xl font-semibold">{{ t('users.title') }}</h1>
    <router-link 
      v-if="can('create_user')" 
      to="/users/create" 
      class="btn btn-primary"
    >
      {{ t('users.create_user') }}
    </router-link>
  </div>
  <UserTable />
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import UserTable from './UserTable.vue';

const { t } = useI18n();
const authStore = useAuthStore();
const can = computed(() => authStore.can);
</script>
