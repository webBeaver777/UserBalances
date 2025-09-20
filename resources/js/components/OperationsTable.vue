<template>
  <div>
    <!-- Фильтры и поиск -->
    <div class="row mb-3" v-if="showFilters">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text">
            <i class="bi bi-search"></i>
          </span>
          <input
            type="text"
            :value="operationStore.search"
            class="form-control"
            placeholder="Поиск по описанию..."
            @input="onSearchInput"
          />
          <button
            type="button"
            class="btn btn-outline-secondary"
            @click="operationStore.clearSearch()"
            v-if="operationStore.search"
          >
            Очистить
          </button>
        </div>
      </div>
      <div class="col-md-6 d-flex justify-content-end align-items-center">
        <span class="text-muted me-3">
          Всего операций: {{ operationStore.totalCount }}
        </span>
      </div>
    </div>

    <!-- Таблица операций -->
    <div v-if="operationStore.loading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Загрузка...</span>
      </div>
    </div>

    <div v-else-if="operationStore.error" class="alert alert-danger">
      <i class="bi bi-exclamation-triangle me-2"></i>
      {{ operationStore.error }}
    </div>

    <div v-else>
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="table-dark">
            <tr>
              <th scope="col">Тип</th>
              <th scope="col">Сумма</th>
              <th scope="col">Описание</th>
              <th
                scope="col"
                @click="operationStore.toggleSort()"
                style="cursor: pointer;"
                class="user-select-none sortable-header"
                v-if="showSorting"
              >
                Дата
                <span
                  class="sort-arrow"
                  style="color: white !important; font-size: 1.4em !important; margin-left: 8px !important;"
                >{{ operationStore.sortDesc ? '↓' : '↑' }}</span>
              </th>
              <th scope="col" v-else>Дата</th>
              <th scope="col">Статус</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="operation in operationStore.operations"
              :key="operation.id"
              :class="{ 'table-danger': operation.status === 'failed' }"
            >
              <td>
                <span
                  v-if="operation.type === 'debit'"
                  class="badge bg-success"
                  data-bs-toggle="tooltip"
                  title="Пополнение баланса"
                >
                  <i class="bi bi-arrow-up me-1"></i>Пополнение
                </span>
                <span
                  v-else-if="operation.type === 'credit'"
                  class="badge bg-danger"
                  data-bs-toggle="tooltip"
                  title="Списание с баланса"
                >
                  <i class="bi bi-arrow-down me-1"></i>Списание
                </span>
                <span
                  v-else
                  class="badge bg-secondary"
                  data-bs-toggle="tooltip"
                  title="Другое"
                >
                  {{ operation.type }}
                </span>
              </td>
              <td :class="{
                'text-success fw-bold': operation.type === 'debit',
                'text-danger fw-bold': operation.type === 'credit'
              }" data-bs-toggle="tooltip" :title="operation.type === 'debit' ? 'Сумма увеличивает баланс' : operation.type === 'credit' ? 'Сумма уменьшает баланс' : 'Сумма операции'">
                <span v-if="operation.type === 'debit'">+{{ operation.amount }} ₽</span>
                <span v-else-if="operation.type === 'credit'">-{{ operation.amount }} ₽</span>
                <span v-else>{{ operation.amount }} ₽</span>
              </td>
              <td>{{ operation.description }}</td>
              <td>{{ formatDate(operation.created_at) }}</td>
              <td>
                <span v-if="operation.status === 'success'" class="badge bg-success">Успешно</span>
                <span
                  v-else
                  class="badge bg-danger"
                  data-bs-toggle="tooltip"
                  :title="operation.fail_reason || 'Ошибка'"
                >
                  Неудачно
                </span>
              </td>
            </tr>
            <tr v-if="operationStore.operations.length === 0">
              <td colspan="5" class="text-center text-muted py-4">
                <i class="bi bi-inbox display-1 text-muted d-block mb-2"></i>
                {{ emptyMessage }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Пагинация -->
      <nav v-if="showPagination && operationStore.totalPages > 1" aria-label="Навигация по страницам" class="mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="text-muted small">
            Показано {{ operationStore.startItem }}-{{ operationStore.endItem }} из {{ operationStore.totalCount }} записей
          </div>
          <div class="text-muted small">
            Страница {{ operationStore.currentPage }} из {{ operationStore.totalPages }}
          </div>
        </div>

        <ul class="pagination justify-content-center mb-0">
          <!-- Первая страница -->
          <li class="page-item" :class="{ disabled: operationStore.currentPage === 1 }">
            <a class="page-link" href="#" @click.prevent="operationStore.setPage(1)" title="Первая страница">
              <i class="bi bi-chevron-double-left"></i>
            </a>
          </li>

          <!-- Предыдущая страница -->
          <li class="page-item" :class="{ disabled: operationStore.currentPage === 1 }">
            <a class="page-link" href="#" @click.prevent="operationStore.setPage(operationStore.currentPage - 1)" title="Предыдущая страница">
              <i class="bi bi-chevron-left"></i>
            </a>
          </li>

          <!-- Номера страниц -->
          <li
            v-for="page in operationStore.visiblePages"
            :key="page"
            class="page-item"
            :class="{ active: page === operationStore.currentPage }"
          >
            <a class="page-link" href="#" @click.prevent="operationStore.setPage(page)">{{ page }}</a>
          </li>

          <!-- Следующая страница -->
          <li class="page-item" :class="{ disabled: operationStore.currentPage === operationStore.totalPages }">
            <a class="page-link" href="#" @click.prevent="operationStore.setPage(operationStore.currentPage + 1)" title="Следующая страница">
              <i class="bi bi-chevron-right"></i>
            </a>
          </li>

          <!-- Последняя страница -->
          <li class="page-item" :class="{ disabled: operationStore.currentPage === operationStore.totalPages }">
            <a class="page-link" href="#" @click.prevent="operationStore.setPage(operationStore.totalPages)" title="Последняя страница">
              <i class="bi bi-chevron-double-right"></i>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { nextTick, watch, onMounted } from 'vue';
import { useOperationStore } from '../store/operationStore';
import { formatDate } from '../utils/date';

// Props
const props = defineProps({
  showFilters: {
    type: Boolean,
    default: true
  },
  showPagination: {
    type: Boolean,
    default: true
  },
  showSorting: {
    type: Boolean,
    default: true
  },
  showPerPageSelector: {
    type: Boolean,
    default: true
  },
  limit: {
    type: Number,
    default: null
  },
  emptyMessage: {
    type: String,
    default: 'Нет операций'
  },
  autoload: {
    type: Boolean,
    default: true
  }
});

// Store
const operationStore = useOperationStore();

// Методы
let searchTimeout;

function onSearchInput(event) {
  const value = event.target.value;

  // Немедленно обновляем значение в store
  operationStore.search = value;

  // Задержка для поиска на сервере для уменьшения количества запросов
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    operationStore.setSearch(value);
  }, 300); // Уменьшил задержку до 300мс для более отзывчивого поиска
}

// Инициализация tooltips
function initTooltips() {
  nextTick(() => {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
      new window.bootstrap.Tooltip(tooltipTriggerEl);
    });
  });
}

// Жизненный цикл компонента
onMounted(async () => {
  if (props.autoload) {
    // Если задан лимит, передаем его в fetchOperations
    const fetchParams = {};
    if (props.limit) {
      fetchParams.limit = props.limit;
    }
    await operationStore.fetchOperations(fetchParams);
  }
  initTooltips();
});

// Следим за изменениями операций для обновления tooltips
watch(() => operationStore.operations, initTooltips);
</script>

<style scoped>
.table {
  font-size: 0.95rem;
  background: #fff;
}

.badge {
  font-size: 0.85em;
  padding: 0.5em 0.75em;
}

.pagination .page-link {
  color: #495057;
  border-color: #dee2e6;
  transition: all 0.2s ease-in-out;
  border-radius: 0.375rem;
  margin: 0 2px;
  min-width: 40px;
  text-align: center;
}

.pagination .page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
  color: white;
  font-weight: 600;
  box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

.pagination .page-link:hover {
  color: #0056b3;
  background-color: #e9ecef;
  border-color: #dee2e6;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.pagination .page-item.disabled .page-link:hover {
  transform: none;
  box-shadow: none;
  background-color: #fff;
  color: #6c757d;
}

.dropdown-item.active {
  background-color: #007bff;
  color: white;
}

.dropdown-header {
  font-weight: 600;
  color: #495057;
}

.user-select-none {
  user-select: none;
}

/* Стили для стрелки сортировки */
.sort-arrow {
  color: #ffffff !important;
  font-size: 1.2em !important;
  font-weight: bold !important;
  display: inline-block !important;
  margin-left: 8px !important;
}

/* При наведении делаем стрелку еще более заметной */
.sortable-header:hover .sort-arrow {
  color: #ffc107 !important;
  transform: scale(1.4) !important;
  text-shadow: 0 0 3px rgba(255, 193, 7, 0.8) !important;
}

/* Стили для сортируемого заголовка */
.sortable-header {
  position: relative;
  transition: all 0.2s ease !important;
  cursor: pointer !important;
}

/* Делаем стрелку сортировки белой и заметной всегда */
.sortable-header .bi {
  color: #ffffff !important;
  font-size: 1.1em;
  margin-left: 8px;
}

/* При наведении на заголовок Дата - НЕ меняем фон, только стрелку */
.table-dark .sortable-header:hover {
  background-color: #212529 !important;
  color: #ffffff !important;
}

/* Делаем стрелку сортировки более заметной при наведении */
.sortable-header:hover .bi {
  color: #ffc107 !important;
  transform: scale(1.3);
  transition: all 0.2s ease-in-out;
}

/* Переопределяем все возможные Bootstrap стили которые могут мешать */
.table-dark th.sortable-header:hover,
.table-dark thead th.sortable-header:hover {
  background-color: #212529 !important;
  color: #ffffff !important;
  border-color: #32383e !important;
}

/* Убираем все старые конфликтующие стили */
th[style*="cursor"] {
  /* Очищаем старые стили */
}

th[style*="cursor"]:hover {
  /* Очищаем старые стили */
}

th[style*="cursor"]:hover i {
  /* Очищаем старые стили */
}

th[style*="cursor"]:hover::after {
  display: none !important;
}

.input-group-text {
  background-color: #f8f9fa;
  border-color: #ced4da;
}

.dropdown-toggle::after {
  margin-left: 0.5em;
}
</style>
