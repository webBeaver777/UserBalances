import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../api';

export const useBalanceStore = defineStore('balance', () => {
  const balance = ref(null);
  const loading = ref(false);
  const error = ref(null);

  function setBalance(val) {
    balance.value = val;
  }

  async function fetchBalance() {
    loading.value = true;
    error.value = null;
    try {
      const response = await api.getBalance();
      balance.value = response.data?.amount ?? 0;
    } catch (e) {
      error.value = 'Ошибка загрузки баланса';
      balance.value = null;
    } finally {
      loading.value = false;
    }
  }

  return { balance, loading, error, setBalance, fetchBalance };
});
