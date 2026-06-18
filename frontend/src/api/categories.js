import api from "./axios";

/**
 * ambil daftar kategori lelang.
 *
 * @param {object} params - Parameter query opsional
 * @returns {Promise} Response dari API
 */
export const getCategories = (params = {}) => {
  return api.get("/categories", { params });
};

/**
 * ambil detail kategori lelang.
 *
 * @param {number|string} id - ID kategori
 * @returns {Promise} Response dari API
 */
export const getCategory = (id) => {
  return api.get(`/categories/${id}`);
};

/**
 * Menambahkan kategori lelang baru (Hanya Admin).
 * Menggunakan FormData karena mendukung upload berkas gambar.
 *
 * @param {FormData} formData - Data kategori beserta berkas gambar
 * @returns {Promise} Response dari API
 */
export const createCategory = (formData) => {
  return api.post("/admin/categories", formData, {
    headers: { "Content-Type": "multipart/form-data" },
  });
};

/**
 * Memperbarui kategori lelang yang sudah ada (Hanya Admin).
 * Menggunakan FormData karena mendukung upload berkas gambar.
 * Di Laravel, jika ada upload berkas gambar saat pembaruan data,
 * disarankan menggunakan metode POST dengan ditambahkan field `_method = PUT`.
 *
 * @param {number|string} id - ID kategori yang diubah
 * @param {FormData} formData - Data kategori baru beserta berkas gambar
 * @returns {Promise} Response dari API
 */
export const updateCategory = (id, formData) => {
  // Tambahkan spoofing method PUT agar Laravel dapat membaca file upload pada request update
  if (formData instanceof FormData && !formData.has("_method")) {
    formData.append("_method", "PUT");
  }
  return api.post(`/admin/categories/${id}`, formData, {
    headers: { "Content-Type": "multipart/form-data" },
  });
};

/**
 * Menghapus kategori lelang (Hanya Admin).
 *
 * @param {number|string} id - ID kategori yang akan dihapus
 * @returns {Promise} Response dari API
 */
export const deleteCategory = (id) => {
  return api.delete(`/admin/categories/${id}`);
};
