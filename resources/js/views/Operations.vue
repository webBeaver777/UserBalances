<template>
  <input v-model="search" placeholder="Поиск по описанию" class="form-control mb-2" />
  <OperationsTable :operations="operations" />
</template>
<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import OperationsTable from '../components/OperationsTable.vue';
const operations = ref([]);
const search = ref('');
async function fetchOperations() {
  const { data } = await axios.get('/api/operations', { params: { description: search.value }, withCredentials: true });
  operations.value = data;
}
watch(search, fetchOperations);
fetchOperations();
</script>

