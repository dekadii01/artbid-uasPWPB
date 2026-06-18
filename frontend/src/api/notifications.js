import api from "./axios";

/**
 * Ambil daftar notifikasi untuk user saat ini.
 */
export const getNotifications = (params = {}) => {
  return api.get("/notifications", { params });
};

/**
 * Tandai satu notifikasi sebagai dibaca.
 */
export const markAsRead = (id) => {
  return api.patch(`/notifications/${id}/read`);
};

/**
 * Tandai semua notifikasi sebagai dibaca.
 */
export const markAllAsRead = () => {
  return api.patch("/notifications/read-all");
};
