<template>
  <div class="flex justify-between mt-4 items-center text-sm">
    <!-- Left side: showing range -->
    <div>
      Showing {{ meta.from }} - {{ meta.to }} of {{ meta.total }}
    </div>

    <!-- Right side: pagination links -->
    <div class="flex flex-wrap items-center">
      <!-- First & Prev -->
      <button 
        :disabled="meta.current_page === 1"
        @click.prevent="changePage(meta.first_page_url)"
        class="px-3 py-1 border mx-1 rounded hover:bg-gray-100"
      >
        « First
      </button>
      <button 
        :disabled="meta.current_page === 1"
        @click.prevent="changePage(meta.prev_page_url)"
        class="px-3 py-1 border mx-1 rounded hover:bg-gray-100"
      >
        Prev
      </button>

      <!-- Sliding window pages -->
      <button
        v-for="page in pagesToShow"
        :key="page"
        :class="[
          'px-3 py-1 border mx-1 rounded transition-all duration-150',
          page === meta.current_page ? 'bg-indigo-500 text-white' : 'hover:bg-gray-100'
        ]"
        @click.prevent="changePage(getPageUrl(page))"
      >
        {{ page }}
      </button>

      <!-- Next & Last -->
      <button 
        :disabled="meta.current_page === meta.last_page"
        @click.prevent="changePage(meta.next_page_url)"
        class="px-3 py-1 border mx-1 rounded hover:bg-gray-100"
      >
        Next
      </button>
      <button 
        :disabled="meta.current_page === meta.last_page"
        @click.prevent="changePage(meta.last_page_url)"
        class="px-3 py-1 border mx-1 rounded hover:bg-gray-100"
      >
        Last »
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  meta: {
    type: Object,
    required: true,
    default: () => ({
      from: 0,
      to: 0,
      total: 0,
      current_page: 1,
      last_page: 1,
      first_page_url: null,
      last_page_url: null,
      prev_page_url: null,
      next_page_url: null,
      links: []
    })
  },
})

const emits = defineEmits(['page-change'])

function changePage(url) {
  if (url) emits('page-change', url)
}

// نحدد عدد الصفحات المراد عرضها في النافذة
const maxPages = 5

const pagesToShow = computed(() => {
  const pages = []
  let start = Math.max(props.meta.current_page - Math.floor(maxPages / 2), 1)
  let end = Math.min(start + maxPages - 1, props.meta.last_page)

  // Adjust start إذا كانت النهاية قريبة من آخر صفحة
  if (end - start + 1 < maxPages) {
    start = Math.max(end - maxPages + 1, 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

// لتحويل رقم الصفحة إلى رابط
function getPageUrl(page) {
  const link = props.meta.links.find(l => l.label == page)
  return link ? link.url : null
}
</script>
