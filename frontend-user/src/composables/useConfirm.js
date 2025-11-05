import { ref } from 'vue'

const confirmState = ref({
  show: false,
  title: '',
  message: '',
  description: '',
  confirmText: '',
  cancelText: '',
  dangerMode: false,
  loading: false,
  onConfirm: null,
  onCancel: null
})

export function useConfirm() {
  const showConfirm = ({
    title,
    message,
    description = '',
    confirmText = '',
    cancelText = '',
    dangerMode = false,
    onConfirm = null,
    onCancel = null
  }) => {
    confirmState.value = {
      show: true,
      title,
      message,
      description,
      confirmText,
      cancelText,
      dangerMode,
      loading: false,
      onConfirm,
      onCancel
    }
  }

  const hideConfirm = () => {
    confirmState.value.show = false
    confirmState.value.loading = false
  }

  const handleConfirm = async () => {
    if (confirmState.value.onConfirm) {
      confirmState.value.loading = true
      try {
        await confirmState.value.onConfirm()
        hideConfirm()
      } catch (error) {
        confirmState.value.loading = false
        throw error
      }
    } else {
      hideConfirm()
    }
  }

  const handleCancel = () => {
    if (confirmState.value.onCancel) {
      confirmState.value.onCancel()
    }
    hideConfirm()
  }

  return {
    confirmState,
    showConfirm,
    hideConfirm,
    handleConfirm,
    handleCancel
  }
}

