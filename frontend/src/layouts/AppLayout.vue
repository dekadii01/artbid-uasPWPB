<template>
  <div class="min-h-screen bg-white">
    <AppNavbar v-if="!hideNavbar" />
    <main :class="hideNavbar ? 'pt-0' : 'pt-16'">
      <slot />
    </main>
    <Footer v-if="!hideFooter" />
  </div>
</template>

<script setup>
import { useRoute } from "vue-router";
import AppNavbar from "../components/Appnavbar.vue";
import Footer from "../components/TheFooter.vue";
import { computed } from "vue";

const route = useRoute();

const hideNavbar = computed(() => {
  return ["/admin/dashboard"].includes(route.path);
});

const hideFooter = computed(() => {
  return ["/admin/dashboard"].includes(route.path);
});
</script>
