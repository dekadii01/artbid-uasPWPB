import { useAuthStore } from "../stores/auth";
import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import LoginView from "../views/LoginView.vue";
import RegisterView from "../views/RegisterView.vue";
import AuctionIndexView from "../views/auction/Index.vue";
import AuctionShowView from "../views/auction/Show.vue";
import AuctionCreateView from "../views/auction/Create.vue";
import MyBidsView from "../views/mybids/Index.vue";
import WatchlistView from "../views/watchlist/Index.vue";
import MyAuctionsView from "../views/myauctions/Index.vue";
import AdminDashboardView from "../views/admin/Admindashboard.vue";
import AdminLayout from "../layouts/AdminLayout.vue";
import AdminauctionsindexView from "../views/admin/auctions/Index.vue";
import AdminUsersView from "../views/admin/users/Index.vue";
import AdminCategoriesView from "../views/admin/categories/Index.vue";
import AdminAuctionShowView from "../views/admin/auctions/Show.vue";
import AdminUsersShowView from "../views/admin/users/Show.vue";
import AdminReportsView from "../views/admin/reports/Index.vue";
import AdminSettingsView from "../views/admin/settings/Index.vue";
import ProfileView from "../views/profile/Index.vue";
import AuctionEditView from "../views/auction/Edit.vue";

const routes = [
  {
    path: "/",
    name: "Home",
    component: HomeView,
    meta: { layout: "default" },
  },
  {
    path: "/login",
    name: "Login",
    component: LoginView,
    meta: { layout: "default", guestOnly: true },
  },
  {
    path: "/register",
    name: "Register",
    component: RegisterView,
    meta: { layout: "default", guestOnly: true },
  },
  {
    path: "/auth/google/callback",
    name: "GoogleCallback",
    component: () => import("../views/auth/GoogleCallback.vue"),
    meta: { layout: "default", guestOnly: true },
  },
  {
    path: "/auctions",
    name: "Auctions",
    component: AuctionIndexView,
    meta: { layout: "app", requiresAuth: true },
  },
  {
    path: "/auction/:id",
    name: "AuctionShow",
    component: AuctionShowView,
    meta: { layout: "app", requiresAuth: true },
  },
  {
    path: "/auction/create",
    name: "AuctionCreate",
    component: AuctionCreateView,
    meta: { layout: "app", requiresAuth: true },
  },
  {
    path: "/auction/edit/:id",
    name: "AuctionEdit",
    component: AuctionEditView,
    meta: { layout: "app", requiresAuth: true },
  },
  {
    path: "/my-bids",
    name: "MyBids",
    component: MyBidsView,
    meta: { layout: "app", requiresAuth: true },
  },
  {
    path: "/watchlist",
    name: "Watchlist",
    component: WatchlistView,
    meta: { layout: "app", requiresAuth: true },
  },
  {
    path: "/my-auctions",
    name: "MyAuctions",
    component: MyAuctionsView,
    meta: { layout: "app", requiresAuth: true },
  },
  {
    path: "/profile",
    name: "Profile",
    component: ProfileView,
    meta: { layout: "app", requiresAuth: true },
  },
  {
    path: "/admin",
    component: AdminLayout,
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: "dashboard",
        name: "AdminDashboard",
        component: AdminDashboardView,
        meta: { layout: "admin" },
      },
      {
        path: "auctions",
        name: "AdminAuctions",
        component: AdminauctionsindexView,
        meta: { layout: "admin" },
      },
      {
        path: "auctions/:id",
        name: "AdminAuctionShow",
        component: AdminAuctionShowView,
        meta: { layout: "admin" },
      },
      {
        path: "users",
        name: "AdminUsers",
        component: AdminUsersView,
        meta: { layout: "admin" },
      },
      {
        path: "users/:id",
        name: "AdminUsersShow",
        component: AdminUsersShowView,
        meta: { layout: "admin" },
      },
      {
        path: "categories",
        name: "AdminCategories",
        component: AdminCategoriesView,
        meta: { layout: "admin" },
      },
      {
        path: "reports",
        name: "AdminReports",
        component: AdminReportsView,
        meta: { layout: "admin" },
      },
      {
        path: "settings",
        name: "AdminSettings",
        component: AdminSettingsView,
        meta: { layout: "admin" },
      },
    ],
  },

  {
    path: "/:pathMatch(.*)*",
    redirect: "/",
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) {
      return new Promise((resolve) => {
        setTimeout(() => {
          resolve({ el: to.hash, behavior: "smooth" });
        }, 150);
      });
    }
    if (savedPosition) return savedPosition;
    return { top: 0 };
  },
});

// ---------------------------------------------------------------------------
// Navigation guard
//
// Dengan fix di main.js (router dipasang SETELAH fetchUser selesai),
// guard ini selalu berjalan dengan state auth yang sudah akurat.
//
// Namun kita tetap tambahkan safety check: jika token ada tapi user
// belum ter-load (race condition edge case), tunggu fetchUser selesai
// sebelum memutuskan redirect. Ini mencegah bug saat ada deep-link
// yang diakses langsung via URL.
// ---------------------------------------------------------------------------

router.beforeEach(async (to) => {
  const auth = useAuthStore();

  // Safety net: token ada tapi user belum ter-load
  // (edge case jika ada navigasi yang terjadi sebelum fetchUser selesai)
  if (auth.token && !auth.user) {
    await auth.fetchUser();
  }

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    // Simpan tujuan asal agar setelah login bisa redirect ke sana
    return { name: "Login", query: { redirect: to.fullPath } };
  }

  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return { name: "Home" };
  }

  if (to.meta.guestOnly && auth.isLoggedIn) {
    return { name: "Home" };
  }
});

export default router;
