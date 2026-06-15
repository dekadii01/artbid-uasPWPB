<template>
  <component :is="currentLayout" v-if="currentLayout">
    <router-view />
  </component>
  <router-view v-else />
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import DefaultLayout from "./layouts/DefaultLayout.vue";
import AppLayout from "./layouts/AppLayout.vue";

const route = useRoute();

const layouts = {
  default: DefaultLayout,
  app: AppLayout,
  // 'admin' sengaja tidak didaftarkan di sini
  // karena AdminLayout sudah jadi component di router
};

const currentLayout = computed(() => layouts[route.meta.layout] ?? null);
</script>
