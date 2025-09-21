<template>

	<div class="login-page">

		<div class="container">

			<div class="row justify-content-center">

				<div class="col-md-6 col-lg-4">

					<div class="card">

						<div class="card-header text-center">

							<h3>Вход в систему</h3>

						</div>

						<div class="card-body">

							<form @submit.prevent="handleLogin">

								<div class="mb-3">
									 <label
										for="email"
										class="form-label"
										>Email</label
									> <input
										v-model="form.email"
										type="email"
										id="email"
										class="form-control"
										required
									/>
								</div>

								<div class="mb-3">
									 <label
										for="password"
										class="form-label"
										>Пароль</label
									> <input
										v-model="form.password"
										type="password"
										id="password"
										class="form-control"
										required
									/>
								</div>

								<div class="d-grid">
									 <button
										type="submit"
										class="btn btn-primary"
										:disabled="userStore.loading"
									>
										 <span
											v-if="userStore.loading"
											class="spinner-border spinner-border-sm me-2"
										></span
										> {{ userStore.loading ? 'Вход...' : 'Войти' }} </button
									>
								</div>

							</form>

							<div
								v-if="userStore.error"
								class="alert alert-danger mt-3"
							>
								 {{ userStore.error }}
							</div>

							<div class="text-center mt-3">
								 <router-link to="/register"
									>Нет аккаунта? Зарегистрироваться</router-link
								>
							</div>

						</div>

					</div>

				</div>

			</div>

		</div>

	</div>

</template>

<script>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../store/userStore';

export default {
	name: 'Login',
	setup() {
		const router = useRouter();
		const userStore = useUserStore();

		const form = reactive({
			email: '',
			password: ''
		});

		const handleLogin = async () => {
			try {
				await userStore.login(form);
				router.push('/balance');
			} catch (error) {
				// Ошибка уже обработана в store
			}
		};

		return {
			userStore,
			form,
			handleLogin
		};
	}
};
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background-color: #f8f9fa;
}
</style>

