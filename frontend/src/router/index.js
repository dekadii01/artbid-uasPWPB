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
    meta: { layout: "default" },
  },
  {
    path: "/register",
    name: "Register",
    component: RegisterView,
    meta: { layout: "default" },
  },
  {
    path: "/auctions",
    name: "Auctions",
    component: AuctionIndexView,
    meta: { layout: "app" },
  },
  {
    path: "/auction/:id",
    name: "AuctionShow",
    component: AuctionShowView,
    meta: { layout: "app" },
  },
  {
    path: "/auction/create",
    name: "AuctionCreate",
    component: AuctionCreateView,
    meta: { layout: "app" },
  },
  {
    path: "/my-bids",
    name: "MyBids",
    component: MyBidsView,
    meta: { layout: "app" },
  },
  {
    path: "/watchlist",
    name: "Watchlist",
    component: WatchlistView,
    meta: { layout: "app" },
  },
  {
    path: "/my-auctions",
    name: "MyAuctions",
    component: MyAuctionsView,
    meta: { layout: "app" },
  },
  {
    path: "/admin",
    component: AdminLayout,
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
        path: "users",
        name: "AdminUsers",
        component: AdminUsersView,
        meta: { layout: "admin" },
      },
      {
        path: "categories",
        name: "AdminCategories",
        component: AdminCategoriesView,
        meta: { layout: "admin" },
      },
    ],
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

export default router;
