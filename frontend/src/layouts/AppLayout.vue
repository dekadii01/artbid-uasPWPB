<template>
  <div class="min-h-screen bg-white">
    <AppNavbar />
    <main class="pt-20">
      <slot />
    </main>
    <Footer />
  </div>
</template>

<script setup>
import AppNavbar from "../components/AppNavbar.vue";
import Footer from "../components/Thefooter.vue";
</script>
