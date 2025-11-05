import { ref } from 'vue'

const toastState = ref({
  show: false,
  message: '',
  type: 'success',
  duration: 2000
})

export function useToast() {
  const showToast = (message, type = 'success', duration = 2000) => {
    toastState.value = {
      show: true,
      message,
      type,
      duration
    }
  }

  const hideToast = () => {
    toastState.value.show = false
  }

  const success = (message, duration) => showToast(message, 'success', duration)
  const error = (message, duration) => showToast(message, 'error', duration)
  const info = (message, duration) => showToast(message, 'info', duration)

  return {
    toastState,
    showToast,
    hideToast,
    success,
    error,
    info
  }
}



