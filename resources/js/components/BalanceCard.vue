<template>
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Баланс</h5>
      <p class="card-text">{{ balance.amount }} ₽</p>
    </div>
  </div>
</template>
<script setup>
const props = defineProps({ balance: Object });
</script>

