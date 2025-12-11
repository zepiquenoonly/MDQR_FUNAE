import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { detectRole, mapRoleName, getRoleLabel, hasRole, getUserRoles } from '@/utils/roles'

/**
 * Composable para gerenciamento de autenticação e roles
 * 
 * @param {Object} options - Opções de configuração
 * @param {Object} options.user - Usuário opcional (se não fornecido, usa usePage)
 * @param {string} options.role - Role opcional (se não fornecido, detecta do usuário)
 * @returns {Object} Objeto com funções e computed properties relacionadas a auth
 */
export function useAuth(options = {}) {
  const page = usePage()
  
  // Obter usuário de forma segura
  const user = computed(() => {
    // Priorizar o user do Inertia props
    return page.props.auth?.user || options.user || null
  })

  // Detectar role do usuário - IMPORTANTE: Usar o user atualizado
  const role = computed(() => {
    // Se um role foi fornecido nas opções, usá-lo
    if (options.role) {
      return mapRoleName(options.role)
    }
    
    // Detectar role do usuário atual
    if (user.value) {
      const detected = detectRole(user.value)
      // console.log('Detected role in useAuth:', detected);
      return detected
    }
    
    // Fallback
    return 'utente'
  })


  // Label do role para exibição
  const roleLabel = computed(() => {
    return getRoleLabel(role.value)
  })

  // Verificar se usuário está autenticado
  const isAuthenticated = computed(() => {
    return !!user.value
  })

  // Verificar se usuário tem um role específico
  const checkRole = (targetRole) => {
    if (!user.value) return false
    return hasRole(user.value, targetRole)
  }

  // Obter todos os roles do usuário
  const roles = computed(() => {
    if (!user.value) return ['utente']
    return getUserRoles(user.value)
  })

  return {
    user,
    role,
    roleLabel,
    isAuthenticated,
    checkRole,
    roles
  }
}

/**
 * Composable simplificado apenas para obter o usuário
 * @returns {Object} Objeto com user computed
 */
export function useUser() {
  const page = usePage()
  
  const user = computed(() => {
    return page.props.auth?.user || null
  })

  return {
    user
  }
}

/**
 * Composable simplificado apenas para obter o role
 * @param {Object} user - Usuário opcional
 * @returns {Object} Objeto com role computed
 */
export function useRole(user = null) {
  const { role } = useAuth({ user })
  return { role }
}

