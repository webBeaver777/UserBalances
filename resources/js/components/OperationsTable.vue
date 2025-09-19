<template>
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <h5 class="card-title px-3 pt-3">История операций</h5>
      <div v-if="loading" class="my-4">
        <div class="d-flex justify-content-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Загрузка...</span>
          </div>
        </div>
      </div>
      <div v-else-if="error" class="text-danger px-3 py-2">
        {{ error }}
      </div>
      <div v-else>
        <form class="px-3 pt-2 pb-1">
          <div class="input-group">
            <input type="text" v-model="search" class="form-control" placeholder="Поиск по описанию..." />
            <button type="button" class="btn btn-outline-secondary" @click="search = ''" v-if="search">Очистить</button>
          </div>
        </form>
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th scope="col">Тип</th>
                <th scope="col">Сумма</th>
                <th scope="col">Описание</th>
                <th scope="col" @click="toggleSort" style="cursor:pointer;">
                  Дата
                  <span v-if="sortDesc">&#8595;</span><span v-else>&#8593;</span>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="op in filteredSorted.slice(0, 5)" :key="op.id">
                <td>{{ op.type }}</td>
                <td>{{ op.amount }} ₽</td>
                <td>{{ op.description }}</td>
                <td>{{ formatDate(op.created_at) }}</td>
              </tr>
              <tr v-if="filteredSorted.length === 0">
                <td colspan="4" class="text-center text-muted">Нет операций</td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Удалено: отображение общего баланса -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OperationsTable',
  props: {
    operations: {
      type: Array,
      default: () => []
    },
    loading: Boolean,
    error: String
  },
  data() {
    return {
      search: '',
      sortDesc: true
    };
  },
  computed: {
    filteredSorted() {
      let ops = this.operations || [];
      if (this.search) {
        ops = ops.filter(op => (op.description || '').toLowerCase().includes(this.search.toLowerCase()));
      }
      ops = ops.slice().sort((a, b) => {
        const da = new Date(a.created_at);
        const db = new Date(b.created_at);
        return this.sortDesc ? db - da : da - db;
      });
      return ops;
    },
    totalAmount() {
      return this.filteredSorted.reduce((sum, op) => sum + (Number(op.amount) || 0), 0);
    }
  },
  methods: {
    formatDate(date) {
      if (!date) return '';
      const d = new Date(date);
      return d.toLocaleString('ru-RU', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
      });
    },
    toggleSort() {
      this.sortDesc = !this.sortDesc;
    }
  }
};
</script>

<style scoped>
.table {
  font-size: 1rem;
}
.card-title {
  margin-bottom: 0.5rem;
}
th[scope="col"] {
  user-select: none;
}
</style>
