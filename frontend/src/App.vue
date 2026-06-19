<template>
  <component :is="currentLayout" v-if="currentLayout">
    <router-view />
  </component>
  <router-view v-else />
</template>

<script setup>
import { computed, watch, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "./stores/auth";
import { getEcho } from "./api/echo";
import DefaultLayout from "./layouts/DefaultLayout.vue";
import AppLayout from "./layouts/AppLayout.vue";

const route = useRoute();
const authStore = useAuthStore();

const layouts = {
  default: DefaultLayout,
  app: AppLayout,
  // 'admin' sengaja tidak didaftarkan di sini
  // karena AdminLayout sudah jadi component di router
};

const currentLayout = computed(() => layouts[route.meta.layout] ?? null);

// Manage online presence channel via Laravel Reverb
let onlineChannel = null;

function syncOnlineStatus() {
  const hasToken = !!localStorage.getItem("auth_token");
  if (hasToken && authStore.user) {
    const echo = getEcho();
    if (echo && !onlineChannel) {
      onlineChannel = echo.join("online");
    }
  } else {
    if (onlineChannel) {
      const echo = getEcho();
      if (echo) {
        echo.leave("online");
      }
      onlineChannel = null;
    }
  }
}

// Watch token & user to join/leave online channel
watch(() => [authStore.token, authStore.user], syncOnlineStatus, { immediate: true });

onUnmounted(() => {
  if (onlineChannel) {
    const echo = getEcho();
    if (echo) {
      echo.leave("online");
    }
  }
});
</script>
