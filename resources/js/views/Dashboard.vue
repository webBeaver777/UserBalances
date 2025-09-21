<template>

	<div class="container-fluid px-4 py-3">

		<div class="row">
			 <!-- Левая часть - баланс и форма -->
			<div class="col-md-6 mb-4">
				 <BalanceCard /> <!-- Форма создания операции -->
				<div class="card shadow-sm mt-4">

					<div class="card-header">

						<h4 class="mb-0">Новая операция</h4>

					</div>

					<div class="card-body">

						<form
							@submit.prevent="handleSubmit"
							class="operation-form"
						>

							<div class="mb-3">
								 <label
									for="type"
									class="form-label"
									>Тип операции</label
								> <select
									v-model="form.type"
									id="type"
									class="form-select"
									required
								>

									<option value="">Выберите тип</option>

									<option value="deposit">Пополнение</option>

									<option value="withdrawal">Списание</option>
									 </select
								>
							</div>

							<div class="mb-3">
								 <label
									for="amount"
									class="form-label"
									>Сумма</label
								> <input
									v-model.number="form.amount"
									type="number"
									id="amount"
									class="form-control"
									step="0.01"
									min="0.01"
									required
								/>
							</div>

							<div class="mb-3">
								 <label
									for="description"
									class="form-label"
									>Описание</label
								> <input
									v-model="form.description"
									type="text"
									id="description"
									class="form-control"
									placeholder="Описание операции"
									required
								/>
							</div>

							<div class="d-grid">
								 <button
									type="submit"
									class="btn btn-primary"
									:disabled="loading"
								>
									 <span
										v-if="loading"
										class="spinner-border spinner-border-sm me-2"
									></span
									> {{ loading ? 'Обработка...' : 'Выполнить операцию' }} </button
								>
							</div>

						</form>

						<div
							v-if="error"
							class="alert alert-danger mt-3"
						>
							 {{ error }}
						</div>

						<div
							v-if="successMessage"
							class="alert alert-success mt-3"
						>
							 {{ successMessage }}
						</div>

					</div>

				</div>

			</div>
			 <!-- Правая часть - последние 5 операций БЕЗ пагинации -->
			<div class="col-md-6 mb-4">
				 <!-- Просто таблица с 5 записями -->
				<div
					class="card shadow-sm"
					style="border: 2px solid #e91e63"
				>

					<div class="card-header d-flex justify-content-between align-items-center">

						<h4 class="mb-0">История операций</h4>
						 <router-link
							to="/history"
							class="btn btn-outline-primary btn-sm"
							> Все операции </router-link
						>
					</div>

					<div class="card-body p-0">

						<div
							v-if="operationStore.loading"
							class="text-center py-4"
						>

							<div
								class="spinner-border"
								role="status"
							>
								 <span class="visually-hidden">Загрузка...</span>
							</div>

						</div>

						<div
							v-else-if="operationStore.error"
							class="alert alert-danger m-3"
							role="alert"
						>
							 {{ operationStore.error }}
						</div>

						<div
							v-else-if="operationStore.operations.length === 0"
							class="text-center text-muted py-4"
						>
							 Нет операций
						</div>

						<div
							v-else
							class="table-responsive"
						>

							<table class="table table-sm mb-0">

								<thead class="table-light">

									<tr>

										<th>Тип</th>

										<th>Сумма</th>

										<th>Описание</th>

										<th>Дата</th>

										<th>Статус</th>

									</tr>

								</thead>

								<tbody>

									<tr
										v-for="operation in operationStore.operations"
										:key="operation.id"
									>

										<td>
											 <span
												class="badge"
												:class="
													operation.type === 'deposit'
														? 'bg-success'
														: 'bg-warning text-dark'
												"
												> {{
													operation.type === 'deposit'
														? 'Пополнение'
														: 'Списание'
												}} </span
											>
										</td>

										<td
											class="fw-bold"
											:class="
												operation.type === 'deposit'
													? 'text-success'
													: 'text-danger'
											"
										>
											 {{ formatAmount(operation.amount, operation.type) }}
										</td>

										<td
											class="text-truncate"
											style="max-width: 150px"
										>
											 {{ operation.description || '—' }}
										</td>

										<td class="text-nowrap small">
											 {{ formatDate(operation.created_at) }}
										</td>

										<td> <span class="badge bg-success"> Выполнено </span> </td>

									</tr>

								</tbody>

							</table>

						</div>

					</div>

				</div>

			</div>

		</div>

	</div>

</template>

<script setup>
import { onMounted, onUnmounted, ref, reactive } from 'vue';
import { useBalanceStore } from '../store/balanceStore';
import { useOperationStore } from '../store/operationStore';
import { formatAmount } from '../utils/money';
import { formatDate } from '../utils/date';
import BalanceCard from '../components/BalanceCard.vue';

const balanceStore = useBalanceStore();
const operationStore = useOperationStore();

// Форма создания операции
const form = reactive({
	type: '',
	amount: '',
	description: ''
});

const loading = ref(false);
const error = ref('');
const successMessage = ref('');

// Таймер для автообновления
let refreshTimer = null;
const REFRESH_INTERVAL = 5000; // 5 секунд

// Обработка создания операции
const handleSubmit = async () => {
	if (!form.type || !form.amount || !form.description) {
		error.value = 'Заполните все обязательные поля';
		return;
	}

	loading.value = true;
	error.value = '';
	successMessage.value = '';

	try {
		const response = await operationStore.createOperation({
			type: form.type,
			amount: form.amount,
			description: form.description
		});

		if (response.data && response.data.success) {
			successMessage.value = response.data.message || 'Операция успешно выполнена';

			// Очищаем форму
			form.type = '';
			form.amount = '';
			form.description = '';

			// Обновляем баланс и операции
			await Promise.all([balanceStore.fetchBalance(), loadOperations()]);

			// Убираем сообщение через 5 секунд
			setTimeout(() => {
				successMessage.value = '';
			}, 5000);
		}
	} catch (err) {
		error.value = err.response?.data?.message || 'Ошибка при создании операции';
	} finally {
		loading.value = false;
	}
};

async function loadDashboardData() {
	operationStore.resetState();
	await Promise.all([balanceStore.fetchBalance(), loadOperations()]);
}

async function loadOperations() {
	// Всегда загружаем только 5 последних операций для главной
	await operationStore.fetchOperations({ limit: 5 });
}

async function refreshData() {
	try {
		await Promise.all([balanceStore.fetchBalance(), loadOperations()]);
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

