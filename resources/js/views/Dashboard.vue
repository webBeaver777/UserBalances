<template>
  <BalanceCard :balance="balance" />
  <OperationsTable :operations="operations" />
</template>
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import BalanceCard from '../components/BalanceCard.vue';
import OperationsTable from '../components/OperationsTable.vue';
const balance = ref({ amount: 0 });
const operations = ref([]);
async function fetchData() {
  const { data } = await axios.get('/api/balance', { withCredentials: true });
  balance.value = data.balance;
  operations.value = data.operations;
}
onMounted(() => {
  fetchData();
  setInterval(fetchData, 5000);
});
</script>

