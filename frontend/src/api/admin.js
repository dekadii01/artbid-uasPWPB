import api from "./axios";

/**
 * Get admin dashboard data.
 */
export const getAdminDashboard = () => {
  return api.get("/admin/dashboard");
};

/**
 * Get admin auctions listing.
 */
export const getAdminAuctions = () => {
  return api.get("/admin/auctions");
};

/**
 * Activate a scheduled auction.
 */
export const activateAuction = (id) => {
  return api.patch(`/admin/auctions/${id}/activate`);
};

/**
 * Force end an active auction.
 */
export const forceEndAuction = (id) => {
  return api.patch(`/admin/auctions/${id}/end`);
};

/**
 * Delete any auction (admin power).
 */
export const deleteAdminAuction = (id) => {
  return api.delete(`/admin/auctions/${id}`);
};

/**
 * Get bids list of an auction.
 */
export const getAuctionBidsAdmin = (id) => {
  return api.get(`/auctions/${id}/bids`);
};

/**
 * Get single admin auction detail.
 */
export const getAdminAuction = (id) => {
  return api.get(`/admin/auctions/${id}`);
};

/**
 * Get admin report statistics based on period and custom dates.
 */
export const getAdminReports = (params) => {
  return api.get("/admin/reports", { params });
};

/**
 * Export admin report as CSV blob.
 */
export const exportAdminReport = (params) => {
  return api.get("/admin/reports/export", { params, responseType: "blob" });
};



