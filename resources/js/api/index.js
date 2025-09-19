import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Accept': 'application/json',
  },
});

// Добавление токена авторизации, если он есть
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default {
  register(data) {
    return api.post('/register', data);
  },
  login(data) {
    return api.post('/login', data);
  },
  logout() {
    return api.post('/logout');
  },
  getUser() {
    return api.get('/user');
  },
  getBalance() {
    return api.get('/balance');
  },
  getOperations(params = {}) {
    return api.get('/operations', { params });
  },
};

