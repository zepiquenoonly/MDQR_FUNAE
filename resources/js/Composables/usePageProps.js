import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Composable para acessar props de forma segura
 * Elimina a repetição do padrão: page.props.X || props.X || defaultValue
 * 
 * @param {Object} props - Props do componente (opcional)
 * @returns {Function} Função para obter props seguras
 */
export function usePageProps(props = {}) {
  const page = usePage()

  /**
   * Obtém uma prop de forma segura, verificando page.props primeiro, depois props, depois defaultValue
   * @param {string} key - Nome da prop
   * @param {*} defaultValue - Valor padrão se não encontrado
   * @returns {import('vue').ComputedRef} Computed ref com o valor seguro
   */
  const getSafeProp = (key, defaultValue = null) => {
    return computed(() => {
      // Primeiro tenta page.props
      if (page.props[key] !== undefined && page.props[key] !== null) {
        return page.props[key]
      }
      
      // Depois tenta props do componente
      if (props[key] !== undefined && props[key] !== null) {
        return props[key]
      }
      
      // Por último usa o valor padrão
      return defaultValue
    })
  }

  /**
   * Obtém múltiplas props de forma segura
   * @param {Object} propMap - Objeto com mapeamento { propName: defaultValue }
   * @returns {Object} Objeto com computed refs para cada prop
   */
  const getSafeProps = (propMap) => {
    const result = {}
    Object.keys(propMap).forEach(key => {
      result[key] = getSafeProp(key, propMap[key])
    })
    return result
  }

  return {
    getSafeProp,
    getSafeProps,
    page
  }
}

/**
 * Helper para criar computed properties seguras comuns
 * @param {Object} props - Props do componente
 * @returns {Object} Objeto com computed properties comuns
 */
export function useCommonSafeProps(props = {}) {
  const { getSafeProp } = usePageProps(props)

  return {
    safeStats: getSafeProp('stats', {}),
    safeGrievances: getSafeProp('grievances', { data: [] }),
    safeComplaints: getSafeProp('complaints', { data: [] }),
    safeAllComplaints: getSafeProp('allComplaints', []),
    safeFilters: getSafeProp('filters', {}),
    safeTechnicians: getSafeProp('technicians', []),
    safeUser: getSafeProp('user', {}),
    safeNotifications: getSafeProp('notifications', [])
  }
}

