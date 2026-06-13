<template>
  <div class="min-h-screen bg-white">
    <AppNavbar />
    <!-- pt-[73px] = tinggi navbar (py-4 + line-height teks ~41px + border) -->
    <main class="pt-20">
      <slot />
    </main>
  </div>
</template>

<script setup>
import AppNavbar from "../components/AppNavbar.vue";
</script>
