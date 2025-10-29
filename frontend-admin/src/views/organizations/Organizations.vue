<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ $t('organizations.title') }}
      </h1>
      <router-link
        to="/organizations/create"
        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
      >
        <i class="ti ti-plus mr-2"></i>
        {{ $t('organizations.create_organization') }}
      </router-link>
    </div>

    <OrganizationTable />
  </div>
</template>

<script setup>
import OrganizationTable from './OrganizationTable.vue'
</script>