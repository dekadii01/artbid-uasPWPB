import api from "./axios";

/**
 * Get all bids for the authenticated user (grouped/unique per auction).
 */
export const getMyBids = (params = {}) => {
  return api.get("/my-bids", { params });
};

/**
 * Get the dashboard/stats data for the user's bids.
 */
export const getMyBidsDashboard = () => {
  return api.get("/my-bids/dashboard");
};
