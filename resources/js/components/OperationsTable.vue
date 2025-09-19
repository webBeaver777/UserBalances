<template>
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <h5 class="card-title px-3 pt-3">История операций</h5>
      <div v-if="operationStore.loading" class="my-4">
        <div class="d-flex justify-content-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Загрузка...</span>
          </div>
        </div>
      </div>
      <div v-else-if="operationStore.error" class="text-danger px-3 py-2">
        {{ operationStore.error }}
      </div>
      <div v-else>
        <form class="px-3 pt-2 pb-1">
          <div class="input-group">
            <input type="text" v-model="operationStore.search" class="form-control" placeholder="Поиск по описанию..." />
            <button type="button" class="btn btn-outline-secondary" @click="operationStore.setSearch('')" v-if="operationStore.search">Очистить</button>
          </div>
        </form>
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th scope="col">Тип</th>
                <th scope="col">Сумма</th>
                <th scope="col">Описание</th>
                <th scope="col" @click="operationStore.toggleSort" style="cursor:pointer;">
                  Дата
                  <span v-if="operationStore.sortDesc">&#8595;</span><span v-else>&#8593;</span>
                </th>
                <th scope="col">Статус</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="op in operationStore.filteredSorted.slice(0, 5)" :key="op.id" :class="{ 'table-danger': op.status === 'failed' }">
                <td>
                  <span v-if="op.type === 'debit'" class="badge bg-success" data-bs-toggle="tooltip" title="Пополнение баланса">
                    <i class="bi bi-arrow-up me-1"></i>Пополнение
                  </span>
                  <span v-else-if="op.type === 'credit'" class="badge bg-danger" data-bs-toggle="tooltip" title="Списание с баланса">
                    <i class="bi bi-arrow-down me-1"></i>Списание
                  </span>
                  <span v-else class="badge bg-secondary" data-bs-toggle="tooltip" title="Другое">{{ op.type }}</span>
                </td>
                <td :class="{
                  'text-success fw-bold': op.type === 'debit',
                  'text-danger fw-bold': op.type === 'credit'
                }" data-bs-toggle="tooltip" :title="op.type === 'debit' ? 'Сумма увеличивает баланс' : op.type === 'credit' ? 'Сумма уменьшает баланс' : 'Сумма операции'">
                  <span v-if="op.type === 'debit'">+{{ op.amount }} ₽</span>
                  <span v-else-if="op.type === 'credit'">-{{ op.amount }} ₽</span>
                  <span v-else>{{ op.amount }} ₽</span>
                </td>
                <td>{{ op.description }}</td>
                <td>{{ formatDate(op.created_at) }}</td>
                <td>
                  <span v-if="op.status === 'success'" class="badge bg-success">Успешно</span>
                  <span v-else class="badge bg-danger" data-bs-toggle="tooltip" :title="op.fail_reason || 'Ошибка'">Неудачно</span>
                </td>
              </tr>
              <tr v-if="operationStore.filteredSorted.length === 0">
                <td colspan="5" class="text-center text-muted">Нет операций</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useOperationStore } from '../store/operationStore';
import { onMounted, onUnmounted, nextTick } from 'vue';
import { formatDate } from '../utils/date';
const operationStore = useOperationStore();

let intervalId = null;

onMounted(async () => {
  await operationStore.fetchOperations();
  intervalId = setInterval(() => {
    operationStore.fetchOperations();
  }, 5000);
  nextTick(() => {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
      new window.bootstrap.Tooltip(tooltipTriggerEl);
    });
  });
});

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>

<style scoped>
.table {
  font-size: 1rem;
  background: #fff;
}
.card-title {
  margin-bottom: 0.5rem;
}
th[scope="col"] {
  user-select: none;
}
td {
  vertical-align: middle;
}
.badge {
  font-size: 0.95em;
  padding: 0.5em 0.8em;
}
</style>
