import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import MainLayout from './components/MainLayout.vue';
import axios from 'axios';
import 'bootstrap/dist/css/bootstrap.min.css';
import { useUserStore } from './store/userStore';

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

// Восстановление авторизации при загрузке страницы
const userStore = useUserStore();
userStore.restore();

app.mount('#app');
