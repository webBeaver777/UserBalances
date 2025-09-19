import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../api';

export const useOperationStore = defineStore('operation', () => {
  const operations = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const search = ref('');
  const sortDesc = ref(true);

  const filteredSorted = computed(() => {
    return sortAndFilter(operations.value, search.value, sortDesc.value);
  });

  function sortAndFilter(ops, searchValue, sortDescValue) {
    let result = ops || [];
    if (searchValue) {
      result = result.filter(op => (op.description || '').toLowerCase().includes(searchValue.toLowerCase()));
    }
    result = result.slice().sort((a, b) => {
      const da = new Date(a.created_at);
      const db = new Date(b.created_at);
      return sortDescValue ? db - da : da - db;
    });
    return result;
  }

  const totalAmount = computed(() => {
    // debit — это пополнение, credit — списание
    return filteredSorted.value.reduce((sum, op) => {
      if (op.type === 'debit') {
        return sum + (Number(op.amount) || 0);
      } else if (op.type === 'credit') {
        return sum - (Number(op.amount) || 0);
      }
      return sum;
    }, 0);
  });

  async function fetchOperations(params = {}) {
    loading.value = true;
    error.value = null;
    try {
      const response = await api.getOperations(params);
      operations.value = response.data || [];
    } catch (e) {
      error.value = 'Ошибка загрузки операций';
      operations.value = [];
    } finally {
      loading.value = false;
    }
  }

  function setSearch(val) {
    search.value = val;
  }

  function toggleSort() {
    sortDesc.value = !sortDesc.value;
  }

  return {
    operations,
    loading,
    error,
    fetchOperations,
    search,
    sortDesc,
    filteredSorted,
    totalAmount,
    setSearch,
    toggleSort
  };
});
