import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../api';

export const useUserStore = defineStore('user', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('token') || null);
  const isAuthenticated = computed(() => !!token.value);

  function setUser(userData, userToken) {
    if (!userData || !userData.email) {
      logout();
      return;
    }
    user.value = userData;
    token.value = userToken;
    localStorage.setItem('token', userToken);
    localStorage.setItem('user', JSON.stringify(userData));
  }

  function restore() {
    const savedUser = localStorage.getItem('user');
    const savedToken = localStorage.getItem('token');
    if (savedToken) {
      token.value = savedToken;
      if (savedUser && savedUser !== 'undefined') {
        user.value = JSON.parse(savedUser);
      } else {
        user.value = null;
      }
    } else {
      user.value = null;
      token.value = null;
    }
  }

  async function login(email, password) {
    try {
      const response = await api.login({ email, password });
      const { user: userData, token: userToken } = response.data;
      if (!userData || !userData.email) {
        await logout();
        return false;
      }
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
      if (!userData || !userData.email) {
        await logout();
        return false;
      }
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
      if (!response.data || !response.data.email) {
        await logout();
        return;
      }
      user.value = response.data;
      localStorage.setItem('user', JSON.stringify(response.data));
    } catch (e) {
      await logout();
    }
  }

  return { user, token, isAuthenticated, setUser, restore, logout, login, register, fetchUser };
});
