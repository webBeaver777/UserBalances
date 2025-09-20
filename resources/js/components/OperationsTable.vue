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
        <div class="dropdown" v-if="showPerPageSelector">
          <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            {{ operationStore.perPage }} на странице
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" @click.prevent="operationStore.setPerPage(10)">10 на странице</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="operationStore.setPerPage(25)">25 на странице</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="operationStore.setPerPage(50)">50 на странице</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="operationStore.setPerPage(100)">100 на странице</a></li>
          </ul>
        </div>
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
                class="user-select-none"
                v-if="showSorting"
              >
                Дата
                <i :class="operationStore.sortDesc ? 'bi bi-arrow-down' : 'bi bi-arrow-up'" class="ms-1"></i>
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
        <ul class="pagination justify-content-center">
          <li class="page-item" :class="{ disabled: operationStore.currentPage === 1 }">
            <a class="page-link" href="#" @click.prevent="operationStore.setPage(operationStore.currentPage - 1)">
              <i class="bi bi-chevron-left"></i>
            </a>
          </li>

          <li
            v-for="page in operationStore.visiblePages"
            :key="page"
            class="page-item"
            :class="{ active: page === operationStore.currentPage }"
          >
            <a class="page-link" href="#" @click.prevent="operationStore.setPage(page)">{{ page }}</a>
          </li>

          <li class="page-item" :class="{ disabled: operationStore.currentPage === operationStore.totalPages }">
            <a class="page-link" href="#" @click.prevent="operationStore.setPage(operationStore.currentPage + 1)">
              <i class="bi bi-chevron-right"></i>
            </a>
          </li>
        </ul>

        <div class="text-center text-muted small mt-2">
          Показано {{ operationStore.startItem }}-{{ operationStore.endItem }} из {{ operationStore.totalCount }} записей
        </div>
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

  // Задержка поиска для уменьшения количества запросов
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    operationStore.setSearch(value);
  }, 500);
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
}

.pagination .page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
}

.pagination .page-link:hover {
  color: #0056b3;
  background-color: #e9ecef;
  border-color: #dee2e6;
}

.user-select-none {
  user-select: none;
}

th[style*="cursor"] {
  transition: background-color 0.2s;
}

th[style*="cursor"]:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.input-group-text {
  background-color: #f8f9fa;
  border-color: #ced4da;
}

.dropdown-toggle::after {
  margin-left: 0.5em;
}
</style>
