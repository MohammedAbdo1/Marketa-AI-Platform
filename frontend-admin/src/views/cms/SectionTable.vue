<template>
  <div class="table-responsive">
    <!-- Debug Info -->
    <div class="alert alert-info mb-3" v-if="!loading">
      <small>Sections found: {{ sections.length }}</small>
    </div>
    
    <table class="table table-hover">
      <thead>
        <tr>
          <th>{{ $t('common.id') }}</th>
          <th>{{ $t('cms.page_slug') }}</th>
          <th>{{ $t('cms.section_type') }}</th>
          <th>{{ $t('cms.title_arabic') }}</th>
          <th>{{ $t('common.sort_order') }}</th>
          <th>{{ $t('common.status') }}</th>
          <th>{{ $t('common.actions') }}</th>
        </tr>
      </thead>
      <tbody v-if="!loading && sections.length > 0">
        <template v-for="section in sections" :key="section.id">
          <tr>
            <td>{{ section.id }}</td>
            <td><code>{{ section.page?.slug }}</code></td>
            <td>
              <span class="badge bg-info">
                {{ $t(`cms.section_types.${section.section_type}`) }}
              </span>
            </td>
            <td>{{ section.title_ar }}</td>
            <td>{{ section.sort_order }}</td>
            <td>
              <span 
                :class="['badge', section.is_active ? 'bg-success' : 'bg-secondary']"
              >
                {{ section.is_active ? $t('common.active') : $t('common.inactive') }}
              </span>
            </td>
            <td>
              <button 
                @click="$emit('add-content', section.id)"
                class="btn btn-sm btn-success me-1"
                :title="$t('cms.create_content')"
              >
                <i class="bx bx-plus"></i>
              </button>
              <button 
                @click="$emit('edit', section)"
                class="btn btn-sm btn-primary me-1"
              >
                <i class="bx bx-edit"></i>
              </button>
              <button 
                @click="$emit('delete', section.id)"
                class="btn btn-sm btn-danger"
              >
                <i class="bx bx-trash"></i>
              </button>
            </td>
          </tr>
          
          <!-- Content rows for this section -->
          <tr v-if="section.content && section.content.length > 0">
            <td colspan="7" class="p-0">
              <div class="bg-light p-3">
                <h6 class="mb-2">{{ $t('cms.content') }}:</h6>
                <table class="table table-sm mb-0">
                  <thead>
                    <tr>
                      <th width="50">ID</th>
                      <th>{{ $t('cms.content_type') }}</th>
                      <th>{{ $t('cms.title_arabic') }}</th>
                      <th width="100">{{ $t('common.actions') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="content in section.content" :key="content.id">
                      <td>{{ content.id }}</td>
                      <td>
                        <span class="badge bg-secondary">
                          {{ $t(`cms.content_types.${content.content_type}`) }}
                        </span>
                      </td>
                      <td>{{ content.title_ar || content.description_ar?.substring(0, 50) }}</td>
                      <td>
                        <button 
                          @click="$emit('edit-content', content)"
                          class="btn btn-sm btn-outline-primary me-1"
                        >
                          <i class="bx bx-edit"></i>
                        </button>
                        <button 
                          @click="$emit('delete-content', content.id)"
                          class="btn btn-sm btn-outline-danger"
                        >
                          <i class="bx bx-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
      <tbody v-else-if="loading">
        <tr>
          <td colspan="7" class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
          </td>
        </tr>
      </tbody>
      <tbody v-else>
        <tr>
          <td colspan="7" class="text-center text-muted py-4">
            {{ $t('common.no_data') }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'SectionTable',
  props: {
    sections: {
      type: Array,
      default: () => []
    },
    loading: Boolean
  },
  emits: ['edit', 'delete', 'add-content', 'edit-content', 'delete-content']
}
</script>

