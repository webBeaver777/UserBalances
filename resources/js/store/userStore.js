import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../api';

export const useUserStore = defineStore('user', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('token') || null);
  const isAuthenticated = computed(() => !!token.value);

  function setUser(userData, userToken) {
    user.value = userData;
    token.value = userToken;
    localStorage.setItem('token', userToken);
    localStorage.setItem('user', JSON.stringify(userData));
  }

  function restore() {
    const savedUser = localStorage.getItem('user');
    const savedToken = localStorage.getItem('token');
    if (savedUser && savedToken) {
      user.value = JSON.parse(savedUser);
      token.value = savedToken;
    }
  }

  async function login(email, password) {
    try {
      const response = await api.login({ email, password });
      const { user: userData, token: userToken } = response.data;
      setUser(userData, userToken);
      return true;
    } catch (e) {
      return false;
    }
  }

  async function register(name, email, password, password_confirmation) {
    try {
      const response = await api.register({ name, email, password, password_confirmation });
      const { user: userData, token: userToken } = response.data;
      setUser(userData, userToken);
      return true;
    } catch (e) {
      return false;
    }
  }

  async function logout() {
    try {
      await api.logout();
    } catch (e) {}
    user.value = null;
    token.value = null;
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  }

  async function fetchUser() {
    try {
      const response = await api.getUser();
      user.value = response.data;
    } catch (e) {
      user.value = null;
    }
  }

  return { user, token, isAuthenticated, setUser, restore, logout, login, register, fetchUser };
});
