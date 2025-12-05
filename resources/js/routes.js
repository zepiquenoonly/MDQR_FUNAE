// Simple route helper for Laravel routes
const routes = {
  'technician.dashboard': '/tecnico/dashboard',
  'manager.dashboard': '/gestor/dashboard',
  'pca.dashboard': '/pca/dashboard',
  'admin.dashboard': '/admin/dashboard',
  'user.dashboard': '/utente/dashboard',
  'home': '/',
  'technician.grievances.show': '/technician/grievances/{grievance}',
  'profile.edit': '/profile',
  'logout': '/logout'
}

window.route = (name, params = {}) => {
  let url = routes[name] || '/'

  // Replace route parameters
  if (params) {
    Object.keys(params).forEach(key => {
      if (typeof params[key] !== 'object') {
        url = url.replace(`{${key}}`, params[key])
      }
    })

    // Add query parameters for non-route params
    const queryParams = Object.keys(params).filter(key => !url.includes(`{${key}}`))
    if (queryParams.length > 0) {
      const queryString = new URLSearchParams()
      queryParams.forEach(key => {
        if (params[key] !== null && params[key] !== undefined) {
          queryString.append(key, params[key])
        }
      })
      if (queryString.toString()) {
        url += (url.includes('?') ? '&' : '?') + queryString.toString()
      }
    }
  }

  return url
}

export default routes
