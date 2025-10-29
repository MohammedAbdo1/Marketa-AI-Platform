<template>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>{{ $t('common.id') }}</th>
          <th>{{ $t('testimonials.name_arabic') }}</th>
          <th>{{ $t('testimonials.company_arabic') }}</th>
          <th>{{ $t('testimonials.rating') }}</th>
          <th>{{ $t('testimonials.is_featured') }}</th>
          <th>{{ $t('common.status') }}</th>
          <th>{{ $t('common.actions') }}</th>
        </tr>
      </thead>
      <tbody v-if="!loading">
        <tr v-for="testimonial in testimonials" :key="testimonial.id">
          <td>{{ testimonial.id }}</td>
          <td>{{ testimonial.name_ar }}</td>
          <td>{{ testimonial.company_ar }}</td>
          <td>
            <span class="text-warning">
              <i class="bx bxs-star" v-for="i in testimonial.rating" :key="i"></i>
            </span>
          </td>
          <td>
            <span 
              :class="['badge', testimonial.is_featured ? 'bg-info' : 'bg-secondary']"
            >
              {{ testimonial.is_featured ? $t('common.yes') : $t('common.no') }}
            </span>
          </td>
          <td>
            <span 
              :class="['badge', testimonial.is_active ? 'bg-success' : 'bg-secondary']"
            >
              {{ testimonial.is_active ? $t('common.active') : $t('common.inactive') }}
            </span>
          </td>
          <td>
            <button 
              @click="$emit('edit', testimonial)"
              class="btn btn-sm btn-primary me-1"
            >
              <i class="bx bx-edit"></i>
            </button>
            <button 
              @click="$emit('delete', testimonial.id)"
              class="btn btn-sm btn-danger"
            >
              <i class="bx bx-trash"></i>
            </button>
          </td>
        </tr>
      </tbody>
      <tbody v-else>
        <tr>
          <td colspan="7" class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'TestimonialTable',
  props: {
    testimonials: {
      type: Array,
      default: () => []
    },
    loading: Boolean
  },
  emits: ['edit', 'delete']
}
</script>

