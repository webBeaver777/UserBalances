import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from './store/userStore'

// Импорт компонентов
import Balance from './views/Balance.vue'
import Dashboard from './views/Dashboard.vue'
import History from './views/History.vue'
import Login from './views/Login.vue'
import Register from './views/Register.vue'

const routes = [
  {
    path: '/',
    redirect: '/balance'
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/balance',
    name: 'Balance',
    component: Balance,
    meta: { requiresAuth: true }
  },
  {
    path: '/history',
    name: 'History',
    component: History,
    meta: { requiresAuth: true }
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresGuest: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Защита маршрутов с проверкой токена
router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore()

  // Если есть токен, но нет пользователя - попробуем загрузить пользователя
  if (userStore.token && !userStore.user) {
    try {
      await userStore.fetchUser()
    } catch (error) {
      // Если не удалось загрузить пользователя - очищаем токен
      userStore.logout()
    }
  }

  if (to.meta.requiresAuth && !userStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresGuest && userStore.isAuthenticated) {
    next('/balance')
  } else {
    next()
  }
})

export default router
