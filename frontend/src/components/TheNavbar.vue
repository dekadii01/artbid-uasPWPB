<script setup>
import { ref, onMounted, onUnmounted } from "vue";

const navLinks = [
  { label: "Beranda", href: "#home" },
  { label: "Lelang Aktif", href: "#auctions" },
  { label: "Cara Kerja", href: "#how-it-works" },
  { label: "Jual Karya", href: "#sellers" },
  { label: "FAQ", href: "#faq" },
];

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

function handleScroll() {
  isScrolled.value = window.scrollY > 20;
}

function toggleMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
}

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});
</script>

<template>
  <nav
    :class="[
      'nav-bar fixed top-0 left-0 right-0 z-50 transition-all duration-300 px-10',
      { 'nav-scrolled': isScrolled },
    ]"
  >
    <!-- Desktop Nav -->
    <div class="hidden md:flex items-center justify-between px-10 py-6">
      <router-link
        to="/"
        class="text-2xl font-bold nav-logo transition-colors duration-300 tracking-tight"
      >
        ArtBid<span class="font-light">Bali</span>
      </router-link>
      <ul class="flex space-x-6">
        <li v-for="link in navLinks" :key="link.href">
          <router-link
            :to="{ path: '/', hash: link.href }"
            class="nav-link transition-colors duration-300 text-gray-600 hover:text-gray-900 text-sm"
          >
            {{ link.label }}
          </router-link>
        </li>
      </ul>
      <div class="flex gap-x-3">
        <router-link
          to="/login"
          class="nav-login px-4 py-2 transition-colors duration-300 text-gray-800 hover:text-black text-sm flex items-center justify-center"
        >
          Masuk
        </router-link>
        <router-link
          to="/register"
          class="nav-signup px-6 py-3 bg-black text-white rounded-lg hover:bg-gray-800 transition-all duration-300 text-sm flex items-center justify-center"
        >
          Daftar Gratis
        </router-link>
      </div>
    </div>

    <!-- Mobile Nav Header -->
    <div
      class="md:hidden px-6 py-5 flex items-center justify-between bg-white/90 backdrop-blur-sm"
    >
      <router-link
        to="/"
        class="text-xl font-bold tracking-tight text-gray-900"
      >
        ArtBid<span class="font-light">Bali</span>
      </router-link>
      <button
        class="flex flex-col gap-1.5 p-1 cursor-pointer"
        @click="toggleMenu"
      >
        <span class="w-6 h-0.5 bg-gray-800 transition-all duration-300"></span>
        <span class="w-6 h-0.5 bg-gray-800 transition-all duration-300"></span>
        <span class="w-4 h-0.5 bg-gray-800 transition-all duration-300"></span>
      </button>
    </div>

    <!-- Mobile Menu -->
    <div
      :class="[
        'mobile-menu md:hidden bg-white/95 backdrop-blur-sm px-6 pb-6',
        { open: isMobileMenuOpen },
      ]"
    >
      <ul class="flex flex-col space-y-4 pt-2">
        <li v-for="link in navLinks" :key="link.href">
          <router-link
            :to="{ path: '/', hash: link.href }"
            class="text-gray-600 hover:text-gray-900 block py-1 text-sm"
            @click="toggleMenu"
          >
            {{ link.label }}
          </router-link>
        </li>
      </ul>
      <div class="flex gap-3 mt-5">
        <router-link
          to="/login"
          class="px-4 py-2 text-gray-800 border border-gray-200 rounded-lg flex-1 text-sm text-center"
          @click="toggleMenu"
          >Masuk</router-link
        >
        <router-link
          to="/register"
          class="px-4 py-2 bg-black text-white rounded-lg flex-1 text-sm text-center"
          @click="toggleMenu"
          >Daftar</router-link
        >
      </div>
    </div>
  </nav>
</template>
