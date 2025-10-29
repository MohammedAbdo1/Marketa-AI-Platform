<template>
  <div class="flex items-center justify-between mb-3">
    <h1 class="text-3xl font-semibold">{{ t('customers.title') }}</h1>
    <router-link 
      v-if="can('create_user')" 
      to="/customers/create" 
      class="btn btn-primary"
    >
      {{ t('customers.create_customer') }}
    </router-link>
  </div>
  <CustomerTable />
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import CustomerTable from './CustomerTable.vue';

const { t } = useI18n();
const authStore = useAuthStore();
const can = computed(() => authStore.can);
</script>

