import "./style.css";
import { createApp } from "vue";
import { createPinia } from "pinia";

import App from "./App.vue";
import router from "./router";
import { useAuthStore } from "./stores/auth";

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// ---------------------------------------------------------------------------
// Saat app pertama kali dimuat, coba pulihkan sesi dari token yang tersimpan.
// Ini penting agar user tidak terlogout setelah refresh halaman.
// ---------------------------------------------------------------------------

const auth = useAuthStore();

auth.fetchUser().finally(() => {
  app.mount("#app");
});
