<template>
  <div class="container-fluid px-4 py-3">
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="fw-bold text-dark mb-0">История операций</h2>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none">Главная</router-link></li>
              <li class="breadcrumb-item active" aria-current="page">История операций</li>
            </ol>
          </nav>
        </div>

        <div class="card shadow-sm">
          <div class="card-body">
            <OperationsTable
              :show-filters="true"
              :show-pagination="true"
              :show-sorting="true"
              :show-per-page-selector="true"
              :show-view-all-link="false"
              empty-message="Нет операций для отображения"
              :autoload="true"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useOperationStore } from '../store/operationStore';
import OperationsTable from '../components/OperationsTable.vue';

const operationStore = useOperationStore();

// Сбрасываем состояние при входе на страницу
onMounted(() => {
  operationStore.resetState();
});

// Опционально: сбрасываем состояние при уходе со страницы
onUnmounted(() => {
  // operationStore.resetState();
});
</script>

<style scoped>
.breadcrumb-item a {
  color: #6c757d;
}

.breadcrumb-item a:hover {
  color: #495057;
}

.card {
  border: none;
  border-radius: 0.5rem;
}
</style>
