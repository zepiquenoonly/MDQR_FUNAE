/**
 * Utilitários para gerenciamento de roles e permissões
 */

// Constantes de roles normalizadas
export const ROLES = {
  TECHNICIAN: 'technician',
  MANAGER: 'manager',
  PCA: 'pca',
  UTENTE: 'utente',
  ADMIN: 'admin'
}

// Mapeamento de nomes de roles do banco para valores normalizados
const ROLE_NAME_MAP = {
  'técnico': ROLES.TECHNICIAN,
  'tecnico': ROLES.TECHNICIAN,
  'gestor': ROLES.MANAGER,
  'pca': ROLES.PCA,
  'utente': ROLES.UTENTE,
  'admin': ROLES.ADMIN
}

// Labels para exibição
const ROLE_LABELS = {
  [ROLES.TECHNICIAN]: 'Técnico',
  [ROLES.MANAGER]: 'Gestor',
  [ROLES.PCA]: 'PCA',
  [ROLES.UTENTE]: 'Utente',
  [ROLES.ADMIN]: 'Administrador'
}

/**
 * Normaliza o nome de um role para o valor padrão
 * @param {string} roleName - Nome do role (pode ser do banco ou já normalizado)
 * @returns {string} Role normalizado
 */
export function mapRoleName(roleName) {
  if (!roleName) return ROLES.UTENTE
  
  const normalized = roleName.toLowerCase().trim()
  return ROLE_NAME_MAP[normalized] || normalized || ROLES.UTENTE
}

/**
 * Detecta o role de um usuário
 * @param {Object} user - Objeto do usuário com propriedade roles
 * @returns {string} Role normalizado do usuário
 */
export function detectRole(user) {
  if (!user || !user.roles || !Array.isArray(user.roles) || user.roles.length === 0) {
    return ROLES.UTENTE
  }

  // Pega o primeiro role (assumindo que usuários têm um role principal)
  const firstRole = user.roles[0]
  const roleName = firstRole?.name || firstRole
  
  return mapRoleName(roleName)
}

/**
 * Obtém o label de exibição para um role
 * @param {string} role - Role normalizado
 * @returns {string} Label para exibição
 */
export function getRoleLabel(role) {
  return ROLE_LABELS[role] || role || 'Utente'
}

/**
 * Verifica se um usuário tem um role específico
 * @param {Object} user - Objeto do usuário
 * @param {string} role - Role a verificar
 * @returns {boolean}
 */
export function hasRole(user, role) {
  const userRole = detectRole(user)
  return userRole === mapRoleName(role)
}

/**
 * Obtém todos os roles de um usuário normalizados
 * @param {Object} user - Objeto do usuário
 * @returns {string[]} Array de roles normalizados
 */
export function getUserRoles(user) {
  if (!user || !user.roles || !Array.isArray(user.roles)) {
    return [ROLES.UTENTE]
  }

  return user.roles.map(role => {
    const roleName = role?.name || role
    return mapRoleName(roleName)
  })
}

