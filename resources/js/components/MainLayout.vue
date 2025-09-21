<template>

	<div class="main-layout">
		 <Header v-if="userStore.isAuthenticated" />
		<main
			class="main-content"
			:class="{ 'with-header': userStore.isAuthenticated }"
		>
			 <slot />
		</main>

	</div>

</template>

<script>
import { onMounted } from 'vue';
import { useUserStore } from '../store/userStore';
import Header from './Header.vue';

export default {
	name: 'MainLayout',
	components: {
		Header
	},
	setup() {
		const userStore = useUserStore();

		// Инициализируем аутентификацию при загрузке
		onMounted(() => {
			userStore.initializeAuth();
		});

		return {
			userStore
		};
	}
};
</script>

<style scoped>
.main-layout {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.main-content {
  padding: 20px;
}

.main-content.with-header {
  padding-top: 80px;
}
</style>

