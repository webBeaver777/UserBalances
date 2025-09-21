import { defineStore } from 'pinia'
import api from '../api'

export const useUserStore = defineStore('user', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token'),
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    userName: (state) => state.user?.name || 'Пользователь'
  },

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null

      try {
        const response = await api.login(credentials)
        const { user, token } = response.data

        this.user = user
        this.token = token
        localStorage.setItem('auth_token', token)

        return user
      } catch (error) {
        this.error = error.response?.data?.message || 'Ошибка входа'
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      this.error = null

      try {
        const response = await api.register(userData)
        const { user, token } = response.data

        this.user = user
        this.token = token
        localStorage.setItem('auth_token', token)

        return user
      } catch (error) {
        this.error = error.response?.data?.message || 'Ошибка регистрации'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        if (this.token) {
          await api.logout()
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        localStorage.removeItem('auth_token')
      }
    },

    async fetchUser() {
      if (!this.token) return

      try {
        const response = await api.me()
        this.user = response.data.user
      } catch (error) {
        console.error('Fetch user error:', error)
        this.logout()
      }
    },

    // Инициализация при загрузке приложения
    async initializeAuth() {
      if (this.token) {
        await this.fetchUser()
      }
    }
  }
})
