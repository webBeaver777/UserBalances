<template>
  <form @submit.prevent="onRegister" class="mx-auto" style="max-width: 400px;">
    <h2 class="mb-4">Регистрация</h2>
    <div v-if="errors.general" class="alert alert-danger">{{ errors.general }}</div>
    <div class="mb-3">
      <label class="form-label">Имя</label>
      <input v-model="name" type="text" class="form-control" required />
      <div v-if="errors.name" class="text-danger small">{{ errors.name }}</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input v-model="email" type="email" class="form-control" required />
      <div v-if="errors.email" class="text-danger small">{{ errors.email }}</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Пароль</label>
      <input v-model="password" type="password" class="form-control" required />
      <div v-if="errors.password" class="text-danger small">{{ errors.password }}</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Подтверждение пароля</label>
      <input v-model="password_confirmation" type="password" class="form-control" required />
      <div v-if="errors.password_confirmation" class="text-danger small">{{ errors.password_confirmation }}</div>
    </div>
    <button class="btn btn-primary w-100" :disabled="loading">
      <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
      Зарегистрироваться
    </button>
    <div class="mt-3 text-center">
      <router-link to="/login">Уже есть аккаунт?</router-link>
    </div>
  </form>
</template>
<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../store/userStore';
const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const errors = ref({});
const loading = ref(false);
const router = useRouter();
const userStore = useUserStore();
async function onRegister() {
  errors.value = {};
  if (password.value !== password_confirmation.value) {
    errors.value.password_confirmation = 'Пароли не совпадают';
    return;
  }
  loading.value = true;
  const success = await userStore.register(name.value, email.value, password.value, password_confirmation.value);
  loading.value = false;
  if (success) {
    router.push('/');
  } else {
    errors.value.general = 'Ошибка регистрации. Проверьте введённые данные или попробуйте позже.';
  }
}
</script>
