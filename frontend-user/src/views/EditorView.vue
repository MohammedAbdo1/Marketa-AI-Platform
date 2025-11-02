<template>
  <div class="editor-view">
    <PostEditor v-if="!loading" />
    
    <div v-else class="loading-container">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">جاري التحميل...</span>
      </div>
      <p class="mt-3">جاري تحميل المحرر...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import PostEditor from '@/components/editor/PostEditor.vue';
import { usePostEditorStore } from '@/stores/postEditor';

const route = useRoute();
const editorStore = usePostEditorStore();
const loading = ref(true);

onMounted(async () => {
  try {
    const postId = route.params.id;
    if (postId) {
      await editorStore.loadPost(postId);
    }
  } catch (error) {
    console.error('Failed to load post:', error);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.editor-view {
  width: 100%;
  height: 100vh;
  overflow: hidden;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background: #f5f5f5;
}

.loading-container p {
  color: #666;
  font-size: 1rem;
}
</style>

