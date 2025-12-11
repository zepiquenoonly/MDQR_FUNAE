import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Composable para sincronizar estado do menu com a URL atual
 */
export function useUrlSync() {
  const page = usePage()
  
  const currentPath = computed(() => page.url)
  const currentRoute = computed(() => page.component)
  
  /**
   * Determina qual item do menu deve estar ativo baseado na URL
   */
  const getActiveMenuItem = () => {
    const path = currentPath.value
    
    // Mapeamento de URLs para itens do menu
    const urlToMenuItem = {
      // Dashboard
      '/': 'dashboard',
      '/dashboard': 'dashboard',
      '/director/dashboard': 'dashboard',
      
      // Tracking
      '/track': 'tracking',
      '/tracking': 'tracking',
      
      // Profile
      '/profile': 'profile',
      '/profile/edit': 'profile',
      
      // Projectos
      '/projectos': 'projectos',
      '/utente/projects': 'projectos',
      '/manager/projects': 'projectos',
      '/pca/projects': 'projectos',
      
      // Submissões
      '/director/complaints-overview': 'submissions',
      
      // Técnicos/Funcionários
      '/gestor/technicians': 'technicians',
      '/director/technicians': 'technicians',
      '/manager/technicians': 'technicians',
      
      // Estatísticas
      '/gestor/estatisticas': 'estatisticas',
      '/director/estatisticas': 'estatisticas',
      '/pca/estatisticas': 'estatisticas',
      
      // Usuários (PCA)
      '/pca/usuarios': 'usuarios',
    }
    
    // Verifica match exato primeiro
    if (urlToMenuItem[path]) {
      return urlToMenuItem[path]
    }
    
    // Verifica match por prefixo
    for (const [urlPrefix, menuItem] of Object.entries(urlToMenuItem)) {
      if (path.startsWith(urlPrefix)) {
        return menuItem
      }
    }
    
    return 'dashboard' // fallback
  }
  
  /**
   * Verifica se uma rota específica está ativa
   */
  const isRouteActive = (routePatterns) => {
    const path = currentPath.value
    if (Array.isArray(routePatterns)) {
      return routePatterns.some(pattern => path.startsWith(pattern))
    }
    return path.startsWith(routePatterns)
  }
  
  return {
    currentPath,
    currentRoute,
    getActiveMenuItem,
    isRouteActive
  }
}