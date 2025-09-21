<template>

	<div class="register-page">

		<div class="container">

			<div class="row justify-content-center">

				<div class="col-md-6 col-lg-4">

					<div class="card">

						<div class="card-header text-center">

							<h3>Регистрация</h3>

						</div>

						<div class="card-body">

							<form @submit.prevent="handleRegister">

								<div class="mb-3">
									 <label
										for="name"
										class="form-label"
										>Имя</label
									> <input
										v-model="form.name"
										type="text"
										id="name"
										class="form-control"
										required
									/>
								</div>

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
										minlength="8"
									/>
								</div>

								<div class="mb-3">
									 <label
										for="password_confirmation"
										class="form-label"
										>Подтверждение пароля</label
									> <input
										v-model="form.password_confirmation"
										type="password"
										id="password_confirmation"
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
										> {{
											userStore.loading
												? 'Регистрация...'
												: 'Зарегистрироваться'
										}} </button
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
								 <router-link to="/login">Уже есть аккаунт? Войти</router-link>
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
	name: 'Register',
	setup() {
		const router = useRouter();
		const userStore = useUserStore();

		const form = reactive({
			name: '',
			email: '',
			password: '',
			password_confirmation: ''
		});

		const handleRegister = async () => {
			if (form.password !== form.password_confirmation) {
				userStore.error = 'Пароли не совпадают';
				return;
			}

			try {
				await userStore.register(form);
				router.push('/balance');
			} catch (error) {
				// Ошибка уже обработана в store
			}
		};

		return {
			userStore,
			form,
			handleRegister
		};
	}
};
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background-color: #f8f9fa;
}
</style>

