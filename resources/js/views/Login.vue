<template>
  <form @submit.prevent="login">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input v-model="email" type="email" class="form-control" required />
    </div>
    <div class="mb-3">
      <label class="form-label">Пароль</label>
      <input v-model="password" type="password" class="form-control" required />
    </div>
    <button class="btn btn-primary">Войти</button>
  </form>
</template>
<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useUserStore } from '../store';
const email = ref('');
const password = ref('');
const store = useUserStore();
async function login() {
  await axios.post('/api/login', { email: email.value, password: password.value }, { withCredentials: true });
  await store.fetchUser();
}
</script>

