import api from "./axios";

/**
 * Get admin dashboard data.
 */
export const getAdminDashboard = () => {
  return api.get("/admin/dashboard");
};
