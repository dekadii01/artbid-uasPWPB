import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? "http://localhost:8000/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

// ---------------------------------------------------------------------------
// Endpoint yang TIDAK boleh di-intercept saat 401.
// Login & register memang bisa return 401/422 — itu flow normal, bukan
// berarti sesi expired. fetchUser (/profile) juga dihandle sendiri di store.
// ---------------------------------------------------------------------------
const AUTH_WHITELIST = ["/auth/login", "/auth/register", "/profile"];

// ---------------------------------------------------------------------------
// Request interceptor — sisipkan token ke setiap request secara otomatis
// ---------------------------------------------------------------------------
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token");

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

// ---------------------------------------------------------------------------
// Response interceptor — tangani error global
// ---------------------------------------------------------------------------
api.interceptors.response.use(
  (response) => response,

  (error) => {
    const status = error.response?.status;
    const requestUrl = error.config?.url ?? "";

    // Cek apakah URL request ini masuk whitelist
    const isWhitelisted = AUTH_WHITELIST.some((path) =>
      requestUrl.endsWith(path),
    );

    if (status === 401 && !isWhitelisted) {
      // Token expired atau tidak valid di endpoint yang butuh auth —
      // paksa logout. Tapi jangan pakai window.location.href agar
      // Pinia store sempat di-clear dengan benar.
      localStorage.removeItem("auth_token");

      // Hindari redirect loop jika sudah di halaman login
      if (!window.location.pathname.startsWith("/login")) {
        window.location.href = "/login";
      }
    }

    if (status === 403) {
      console.warn("Akses ditolak:", error.response?.data?.message);
    }

    return Promise.reject(error);
  },
);

export default api;
