import { createRouter, createWebHistory } from 'vue-router';
import { useUserStore } from './store/userStore';
import Login from './views/Login.vue';
import Register from './views/Register.vue';
import Dashboard from './views/Dashboard.vue';
import Balance from './views/Balance.vue';
import History from './views/History.vue';

const routes = [
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/balance', name: 'balance', component: Balance, meta: { requiresAuth: true } },
  { path: '/history', name: 'history', component: History, meta: { requiresAuth: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore();
  // Восстановить токен, если пользователь не загружен
  if (userStore.token && !userStore.user) {
    await userStore.fetchUser();
  }
  // Проверка защищённых маршрутов
  if (to.meta.requiresAuth) {
    if (!userStore.token || !userStore.user) {
      await userStore.logout();
      next('/login');
      return;
    }
  }
  // Если пользователь уже авторизован, не пускать на login/register
  if ((to.path === '/login' || to.path === '/register') && userStore.token && userStore.user) {
    next('/');
    return;
  }
  next();
});

export default router;
