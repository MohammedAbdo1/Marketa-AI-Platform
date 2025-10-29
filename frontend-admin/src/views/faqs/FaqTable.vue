<template>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>{{ $t('common.id') }}</th>
          <th>{{ $t('faqs.category_arabic') }}</th>
          <th>{{ $t('faqs.question_arabic') }}</th>
          <th>{{ $t('common.sort_order') }}</th>
          <th>{{ $t('common.status') }}</th>
          <th>{{ $t('common.actions') }}</th>
        </tr>
      </thead>
      <tbody v-if="!loading">
        <tr v-for="faq in faqs" :key="faq.id">
          <td>{{ faq.id }}</td>
          <td>{{ faq.category_ar || '-' }}</td>
          <td>{{ faq.question_ar }}</td>
          <td>{{ faq.sort_order }}</td>
          <td>
            <span 
              :class="['badge', faq.is_active ? 'bg-success' : 'bg-secondary']"
            >
              {{ faq.is_active ? $t('common.active') : $t('common.inactive') }}
            </span>
          </td>
          <td>
            <button 
              @click="$emit('edit', faq)"
              class="btn btn-sm btn-primary me-1"
            >
              <i class="bx bx-edit"></i>
            </button>
            <button 
              @click="$emit('delete', faq.id)"
              class="btn btn-sm btn-danger"
            >
              <i class="bx bx-trash"></i>
            </button>
          </td>
        </tr>
      </tbody>
      <tbody v-else>
        <tr>
          <td colspan="6" class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'FaqTable',
  props: {
    faqs: {
      type: Array,
      default: () => []
    },
    loading: Boolean
  },
  emits: ['edit', 'delete']
}
</script>

