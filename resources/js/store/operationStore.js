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
    loading.value = true;
    error.value = null;

    try {
      const requestParams = {
        page: params.page || currentPage.value,
        per_page: params.per_page || params.limit || perPage.value,
        search: params.search !== undefined ? params.search : search.value,
        sort_desc: params.sort_desc !== undefined ? params.sort_desc : sortDesc.value,
        ...params
      };

      console.log('Отправляем запрос с параметрами:', requestParams);

      const response = await api.getOperations(requestParams);
      console.log('Получили ответ от API:', response.data);
      console.log('Структура ответа:', {
        hasData: !!response.data.data,
        hasTotal: typeof response.data.total === 'number',
        isArray: Array.isArray(response.data),
        keys: Object.keys(response.data || {})
      });

      if (response.data) {
        // Проверяем структуру ответа
        if (response.data.data && typeof response.data.total === 'number') {
          // Серверная пагинация - API возвращает объект с data и meta-информацией
          operations.value = response.data.data;
          totalCount.value = response.data.total;

          // Обновляем текущую страницу если она передана в ответе
          if (response.data.current_page) {
            currentPage.value = response.data.current_page;
          }

          console.log('Используем серверную пагинацию:', {
            operations: operations.value.length,
            total: totalCount.value,
            page: currentPage.value
          });
        } else {
          // Простой массив операций - применяем клиентскую пагинацию
          const operationsData = Array.isArray(response.data) ? response.data : [];

          // Если задан лимит (для Dashboard), просто возвращаем данные как есть
          if (params.limit) {
            operations.value = operationsData.slice(0, params.limit);
            totalCount.value = operationsData.length;
          } else {
            // Применяем клиентскую пагинацию для страницы History
            const startIndex = (currentPage.value - 1) * perPage.value;
            const endIndex = startIndex + perPage.value;

            operations.value = operationsData.slice(startIndex, endIndex);
            totalCount.value = operationsData.length;
          }

          console.log('Используем клиентскую пагинацию:', {
            totalOperations: operationsData.length,
            displayedOperations: operations.value.length,
            currentPage: currentPage.value,
            perPage: perPage.value,
            totalPages: Math.ceil(operationsData.length / perPage.value)
          });
        }
      } else {
        operations.value = [];
        totalCount.value = 0;
      }
    } catch (e) {
      console.error('Ошибка загрузки операций:', e);
      error.value = 'Ошибка загрузки операций';
      operations.value = [];
      totalCount.value = 0;
    } finally {
      loading.value = false;
    }
  }

  // Методы управления
  function setPage(page) {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page;
      fetchOperations();
    }
  }

  function setPerPage(newPerPage) {
    perPage.value = newPerPage;
    currentPage.value = 1;
    fetchOperations();
  }

  function setSearch(searchValue) {
    search.value = searchValue;
    currentPage.value = 1;
    fetchOperations();
  }

  function toggleSort() {
    sortDesc.value = !sortDesc.value;
    currentPage.value = 1;
    fetchOperations();
  }

  function clearSearch() {
    search.value = '';
    currentPage.value = 1;
    fetchOperations();
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
    resetState
  };
});
