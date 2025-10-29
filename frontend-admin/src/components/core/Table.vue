<template>
  <div class="table-container">
    <!-- Table Header with Search and Actions -->
    <div class="table-header" v-if="showHeader">
      <div class="table-header-left">
        <slot name="header-left">
          <h2 v-if="title" class="table-title">{{ title }}</h2>
        </slot>
      </div>
      
      <div class="table-header-right">
        <slot name="header-right">
          <div class="table-actions" v-if="searchable || showRefresh">
            <input
              v-if="searchable"
              v-model="searchQuery"
              type="text"
              :placeholder="searchPlaceholder"
              class="table-search"
              @input="handleSearch"
            />
            <Button
              v-if="showRefresh"
              variant="outline-secondary"
              size="sm"
              icon="refresh"
              @click="$emit('refresh')"
              :loading="loading"
            />
          </div>
        </slot>
      </div>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
      <table class="table">
        <thead v-if="showHeader">
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              :class="getHeaderClass(column)"
              @click="handleSort(column)"
            >
              <div class="header-content">
                <span>{{ column.label }}</span>
                <svg
                  v-if="column.sortable && sortKey === column.key"
                  class="sort-icon"
                  :class="{ 'sort-desc': sortDirection === 'desc' }"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
              </div>
            </th>
            <th v-if="hasActions" class="table-header-actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading && !data.length" class="table-loading">
            <td :colspan="columns.length + (hasActions ? 1 : 0)" class="text-center py-8">
              <div class="loading-spinner">
                <div class="spinner"></div>
                <span>Loading...</span>
              </div>
            </td>
          </tr>
          
          <tr v-else-if="!data.length && !loading" class="table-empty">
            <td :colspan="columns.length + (hasActions ? 1 : 0)" class="text-center py-8">
              <div class="empty-state">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <span>{{ emptyText }}</span>
              </div>
            </td>
          </tr>
          
          <tr v-else v-for="(row, index) in paginatedData" :key="getRowKey(row, index)" class="table-row">
            <td
              v-for="column in columns"
              :key="column.key"
              :class="getCellClass(column)"
            >
              <slot
                :name="`cell-${column.key}`"
                :row="row"
                :value="getCellValue(row, column)"
                :index="index"
              >
                <span v-if="column.formatter">
                  {{ column.formatter(getCellValue(row, column), row, column) }}
                </span>
                <span v-else>{{ getCellValue(row, column) }}</span>
              </slot>
            </td>
            <td v-if="hasActions" class="table-cell-actions">
              <slot name="actions" :row="row" :index="index">
                <div class="action-buttons">
                  <Button
                    variant="outline-primary"
                    size="sm"
                    icon="edit"
                    @click="$emit('edit', row, index)"
                  />
                  <Button
                    variant="outline-danger"
                    size="sm"
                    icon="delete"
                    @click="$emit('delete', row, index)"
                  />
                </div>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="table-footer" v-if="paginated && totalPages > 1">
      <div class="pagination-info">
        Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, totalItems) }} of {{ totalItems }} entries
      </div>
      
      <div class="pagination-controls">
        <Button
          variant="outline-secondary"
          size="sm"
          :disabled="currentPage === 1"
          @click="goToPage(currentPage - 1)"
        >
          Previous
        </Button>
        
        <div class="pagination-pages">
          <button
            v-for="page in visiblePages"
            :key="page"
            :class="['pagination-page', { 'active': page === currentPage }]"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
        </div>
        
        <Button
          variant="outline-secondary"
          size="sm"
          :disabled="currentPage === totalPages"
          @click="goToPage(currentPage + 1)"
        >
          Next
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Button from './Button.vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  columns: {
    type: Array,
    required: true
  },
  title: {
    type: String,
    default: ''
  },
  loading: {
    type: Boolean,
    default: false
  },
  searchable: {
    type: Boolean,
    default: false
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  showRefresh: {
    type: Boolean,
    default: true
  },
  emptyText: {
    type: String,
    default: 'No data available'
  },
  paginated: {
    type: Boolean,
    default: true
  },
  pageSize: {
    type: Number,
    default: 10
  },
  rowKey: {
    type: String,
    default: 'id'
  },
  striped: {
    type: Boolean,
    default: true
  },
  hoverable: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits([
  'search', 'refresh', 'edit', 'delete', 'sort', 'page-change'
])

const searchQuery = ref('')
const currentPage = ref(1)
const sortKey = ref('')
const sortDirection = ref('asc')

const hasActions = computed(() => {
  return !!$slots.actions
})

const filteredData = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return props.data
  }
  
  return props.data.filter(row => {
    return props.columns.some(column => {
      const value = getCellValue(row, column)
      return String(value).toLowerCase().includes(searchQuery.value.toLowerCase())
    })
  })
})

const sortedData = computed(() => {
  if (!sortKey.value) {
    return filteredData.value
  }
  
  return [...filteredData.value].sort((a, b) => {
    const aValue = getCellValue(a, { key: sortKey.value })
    const bValue = getCellValue(b, { key: sortKey.value })
    
    if (aValue < bValue) return sortDirection.value === 'asc' ? -1 : 1
    if (aValue > bValue) return sortDirection.value === 'asc' ? 1 : -1
    return 0
  })
})

const totalItems = computed(() => sortedData.value.length)
const totalPages = computed(() => Math.ceil(totalItems.value / props.pageSize))

const paginatedData = computed(() => {
  if (!props.paginated) {
    return sortedData.value
  }
  
  const start = (currentPage.value - 1) * props.pageSize
  const end = start + props.pageSize
  return sortedData.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  
  let start = Math.max(1, current - 2)
  let end = Math.min(total, current + 2)
  
  if (end - start < 4) {
    if (start === 1) {
      end = Math.min(total, start + 4)
    } else {
      start = Math.max(1, end - 4)
    }
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

function getCellValue(row, column) {
  const keys = column.key.split('.')
  let value = row
  
  for (const key of keys) {
    value = value?.[key]
  }
  
  return value ?? ''
}

function getRowKey(row, index) {
  return row[props.rowKey] || index
}

function getHeaderClass(column) {
  const classes = ['table-header-cell']
  
  if (column.sortable) {
    classes.push('sortable')
  }
  
  if (column.align) {
    classes.push(`text-${column.align}`)
  }
  
  return classes.join(' ')
}

function getCellClass(column) {
  const classes = ['table-cell']
  
  if (column.align) {
    classes.push(`text-${column.align}`)
  }
  
  return classes.join(' ')
}

function handleSearch() {
  emit('search', searchQuery.value)
  currentPage.value = 1
}

function handleSort(column) {
  if (!column.sortable) return
  
  if (sortKey.value === column.key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = column.key
    sortDirection.value = 'asc'
  }
  
  emit('sort', { key: sortKey.value, direction: sortDirection.value })
  currentPage.value = 1
}

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    emit('page-change', page)
  }
}

// Reset pagination when data changes
watch(() => props.data, () => {
  currentPage.value = 1
})
</script>

<style scoped>
.table-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.table-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
}

.table-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.table-search {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  width: 200px;
}

.table-search:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.table-wrapper {
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table-header-cell {
  padding: 0.75rem 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.table-header-cell.sortable {
  cursor: pointer;
  user-select: none;
}

.table-header-cell.sortable:hover {
  background-color: #f3f4f6;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sort-icon {
  width: 1rem;
  height: 1rem;
  transition: transform 0.2s;
}

.sort-icon.sort-desc {
  transform: rotate(180deg);
}

.table-row {
  border-bottom: 1px solid #e5e7eb;
  transition: background-color 0.2s;
}

.table-row:hover {
  background-color: #f9fafb;
}

.table-row:nth-child(even) {
  background-color: #fafafa;
}

.table-row:nth-child(even):hover {
  background-color: #f3f4f6;
}

.table-cell {
  padding: 0.75rem 1rem;
  color: #374151;
}

.table-header-actions,
.table-cell-actions {
  width: 120px;
  text-align: center;
}

.action-buttons {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
}

.table-loading,
.table-empty {
  text-align: center;
}

.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: #6b7280;
}

.spinner {
  width: 2rem;
  height: 2rem;
  border: 2px solid #e5e7eb;
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: #6b7280;
}

.empty-icon {
  width: 3rem;
  height: 3rem;
}

.table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.pagination-info {
  font-size: 0.875rem;
  color: #6b7280;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-pages {
  display: flex;
  gap: 0.25rem;
}

.pagination-page {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  background: white;
  color: #374151;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s;
}

.pagination-page:hover {
  background-color: #f3f4f6;
}

.pagination-page.active {
  background-color: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 768px) {
  .table-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .table-actions {
    justify-content: space-between;
  }
  
  .table-search {
    width: 100%;
  }
  
  .table-footer {
    flex-direction: column;
    gap: 1rem;
    align-items: center;
  }
  
  .pagination-controls {
    flex-wrap: wrap;
    justify-content: center;
  }
}
</style>
