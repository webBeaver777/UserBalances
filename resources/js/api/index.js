import axios from 'axios'

// Настройка axios
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Добавляем токен к каждому запросу
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => Promise.reject(error)
)

// Обработка ошибок авторизации
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default {
  // Авторизация
  register(data) {
    return api.post('/register', data)
  },

  login(data) {
    return api.post('/login', data)
  },

  logout() {
    return api.post('/logout')
  },

  me() {
    return api.get('/me')
  },

  // Баланс
  getBalance() {
    return api.get('/balance')
  },

  // Операции
  getOperations(params = {}) {
    return api.get('/operations', { params })
  },

  createOperation(data) {
    return api.post('/operations', data)
  }
}
