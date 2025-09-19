<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
    <div class="container-fluid">
      <span class="navbar-brand">Админ-панель</span>
      <div class="d-flex align-items-center">
        <span v-if="user" class="me-3">{{ user.name || user.email }}</span>
        <button v-if="user" class="btn btn-outline-secondary btn-sm" @click="handleLogout">Выйти</button>
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
