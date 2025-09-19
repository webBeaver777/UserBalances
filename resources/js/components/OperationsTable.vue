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
              </tr>
            </thead>
            <tbody>
              <tr v-for="op in operationStore.filteredSorted.slice(0, 5)" :key="op.id">
                <td>
                  <span v-if="op.type === 'debit'" class="badge bg-danger">
                    <i class="bi bi-arrow-down me-1"></i>Списание
                  </span>
                  <span v-else-if="op.type === 'credit'" class="badge bg-success">
                    <i class="bi bi-arrow-up me-1"></i>Пополнение
                  </span>
                  <span v-else class="badge bg-secondary">{{ op.type }}</span>
                </td>
                <td :class="{
                  'text-danger fw-bold': op.type === 'debit',
                  'text-success fw-bold': op.type === 'credit'
                }">
                  {{ op.amount }} ₽
                </td>
                <td>{{ op.description }}</td>
                <td>{{ formatDate(op.created_at) }}</td>
              </tr>
              <tr v-if="operationStore.filteredSorted.length === 0">
                <td colspan="4" class="text-center text-muted">Нет операций</td>
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
import { onMounted } from 'vue';
import { formatDate } from '../utils/date';
const operationStore = useOperationStore();

onMounted(() => {
  operationStore.fetchOperations();
});
</script>

<style scoped>
.table {
  font-size: 1rem;
}
.card-title {
  margin-bottom: 0.5rem;
}
th[scope="col"] {
  user-select: none;
}
</style>
