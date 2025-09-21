<template>
    <div class="balance-page">
        <div class="container-fluid">
            <!-- Верхний ряд: Баланс и Новая операция на пол экрана каждая -->
            <div class="row align-items-stretch">
                <div class="col-md-6 mb-4">
                    <BalanceCard/>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h4>Новая операция</h4>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="handleSubmit" class="operation-form">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Тип операции</label>
                                    <select v-model="form.type" id="type" class="form-select" required>
                                        <option value="">Выберите тип</option>
                                        <option value="deposit">Пополнение</option>
                                        <option value="withdrawal">Списание</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Сумма</label>
                                    <input v-model.number="form.amount" type="number" id="amount"
                                           class="form-control" step="0.01" min="0.01" required/>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Описание</label>
                                    <input v-model="form.description" type="text" id="description"
                                           class="form-control" placeholder="Описание операции" required/>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary" :disabled="loading">
                                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                        {{ loading ? 'Обработка...' : 'Выполнить операцию' }}
                                    </button>
                                </div>
                            </form>
                            <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>
                            <div v-if="successMessage" class="alert alert-success mt-3">{{ successMessage }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Нижний ряд: История операций на всю ширину -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">История операций</h4>
                            <router-link to="/history" class="btn btn-outline-primary btn-sm">Все операции</router-link>
                        </div>
                        <div class="card-body p-0">
                            <div v-if="operationStore.loading" class="text-center py-4">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Загрузка...</span>
                                </div>
                            </div>
                            <div v-else-if="operationStore.error" class="alert alert-danger m-3" role="alert">
                                {{ operationStore.error }}
                            </div>
                            <div v-else-if="operationStore.operations.length === 0" class="text-center text-muted py-4">
                                Нет операций
                            </div>
                            <div v-else class="table-responsive">
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
                                        <tr v-for="operation in operationStore.operations" :key="operation.id">
                                            <td>
                                                <span class="badge" :class="operation.type === 'deposit' ? 'bg-success' : 'bg-warning text-dark'">
                                                    {{ operation.type === 'deposit' ? 'Пополнение' : 'Списание' }}
                                                </span>
                                            </td>
                                            <td class="fw-bold" :class="operation.type === 'deposit' ? 'text-success' : 'text-danger'">
                                                {{ formatAmount(operation.amount, operation.type) }}
                                            </td>
                                            <td>{{ operation.description || '—' }}</td>
                                            <td class="text-nowrap small">{{ formatDate(operation.created_at) }}</td>
                                            <td>
                                                <span class="badge bg-success">Выполнено</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {ref, reactive, onMounted, onUnmounted} from 'vue'
import {useBalanceStore} from '../store/balanceStore'
import {useOperationStore} from '../store/operationStore'
import api from '../api'
import BalanceCard from '../components/BalanceCard.vue'

const balanceStore = useBalanceStore()
const operationStore = useOperationStore()

const form = reactive({
    type: '',
    amount: '',
    description: ''
})

const loading = ref(false)
const error = ref('')
const successMessage = ref('')
const syncingBalance = ref(false)

// Таймер автообновления (polling)
let refreshTimer = null
const REFRESH_INTERVAL = 5000 // 5 секунд

// Методы форматирования
const formatAmount = (amount, type) => {
    const prefix = type === 'deposit' ? '+' : '-'
    return `${prefix}${new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' }).format(amount)}`
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return new Intl.DateTimeFormat('ru-RU', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(date)
}

// Загрузка 5 последних операций
const loadOperations = async () => {
    await operationStore.fetchOperations({ limit: 5 })
}

async function refreshData() {
    try {
        await Promise.all([
            balanceStore.fetchBalance(),
            loadOperations()
        ])
    } catch (e) {
        console.error('Background refresh error:', e)
    }
}

function startAutoRefresh() {
    refreshTimer = setInterval(refreshData, REFRESH_INTERVAL)
}

function stopAutoRefresh() {
    if (refreshTimer) {
        clearInterval(refreshTimer)
        refreshTimer = null
    }
}

// Сбрасываем состояние и включаем polling вместо SSE
onMounted(async () => {
    operationStore.resetState()
    await refreshData()
    startAutoRefresh()
})

onUnmounted(() => {
    stopAutoRefresh()
})

const handleSubmit = async () => {
    if (!form.type || !form.amount || !form.description) {
        error.value = 'Заполните все обязательные поля'
        return
    }

    loading.value = true
    error.value = ''
    successMessage.value = ''

    try {
        const initialAmount = balanceStore.balance.amount

        const {data} = await api.createOperation({
            type: form.type,
            amount: form.amount,
            description: form.description
        })

        if (data && data.success) {
            successMessage.value = data.message || 'Операция поставлена в очередь'
            form.type = ''
            form.amount = ''
            form.description = ''

            // Ждем фактического изменения баланса и затем обновляем 5 операций
            syncingBalance.value = true
            const result = await balanceStore.waitForBalanceChange(initialAmount, 20000, 800)
            syncingBalance.value = false

            await loadOperations()

            if (!result.changed) {
                successMessage.value = 'Операция поставлена в очередь. Обновление баланса произойдет в фоне.'
            }

            setTimeout(() => { successMessage.value = '' }, 5000)
        }
    } catch (err) {
        error.value = err?.response?.data?.message || 'Ошибка при создании операции'
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.balance-page {
    padding: 20px 0;
}

.operation-form .form-label {
    font-weight: 600;
}
</style>
