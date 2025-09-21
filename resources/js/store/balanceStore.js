import { defineStore } from 'pinia'
import api from '../api'

export const useBalanceStore = defineStore('balance', {
  state: () => ({
    balance: {
      userId: null,
      amount: 0
    },
    loading: false,
    error: null
  }),

  getters: {
    formattedBalance: (state) => {
      return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB'
      }).format(state.balance.amount || 0)
    }
  },

  actions: {
    async fetchBalance() {
      this.loading = true
      this.error = null

      try {
        const response = await api.getBalance()

        if (response.data && response.data.success) {
          // API возвращает { success: true, balance: number }
          this.balance = {
            userId: null,
            amount: Number(response.data.balance) || 0
          }
        } else {
          // Fallback
          this.balance = { userId: null, amount: 0 }
        }

      } catch (error) {
        this.error = error.response?.data?.message || 'Ошибка загрузки баланса'
        this.balance = { userId: null, amount: 0 }
      } finally {
        this.loading = false
      }
    },

    // Ожидаем изменения баланса после постановки операции в очередь
    async waitForBalanceChange(initialAmount, timeoutMs = 15000, intervalMs = 1000) {
      const start = Date.now()
      let attempts = 0
      while (Date.now() - start < timeoutMs) {
        await this.fetchBalance()
        attempts++
        if (Number(this.balance.amount) !== Number(initialAmount)) {
          return { changed: true, attempts, newAmount: this.balance.amount }
        }
        await new Promise(r => setTimeout(r, intervalMs))
      }
      return { changed: false, attempts, newAmount: this.balance.amount }
    }
  }
})
