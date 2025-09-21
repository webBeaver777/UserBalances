import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../api';

export const useOperationStore = defineStore('operation', () => {
  // Данные операций
  const operations = ref([]);
  const loading = ref(false);
  const error = ref(null);

  // Параметры фильтрации и сортировки
  const search = ref('');
  const sortDesc = ref(true);

  // Параметры пагинации
  const currentPage = ref(1);
  const perPage = ref(10); // Уменьшаем до 10 записей на странице для демонстрации пагинации
  const totalCount = ref(0);

  // Внутренний указатель текущего запроса (для предотвращения оверлапов)
  let currentRequest = null;

  // Вычисляемые свойства для пагинации
  const totalPages = computed(() => Math.ceil(totalCount.value / perPage.value));

  const visiblePages = computed(() => {
    const pages = [];
    const start = Math.max(1, currentPage.value - 2);
    const end = Math.min(totalPages.value, currentPage.value + 2);

    for (let i = start; i <= end; i++) {
      pages.push(i);
    }

    return pages;
  });

  const startItem = computed(() => {
    return totalCount.value === 0 ? 0 : (currentPage.value - 1) * perPage.value + 1;
  });

  const endItem = computed(() => {
    return Math.min(currentPage.value * perPage.value, totalCount.value);
  });

  // Для обратной совместимости
  const filteredSorted = computed(() => {
    let result = operations.value || [];
    if (search.value) {
      result = result.filter(op => (op.description || '').toLowerCase().includes(search.value.toLowerCase()));
    }
    result = result.slice().sort((a, b) => {
      const da = new Date(a.created_at);
      const db = new Date(b.created_at);
      return sortDesc.value ? db - da : da - db;
    });
    return result;
  });

  const totalAmount = computed(() => {
    return filteredSorted.value.reduce((sum, op) => {
      if (op.type === 'debit') {
        return sum + (Number(op.amount) || 0);
      } else if (op.type === 'credit') {
        return sum - (Number(op.amount) || 0);
      }
      return sum;
    }, 0);
  });

  // Функция загрузки операций
  async function fetchOperations(params = {}) {
    // Если уже есть активный запрос — возвращаем его, чтобы не плодить параллельные запросы
    if (currentRequest) {
      return currentRequest;
    }

    error.value = null;

    // Показываем спиннер только при первой загрузке (когда список пуст)
    const showLoader = (operations.value.length === 0);
    if (showLoader) {
      loading.value = true;
    }

    const requestParams = {
      page: params.page || currentPage.value,
      search: params.search !== undefined ? params.search : search.value,
      ...params
    };

    try {
      // Сохраняем промис текущего запроса, чтобы вернуть его при повторных вызовах
      currentRequest = api.getOperations(requestParams);
      const response = await currentRequest;

      if (response.data && response.data.success) {
        const responseData = response.data;

        // Проверяем есть ли пагинация в ответе
        if (responseData.pagination && responseData.pagination.total) {
          // Серверная пагинация
          operations.value = responseData.data || [];
          totalCount.value = responseData.pagination.total;
          currentPage.value = responseData.pagination.current_page || 1;
        } else {
          // Простой список без пагинации (для Dashboard с limit)
          operations.value = responseData.data || [];
          totalCount.value = operations.value.length;
        }
      } else {
        operations.value = [];
        totalCount.value = 0;
      }

      return response;
    } catch (e) {
      console.error('Ошибка загрузки операций:', e);
      error.value = 'Ошибка загрузки операций';
      operations.value = [];
      totalCount.value = 0;
      throw e;
    } finally {
      if (showLoader) {
        loading.value = false;
      }
      currentRequest = null;
    }
  }

  // Методы управления
  function setPage(page) {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page;
    }
  }

  function setPerPage(newPerPage) {
    perPage.value = newPerPage;
    currentPage.value = 1;
  }

  function setSearch(searchValue) {
    search.value = searchValue;
    currentPage.value = 1;
  }

  function toggleSort() {
    sortDesc.value = !sortDesc.value;
    currentPage.value = 1;
  }

  function clearSearch() {
    search.value = '';
    currentPage.value = 1;
  }

  // Сброс состояния
  function resetState() {
    operations.value = [];
    currentPage.value = 1;
    totalCount.value = 0;
    search.value = '';
    sortDesc.value = true;
    error.value = null;
  }

  // Заменить список операций целиком (для SSE обновлений)
  function replaceWith(list = []) {
    operations.value = Array.isArray(list) ? list : [];
    totalCount.value = operations.value.length;
  }

  return {
    // Состояние
    operations,
    loading,
    error,
    search,
    sortDesc,
    currentPage,
    perPage,
    totalCount,

    // Вычисляемые свойства
    totalPages,
    visiblePages,
    startItem,
    endItem,
    filteredSorted,
    totalAmount,

    // Методы
    fetchOperations,
    setPage,
    setPerPage,
    setSearch,
    toggleSort,
    clearSearch,
    resetState,
    replaceWith
  };
});
