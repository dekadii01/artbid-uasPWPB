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
