<template>

	<div class="container-fluid px-4 py-3">

		<div class="row">

			<div class="col-12">
				 <!-- Заголовок -->
				<div class="d-flex justify-content-between align-items-center mb-4">

					<h2 class="fw-bold text-dark mb-0">История операций</h2>

					<nav aria-label="breadcrumb">

						<ol class="breadcrumb mb-0">

							<li class="breadcrumb-item">
								 <router-link
									to="/balance"
									class="text-decoration-none"
									>Главная</router-link
								>
							</li>

							<li
								class="breadcrumb-item active"
								aria-current="page"
							>
								История операций
							</li>

						</ol>

					</nav>

				</div>
				 <!-- Фильтры и поиск -->
				<div class="card shadow-sm mb-4">

					<div class="card-body">

						<div class="row g-3">

							<div class="col-md-6">
								 <label
									for="searchInput"
									class="form-label"
									>Поиск по описанию</label
								> <input
									id="searchInput"
									v-model="searchQuery"
									type="text"
									class="form-control"
									placeholder="Введите описание операции..."
									@input="handleSearchInput"
								/>
							</div>

							<div class="col-md-3">
								 <label class="form-label">Сортировка по дате</label> <select
									v-model="sortDirection"
									@change="handleSortChange"
									class="form-select"
								>

									<option value="desc">Сначала новые</option>

									<option value="asc">Сначала старые</option>
									 </select
								>
							</div>

							<div class="col-md-3 d-flex align-items-end">
								 <button
									@click="resetFilters"
									class="btn btn-outline-secondary"
								>
									 <i class="bi bi-arrow-clockwise me-1"></i> Сбросить </button
								>
							</div>

						</div>

					</div>

				</div>
				 <!-- Полная таблица операций с пагинацией -->
				<div
					class="card shadow-sm"
					style="border: 2px solid #e91e63"
				>

					<div class="card-header">

						<h4 class="mb-0">История операций</h4>

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
							 {{
								searchQuery
									? `По запросу "${searchQuery}" ничего не найдено`
									: 'Нет операций для отображения'
							}}
						</div>

						<div v-else>

							<div class="table-responsive">

								<table class="table mb-0">

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
												 {{
													formatAmount(operation.amount, operation.type)
												}}
											</td>

											<td>
												 <span
													v-if="searchQuery"
													v-html="highlightSearch(operation.description)"
												></span
												> <span v-else>{{
													operation.description || '—'
												}}</span
												>
											</td>

											<td class="text-nowrap">
												{{ formatDate(operation.created_at) }}
											</td>

											<td>
												 <span class="badge bg-success">Выполнено</span>
											</td>

										</tr>

									</tbody>

								</table>

							</div>
							 <!-- Пагинация точно как на скриншоте -->
							<div
								v-if="operationStore.totalPages > 1"
								class="card-footer bg-light"
							>

								<div class="d-flex justify-content-between align-items-center">

									<div class="text-muted">
										 Показано {{ operationStore.startItem }} - {{
											operationStore.endItem
										}} из {{ operationStore.totalCount }} записей
									</div>

									<nav aria-label="Пагинация операций">

										<ul class="pagination mb-0">

											<li
												class="page-item"
												:class="{
													disabled: operationStore.currentPage === 1
												}"
											>
												 <button
													class="page-link"
													@click="
														handlePageChange(
															operationStore.currentPage - 1
														)
													"
													:disabled="operationStore.currentPage === 1"
												>
													 Предыдущая </button
												>
											</li>

											<li
												v-for="page in operationStore.visiblePages"
												:key="page"
												class="page-item"
												:class="{
													active: page === operationStore.currentPage
												}"
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
													disabled:
														operationStore.currentPage ===
														operationStore.totalPages
												}"
											>
												 <button
													class="page-link"
													@click="
														handlePageChange(
															operationStore.currentPage + 1
														)
													"
													:disabled="
														operationStore.currentPage ===
														operationStore.totalPages
													"
												>
													 Следующая </button
												>
											</li>

										</ul>

									</nav>

								</div>

							</div>

						</div>

					</div>

				</div>

			</div>

		</div>

	</div>

</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useOperationStore } from '../store/operationStore';

const operationStore = useOperationStore();

// Локальные фильтры
const searchQuery = ref('');
const sortDirection = ref('desc');

// Таймер для автообновления
let refreshTimer = null;
const REFRESH_INTERVAL = 10000; // 10 секунд для истории

// Таймер для задержки поиска
let searchTimer = null;
const SEARCH_DELAY = 500;

// Методы форматирования
const formatAmount = (amount, type) => {
	const prefix = type === 'deposit' ? '+' : '-';
	return `${prefix}${new Intl.NumberFormat('ru-RU', {
		style: 'currency',
		currency: 'RUB'
	}).format(amount)}`;
};

const formatDate = (dateString) => {
	const date = new Date(dateString);
	return new Intl.DateTimeFormat('ru-RU', {
		day: 'numeric',
		month: 'short',
		year: 'numeric',
		hour: '2-digit',
		minute: '2-digit'
	}).format(date);
};

const highlightSearch = (text) => {
	if (!searchQuery.value || !text) return text;
	const regex = new RegExp(`(${searchQuery.value})`, 'gi');
	return text.replace(regex, '<mark class="bg-warning">$1</mark>');
};

async function loadOperations() {
	// Для истории не передаем limit, используем пагинацию и фильтры
	const params = {
		search: searchQuery.value,
		sort_direction: sortDirection.value,
		page: operationStore.currentPage
	};
	await operationStore.fetchOperations(params);
}

function handleSearchInput() {
	// Очищаем предыдущий таймер
	if (searchTimer) {
		clearTimeout(searchTimer);
	}

	// Устанавливаем новый таймер для задержки поиска
	searchTimer = setTimeout(() => {
		operationStore.currentPage = 1; // Сбрасываем на первую страницу при поиске
		loadOperations();
	}, SEARCH_DELAY);
}

function handleSortChange() {
	operationStore.currentPage = 1; // Сбрасываем на первую страницу при изменении сортировки
	loadOperations();
}

function resetFilters() {
	searchQuery.value = '';
	sortDirection.value = 'desc';
	operationStore.currentPage = 1;
	loadOperations();
}

function handlePageChange(page) {
	if (page >= 1 && page <= operationStore.totalPages) {
		operationStore.setPage(page);
		loadOperations();
	}
}

async function refreshData() {
	try {
		await loadOperations();
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

// Сбрасываем состояние при входе на страницу
onMounted(async () => {
	operationStore.resetState();
	await loadOperations();
	startAutoRefresh();
});

// Останавливаем автообновление при уходе со страницы
onUnmounted(() => {
	stopAutoRefresh();
	if (searchTimer) {
		clearTimeout(searchTimer);
	}
});
</script>

<style scoped>
.breadcrumb-item a {
  color: #0d6efd;
}
</style>

