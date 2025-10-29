<!-- components/ConfirmDialog.vue -->
<template>
    <div
      v-if="show"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-xl shadow-lg p-6 w-[400px] text-center">
        <h3 class="text-lg font-semibold mb-2">{{ title }}</h3>
        <p class="text-gray-600 mb-6">{{ message }}</p>
        <div class="flex justify-center gap-3">
          <button
            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
            @click="emit('onCancelDialog')"
          >
            {{ cancelText }}
          </button>
          <button
            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            @click="emit('onConfirmDialog')"
          >
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  const props = defineProps({
    show: Boolean,
    title: {
      type: String,
      default: "هل أنت متأكد؟",
    },
    message: {
      type: String,
      default: "لن يمكنك التراجع بعد هذه العملية!",
    },
    confirmText: {
      type: String,
      default: "تأكيد",
    },
    cancelText: {
      type: String,
      default: "إلغاء",
    },
  })
  
  const emit = defineEmits(["onConfirmDialog", "onCancelDialog"])
  </script>
  