<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";

const router = useRouter();
const auth = useAuthStore();

const navLinks = [
  { label: "Beranda", href: "#home" },
  { label: "Lelang Aktif", href: "#auctions" },
  { label: "Cara Kerja", href: "#how-it-works" },
  { label: "Jual Karya", href: "#sellers" },
  { label: "FAQ", href: "#faq" },
];

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

// ─── Auth state ──────────────────────────────────────────────
const isLoggedIn = computed(() => auth.isLoggedIn);
const userName = computed(() => auth.user?.full_name ?? "");
const userEmail = computed(() => auth.user?.email ?? "");
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());
const isAdmin = computed(() => auth.isAdmin);

const dropdownItems = computed(() => {
  if (isAdmin.value) {
    return [
      {
        label: "Admin Dashboard",
        to: "/admin/dashboard",
        icon: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
      },
    ];
  }

  return [
    {
      label: "Lelang Saya",
      to: "/my-auctions",
      icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
    },
    {
      label: "Penawaran Saya",
      to: "/my-bids",
      icon: "M13 10V3L4 14h7v7l9-11h-7z",
    },
    {
      label: "Watchlist",
      to: "/watchlist",
      icon: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z",
    },
  ];
});
// ─── Handlers ────────────────────────────────────────────────
function handleScroll() {
  isScrolled.value = window.scrollY > 20;
}

function toggleMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
}

function toggleDropdown() {
  isDropdownOpen.value = !isDropdownOpen.value;
}

function closeDropdown() {
  isDropdownOpen.value = false;
}

// Tutup dropdown saat klik di luar
function handleClickOutside(e) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    closeDropdown();
  }
}

async function handleLogout() {
  closeDropdown();
  isMobileMenuOpen.value = false;
  await auth.logout();
  router.push("/login");
}

function navigateTo(to) {
  closeDropdown();
  isMobileMenuOpen.value = false;
  router.push(to);
}

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
  document.addEventListener("mousedown", handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
  document.removeEventListener("mousedown", handleClickOutside);
});
</script>

<template>
  <nav
    :class="[
      'nav-bar fixed top-0 left-0 right-0 z-50 transition-all duration-300 px-10',
      { 'nav-scrolled': isScrolled },
    ]"
  >
    <!-- ── Desktop Nav ── -->
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

      <!-- Guest buttons -->
      <div v-if="!isLoggedIn" class="flex gap-x-3">
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

      <!-- Logged-in user dropdown -->
      <div v-else class="relative" ref="dropdownRef">
        <button
          @click="toggleDropdown"
          class="flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-xl border border-gray-200 hover:border-gray-400 transition-all duration-200 bg-white"
        >
          <!-- Avatar -->
          <div
            class="w-8 h-8 rounded-lg bg-gray-900 flex items-center justify-center text-white text-xs font-bold shrink-0"
          >
            {{ userInitial }}
          </div>
          <!-- Name -->
          <span class="text-sm font-medium text-gray-800 max-w-28 truncate">{{
            userName
          }}</span>
          <!-- Chevron -->
          <svg
            class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200"
            :class="{ 'rotate-180': isDropdownOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 9l-7 7-7-7"
            />
          </svg>
        </button>

        <!-- Dropdown panel -->
        <transition name="dropdown">
          <div
            v-if="isDropdownOpen"
            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl border border-gray-100 shadow-xl shadow-black/5 overflow-hidden py-1"
          >
            <!-- User info header -->
            <div class="px-4 py-3 border-b border-gray-100">
              <p class="text-sm font-semibold text-black truncate">
                {{ userName }}
              </p>
              <p class="text-xs text-gray-400 mt-0.5 truncate">
                {{ userEmail }}
              </p>
              <span
                v-if="isAdmin"
                class="inline-block mt-1.5 text-xs bg-black text-white px-2 py-0.5 rounded-full font-medium"
                >Admin</span
              >
            </div>

            <!-- Menu items -->
            <div class="py-1">
              <button
                v-for="item in dropdownItems"
                :key="item.to"
                @click="navigateTo(item.to)"
                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-colors text-left"
              >
                <svg
                  class="w-4 h-4 text-gray-400 shrink-0"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    :d="item.icon"
                  />
                </svg>
                {{ item.label }}
              </button>
            </div>

            <!-- Logout -->
            <div class="border-t border-gray-100 py-1">
              <button
                @click="handleLogout"
                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-500 hover:bg-gray-50 hover:text-black transition-colors text-left"
              >
                <svg
                  class="w-4 h-4 text-gray-400 shrink-0"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                  />
                </svg>
                Keluar
              </button>
            </div>
          </div>
        </transition>
      </div>
    </div>

    <!-- ── Mobile Nav Header ── -->
    <div
      class="md:hidden px-6 py-5 flex items-center justify-between bg-white/90 backdrop-blur-sm"
    >
      <router-link
        to="/"
        class="text-xl font-bold tracking-tight text-gray-900"
      >
        ArtBid<span class="font-light">Bali</span>
      </router-link>

      <!-- Mobile: avatar inisial kalau login -->
      <div class="flex items-center gap-3">
        <div
          v-if="isLoggedIn"
          class="w-8 h-8 rounded-lg bg-gray-900 flex items-center justify-center text-white text-xs font-bold"
        >
          {{ userInitial }}
        </div>
        <button
          class="flex flex-col gap-1.5 p-1 cursor-pointer"
          @click="toggleMenu"
        >
          <span
            class="w-6 h-0.5 bg-gray-800 transition-all duration-300"
          ></span>
          <span
            class="w-6 h-0.5 bg-gray-800 transition-all duration-300"
          ></span>
          <span
            class="w-4 h-0.5 bg-gray-800 transition-all duration-300"
          ></span>
        </button>
      </div>
    </div>

    <!-- ── Mobile Menu ── -->
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

      <!-- Mobile: guest -->
      <div v-if="!isLoggedIn" class="flex gap-3 mt-5">
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

      <!-- Mobile: logged-in -->
      <div v-else class="mt-5 border-t border-gray-100 pt-4 space-y-1">
        <!-- User info -->
        <div class="flex items-center gap-3 pb-3">
          <div
            class="w-9 h-9 rounded-xl bg-gray-900 flex items-center justify-center text-white text-sm font-bold shrink-0"
          >
            {{ userInitial }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-black truncate">
              {{ userName }}
            </p>
            <p class="text-xs text-gray-400 truncate">{{ userEmail }}</p>
          </div>
        </div>

        <!-- Mobile menu items -->
        <button
          v-for="item in dropdownItems"
          :key="item.to"
          @click="navigateTo(item.to)"
          class="w-full flex items-center gap-3 py-2.5 text-sm text-gray-600 hover:text-black transition-colors text-left"
        >
          <svg
            class="w-4 h-4 text-gray-400 shrink-0"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="item.icon"
            />
          </svg>
          {{ item.label }}
        </button>

        <!-- Mobile logout -->
        <button
          @click="handleLogout"
          class="w-full flex items-center gap-3 py-2.5 text-sm text-gray-400 hover:text-black transition-colors text-left border-t border-gray-100 mt-1 pt-3"
        >
          <svg
            class="w-4 h-4 shrink-0"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
            />
          </svg>
          Keluar
        </button>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.dropdown-enter-active {
  transition: all 0.18s ease;
}
.dropdown-leave-active {
  transition: all 0.14s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(0.97);
}
</style>
