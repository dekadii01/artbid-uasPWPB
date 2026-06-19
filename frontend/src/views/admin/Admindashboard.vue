<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { getAdminDashboard } from "../../api/admin";

const router = useRouter();

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

onMounted(() => {
  try {
    const userStr = localStorage.getItem("artbid_user");
    if (userStr) {
      const user = JSON.parse(userStr);
      if (user.first_name) {
        adminName.value = `${user.first_name} ${user.last_name || ""}`.trim();
      }
    }
  } catch (e) {
    console.error("Gagal load profile dari localStorage:", e);
  }
});

function handleLogout() {
  localStorage.removeItem("artbid_token");
  localStorage.removeItem("artbid_user");
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

// ─── Fetch data from API ──────────────────────────────────────
const isLoading = ref(true);
const isError = ref(false);

const mainStats = ref([]);
const auctionStatus = ref([]);
const topCategories = ref([]);
const monthlyChart = ref([]);
const dailyBidsChart = ref([]);
const systemActivities = ref([]);
const endingAuctions = ref([]);
const popularAuctions = ref([]);
const activeUsers = ref([]);
const financial = ref({
  totalTransactions: 0,
  averageTransaction: 0,
  commissionPlatform: 0,
});

// Live ticker for ending soon countdowns
const now = ref(new Date());
let ticker = null;

function updateCountdowns() {
  now.value = new Date();
  
  if (endingAuctions.value) {
    endingAuctions.value.forEach((item) => {
      if (item.endsAt) {
        const target = new Date(item.endsAt);
        const diff = Math.max(0, target - now.value);
        if (diff <= 0) {
          item.countdown = "00:00:00";
        } else {
          const h = Math.floor(diff / 3600000);
          const m = Math.floor((diff % 3600000) / 60000);
          const s = Math.floor((diff % 60000) / 1000);
          item.countdown = `${String(h).padStart(2, "0")}:${String(m).padStart(2, "0")}:${String(s).padStart(2, "0")}`;
        }
      }
    });
  }
}

async function fetchData(isSilent = false) {
  if (!isSilent) isLoading.value = true;
  isError.value = false;
  try {
    const res = await getAdminDashboard();
    const d = res.data;

    mainStats.value = d.mainStats ?? [];
    auctionStatus.value = d.auctionStatus ?? [];
    topCategories.value = d.topCategories ?? [];
    monthlyChart.value = d.monthlyChart ?? [];
    dailyBidsChart.value = d.dailyBidsChart ?? [];
    systemActivities.value = d.systemActivities ?? [];
    endingAuctions.value = d.endingAuctions ?? [];
    popularAuctions.value = d.popularAuctions ?? [];
    activeUsers.value = d.activeUsers ?? [];
    financial.value = d.financial ?? {
      totalTransactions: 0,
      averageTransaction: 0,
      commissionPlatform: 0,
    };

    updateCountdowns();
  } catch (err) {
    console.error("Gagal mengambil data dashboard admin:", err);
    isError.value = true;
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchData();
  ticker = setInterval(updateCountdowns, 1000);
});

onUnmounted(() => {
  if (ticker) clearInterval(ticker);
});

// ─── System notifications & alerts ────────────────────────────
const systemAlerts = computed(() => {
  const scheduledCount = auctionStatus.value.find((s) => s.label === "Akan Datang" || s.label === "scheduled")?.count ?? 0;
  const alerts = [];
  
  if (scheduledCount > 0) {
    alerts.push({
      text: `Terdapat ${scheduledCount} lelang yang menunggu verifikasi admin.`,
      action: "Verifikasi",
      dark: true,
      icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
      to: "/admin/auctions"
    });
  }

  alerts.push({
    text: "Sistem realtime broadcasting berjalan normal.",
    action: "Status",
    dark: false,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
    to: ""
  });

  return alerts;
});

const systemNotifs = computed(() => {
  const list = [];
  const scheduledCount = auctionStatus.value.find((s) => s.label === "Akan Datang" || s.label === "scheduled")?.count ?? 0;
  if (scheduledCount > 0) {
    list.push({
      text: `Terdapat ${scheduledCount} lelang menunggu verifikasi.`,
      time: "Baru saja",
      dark: true,
      icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
    });
  }
  list.push({
    text: "Sistem realtime broadcasting berjalan normal.",
    time: "1 jam lalu",
    dark: false,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  });
  return list;
});

// ─── Quick actions ────────────────────────────────────────────
const quickActions = [
  {
    label: "Verifikasi Lelang",
    to: "/admin/auctions",
    dark: true,
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
  },
  {
    label: "Kelola Pengguna",
    to: "/admin/users",
    dark: false,
    icon: "M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
  },
  {
    label: "Kategori Karya",
    to: "/admin/categories",
    dark: false,
    icon: "M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
  },
  {
    label: "Laporan Keuangan",
    to: "/admin/reports",
    dark: false,
    icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
  }
];

// ─── Helpers ─────────────────────────────────────────────────
function formatRp(val) {
  if (val === undefined || val === null) return "Rp 0";
  if (val >= 1000000000) {
    return "Rp " + (val / 1000000000).toLocaleString("id-ID", { maximumFractionDigits: 1 }) + " M";
  }
  if (val >= 1000000) {
    return "Rp " + (val / 1000000).toLocaleString("id-ID", { maximumFractionDigits: 1 }) + " Jt";
  }
  return "Rp " + Number(val).toLocaleString("id-ID");
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
    <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
      <!-- ═══════════════════ PAGE CONTENT ═══════════════════ -->
      <main class="flex-1 px-8 py-8 overflow-y-auto">
        <!-- Loading state -->
        <div v-if="isLoading" class="flex flex-col items-center justify-center py-32">
          <div class="w-8 h-8 border-2 border-black border-t-transparent rounded-full animate-spin mb-4"></div>
          <p class="text-sm text-gray-500 font-medium">Memuat data dashboard...</p>
        </div>

        <!-- Error state -->
        <div v-else-if="isError" class="text-center py-32">
          <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <p class="text-lg font-semibold text-gray-800 mb-2">Terjadi Kesalahan</p>
          <p class="text-sm text-gray-500 mb-6">Gagal memuat data dashboard admin.</p>
          <button @click="fetchData(false)" class="px-6 py-2.5 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
            Coba Lagi
          </button>
        </div>

        <template v-else>
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
              @click="alert.to ? $router.push(alert.to) : null"
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
              <p class="text-white font-bold text-3xl">{{ formatRp(financial.totalTransactions) }}</p>
              <p class="text-white/40 text-xs mt-1.5">
                Nilai total seluruh lelang selesai
              </p>
              <div
                class="mt-5 pt-5 border-t border-white/10 grid grid-cols-2 gap-3"
              >
                <div>
                  <p class="text-white/40 text-xs mb-1">Rata-rata/lelang</p>
                  <p class="text-white text-sm font-semibold">{{ formatRp(financial.averageTransaction) }}</p>
                </div>
                <div>
                  <p class="text-white/40 text-xs mb-1">Komisi platform (8%)</p>
                  <p class="text-white text-sm font-semibold">{{ formatRp(financial.commissionPlatform) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
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
