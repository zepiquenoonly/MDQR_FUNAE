import { ref } from 'vue'

const toast = ref(null)

export function useToast() {
    const show = (message, type = 'success') => {
        if (toast.value) {
            toast.value.showToast(message, type)
        }
    }

    const success = (message) => show(message, 'success')
    const error = (message) => show(message, 'error')

    return {
        show,
        success,
        error,
        setToast: (toastInstance) => {
            toast.value = toastInstance
        }
    }
}