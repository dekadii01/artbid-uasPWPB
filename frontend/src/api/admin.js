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
 * Get all users list (admin only).
 */
export const getAdminUsers = () => {
  return api.get("/admin/users");
};

/**
 * Update user details/status (admin only).
 */
export const updateAdminUser = (id, data) => {
  return api.put(`/admin/users/${id}`, data);
};



