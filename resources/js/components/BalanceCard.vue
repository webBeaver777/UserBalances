<template>
  <div class="card text-center shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title">Текущий баланс</h5>
      <div v-if="loading" class="my-4">
        <div class="d-flex justify-content-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Загрузка...</span>
          </div>
        </div>
      </div>
      <div v-else-if="error" class="text-danger my-4">
        {{ error }}
      </div>
      <p v-else class="display-4 fw-bold mb-0">{{ balance?.amount ?? 0 }} ₽</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BalanceCard',
  props: {
    balance: Object,
    loading: Boolean,
    error: String
  }
};
</script>

<style scoped>
.display-4 {
  font-size: 2.5rem;
}
</style>
