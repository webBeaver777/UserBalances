<template>
  <div v-if="user && user.email" class="d-flex align-items-center justify-content-end w-100 py-2 px-3 bg-light border-bottom">
    <span class="me-3 fw-bold text-primary">
      <i class="bi bi-person-circle me-1"></i>{{ user.email }}
    </span>
    <button class="btn btn-outline-danger btn-sm" @click="handleLogout">
      <i class="bi bi-box-arrow-right me-1"></i>Выйти
    </button>
  </div>
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
