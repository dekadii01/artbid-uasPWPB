<script setup>
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

onMounted(async () => {
  const token = route.query.token;

  if (token) {
    try {
      // Simpan token ke local storage & pinia store
      authStore.saveToken(token);
      
      // Ambil profile user terbaru
      await authStore.fetchUser();

      // Redirect berdasarkan role
      if (authStore.isAdmin) {
        router.replace("/admin/dashboard");
      } else {
        router.replace("/auctions");
      }
    } catch (err) {
      console.error("Gagal melakukan login via Google:", err);
      router.replace("/login?error=oauth_failed");
    }
  } else {
    router.replace("/login?error=no_token");
  }
});
</script>

<template>
  <div class="min-h-screen bg-white flex flex-col items-center justify-center font-sans p-6">
    <div class="w-16 h-16 relative mb-6">
      <!-- Loading Spinner -->
      <div class="absolute inset-0 border-4 border-gray-100 rounded-full"></div>
      <div class="absolute inset-0 border-4 border-black border-t-transparent rounded-full animate-spin"></div>
    </div>
    
    <h2 class="text-xl font-bold text-black mb-1">Mencocokkan Kredensial</h2>
    <p class="text-gray-400 text-sm">Menghubungkan dengan Google, mohon tunggu sebentar...</p>
  </div>
</template>

<style scoped>
.font-sans {
  font-family: "DM Sans", sans-serif;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
.animate-spin {
  animation: spin 0.8s linear infinite;
}
</style>
