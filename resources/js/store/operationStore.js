import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../api';

export const useOperationStore = defineStore('operation', () => {
  const operations = ref([]);
  const loading = ref(false);
  const error = ref(null);

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

  function getFilteredSorted(desc, search) {
    let ops = [...operations.value];
    if (search) {
      ops = ops.filter(o => (o.description || '').toLowerCase().includes(search.toLowerCase()));
    }
    ops.sort((a, b) => desc ? new Date(b.created_at) - new Date(a.created_at) : new Date(a.created_at) - new Date(b.created_at));
    return ops;
  }

  return { operations, loading, error, fetchOperations, getFilteredSorted };
});
