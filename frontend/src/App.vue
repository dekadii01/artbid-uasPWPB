<template>
  <component :is="currentLayout">
    <router-view />
  </component>
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
};

const currentLayout = computed(
  () => layouts[route.meta.layout] ?? DefaultLayout,
);
</script>
