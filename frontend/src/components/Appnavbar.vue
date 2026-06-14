<template>
  <nav
    class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-100"
  >
    <div class="hidden md:flex items-center justify-between px-10 py-4">
      <!-- Logo -->
      <router-link to="/" class="text-xl font-bold tracking-tight text-black">
        ArtBid<span class="font-light">Bali</span>
      </router-link>

      <!-- Nav links -->
      <ul class="flex items-center gap-1">
        <li v-for="link in navLinks" :key="link.to">
          <router-link
            :to="link.to"
            class="px-4 py-2 rounded-lg text-sm text-gray-500 hover:text-black hover:bg-gray-50 transition-all duration-200"
            active-class="text-black bg-gray-100 font-medium"
          >
            {{ link.label }}
          </router-link>
        </li>
      </ul>

      <!-- Right actions -->
      <div class="flex items-center gap-3">
        <!-- Notifications -->
        <button
          class="relative w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:border-black hover:text-black transition-colors"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            />
          </svg>
          <!-- Unread dot -->
          <span
            class="absolute top-1.5 right-1.5 w-2 h-2 bg-black rounded-full border-2 border-white"
          ></span>
        </button>

        <!-- Divider -->
        <div class="w-px h-5 bg-gray-200"></div>

        <!-- User avatar / profile dropdown -->
        <div class="relative" ref="profileRef">
          <button
            @click="profileOpen = !profileOpen"
            class="flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <div
              class="w-7 h-7 rounded-lg bg-gray-900 flex items-center justify-center text-white text-xs font-bold shrink-0"
            >
              {{ userInitials }}
            </div>
            <span class="text-sm font-medium text-gray-800">{{
              userName
            }}</span>
            <svg
              class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200"
              :class="{ 'rotate-180': profileOpen }"
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

          <!-- Dropdown -->
          <transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
          >
            <div
              v-if="profileOpen"
              class="absolute right-0 top-full mt-2 w-52 bg-white rounded-xl border border-gray-100 shadow-lg overflow-hidden py-1.5"
            >
              <div class="px-4 py-2.5 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-900">
                  {{ userName }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ userEmail }}</p>
              </div>
              <div class="py-1">
                <router-link
                  v-for="item in profileMenuItems"
                  :key="item.label"
                  :to="item.to"
                  @click="profileOpen = false"
                  class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-colors"
                >
                  <component :is="item.icon" class="w-4 h-4 shrink-0" />
                  {{ item.label }}
                </router-link>
              </div>
              <div class="border-t border-gray-100 py-1">
                <button
                  @click="handleLogout"
                  class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors"
                >
                  <svg
                    class="w-4 h-4"
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

        <!-- Buat Lelang CTA -->
        <!-- <button
          @click="router.push('/auction/create')"
          class="cursor-pointer px-5 py-2 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
        >
          + Buat Lelang
        </button> -->
      </div>
    </div>

    <!-- ── MOBILE ──────────────────────────────────────────────── -->
    <div class="md:hidden px-5 py-3.5 flex items-center justify-between">
      <router-link to="/" class="text-lg font-bold tracking-tight">
        ArtBid<span class="font-light">Bali</span>
      </router-link>

      <div class="flex items-center gap-2">
        <!-- Notifications mobile -->
        <button
          class="relative w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            />
          </svg>
          <span
            class="absolute top-1.5 right-1.5 w-2 h-2 bg-black rounded-full border-2 border-white"
          ></span>
        </button>

        <!-- Hamburger -->
        <button
          @click="mobileOpen = !mobileOpen"
          class="w-9 h-9 rounded-lg border border-gray-200 flex flex-col items-center justify-center gap-1.5"
        >
          <span
            class="w-4.5 h-0.5 bg-gray-800 block transition-all duration-300 origin-center"
            :class="mobileOpen ? 'rotate-45 translate-y-2' : ''"
          ></span>
          <span
            class="w-4.5 h-0.5 bg-gray-800 block transition-all duration-300"
            :class="mobileOpen ? 'opacity-0 scale-x-0' : ''"
          ></span>
          <span
            class="w-4.5 h-0.5 bg-gray-800 block transition-all duration-300 origin-center"
            :class="mobileOpen ? '-rotate-45 -translate-y-2' : ''"
          ></span>
        </button>
      </div>
    </div>

    <!-- Mobile menu panel -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="mobileOpen"
        class="md:hidden border-t border-gray-100 bg-white px-5 pb-5 pt-3"
      >
        <!-- User info -->
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 mb-3">
          <div
            class="w-9 h-9 rounded-xl bg-gray-900 flex items-center justify-center text-white text-sm font-bold"
          >
            {{ userInitials }}
          </div>
          <div>
            <p class="text-sm font-semibold">{{ userName }}</p>
            <p class="text-xs text-gray-400">{{ userEmail }}</p>
          </div>
        </div>

        <!-- Nav links mobile -->
        <ul class="space-y-1 mb-4">
          <li v-for="link in navLinks" :key="link.to">
            <router-link
              :to="link.to"
              @click="mobileOpen = false"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-colors"
              active-class="bg-gray-100 text-black font-medium"
            >
              {{ link.label }}
            </router-link>
          </li>
        </ul>

        <!-- Profile links mobile -->
        <ul class="space-y-1 border-t border-gray-100 pt-3 mb-4">
          <li v-for="item in profileMenuItems" :key="item.label">
            <router-link
              :to="item.to"
              @click="mobileOpen = false"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-colors"
            >
              {{ item.label }}
            </router-link>
          </li>
        </ul>

        <!-- CTA + Logout -->
        <div class="flex gap-2 pt-2 border-t border-gray-100">
          <!-- <button
            @click="router.push('/auction/create')"
            class="cursor-pointer flex-1 py-2.5 bg-black text-white rounded-lg text-sm font-medium"
          >
            + Buat Lelang
          </button> -->
          <button
            @click="handleLogout"
            class="px-4 py-2.5 border border-red-100 text-red-500 rounded-lg text-sm font-medium hover:bg-red-50 transition-colors"
          >
            Keluar
          </button>
        </div>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

// ── State ──────────────────────────────────────────────────────────
const profileOpen = ref(false);
const mobileOpen = ref(false);
const profileRef = ref(null);

// ── User data — ganti dengan store/auth kamu ──────────────────────
const userName = ref("Budi Santoso");
const userEmail = ref("budi@email.com");
const userInitials = computed(() =>
  userName.value
    .split(" ")
    .map((w) => w[0])
    .slice(0, 2)
    .join("")
    .toUpperCase(),
);

// ── Nav links ─────────────────────────────────────────────────────
const navLinks = [
  { label: "Daftar Lelang", to: "/auctions" },
  { label: "Tawaran Saya", to: "/my-bids" },
  { label: "Watchlist", to: "/watchlist" },
  { label: "Karya Saya", to: "/my-auctions" },
];

// ── Profile dropdown menu ──────────────────────────────────────────
const profileMenuItems = [
  {
    label: "Profil Saya",
    to: "/profile",
    icon: {
      render: () =>
        iconPath(
          "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
        ),
    },
  },
  {
    label: "Riwayat Lelang",
    to: "/history",
    icon: {
      render: () =>
        iconPath(
          "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
        ),
    },
  },
  {
    label: "Pengaturan",
    to: "/settings",
    icon: {
      render: () =>
        iconPath(
          "M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z",
        ),
    },
  },
];

// SVG icon helper (renders inline SVG via Vue render fn)
import { h } from "vue";
function iconPath(d) {
  return h(
    "svg",
    {
      class: "w-4 h-4",
      fill: "none",
      stroke: "currentColor",
      viewBox: "0 0 24 24",
    },
    [
      h("path", {
        "stroke-linecap": "round",
        "stroke-linejoin": "round",
        "stroke-width": "2",
        d,
      }),
    ],
  );
}

// ── Logout ────────────────────────────────────────────────────────
function handleLogout() {
  profileOpen.value = false;
  mobileOpen.value = false;
  // TODO: panggil auth store logout kamu di sini, lalu redirect
  router.push("/login");
}

// ── Click-outside to close dropdown ───────────────────────────────
function handleClickOutside(e) {
  if (profileRef.value && !profileRef.value.contains(e.target)) {
    profileOpen.value = false;
  }
}
onMounted(() => document.addEventListener("click", handleClickOutside));
onUnmounted(() => document.removeEventListener("click", handleClickOutside));
</script>

<style scoped>
/* Pastikan DM Sans sudah di-import global di project kamu */
nav {
  font-family: "DM Sans", sans-serif;
}
</style>
