import { router } from '@inertiajs/vue3'
import { getDashboardRouteByRole, getDashboardUrlByRole, buildRoute } from '@/utils/routes'
import { useAuth } from '@/Composables/useAuth'

/**
 * Composable para gerenciamento de navegação
 * 
 * @param {Object} options - Opções de configuração
 * @param {Object} options.user - Usuário opcional para detectar role
 * @param {string} options.role - Role opcional (se não fornecido, detecta do usuário)
 * @returns {Object} Objeto com funções de navegação
 */
export function useNavigation(options = {}) {
  const { role } = useAuth(options)

  /**
   * Obtém a rota do dashboard baseado no role do usuário
   * @returns {string} Nome da rota do dashboard
   */
  const getDashboardRoute = () => {
    return getDashboardRouteByRole(role.value)
  }

  /**
   * Obtém a URL do dashboard baseado no role do usuário
   * @returns {string} URL do dashboard
   */
  const getDashboardUrl = () => {
    return getDashboardUrlByRole(role.value)
  }

  /**
   * Navega para o dashboard apropriado baseado no role
   * @param {Object} options - Opções de navegação do Inertia
   */
  const navigateToDashboard = (options = {}) => {
    const url = getDashboardUrl()
    router.visit(url, {
      preserveState: true,
      preserveScroll: true,
      ...options
    })
  }

  /**
   * Navega para uma rota específica
   * @param {string} routeName - Nome da rota
   * @param {Object} params - Parâmetros da rota
   * @param {Object} options - Opções de navegação do Inertia
   */
  const navigateTo = (routeName, params = {}, options = {}) => {
    const url = buildRoute(routeName, params)
    router.visit(url, {
      preserveState: true,
      preserveScroll: true,
      ...options
    })
  }

  /**
   * Navega para o perfil do usuário
   */
  const navigateToProfile = () => {
    navigateTo('profile.edit')
  }

  /**
   * Navega para a página de tracking
   */
  const navigateToTracking = () => {
    navigateTo('track')
  }

  /**
   * Navega para login
   */
  const navigateToLogin = () => {
    navigateTo('login')
  }

  /**
   * Navega para registro
   */
  const navigateToRegister = () => {
    navigateTo('register')
  }

  /**
   * Faz logout do usuário
   */
  const logout = () => {
    router.post('/logout')
  }

  // Funções de navegação para o Admin
  const navigateToAdminUsers = () => {
    navigateTo('admin.users.index')
  }

  const navigateToAdminProjects = () => {
    // Por enquanto, pode ser uma rota Inertia para uma página de listagem de projetos
    // ou um link direto para a API se a UI for separada.
    // Para este caso, vamos assumir uma rota Inertia 'admin.projects.index'
    navigateTo('admin.projects.index')
  }

  const navigateToAdminDepartments = () => {
    navigateTo('admin.departments.index')
  }

  const navigateToAdminRoles = () => {
    // Esta rota ainda não existe, é um placeholder para futura implementação
    // ou pode ser uma rota para uma página de configurações gerais
    navigateTo('admin.roles.index') // Placeholder
  }

  return {
    role,
    getDashboardRoute,
    getDashboardUrl,
    navigateToDashboard,
    navigateTo,
    navigateToProfile,
    navigateToTracking,
    navigateToLogin,
    navigateToRegister,
    logout,
    navigateToAdminUsers,
    navigateToAdminProjects,
    navigateToAdminDepartments,
    navigateToAdminRoles,
  }
}

/**
 * Composable simplificado apenas para navegação de dashboard
 * @param {Object} user - Usuário opcional
 * @returns {Object} Objeto com funções de navegação de dashboard
 */
export function useRoleNavigation(user = null) {
  return useNavigation({ user })
}

