import { ref } from 'vue'

export function useDropdownManager() {
    const openDropdownId = ref(null)

    const openDropdown = (id) => {
        openDropdownId.value = id
    }

    const closeDropdown = () => {
        openDropdownId.value = null
    }

    const isDropdownOpen = (id) => {
        return openDropdownId.value === id
    }

    const toggleDropdown = (id) => {
        if (isDropdownOpen(id)) {
            closeDropdown()
        } else {
            openDropdown(id)
        }
    }

    return {
        openDropdownId,
        openDropdown,
        closeDropdown,
        isDropdownOpen,
        toggleDropdown
    }
}