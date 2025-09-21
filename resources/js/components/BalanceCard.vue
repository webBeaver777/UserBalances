<template>

	<div class="balance-card">

		<div class="card">

			<div class="card-header">

				<h3>Текущий баланс</h3>
				 <button
					@click="balanceStore.fetchBalance"
					class="btn btn-outline-light btn-sm"
					:disabled="balanceStore.loading"
				>
					 <span
						:class="{ 'spinner-border spinner-border-sm': balanceStore.loading }"
					></span
					> {{ balanceStore.loading ? 'Загрузка...' : '🔄 Обновить' }} </button
				>
			</div>

			<div class="card-body">

				<div
					v-if="balanceStore.loading"
					class="text-center"
				>

					<div
						class="spinner-border"
						role="status"
					></div>

				</div>

				<div
					v-else-if="balanceStore.error"
					class="alert alert-danger"
				>
					 {{ balanceStore.error }}
				</div>

				<div v-else>

					<h2
						class="balance-amount"
						:class="{ 'text-success': balanceStore.balance.amount > 0 }"
					>
						 {{ balanceStore.formattedBalance }}
					</h2>
					 <small class="text-muted">Доступно для операций</small>
				</div>

			</div>

		</div>

	</div>

</template>

<script>
import { onMounted } from 'vue';
import { useBalanceStore } from '../store/balanceStore';

export default {
	name: 'BalanceCard',
	setup() {
		const balanceStore = useBalanceStore();

		onMounted(() => {
			balanceStore.fetchBalance();
		});

		return {
			balanceStore
		};
	}
};
</script>

<style scoped>
.balance-card .card {
  border: none;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  min-height: 260px; /* выравниваем высоту с правым блоком */
  height: 100%;
}

.card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.balance-amount {
  font-size: 2.5rem;
  font-weight: bold;
  margin: 0;
}
</style>

