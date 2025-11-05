<template>
  <Teleport to="body">
    <div 
      v-if="isOpen" 
      class="context-menu-backdrop"
      @click="close"
    ></div>
    <div 
      v-if="isOpen"
      ref="menuRef"
      class="context-menu"
      :style="menuStyle"
      @click.stop
    >
      <!-- Open in new tab -->
      <button class="context-menu-item" @click="handleAction('open-new-tab')">
        <i class="bx bx-window-open"></i>
        <span>{{ $t('designs.contextMenu.openNewTab') }}</span>
      </button>

      <!-- Details -->
      <button class="context-menu-item" @click="handleAction('details')">
        <i class="bx bx-info-circle"></i>
        <span>{{ $t('designs.contextMenu.details') }}</span>
      </button>

      <!-- Rename -->
      <button class="context-menu-item" @click="handleAction('rename')">
        <i class="bx bx-pencil"></i>
        <span>{{ $t('designs.contextMenu.rename') }}</span>
      </button>

      <div class="context-menu-divider"></div>

      <!-- Make a copy -->
      <button class="context-menu-item" @click="handleAction('duplicate')">
        <i class="bx bx-copy"></i>
        <span>{{ $t('designs.contextMenu.duplicate') }}</span>
      </button>

      <!-- Download -->
      <button class="context-menu-item" @click="handleAction('download')">
        <i class="bx bx-download"></i>
        <span>{{ $t('designs.contextMenu.download') }}</span>
      </button>

      <!-- Share -->
      <button class="context-menu-item" @click="handleAction('share')">
        <i class="bx bx-share-alt"></i>
        <span>{{ $t('designs.contextMenu.share') }}</span>
      </button>

      <!-- Copy link -->
      <button class="context-menu-item" @click="handleAction('copy-link')">
        <i class="bx bx-link"></i>
        <span>{{ $t('designs.contextMenu.copyLink') }}</span>
      </button>

      <div class="context-menu-divider"></div>

      <!-- Add to campaign / Remove from favorites (conditional) -->
      <button 
        v-if="!showUnfavorite" 
        class="context-menu-item" 
        @click="handleAction('add-to-campaign')"
      >
        <i class="bx bx-plus-circle"></i>
        <span>{{ $t('designs.contextMenu.addToCampaign') }}</span>
      </button>

      <button 
        v-if="showUnfavorite" 
        class="context-menu-item" 
        @click="handleAction('unfavorite')"
      >
        <i class="bx bx-star"></i>
        <span>{{ $t('designs.contextMenu.unfavorite') }}</span>
      </button>

      <div class="context-menu-divider"></div>

      <!-- Move to trash -->
      <button class="context-menu-item danger" @click="handleAction('move-to-trash')">
        <i class="bx bx-trash"></i>
        <span>{{ $t('designs.contextMenu.moveToTrash') }}</span>
      </button>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
  design: {
    type: Object,
    required: true
  },
  showUnfavorite: {
    type: Boolean,
    default: false
  },
  position: {
    type: Object,
    default: () => ({ x: 0, y: 0 })
  }
})

const emit = defineEmits(['action', 'close'])

const isOpen = ref(true)
const menuRef = ref(null)

const menuStyle = computed(() => {
  return {
    top: `${props.position.y}px`,
    left: `${props.position.x}px`
  }
})

const handleAction = (action) => {
  emit('action', { action, design: props.design })
  close()
}

const close = () => {
  isOpen.value = false
  emit('close')
}

const handleClickOutside = (event) => {
  if (menuRef.value && !menuRef.value.contains(event.target)) {
    close()
  }
}

onMounted(() => {
  nextTick(() => {
    document.addEventListener('click', handleClickOutside)
    document.addEventListener('contextmenu', handleClickOutside)
  })
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('contextmenu', handleClickOutside)
})
</script>

<style scoped>
.context-menu-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 999;
}

.context-menu {
  position: fixed;
  background: var(--color-bg-primary);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-dropdown);
  min-width: 220px;
  padding: var(--space-2);
  z-index: 1000;
  animation: fadeInDown var(--duration-fast) var(--ease-out);
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.context-menu-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-2) var(--space-3);
  border: none;
  background: transparent;
  color: var(--color-text-primary);
  font-size: var(--text-sm);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition-fast);
  text-align: start;
}

.context-menu-item:hover {
  background: var(--color-bg-hover);
}

.context-menu-item i {
  font-size: 18px;
  flex-shrink: 0;
}

.context-menu-item span {
  flex: 1;
}

.context-menu-item.danger {
  color: var(--color-error);
}

.context-menu-item.danger:hover {
  background: var(--color-error-bg);
  color: var(--color-error);
}

.context-menu-divider {
  height: 1px;
  background: var(--color-border-light);
  margin: var(--space-2) 0;
}

/* RTL Support */
[dir="rtl"] .context-menu-item {
  text-align: right;
}
</style>

