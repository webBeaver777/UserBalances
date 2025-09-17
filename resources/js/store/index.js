import { defineStore } from 'pinia';
import axios from 'axios';

export const useUserStore = defineStore('user', {
    state: () => ({ user: null }),
    actions: {
        async fetchUser() {
            const { data } = await axios.get('/api/me', { withCredentials: true });
            this.user = data;
        },
        async logout() {
            await axios.post('/api/logout', {}, { withCredentials: true });
            this.user = null;
        }
    }
});

