/**
 * Utilitários para gerenciamento de rotas
 */

// Mapeamento de rotas nomeadas
const routes = {
  'technician.dashboard': '/tecnico/dashboard',
  'manager.dashboard': '/gestor/dashboard',
  'pca.dashboard': '/pca/dashboard',
  'admin.dashboard': '/admin/dashboard',
  'user.dashboard': '/utente/dashboard',
  'home': '/',
  'technician.grievances.show': '/technician/grievances/{grievance}',
  'profile.edit': '/profile',
  'logout': '/logout',
  'track': '/track',
  'grievance.track.search': '/track',
  'login': '/login',
  'register': '/register',
  
  // Admin Routes
  'admin.departments.index': '/admin/departments',
  'admin.projects.index': '/admin/projects',
  'admin.users.index': '/admin/users',
  'admin.settings': '/profile',
}

// Mapeamento de roles para rotas de dashboard
const ROLE_DASHBOARD_ROUTES = {
  'technician': 'technician.dashboard',
  'manager': 'manager.dashboard',
  'pca': 'pca.dashboard',
  'admin': 'admin.dashboard',
  'utente': 'user.dashboard'
}

/**
 * Constrói uma URL a partir de um nome de rota e parâmetros
 * @param {string} name - Nome da rota
 * @param {Object} params - Parâmetros da rota e query string
 * @returns {string} URL construída
 */
export function buildRoute(name, params = {}) {
  let url = routes[name] || '/'

  if (!params || Object.keys(params).length === 0) {
    return url
  }

  // Substituir parâmetros de rota (ex: {grievance})
  Object.keys(params).forEach(key => {
    if (typeof params[key] !== 'object' && params[key] !== null && params[key] !== undefined) {
      url = url.replace(`{${key}}`, params[key])
    }
  })

  // Adicionar query parameters para parâmetros não usados na rota
  const queryParams = Object.keys(params).filter(key => !url.includes(`{${key}}`))
  if (queryParams.length > 0) {
    const queryString = new URLSearchParams()
    queryParams.forEach(key => {
      if (params[key] !== null && params[key] !== undefined && typeof params[key] !== 'object') {
        queryString.append(key, params[key])
      }
    })
    if (queryString.toString()) {
      url += (url.includes('?') ? '&' : '?') + queryString.toString()
    }
  }

  return url
}

/**
 * Obtém a rota do dashboard baseado no role
 * @param {string} role - Role normalizado
 * @returns {string} Nome da rota do dashboard
 */
export function getDashboardRouteByRole(role) {
  return ROLE_DASHBOARD_ROUTES[role] || ROLE_DASHBOARD_ROUTES.utente
}

/**
 * Obtém a URL do dashboard baseado no role
 * @param {string} role - Role normalizado
 * @returns {string} URL do dashboard
 */
export function getDashboardUrlByRole(role) {
  const routeName = getDashboardRouteByRole(role)
  return routes[routeName] || routes['user.dashboard']
}

/**
 * Helper global para compatibilidade com código existente
 * Mantém a função window.route para não quebrar código existente
 */
if (typeof window !== 'undefined') {
  window.route = buildRoute
}

export default routes

