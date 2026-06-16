import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? "http://localhost:8000/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

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

    // Token expired atau tidak valid — paksa logout
    if (status === 401) {
      localStorage.removeItem("auth_token");
      window.location.href = "/login";
    }

    // Forbidden — user tidak punya akses
    if (status === 403) {
      console.warn("Akses ditolak:", error.response?.data?.message);
    }

    // Lempar error agar bisa ditangani di masing-masing pemanggil
    return Promise.reject(error);
  },
);

export default api;
