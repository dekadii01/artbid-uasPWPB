import api from "./axios";

/**
 * Daftar lelang aktif (publik).
 */
export const getAuctions = (params = {}) => {
  return api.get("/auctions", { params });
};

/**
 * Detail satu lelang (publik).
 */
export const getAuction = (id) => {
  return api.get(`/auctions/${id}`);
};

/**
 * Buat lelang baru. Wajib FormData karena ada file (main_photo, extra_photos).
 *
 * @param {FormData} formData
 */
export const createAuction = (formData) => {
  return api.post("/auctions", formData, {
    headers: { "Content-Type": "multipart/form-data" },
  });
};

/**
 * Lelang milik seller yang sedang login.
 */
export const getMyAuctions = (params = {}) => {
  return api.get("/my-auctions", { params });
};

/**
 * Dashboard data untuk seller yang sedang login.
 */
export const getMyAuctionsDashboard = () => {
  return api.get("/my-auctions/dashboard");
};

/**
 * Hapus lelang milik seller (hanya jika scheduled).
 */
export const deleteAuction = (id) => {
  return api.delete(`/auctions/${id}`);
};

/**
 * Tambah foto ke lelang yang sudah ada.
 *
 * @param {number} auctionId
 * @param {FormData} formData - field 'photos[]'
 */
export const addAuctionImages = (auctionId, formData) => {
  return api.post(`/auctions/${auctionId}/images`, formData, {
    headers: { "Content-Type": "multipart/form-data" },
  });
};

/**
 * Hapus satu foto dari lelang.
 */
export const deleteAuctionImage = (auctionId, imageId) => {
  return api.delete(`/auctions/${auctionId}/images/${imageId}`);
};

/**
 * Ajukan penawaran (bid).
 */
export const placeBid = (auctionId, amount) => {
  return api.post(`/auctions/${auctionId}/bids`, { amount });
};

/**
 * Lakukan Buy Now untuk memenangkan lelang instan.
 */
export const buyNow = (auctionId) => {
  return api.post(`/auctions/${auctionId}/buy-now`);
};

/**
 * Toggle watchlist (tambah / hapus).
 */
export const toggleWatchlist = (auctionId) => {
  return api.post(`/auctions/${auctionId}/watchlist`);
};

/**
 * Ambil daftar watchlist user saat ini.
 */
export const getWatchlist = (params = {}) => {
  return api.get("/watchlist", { params });
};

/**
 * Update lelang yang sudah ada.
 */
export const updateAuction = (id, data) => {
  return api.put(`/auctions/${id}`, data);
};

