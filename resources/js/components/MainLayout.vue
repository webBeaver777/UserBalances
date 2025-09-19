<template>
  <div class="d-flex flex-column vh-100">
    <Header />
    <main class="flex-grow-1 p-4 bg-white">
      <router-view />
    </main>
  </div>
</template>
<script setup>
import Header from './Header.vue';
import { useUserStore } from '../store/userStore';
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
const router = useRouter();
const userStore = useUserStore();
onMounted(async () => {
  userStore.restore();
  if (userStore.token) {
    await userStore.fetchUser();
    if (!userStore.user || !userStore.user.email) {
      await userStore.logout();
      router.push('/login');
    }
  }
});
</script>
