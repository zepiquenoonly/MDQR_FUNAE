import { useNavigation } from '@/Composables/useNavigation'

/**
 * Diretiva para lidar com navegação por clique mantendo estado do menu
 */
export const clickNavigation = {
  beforeMount(el, binding) {
    const { navigateTo } = useNavigation()
    
    el.addEventListener('click', (e) => {
      e.preventDefault()
      e.stopPropagation()
      
      const routeName = binding.value
      if (routeName) {
        navigateTo(routeName)
        
        // Emitir evento para atualizar menu (opcional)
        const event = new CustomEvent('menu-navigation', { 
          detail: { route: routeName }
        })
        window.dispatchEvent(event)
      }
    })
  }
}
