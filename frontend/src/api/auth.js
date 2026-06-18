import api from "./axios";

/**
 * Registrasi user baru.
 *
 * @param {{ name: string, email: string, password: string, password_confirmation: string }} data
 */
export const register = (data) => {
  return api.post("/auth/register", data);
};

/**
 * Login dan dapatkan token Sanctum.
 *
 * @param {{ email: string, password: string }} data
 */
export const login = (data) => {
  return api.post("/auth/login", data);
};

/**
 * Logout dan cabut token aktif di server.
 */
export const logout = () => {
  return api.post("/auth/logout");
};

/**
 * Ambil data profil user yang sedang login.
 */
export const getProfile = () => {
  return api.get("/profile");
};

/**
 * Update data profil user yang sedang login.
 * Menggunakan FormData karena mendukung pengunggahan file (avatar).
 * Menggunakan POST dengan _method = 'PUT' (spoofing) agar ditangani Laravel sebagai PUT request.
 *
 * @param {FormData} data
 */
export const updateProfile = (data) => {
  return api.post("/profile", data, {
    headers: {
      "Content-Type": "multipart/form-data",
    },
  });
};
