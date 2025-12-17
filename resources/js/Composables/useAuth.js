// useAuth.js
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import {
    detectRole,
    mapRoleName,
    getRoleLabel,
    hasRole,
    getUserRoles
} from '@/utils/roles'

/**
 * Composable para gerenciamento de autenticação e roles
 * Versão robusta com múltiplos fallbacks para garantir que o role esteja sempre disponível
 *
 * @param {Object} options - Opções de configuração
 * @param {Object} options.user - Usuário opcional (se não fornecido, usa usePage)
 * @param {string} options.role - Role opcional (se não fornecido, detecta do usuário)
 * @param {boolean} options.debug - Ativar logs de debug
 * @returns {Object} Objeto com funções e computed properties relacionadas a auth
 */
export function useAuth (options = {}) {
    const page = usePage()

    // Configuração de debug
    const debug = options.debug || false

    // Helper para log de debug
    const logDebug = (message, data = null) => {
        if (debug) {
            console.group('🔍 useAuth Debug')
            console.log(message)
            if (data) console.log(data)
            console.groupEnd()
        }
    }

    // Obter usuário de forma segura com múltiplos fallbacks
    const user = computed(() => {
        const authUser = page.props.auth?.user
        const propsUser = options.user

        // Prioridade 1: auth.user do Inertia (mais confiável)
        if (authUser) {
            logDebug('Usuário encontrado no page.props.auth.user:', authUser)
            return authUser
        }

        // Prioridade 2: user das opções
        if (propsUser) {
            logDebug('Usuário encontrado nas opções:', propsUser)
            return propsUser
        }

        // Prioridade 3: Tentar extrair de page.props.user
        if (page.props.user) {
            logDebug('Usuário encontrado no page.props.user:', page.props.user)
            return page.props.user
        }

        logDebug('Nenhum usuário encontrado', {
            'page.props.auth': page.props.auth,
            'options.user': options.user,
            'page.props.user': page.props.user
        })

        return null
    })

    // Detectar role do usuário com múltiplos fallbacks
    const role = computed(() => {
        logDebug('Iniciando detecção de role...', {
            'options.role': options.role,
            'page.props.auth?.user?.role': page.props.auth?.user?.role,
            'page.props.role': page.props.role,
            'user.value': user.value
        })

        // Prioridade 1: Role fornecido explicitamente nas opções
        if (options.role) {
            const mappedRole = mapRoleName(options.role)
            logDebug(
                `Role fornecido nas opções: "${options.role}" -> mapeado para: "${mappedRole}"`
            )
            return mappedRole
        }

        // Prioridade 2: Role do usuário do Inertia (auth.user.role)
        if (page.props.auth?.user?.role) {
            const mappedRole = mapRoleName(page.props.auth.user.role)
            logDebug(
                `Role do auth.user: "${page.props.auth.user.role}" -> mapeado para: "${mappedRole}"`
            )
            return mappedRole
        }

        // Prioridade 3: Role direto nos props (page.props.role)
        if (page.props.role) {
            const mappedRole = mapRoleName(page.props.role)
            logDebug(
                `Role direto nos props: "${page.props.role}" -> mapeado para: "${mappedRole}"`
            )
            return mappedRole
        }

        // Prioridade 4: Detectar do objeto usuário usando função de detecção
        if (user.value) {
            try {
                const detected = detectRole(user.value)
                logDebug(`Role detectado do objeto usuário: "${detected}"`, {
                    'user.value': user.value,
                    'user.value.roles': user.value.roles,
                    'user.value.role_names': user.value.role_names
                })
                return detected
            } catch (error) {
                logDebug('Erro ao detectar role do usuário:', error)
            }
        }

        // Prioridade 5: Verificar se há role no array de roles do usuário
        if (
            user.value?.roles &&
            Array.isArray(user.value.roles) &&
            user.value.roles.length > 0
        ) {
            const firstRole = user.value.roles[0]
            const mappedRole = mapRoleName(firstRole.name || firstRole)
            logDebug(
                `Role do array de roles: "${firstRole}" -> mapeado para: "${mappedRole}"`
            )
            return mappedRole
        }

        // Prioridade 6: Verificar se há role_name no usuário
        if (user.value?.role_name) {
            const mappedRole = mapRoleName(user.value.role_name)
            logDebug(
                `Role do role_name: "${user.value.role_name}" -> mapeado para: "${mappedRole}"`
            )
            return mappedRole
        }

        // Prioridade 7: Verificar se há primary_role no usuário
        if (user.value?.primary_role) {
            const mappedRole = mapRoleName(user.value.primary_role)
            logDebug(
                `Role do primary_role: "${user.value.primary_role}" -> mapeado para: "${mappedRole}"`
            )
            return mappedRole
        }

        // Fallback final: utente (usuário não autenticado/visitante)
        logDebug('Nenhum role encontrado, usando fallback: "utente"')
        return 'utente'
    })

    // Label do role para exibição
    const roleLabel = computed(() => {
        const label = getRoleLabel(role.value)
        logDebug(`Role label para "${role.value}": "${label}"`)
        return label
    })

    // Verificar se usuário está autenticado
    const isAuthenticated = computed(() => {
        const authenticated = !!user.value
        logDebug(`Usuário autenticado: ${authenticated}`)
        return authenticated
    })

    // Verificar se usuário tem um role específico
    const checkRole = targetRole => {
        if (!user.value) {
            logDebug(`checkRole("${targetRole}"): false (sem usuário)`)
            return false
        }

        const hasTargetRole = hasRole(user.value, targetRole)
        logDebug(`checkRole("${targetRole}"): ${hasTargetRole}`)
        return hasTargetRole
    }

    // Verificar se usuário tem pelo menos um dos roles especificados
    const checkAnyRole = targetRoles => {
        if (!user.value) {
            logDebug(
                `checkAnyRole(${JSON.stringify(
                    targetRoles
                )}): false (sem usuário)`
            )
            return false
        }

        const rolesArray = Array.isArray(targetRoles)
            ? targetRoles
            : [targetRoles]
        const hasAnyRole = rolesArray.some(targetRole =>
            hasRole(user.value, targetRole)
        )
        logDebug(`checkAnyRole(${JSON.stringify(targetRoles)}): ${hasAnyRole}`)
        return hasAnyRole
    }

    // Obter todos os roles do usuário
    const roles = computed(() => {
        if (!user.value) {
            logDebug('roles: [] (sem usuário)')
            return ['utente']
        }

        const userRoles = getUserRoles(user.value)
        logDebug(`roles do usuário: ${JSON.stringify(userRoles)}`)
        return userRoles
    })

    // Verificar permissões específicas baseadas no role
    const permissions = computed(() => {
        const currentRole = role.value

        // Definição de permissões por role
        const permissionMap = {
            admin: {
                canManageUsers: true,
                canManageProjects: true,
                canManageAllComplaints: true,
                canValidate: true,
                canExport: true,
                canViewStatistics: true,
                canEditEverything: true
            },
            director: {
                canManageUsers: true,
                canManageProjects: true,
                canManageAllComplaints: true,
                canValidate: true,
                canExport: true,
                canViewStatistics: true,
                canEditEverything: false
            },
            manager: {
                canManageUsers: false,
                canManageProjects: true,
                canManageAllComplaints: true,
                canValidate: false,
                canExport: true,
                canViewStatistics: true,
                canEditEverything: false
            },
            technician: {
                canManageUsers: false,
                canManageProjects: false,
                canManageAllComplaints: false,
                canValidate: false,
                canExport: false,
                canViewStatistics: false,
                canEditEverything: false,
                canManageAssignedTasks: true
            },
            pca: {
                canManageUsers: false,
                canManageProjects: false,
                canManageAllComplaints: false,
                canValidate: true,
                canExport: false,
                canViewStatistics: true,
                canEditEverything: false
            },
            utente: {
                canManageUsers: false,
                canManageProjects: false,
                canManageAllComplaints: false,
                canValidate: false,
                canExport: false,
                canViewStatistics: false,
                canEditEverything: false,
                canSubmitComplaints: true
            }
        }

        const perms = permissionMap[currentRole] || permissionMap.utente
        logDebug(`Permissões para role "${currentRole}":`, perms)
        return perms
    })

    // Método para debug detalhado
    const debugInfo = () => {
        console.group('🔍 useAuth - Informações de Debug')
        console.log('📄 Page Props:', page.props)
        console.log('👤 Usuário:', user.value)
        console.log('🎭 Role:', role.value)
        console.log('🏷️ Role Label:', roleLabel.value)
        console.log('🔐 Autenticado:', isAuthenticated.value)
        console.log('👥 Roles:', roles.value)
        console.log('🔑 Permissões:', permissions.value)
        console.log('⚙️ Opções:', options)
        console.groupEnd()
    }

    return {
        // Computed properties
        user,
        role,
        roleLabel,
        isAuthenticated,
        roles,
        permissions,

        // Métodos
        checkRole,
        checkAnyRole,
        debugInfo,

        // Alias para compatibilidade
        hasRole: checkRole,
        hasAnyRole: checkAnyRole,
        getPermissions: () => permissions.value,

        // Utilitários
        isAdmin: computed(() => role.value === 'admin'),
        isDirector: computed(() => role.value === 'director'),
        isManager: computed(() => role.value === 'manager'),
        isTechnician: computed(() => role.value === 'technician'),
        isPCA: computed(() => role.value === 'pca'),
        isUtente: computed(() => role.value === 'utente')
    }
}

/**
 * Composable simplificado apenas para obter o usuário
 * @param {boolean} debug - Ativar logs de debug
 * @returns {Object} Objeto com user computed
 */
export function useUser (debug = false) {
    const page = usePage()

    const user = computed(() => {
        if (debug && !page.props.auth?.user) {
            console.warn(
                '⚠️ useUser: Nenhum usuário encontrado em page.props.auth.user'
            )
            console.log('Page props:', page.props)
        }

        return page.props.auth?.user || null
    })

    return {
        user
    }
}

/**
 * Composable simplificado apenas para obter o role
 * @param {Object} user - Usuário opcional
 * @param {string} fallbackRole - Role fallback se não detectado
 * @param {boolean} debug - Ativar logs de debug
 * @returns {Object} Objeto com role computed
 */
export function useRole (user = null, fallbackRole = 'utente', debug = false) {
    const { role } = useAuth({
        user,
        debug,
        role: fallbackRole
    })

    return {
        role,
        // Métodos de conveniência
        is: targetRole => role.value === targetRole,
        isAny: targetRoles => {
            const rolesArray = Array.isArray(targetRoles)
                ? targetRoles
                : [targetRoles]
            return rolesArray.includes(role.value)
        }
    }
}

/**
 * Composable para permissões baseadas em role
 * @param {string} role - Role específico (opcional)
 * @returns {Object} Objeto com permissões
 */
export function usePermissions (specificRole = null) {
    const { role: detectedRole, permissions } = specificRole
        ? { role: { value: specificRole }, permissions: { value: {} } }
        : useAuth()

    return {
        permissions,
        can: permission => permissions.value[permission] || false,
        hasAnyPermission: permissionList => {
            const list = Array.isArray(permissionList)
                ? permissionList
                : [permissionList]
            return list.some(perm => permissions.value[perm] || false)
        },
        role: detectedRole
    }
}

/**
 * Helper para verificar acesso a rotas baseado no role
 * @param {string|Array} allowedRoles - Roles permitidos
 * @param {Object} options - Opções adicionais
 * @returns {boolean} Se o acesso é permitido
 */
export function checkRouteAccess (allowedRoles, options = {}) {
    const { role: userRole } = useRole()

    const rolesArray = Array.isArray(allowedRoles)
        ? allowedRoles
        : [allowedRoles]
    const normalizedRoles = rolesArray.map(role => mapRoleName(role))

    if (options.debug) {
        console.log('🔐 checkRouteAccess:', {
            allowedRoles,
            normalizedRoles,
            userRole: userRole.value,
            hasAccess: normalizedRoles.includes(userRole.value)
        })
    }

    return normalizedRoles.includes(userRole.value)
}

/**
 * Hook para proteger componentes baseado no role
 * @param {string|Array} requiredRoles - Roles necessários
 * @param {Object} options - Opções
 * @returns {Object} Hook com métodos de verificação
 */
export function useRoleGuard (requiredRoles, options = {}) {
    const { role: userRole, isAuthenticated } = useAuth(options)

    const rolesArray = Array.isArray(requiredRoles)
        ? requiredRoles
        : [requiredRoles]
    const normalizedRoles = rolesArray.map(role => mapRoleName(role))

    const hasRequiredRole = computed(() => {
        return normalizedRoles.includes(userRole.value)
    })

    const canAccess = computed(() => {
        return isAuthenticated.value && hasRequiredRole.value
    })

    const showFallback = computed(() => {
        return isAuthenticated.value && !hasRequiredRole.value
    })

    return {
        hasRequiredRole,
        canAccess,
        showFallback,
        userRole,
        isAuthenticated,
        // Método para renderização condicional
        renderIfAuthorized: (component, fallback = null) => {
            return canAccess.value ? component : fallback
        }
    }
}
