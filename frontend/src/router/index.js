import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import LoginView from "../views/LoginView.vue";
import RegisterView from "../views/RegisterView.vue";
import AuctionIndexView from "../views/auction/Index.vue";
import AuctionShowView from "../views/auction/Show.vue";
import AuctionCreateView from "../views/auction/Create.vue";
import MyBidsView from "../views/mybids/Index.vue";

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
