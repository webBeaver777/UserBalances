<template>
  <div class="container py-4">
    <div class="row justify-content-center mb-4">
      <div class="col-12 col-md-8">
        <div class="card bg-primary text-white shadow-sm mb-3">
          <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div>
              <h4 class="mb-2">Добро пожаловать!</h4>
              <p class="mb-0">Следите за своим балансом и операциями в реальном времени.</p>
            </div>
            <div class="mt-3 mt-md-0">
              <i class="bi bi-wallet2 display-6"></i>
            </div>
          </div>
        </div>
        <div v-if="operationStore.error || balanceStore.error" class="alert alert-danger mb-3" role="alert">
          {{ operationStore.error || balanceStore.error }}
        </div>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-12 col-md-6 mb-4">
        <BalanceCard :balance="balanceStore.balance" :loading="balanceStore.loading" :error="balanceStore.error" />
      </div>
      <div class="col-12 col-md-8">
        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Последние операции</h5>
            <router-link to="/history" class="btn btn-outline-primary btn-sm">
              <i class="bi bi-list-ul me-1"></i>
              Все операции
            </router-link>
          </div>
          <div class="card-body">
            <OperationsTable
              :show-filters="false"
              :show-pagination="false"
              :show-sorting="false"
              :show-per-page-selector="false"
              :limit="5"
              empty-message="Нет операций"
              :autoload="false"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useBalanceStore } from '../store/balanceStore';
import { useOperationStore } from '../store/operationStore';
import BalanceCard from '../components/BalanceCard.vue';
import OperationsTable from '../components/OperationsTable.vue';

const balanceStore = useBalanceStore();
const operationStore = useOperationStore();

// Таймер для автообновления
let refreshTimer = null;
const REFRESH_INTERVAL = 5000;

async function loadDashboardData() {
  operationStore.resetState();
  await balanceStore.fetchBalance();
  await operationStore.fetchOperations({ limit: 5 });
}

async function refreshData() {
  try {
    await balanceStore.fetchBalance();
    await operationStore.fetchOperations({ limit: 5 });
  } catch (e) {
    console.error('Background refresh error:', e);
  }
}

function startAutoRefresh() {
  refreshTimer = setInterval(refreshData, REFRESH_INTERVAL);
}

function stopAutoRefresh() {
  if (refreshTimer) {
    clearInterval(refreshTimer);
    refreshTimer = null;
  }
}

onMounted(async () => {
  await loadDashboardData();
  startAutoRefresh();
});

onUnmounted(() => {
  stopAutoRefresh();
});
</script>

<style scoped>
.card {
  border: none;
  border-radius: 0.5rem;
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}
</style>
