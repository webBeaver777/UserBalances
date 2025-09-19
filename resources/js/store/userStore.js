import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../api';

function saveUserToStorage(userData) {
  localStorage.setItem('user', JSON.stringify(userData));
}
function saveTokenToStorage(token) {
  localStorage.setItem('token', token);
}
function removeUserFromStorage() {
  localStorage.removeItem('user');
}
function removeTokenFromStorage() {
  localStorage.removeItem('token');
}
function getUserFromStorage() {
  const savedUser = localStorage.getItem('user');
  return savedUser && savedUser !== 'undefined' ? JSON.parse(savedUser) : null;
}
function getTokenFromStorage() {
  return localStorage.getItem('token');
}

export const useUserStore = defineStore('user', () => {
  const user = ref(null);
  const token = ref(getTokenFromStorage() || null);
  const isAuthenticated = computed(() => !!token.value);

  function setUser(userData, userToken) {
    if (!userData || !userData.email) {
      logout();
      return;
    }
    user.value = userData;
    token.value = userToken;
    saveTokenToStorage(userToken);
    saveUserToStorage(userData);
  }

  function restore() {
    const savedUser = getUserFromStorage();
    const savedToken = getTokenFromStorage();
    if (savedToken) {
      token.value = savedToken;
      user.value = savedUser;
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
    removeTokenFromStorage();
    removeUserFromStorage();
  }

  async function fetchUser() {
    try {
      const response = await api.getUser();
      if (!response.data || !response.data.email) {
        await logout();
        return;
      }
      user.value = response.data;
      saveUserToStorage(response.data);
    } catch (e) {
      await logout();
    }
  }

  return { user, token, isAuthenticated, setUser, restore, logout, login, register, fetchUser };
});
