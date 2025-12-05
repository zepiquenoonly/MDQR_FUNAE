// Re-exportar do utils/routes.js para compatibilidade
// Este arquivo mantém compatibilidade com código existente
import routes, { buildRoute } from './utils/routes'

// Manter window.route para compatibilidade
if (typeof window !== 'undefined') {
  window.route = buildRoute
}

export default routes
export { buildRoute }
