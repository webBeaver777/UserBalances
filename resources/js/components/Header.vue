<template>

	<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">

		<div class="container-fluid">
			 <router-link
				class="navbar-brand"
				to="/"
				> 💰 Баланс пользователя </router-link
			>
			<div
				class="navbar-nav ms-auto"
				v-if="userStore.isAuthenticated"
			>
				 <span class="navbar-text me-3"> Добро пожаловать, {{ userStore.userName }} </span>
				<button
					@click="handleLogout"
					class="btn btn-outline-light btn-sm"
				>
					 🚪 Выйти </button
				>
			</div>

		</div>

	</nav>

</template>

<script>
import { useRouter } from 'vue-router';
import { useUserStore } from '../store/userStore';

export default {
	name: 'Header',
	setup() {
		const router = useRouter();
		const userStore = useUserStore();

		const handleLogout = async () => {
			await userStore.logout();
			router.push('/login');
		};

		return {
			userStore,
			handleLogout
		};
	}
};
</script>

