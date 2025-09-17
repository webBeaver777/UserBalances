<template>
  <table class="table">
    <thead>
      <tr>
        <th>Дата</th>
        <th>Тип</th>
        <th>Сумма</th>
        <th>Описание</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="op in operations" :key="op.id">
        <td>{{ op.created_at }}</td>
        <td>{{ op.type }}</td>
        <td>{{ op.amount }}</td>
        <td>{{ op.description }}</td>
      </tr>
    </tbody>
  </table>
</template>
<script setup>
const props = defineProps({ operations: Array });
</script>

