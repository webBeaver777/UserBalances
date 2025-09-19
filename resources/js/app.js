import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import MainLayout from './components/MainLayout.vue';
import axios from 'axios';
import 'bootstrap/dist/css/bootstrap.min.css';

// Axios interceptor
axios.defaults.baseURL = '/api';
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

const app = createApp(MainLayout);
app.use(createPinia());
app.use(router);
app.mount('#app');

