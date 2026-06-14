<template>
  <TheNavbar v-if="!hideNavbar" />

  <slot />

  <TheFooter v-if="!hideFooter" />
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";

import TheNavbar from "../components/TheNavbar.vue";
import TheFooter from "../components/TheFooter.vue";

const route = useRoute();

const hideNavbar = computed(() => {
  return ["/login", "/register"].includes(route.path);
});

const hideFooter = computed(() => {
  return ["/login", "/register"].includes(route.path);
});
</script>
