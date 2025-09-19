<template>
  <form @submit.prevent="onLogin" class="mx-auto" style="max-width: 400px;">
    <h2 class="mb-4">Вход</h2>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input v-model="email" type="email" class="form-control" required />
    </div>
    <div class="mb-3">
      <label class="form-label">Пароль</label>
      <input v-model="password" type="password" class="form-control" required />
    </div>
    <button class="btn btn-primary w-100">Войти</button>
    <div v-if="error" class="text-danger mt-2">{{ error }}</div>
    <div class="mt-3 text-center">
      <router-link to="/register">Регистрация</router-link>
    </div>
  </form>
</template>
<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../store/userStore';
const email = ref('');
const password = ref('');
const error = ref('');
const router = useRouter();
const userStore = useUserStore();
async function onLogin() {
  error.value = '';
  const success = await userStore.login(email.value, password.value);
  if (success) {
    router.push('/');
  } else {
    error.value = 'Неверный email или пароль';
  }
}
</script>
