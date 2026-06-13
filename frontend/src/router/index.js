import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import LoginView from "../views/LoginView.vue";
import RegisterView from "../views/RegisterView.vue";
import AuctionIndexView from "../views/auction/Index.vue";

const routes = [
  {
    path: "/",
    name: "Home",
    component: HomeView,
    meta: { layout: "default" }, // ← landing page, navbar transparan
  },
  {
    path: "/login",
    name: "Login",
    component: LoginView,
    meta: { layout: "default" }, // ← auth page, tanpa AppNavbar
  },
  {
    path: "/register",
    name: "Register",
    component: RegisterView,
    meta: { layout: "default" }, // ← auth page, tanpa AppNavbar
  },
  {
    path: "/auctions",
    name: "Auctions",
    component: AuctionIndexView,
    meta: { layout: "app" }, // ← pakai AppNavbar putih
  },
  // Tambah route baru di sini dengan meta: { layout: "app" }
  // {
  //   path: "/my-bids",
  //   name: "MyBids",
  //   component: MyBidsView,
  //   meta: { layout: "app" },
  // },
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
