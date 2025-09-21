<template>

	<div class="operations-table">
		 <!-- Показываем заголовок только если не скрыт -->
		<div
			v-if="showHeader"
			class="card-header"
		>

			<h4>История операций</h4>

		</div>

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
			class="alert alert-danger"
			role="alert"
		>
			 {{ operationStore.error }}
		</div>

		<div
			v-else-if="operationStore.operations.length === 0"
			class="text-center text-muted py-4"
		>
			 {{ emptyMessage }}
		</div>

		<div v-else>

			<div class="table-responsive">

				<table class="table table-hover">

					<thead>

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
										operation.type === 'deposit' ? 'bg-success' : 'bg-warning'
									"
									> {{ operation.type === 'deposit' ? 'Пополнение' : 'Списание' }}
									</span
								>
							</td>

							<td
								class="amount"
								:class="
									operation.type === 'deposit' ? 'text-success' : 'text-danger'
								"
							>
								 {{ formatAmount(operation.amount, operation.type) }}
							</td>

							<td>{{ operation.description || '—' }}</td>

							<td>{{ formatDate(operation.created_at) }}</td>

							<td>
								 <span
									class="badge"
									:class="getStatusClass(operation.status)"
									> {{ getStatusLabel(operation.status) }} </span
								>
							</td>

						</tr>

					</tbody>

				</table>

			</div>
			 <!-- Пагинация -->
			<div
				v-if="showPagination && operationStore.totalPages > 1"
				class="d-flex justify-content-between align-items-center mt-3"
			>

				<div class="text-muted">
					 Показано {{ operationStore.startItem }} - {{ operationStore.endItem }} из {{
						operationStore.totalCount
					}} записей
				</div>

				<nav aria-label="Пагинация операций">

					<ul class="pagination mb-0">

						<li
							class="page-item"
							:class="{ disabled: operationStore.currentPage === 1 }"
						>
							 <button
								class="page-link"
								@click="handlePageChange(operationStore.currentPage - 1)"
								:disabled="operationStore.currentPage === 1"
							>
								 Предыдущая </button
							>
						</li>

						<li
							v-for="page in operationStore.visiblePages"
							:key="page"
							class="page-item"
							:class="{ active: page === operationStore.currentPage }"
						>
							 <button
								class="page-link"
								@click="handlePageChange(page)"
							>
								 {{ page }} </button
							>
						</li>

						<li
							class="page-item"
							:class="{
								disabled: operationStore.currentPage === operationStore.totalPages
							}"
						>
							 <button
								class="page-link"
								@click="handlePageChange(operationStore.currentPage + 1)"
								:disabled="operationStore.currentPage === operationStore.totalPages"
							>
								 Следующая </button
							>
						</li>

					</ul>

				</nav>

			</div>

		</div>

	</div>

</template>

<script setup>
import { onMounted, watch } from 'vue';
import { useOperationStore } from '../store/operationStore';

// Пропсы для настройки компонента
const props = defineProps({
	showHeader: {
		type: Boolean,
		default: true
	},
	showFilters: {
		type: Boolean,
		default: true
	},
	showPagination: {
		type: Boolean,
		default: true
	},
	showSorting: {
		type: Boolean,
		default: true
	},
	showPerPageSelector: {
		type: Boolean,
		default: true
	},
	limit: {
		type: Number,
		default: null
	},
	emptyMessage: {
		type: String,
		default: 'Операций пока нет'
	},
	autoload: {
		type: Boolean,
		default: true
	}
});

const operationStore = useOperationStore();

const formatAmount = (amount, type) => {
	const prefix = type === 'deposit' ? '+' : '-';
	return `${prefix}${new Intl.NumberFormat('ru-RU', {
		style: 'currency',
		currency: 'RUB'
	}).format(amount)}`;
};

const getStatusLabel = (status) => {
	const labels = {
		pending: 'Ожидание',
		completed: 'Выполнено',
		failed: 'Неудачно'
	};
	return labels[status] || status;
};

const getStatusClass = (status) => {
	const classes = {
		pending: 'bg-warning',
		completed: 'bg-success',
		failed: 'bg-danger'
	};
	return classes[status] || 'bg-secondary';
};

const formatDate = (dateString) => {
	const date = new Date(dateString);
	return new Intl.DateTimeFormat('ru-RU', {
		year: 'numeric',
		month: 'short',
		day: 'numeric',
		hour: '2-digit',
		minute: '2-digit'
	}).format(date);
};

// Загружаем операции при монтировании, если autoload включен
onMounted(() => {
	if (props.autoload) {
		loadOperations();
	}
});

// Следим за изменением лимита
watch(
	() => props.limit,
	() => {
		if (props.autoload) {
			loadOperations();
		}
	}
);

async function loadOperations() {
	const params = {};

	// Если задан лимит - передаем его в API
	if (props.limit !== null && props.limit !== undefined) {
		params.limit = props.limit;
	}

	await operationStore.fetchOperations(params);
}

function handlePageChange(page) {
	operationStore.setPage(page);
	// Эмитим событие для родительского компонента, чтобы он обновил данные
	emit('page-changed', page);
}

// Эмиты
const emit = defineEmits(['page-changed']);

// Экспортируем функцию для ручной загрузки
defineExpose({
	loadOperations
});
</script>

<style scoped>
.amount {
  font-weight: bold;
}

.table th {
  border-top: none;
  font-weight: 600;
}
</style>

