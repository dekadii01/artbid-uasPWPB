<script setup>
import { ref, computed, onMounted, onUnmounted, inject } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

// ─── Sidebar width sync (matches AdminSidebar collapsed state) ─
// Use a simple approach: expose collapsed via provide/inject or just hardcode widths
// For now we read from a shared ref — connect to your state management as needed
const sidebarCollapsed = inject("sidebarCollapsed", ref(false));
const sidebarWidth = computed(() =>
  sidebarCollapsed.value ? "50px" : "240px",
);

// ─── Topbar state ────────────────────────────────────────────
const searchQuery = ref("");
const notifOpen = ref(false);
const profileOpen = ref(false);
const notifRef = ref(null);
const profileRef = ref(null);

const adminName = ref("Admin Bali");
const adminInitials = computed(() =>
  adminName.value
    .split(" ")
    .map((w) => w[0])
    .slice(0, 2)
    .join("")
    .toUpperCase(),
);

function handleLogout() {
  router.push("/admin/login");
}

function handleClickOutside(e) {
  if (notifRef.value && !notifRef.value.contains(e.target))
    notifOpen.value = false;
  if (profileRef.value && !profileRef.value.contains(e.target))
    profileOpen.value = false;
}
onMounted(() => document.addEventListener("click", handleClickOutside));
onUnmounted(() => document.removeEventListener("click", handleClickOutside));

// ─── System notifications ────────────────────────────────────
const systemNotifs = [
  {
    text: "Terdapat 3 laporan pengguna yang belum ditinjau.",
    time: "5 menit lalu",
    dark: false,
    icon: "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    text: "Terdapat 2 lelang yang menunggu verifikasi.",
    time: "12 menit lalu",
    dark: true,
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
  },
  {
    text: "Sistem realtime broadcasting berjalan normal.",
    time: "1 jam lalu",
    dark: false,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
];

const systemAlerts = [
  {
    text: "Terdapat 3 laporan pengguna yang belum ditinjau.",
    action: "Tinjau Sekarang",
    dark: false,
    icon: "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    text: "Terdapat 2 lelang yang menunggu verifikasi admin.",
    action: "Verifikasi",
    dark: true,
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
  },
];

// ─── Main stats ──────────────────────────────────────────────
const mainStats = [
  {
    label: "Total Pengguna",
    value: "1.245",
    sub: "+25 pengguna baru bulan ini",
    badge: "+2%",
    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z",
    dark: false,
  },
  {
    label: "Total Lelang",
    value: "325",
    sub: "Total seluruh lelang dibuat",
    badge: "All time",
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
    dark: true,
  },
  {
    label: "Lelang Aktif",
    value: "78",
    sub: "Sedang berlangsung saat ini",
    badge: "Live",
    icon: "M13 10V3L4 14h7v7l9-11h-7z",
    dark: false,
  },
  {
    label: "Total Penawaran",
    value: "4.521",
    sub: "Seluruh penawaran ke sistem",
    badge: "+318 hari ini",
    icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
    dark: false,
  },
];

// ─── Auction status ──────────────────────────────────────────
const auctionStatus = [
  { label: "Sedang Berlangsung", count: 78, dot: "bg-black", pct: 72 },
  { label: "Akan Datang", count: 32, dot: "bg-gray-400", pct: 30 },
  { label: "Selesai", count: 215, dot: "bg-gray-200", pct: 100 },
];

// ─── Top categories ──────────────────────────────────────────
const topCategories = [
  { name: "Lukisan", emoji: "🖼", count: 92 },
  { name: "Patung", emoji: "🗿", count: 68 },
  { name: "Topeng Tradisional", emoji: "🎭", count: 47 },
  { name: "Ukiran Kayu", emoji: "🪵", count: 38 },
  { name: "Barang Antik", emoji: "🏺", count: 29 },
];

// ─── Monthly chart ───────────────────────────────────────────
const monthlyChart = [
  { month: "Jan", val: 18, pct: 36, active: false },
  { month: "Feb", val: 24, pct: 48, active: false },
  { month: "Mar", val: 31, pct: 62, active: false },
  { month: "Apr", val: 28, pct: 56, active: false },
  { month: "Mei", val: 42, pct: 84, active: false },
  { month: "Jun", val: 50, pct: 100, active: true },
];

// ─── Daily bids chart ────────────────────────────────────────
const dailyBidsChart = [
  { day: "Sen", val: 312, pct: 62, active: false },
  { day: "Sel", val: 285, pct: 57, active: false },
  { day: "Rab", val: 498, pct: 100, active: false },
  { day: "Kam", val: 421, pct: 84, active: false },
  { day: "Jum", val: 389, pct: 78, active: false },
  { day: "Sab", val: 446, pct: 89, active: false },
  { day: "Min", val: 318, pct: 64, active: true },
];

// ─── Quick actions ───────────────────────────────────────────
const quickActions = [
  {
    label: "Kelola Lelang",
    to: "/admin/auctions",
    dark: true,
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
  },
  {
    label: "Kelola Pengguna",
    to: "/admin/users",
    dark: false,
    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z",
  },
  {
    label: "Kelola Kategori",
    to: "/admin/categories",
    dark: false,
    icon: "M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z",
  },
  {
    label: "Lihat Laporan",
    to: "/admin/reports",
    dark: false,
    icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
  },
];

// ─── System activities ───────────────────────────────────────
const systemActivities = [
  {
    type: "user",
    text: 'Pengguna <strong class="text-black">"I Putu Arya"</strong> membuat lelang baru',
    time: "2 menit yang lalu",
  },
  {
    type: "bid",
    text: 'Lelang <strong class="text-black">"Patung Garuda Bali"</strong> menerima penawaran baru sebesar <strong class="text-black">Rp 12.500.000</strong>',
    time: "5 menit yang lalu",
  },
  {
    type: "user",
    text: 'Pengguna baru <strong class="text-black">"Ni Made Ayu"</strong> berhasil melakukan registrasi',
    time: "10 menit yang lalu",
  },
  {
    type: "ended",
    text: 'Lelang <strong class="text-black">"Topeng Barong Antik"</strong> telah berakhir dan pemenang berhasil ditentukan',
    time: "15 menit yang lalu",
  },
  {
    type: "bid",
    text: 'Lelang <strong class="text-black">"Harmoni Semesta"</strong> menerima 5 penawaran baru dalam 10 menit',
    time: "22 menit yang lalu",
  },
  {
    type: "user",
    text: 'Pengguna <strong class="text-black">"Ketut Wirawan"</strong> mengirimkan laporan pada lelang #AB-2844',
    time: "38 menit yang lalu",
  },
];

// ─── Ending auctions ─────────────────────────────────────────
const endingAuctions = [
  {
    id: 1,
    name: "Lukisan Bali Klasik 1980",
    image:
      "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=100&q=80",
    currentPrice: 8500000,
    countdown: "00:25:08",
  },
  {
    id: 2,
    name: "Topeng Barong Antik",
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=100&q=80",
    currentPrice: 12500000,
    countdown: "00:09:45",
  },
  {
    id: 3,
    name: "Patung Dewi Saraswati",
    image:
      "https://images.unsplash.com/photo-1578926288207-32356f3e5e93?w=100&q=80",
    currentPrice: 6200000,
    countdown: "00:41:20",
  },
];

// ─── Popular auctions ────────────────────────────────────────
const popularAuctions = [
  {
    id: 1,
    name: "Topeng Barong Antik",
    seller: "Ni Luh Eka Sari",
    bids: 52,
    viewers: 28,
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=100&q=80",
  },
  {
    id: 2,
    name: "Lukisan Bali Klasik 1980",
    seller: "I Wayan Sukerta",
    bids: 35,
    viewers: 42,
    image:
      "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=100&q=80",
  },
  {
    id: 3,
    name: "Patung Garuda Bali",
    seller: "Ketut Suardana",
    bids: 29,
    viewers: 19,
    image:
      "https://images.unsplash.com/photo-1578926288207-32356f3e5e93?w=100&q=80",
  },
  {
    id: 4,
    name: "Harmoni Semesta",
    seller: "I Made Wijaya",
    bids: 22,
    viewers: 31,
    image:
      "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=100&q=80",
  },
];

// ─── Active users ────────────────────────────────────────────
const activeUsers = [
  { id: 1, name: "Budi Santoso", initials: "BS", bids: 24, auctions: 3 },
  { id: 2, name: "I Putu Arya", initials: "PA", bids: 18, auctions: 5 },
  { id: 3, name: "Ni Made Ratna", initials: "MR", bids: 15, auctions: 2 },
  { id: 4, name: "Gede Mahendra", initials: "GM", bids: 12, auctions: 1 },
];

// ─── Helpers ─────────────────────────────────────────────────
function formatRp(val) {
  return "Rp " + val.toLocaleString("id-ID");
}

function actStyle(type) {
  const map = {
    bid: {
      bg: "bg-black",
      icon: "text-white",
      path: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
    },
    user: {
      bg: "bg-gray-100",
      icon: "text-gray-700",
      path: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
    },
    ended: {
      bg: "bg-gray-100",
      icon: "text-gray-500",
      path: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
    },
  };
  return map[type] ?? map.user;
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 font-sans flex">
    <!-- Main area — offset matches sidebar width via CSS var -->
    <div
      class="flex-1 flex flex-col min-w-0 transition-all duration-300"
      :style="{ marginLeft: sidebarWidth }"
    >
      <!-- ═══════════════════ TOPBAR ═══════════════════ -->
      <header
        class="sticky top-0 z-30 bg-white border-b border-gray-100 px-8 py-3.5 flex items-center justify-between gap-4"
      >
        <!-- Search -->
        <div class="relative flex-1 max-w-md">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari pengguna, lelang, atau kategori..."
            class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-sm text-black placeholder-gray-400 outline-none focus:border-black transition-colors bg-gray-50"
          />
          <svg
            class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        </div>

        <div class="flex items-center gap-2">
          <!-- Notification bell -->
          <div class="relative" ref="notifRef">
            <button
              @click="notifOpen = !notifOpen"
              class="relative w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-gray-500 hover:border-black hover:text-black transition-colors"
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

            <transition name="dropdown">
              <div
                v-if="notifOpen"
                class="absolute right-0 top-full mt-2 w-80 bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden z-50"
              >
                <div
                  class="px-5 py-4 border-b border-gray-100 flex items-center justify-between"
                >
                  <p class="font-semibold text-sm text-black">
                    Notifikasi Sistem
                  </p>
                  <span
                    class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full"
                    >{{ systemNotifs.length }} baru</span
                  >
                </div>
                <div class="divide-y divide-gray-50">
                  <div
                    v-for="(n, i) in systemNotifs"
                    :key="i"
                    class="px-5 py-3.5 hover:bg-gray-50 transition-colors cursor-pointer"
                  >
                    <div class="flex items-start gap-3">
                      <div
                        :class="[
                          'w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5',
                          n.dark ? 'bg-black' : 'bg-gray-100',
                        ]"
                      >
                        <svg
                          class="w-3.5 h-3.5"
                          :class="n.dark ? 'text-white' : 'text-gray-600'"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            :d="n.icon"
                          />
                        </svg>
                      </div>
                      <div>
                        <p class="text-xs text-gray-700 leading-relaxed">
                          {{ n.text }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ n.time }}</p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="px-5 py-3 border-t border-gray-100 text-center">
                  <button
                    class="text-xs text-gray-500 hover:text-black transition-colors font-medium"
                  >
                    Lihat semua notifikasi
                  </button>
                </div>
              </div>
            </transition>
          </div>

          <div class="w-px h-5 bg-gray-200"></div>

          <!-- Admin profile -->
          <div class="relative" ref="profileRef">
            <button
              @click="profileOpen = !profileOpen"
              class="flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-xl hover:bg-gray-50 transition-colors"
            >
              <div
                class="w-7 h-7 rounded-lg bg-black flex items-center justify-center text-white text-xs font-bold shrink-0"
              >
                {{ adminInitials }}
              </div>
              <div class="text-left hidden sm:block">
                <p class="text-xs font-semibold text-gray-800 leading-none">
                  {{ adminName }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Administrator</p>
              </div>
              <svg
                class="w-3 h-3 text-gray-400 transition-transform duration-200"
                :class="profileOpen ? 'rotate-180' : ''"
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

            <transition name="dropdown">
              <div
                v-if="profileOpen"
                class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl border border-gray-100 shadow-lg overflow-hidden py-1.5 z-50"
              >
                <router-link
                  to="/admin/profile"
                  @click="profileOpen = false"
                  class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-colors"
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
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    />
                  </svg>
                  Profil
                </router-link>
                <router-link
                  to="/admin/settings"
                  @click="profileOpen = false"
                  class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-colors"
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
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                  </svg>
                  Pengaturan
                </router-link>
                <div class="border-t border-gray-100 mt-1 pt-1">
                  <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-500 hover:bg-gray-50 hover:text-black transition-colors"
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
                    Logout
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </header>

      <!-- ═══════════════════ PAGE CONTENT ═══════════════════ -->
      <main class="flex-1 px-8 py-8 overflow-y-auto">
        <!-- Header -->
        <div class="mb-8">
          <span
            class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
            >Admin Panel</span
          >
          <h1 class="text-3xl font-bold text-black mt-1.5 tracking-tight">
            Dashboard
          </h1>
          <p class="text-gray-500 text-sm mt-2 max-w-2xl leading-relaxed">
            Pantau aktivitas platform lelang secara keseluruhan, termasuk
            statistik pengguna, lelang aktif, penawaran yang masuk, dan
            aktivitas terbaru yang terjadi di sistem.
          </p>
        </div>

        <!-- System alert banners -->
        <div class="space-y-2 mb-8">
          <div
            v-for="alert in systemAlerts"
            :key="alert.text"
            class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3"
          >
            <div
              :class="[
                'w-7 h-7 rounded-lg flex items-center justify-center shrink-0',
                alert.dark ? 'bg-black' : 'bg-gray-100',
              ]"
            >
              <svg
                class="w-3.5 h-3.5"
                :class="alert.dark ? 'text-white' : 'text-gray-600'"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  :d="alert.icon"
                />
              </svg>
            </div>
            <p class="text-sm text-gray-700 flex-1">{{ alert.text }}</p>
            <button
              class="text-xs text-gray-500 hover:text-black font-medium transition-colors shrink-0"
            >
              {{ alert.action }}
            </button>
          </div>
        </div>

        <!-- ── Stats grid ── -->
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
          <div
            v-for="stat in mainStats"
            :key="stat.label"
            :class="[
              'rounded-2xl px-6 py-6 border card-lift',
              stat.dark ? 'bg-black border-black' : 'bg-white border-gray-100',
            ]"
          >
            <div class="flex items-start justify-between mb-4">
              <div
                :class="[
                  'w-9 h-9 rounded-xl flex items-center justify-center',
                  stat.dark ? 'bg-white/15' : 'bg-gray-100',
                ]"
              >
                <svg
                  class="w-4 h-4"
                  :class="stat.dark ? 'text-white' : 'text-black'"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    :d="stat.icon"
                  />
                </svg>
              </div>
              <span
                :class="[
                  'text-xs px-2 py-0.5 rounded-full font-medium',
                  stat.dark
                    ? 'bg-white/10 text-white/60'
                    : 'bg-gray-100 text-gray-500',
                ]"
              >
                {{ stat.badge }}
              </span>
            </div>
            <p
              class="text-2xl font-bold"
              :class="stat.dark ? 'text-white' : 'text-black'"
            >
              {{ stat.value }}
            </p>
            <p
              class="text-sm font-medium mt-0.5"
              :class="stat.dark ? 'text-white/70' : 'text-gray-700'"
            >
              {{ stat.label }}
            </p>
            <p
              class="text-xs mt-1"
              :class="stat.dark ? 'text-white/40' : 'text-gray-400'"
            >
              {{ stat.sub }}
            </p>
          </div>
        </div>

        <!-- ── Auction status + Charts row ── -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
          <!-- Auction status distribution -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-sm text-black mb-5">Status Lelang</h2>
            <div class="space-y-4">
              <div v-for="s in auctionStatus" :key="s.label">
                <div class="flex items-center justify-between mb-1.5">
                  <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" :class="s.dot"></span>
                    <span class="text-sm text-gray-600">{{ s.label }}</span>
                  </div>
                  <span class="text-sm font-bold text-black">{{
                    s.count
                  }}</span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-black rounded-full transition-all duration-700"
                    :style="{ width: s.pct + '%' }"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Category breakdown -->
            <div class="mt-6 pt-6 border-t border-gray-100">
              <h3
                class="font-semibold text-xs text-gray-400 uppercase tracking-widest mb-4"
              >
                Kategori Terpopuler
              </h3>
              <div class="space-y-2.5">
                <div
                  v-for="cat in topCategories"
                  :key="cat.name"
                  class="flex items-center justify-between"
                >
                  <div class="flex items-center gap-2.5">
                    <div
                      class="w-6 h-6 bg-gray-100 rounded-lg flex items-center justify-center text-xs"
                    >
                      {{ cat.emoji }}
                    </div>
                    <span class="text-sm text-gray-600">{{ cat.name }}</span>
                  </div>
                  <span class="text-xs font-semibold text-black"
                    >{{ cat.count }} lelang</span
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Charts area -->
          <div class="xl:col-span-2 space-y-4">
            <!-- Monthly auction chart -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
              <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-sm text-black">
                  Jumlah Lelang Per Bulan
                </h2>
                <span
                  class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full"
                  >2026</span
                >
              </div>
              <div class="flex items-end gap-2 h-28">
                <div
                  v-for="(bar, i) in monthlyChart"
                  :key="i"
                  class="flex-1 flex flex-col items-center gap-1.5"
                >
                  <span class="text-xs text-gray-500 font-medium">{{
                    bar.val
                  }}</span>
                  <div
                    class="w-full rounded-t-lg transition-all duration-700"
                    :class="
                      bar.active ? 'bg-black' : 'bg-gray-100 hover:bg-gray-200'
                    "
                    :style="{ height: bar.pct + '%' }"
                  ></div>
                  <span class="text-xs text-gray-400">{{ bar.month }}</span>
                </div>
              </div>
            </div>

            <!-- Daily bids chart -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
              <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-sm text-black">
                  Jumlah Penawaran Per Hari
                </h2>
                <span
                  class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full"
                  >7 hari terakhir</span
                >
              </div>
              <div class="flex items-end gap-2 h-20">
                <div
                  v-for="(bar, i) in dailyBidsChart"
                  :key="i"
                  class="flex-1 flex flex-col items-center gap-1.5"
                >
                  <span class="text-xs text-gray-500 font-medium">{{
                    bar.val
                  }}</span>
                  <div
                    class="w-full rounded-t-lg transition-all duration-700"
                    :class="
                      bar.active ? 'bg-black' : 'bg-gray-100 hover:bg-gray-200'
                    "
                    :style="{ height: bar.pct + '%' }"
                  ></div>
                  <span class="text-xs text-gray-400">{{ bar.day }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Quick actions ── -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
          <button
            v-for="action in quickActions"
            :key="action.label"
            @click="$router.push(action.to)"
            :class="[
              'flex items-center gap-3 px-4 py-4 rounded-2xl border text-sm font-medium transition-all duration-300 card-lift text-left',
              action.dark
                ? 'bg-black border-black text-white hover:bg-gray-800'
                : 'bg-white border-gray-100 text-gray-700 hover:border-black hover:text-black',
            ]"
          >
            <div
              :class="[
                'w-9 h-9 rounded-xl flex items-center justify-center shrink-0',
                action.dark ? 'bg-white/15' : 'bg-gray-100',
              ]"
            >
              <svg
                class="w-4 h-4"
                :class="action.dark ? 'text-white' : 'text-black'"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  :d="action.icon"
                />
              </svg>
            </div>
            {{ action.label }}
          </button>
        </div>

        <!-- ── Bottom 3-col grid ── -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- Activity feed -->
          <div class="xl:col-span-2 space-y-4">
            <!-- System activity -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
              <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-sm text-black">
                  Aktivitas Sistem Terbaru
                </h2>
                <div class="flex items-center gap-1.5">
                  <span
                    class="live-dot w-1.5 h-1.5 rounded-full bg-black inline-block"
                  ></span>
                  <span class="text-xs text-gray-400">Realtime</span>
                </div>
              </div>
              <div class="space-y-0">
                <div
                  v-for="(act, i) in systemActivities"
                  :key="i"
                  :class="[
                    'flex items-start gap-3 py-3.5',
                    i < systemActivities.length - 1
                      ? 'border-b border-gray-50'
                      : '',
                  ]"
                >
                  <div
                    :class="[
                      'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5',
                      actStyle(act.type).bg,
                    ]"
                  >
                    <svg
                      class="w-3.5 h-3.5"
                      :class="actStyle(act.type).icon"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        :d="actStyle(act.type).path"
                      />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p
                      class="text-sm text-gray-700 leading-relaxed"
                      v-html="act.text"
                    ></p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ act.time }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Ending soon auctions -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
              <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-sm text-black">
                  Lelang Akan Segera Berakhir
                </h2>
                <button
                  class="text-xs text-gray-500 hover:text-black font-medium transition-colors"
                >
                  Lihat Semua
                </button>
              </div>
              <div class="space-y-3">
                <div
                  v-for="auction in endingAuctions"
                  :key="auction.id"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100 hover:border-gray-300 transition-colors"
                >
                  <div class="flex items-center gap-3 min-w-0">
                    <div
                      class="w-10 h-10 rounded-lg bg-cover bg-center bg-gray-200 shrink-0"
                      :style="{ backgroundImage: `url('${auction.image}')` }"
                    ></div>
                    <div class="min-w-0">
                      <p class="text-sm font-medium text-black truncate">
                        {{ auction.name }}
                      </p>
                      <p class="text-xs text-gray-500">
                        {{ formatRp(auction.currentPrice) }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3 shrink-0">
                    <span class="text-xs font-mono font-semibold text-black">{{
                      auction.countdown
                    }}</span>
                    <button
                      @click="$router.push(`/admin/auctions/${auction.id}`)"
                      class="px-3 py-1.5 border border-gray-200 text-gray-600 rounded-lg text-xs font-medium hover:border-black hover:text-black transition-all duration-300"
                    >
                      Detail
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right col -->
          <div class="space-y-4">
            <!-- Popular auctions -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
              <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-sm text-black">
                  Lelang Terpopuler
                </h2>
                <button
                  class="text-xs text-gray-500 hover:text-black font-medium transition-colors"
                >
                  Lihat Semua
                </button>
              </div>
              <div class="space-y-4">
                <div
                  v-for="(item, i) in popularAuctions"
                  :key="item.id"
                  :class="[
                    'flex items-start gap-3',
                    i < popularAuctions.length - 1
                      ? 'pb-4 border-b border-gray-50'
                      : '',
                  ]"
                >
                  <span
                    class="text-xs font-bold text-gray-300 w-4 shrink-0 mt-1"
                    >{{ String(i + 1).padStart(2, "0") }}</span
                  >
                  <div
                    class="w-9 h-9 rounded-lg bg-cover bg-center bg-gray-200 shrink-0"
                    :style="{ backgroundImage: `url('${item.image}')` }"
                  ></div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-black truncate">
                      {{ item.name }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5 truncate">
                      {{ item.seller }}
                    </p>
                    <div class="flex items-center gap-3 mt-1.5">
                      <span class="text-xs text-gray-500"
                        >{{ item.bids }} bid</span
                      >
                      <span class="text-xs text-gray-400">·</span>
                      <span class="text-xs text-gray-500"
                        >{{ item.viewers }} lihat</span
                      >
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Active users -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
              <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-sm text-black">
                  Pengguna Teraktif
                </h2>
                <button
                  class="text-xs text-gray-500 hover:text-black font-medium transition-colors"
                >
                  Lihat Semua
                </button>
              </div>
              <div class="space-y-3">
                <div
                  v-for="(user, i) in activeUsers"
                  :key="user.id"
                  :class="[
                    'flex items-center gap-3',
                    i < activeUsers.length - 1
                      ? 'pb-3 border-b border-gray-50'
                      : '',
                  ]"
                >
                  <div
                    class="w-8 h-8 rounded-xl bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600 shrink-0"
                  >
                    {{ user.initials }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-black truncate">
                      {{ user.name }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ user.bids }} bid · {{ user.auctions }} lelang
                    </p>
                  </div>
                  <span class="text-xs font-bold text-black shrink-0"
                    >#{{ i + 1 }}</span
                  >
                </div>
              </div>
            </div>

            <!-- Summary card -->
            <div class="bg-black rounded-2xl p-6">
              <p class="text-white/50 text-xs uppercase tracking-widest mb-3">
                Total Transaksi
              </p>
              <p class="text-white font-bold text-3xl">Rp 2,4 M</p>
              <p class="text-white/40 text-xs mt-1.5">
                Nilai total seluruh lelang selesai
              </p>
              <div
                class="mt-5 pt-5 border-t border-white/10 grid grid-cols-2 gap-3"
              >
                <div>
                  <p class="text-white/40 text-xs mb-1">Rata-rata/lelang</p>
                  <p class="text-white text-sm font-semibold">Rp 11,2 Jt</p>
                </div>
                <div>
                  <p class="text-white/40 text-xs mb-1">Komisi platform</p>
                  <p class="text-white text-sm font-semibold">Rp 192 Jt</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
.font-sans {
  font-family: "DM Sans", sans-serif;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.fade-up {
  animation: fadeUp 0.6s ease forwards;
}
.delay-1 {
  animation-delay: 0.08s;
  opacity: 0;
}
.delay-2 {
  animation-delay: 0.18s;
  opacity: 0;
}
.delay-3 {
  animation-delay: 0.28s;
  opacity: 0;
}
.delay-4 {
  animation-delay: 0.38s;
  opacity: 0;
}

@keyframes pulseDot {
  0%,
  100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.5);
    opacity: 0.5;
  }
}
.live-dot {
  animation: pulseDot 1.4s ease-in-out infinite;
}

.card-lift {
  transition:
    transform 0.3s ease,
    box-shadow 0.3s ease;
}
.card-lift:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 32px rgba(0, 0, 0, 0.08);
}

.dropdown-enter-active {
  transition:
    opacity 0.15s ease,
    transform 0.15s ease;
}
.dropdown-leave-active {
  transition:
    opacity 0.1s ease,
    transform 0.1s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(6px);
}
</style>
