import "./style.css";
import { createApp } from "vue";
import { createPinia } from "pinia";

import App from "./App.vue";
import router from "./router";
import { useAuthStore } from "./stores/auth";

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);

// ---------------------------------------------------------------------------
// PENTING: fetchUser() harus SELESAI sebelum router dipasang ke app.
// Kalau router dipasang dulu (app.use(router)), beforeEach akan jalan
// saat navigasi pertama dengan user masih null — meski token ada di
// localStorage — sehingga guard isAdmin/isLoggedIn memberikan hasil
// yang salah dan user kena redirect.
// ---------------------------------------------------------------------------
const auth = useAuthStore();

auth.fetchUser().finally(() => {
  // Router baru dipasang setelah state auth sudah settled (user ter-load
  // atau token invalid dan sudah di-clear). Dengan begini, beforeEach
  // selalu berjalan dengan state yang akurat.
  app.use(router);
  app.mount("#app");
});
