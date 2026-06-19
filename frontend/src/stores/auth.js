import { defineStore } from "pinia";
import { computed, ref } from "vue";
import * as authApi from "../api/auth";

export const useAuthStore = defineStore("auth", () => {
  // -----------------------------------------------------------------------
  // State
  // -----------------------------------------------------------------------

  const user = ref(null);
  const token = ref(localStorage.getItem("auth_token") ?? null);

  // -----------------------------------------------------------------------
  // Getters
  // -----------------------------------------------------------------------

  const isLoggedIn = computed(() => !!token.value);

  const isAdmin = computed(() => user.value?.role === "admin");

  // Seller & buyer ditentukan dari aktivitas, bukan role —
  // backend sudah handle ini, frontend cukup cek dari data yang dikembalikan API
  const isSeller = computed(() => user.value?.auctions_count > 0);
  const isBuyer = computed(() => user.value?.bids_count > 0);

  // -----------------------------------------------------------------------
  // Helpers internal
  // -----------------------------------------------------------------------

  function saveToken(newToken) {
    token.value = newToken;
    localStorage.setItem("auth_token", newToken);
  }

  function clearSession() {
    user.value = null;
    token.value = null;
    localStorage.removeItem("auth_token");
  }

  // -----------------------------------------------------------------------
  // Actions
  // -----------------------------------------------------------------------

  /**
   * Registrasi user baru.
   * Setelah berhasil, langsung simpan token & data user.
   */
  async function register(form) {
    const { data } = await authApi.register(form);

    saveToken(data.token);
    user.value = data.user;

    return data;
  }

  /**
   * Login dengan email & password.
   */
  async function login(form) {
    const { data } = await authApi.login(form);

    saveToken(data.token);
    user.value = data.user;

    return data;
  }

  /**
   * Logout — cabut token di server lalu bersihkan state lokal.
   */
  async function logout() {
    try {
      await authApi.logout();
    } finally {
      // Tetap clear session meskipun request gagal
      clearSession();
    }
  }

  /**
   * Ambil ulang data user dari server.
   * Dipanggil saat app pertama kali dimuat (jika token masih ada).
   */
  async function fetchUser() {
    if (!token.value) return;

    try {
      const { data } = await authApi.getProfile();
      user.value = data.user;
    } catch {
      // Token tidak valid — paksa logout
      clearSession();
    }
  }

  return {
    // state
    user,
    token,
    // getters
    isLoggedIn,
    isAdmin,
    isSeller,
    isBuyer,
    // actions
    register,
    login,
    logout,
    fetchUser,
    saveToken,
  };
});
