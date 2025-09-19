<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
    <div class="container-fluid">
      <span class="navbar-brand">Баланс пользователя</span>
      <div class="d-flex align-items-center ms-auto">
        <span v-if="user" class="me-3 fw-bold text-primary">
          <i class="bi bi-person-circle me-1"></i>{{ user.email }}
        </span>
        <button v-if="user" class="btn btn-outline-danger btn-sm" @click="handleLogout">
          <i class="bi bi-box-arrow-right me-1"></i>Выйти
        </button>
      </div>
    </div>
  </nav>
</template>
<script setup>
import { storeToRefs } from 'pinia';
import { useUserStore } from '../store/userStore';
import { useRouter } from 'vue-router';
const userStore = useUserStore();
const { user } = storeToRefs(userStore);
const router = useRouter();
function handleLogout() {
  userStore.logout();
  router.push('/login');
}
</script>
