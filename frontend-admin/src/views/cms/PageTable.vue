<template>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>{{ $t('common.id') }}</th>
          <th>{{ $t('cms.page_slug') }}</th>
          <th>{{ $t('cms.title_arabic') }}</th>
          <th>{{ $t('cms.title_english') }}</th>
          <th>{{ $t('common.status') }}</th>
          <th>{{ $t('common.actions') }}</th>
        </tr>
      </thead>
      <tbody v-if="!loading">
        <tr v-for="page in pages" :key="page.id">
          <td>{{ page.id }}</td>
          <td><code>{{ page.slug }}</code></td>
          <td>{{ page.title_ar }}</td>
          <td>{{ page.title_en }}</td>
          <td>
            <span 
              :class="['badge', page.is_active ? 'bg-success' : 'bg-secondary']"
            >
              {{ page.is_active ? $t('common.active') : $t('common.inactive') }}
            </span>
          </td>
          <td>
            <button 
              @click="$emit('edit', page)"
              class="btn btn-sm btn-primary"
            >
              <i class="bx bx-edit"></i> {{ $t('common.edit') }}
            </button>
          </td>
        </tr>
      </tbody>
      <tbody v-else>
        <tr>
          <td colspan="6" class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">{{ $t('common.loading') }}</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'PageTable',
  props: {
    pages: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['edit']
}
</script>

