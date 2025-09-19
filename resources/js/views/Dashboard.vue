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
        <div v-if="error" class="alert alert-danger mb-3" role="alert">
          {{ error }}
        </div>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-12 col-md-6 mb-4">
        <BalanceCard :balance="{ amount: balance }" :loading="loading" :error="error" />
      </div>
      <div class="col-12 col-md-8">
        <OperationsTable :operations="operations" :loading="loading" :error="error" />
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import api from '../api';
import BalanceCard from '../components/BalanceCard.vue';
import OperationsTable from '../components/OperationsTable.vue';
const operations = ref([]);
const loading = ref(true);
const error = ref(null);
const REFRESH_INTERVAL = 5000; // ms
let timer = null;

const balance = computed(() => {
  return operations.value.reduce((sum, op) => sum + (Number(op.amount) || 0), 0);
});

async function fetchData() {
  loading.value = true;
  error.value = null;
  try {
    const opsRes = await api.getOperations();
    operations.value = opsRes.data;
  } catch (e) {
    error.value = e?.response?.data?.message || e?.message || 'Ошибка загрузки данных';
    operations.value = [];
    console.error('Ошибка загрузки операций:', e);
  } finally {
    loading.value = false;
  }
}
onMounted(() => {
  fetchData();
  timer = setInterval(fetchData, REFRESH_INTERVAL);
});
onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
