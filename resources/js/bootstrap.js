import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Send cookies with cross-site requests when using axios (needed for Sanctum/session auth)
window.axios.defaults.withCredentials = true;
