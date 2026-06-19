<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Icon } from "@iconify/vue";
import { getMyBids, getMyBidsDashboard } from "../../api/bids";

// ─── State ────────────────────────────────────────────────────
const allBids = ref([]);
const dashboardData = ref({
  stats: { total: 0, leading: 0, outbid: 0, won: 0, lost: 0, active: 0, ended: 0 },
  endingSoon: [],
  activities: [],
  totalSpentActive: 0,
  activeBidsCount: 0
});

const isLoading = ref(true);
const isError = ref(false);

const activeFilter = ref("all");
const search = ref("");
const sortBy = ref("newest");

// ─── Stats computed ───────────────────────────────────────────
const stats = computed(() => [
  {
    label: "Lelang Diikuti",
    value: String(dashboardData.value.stats?.total ?? 0),
    icon: "lucide:scroll-text",
    dark: false,
  },
  {
    label: "Sedang Memimpin",
    value: String(dashboardData.value.stats?.leading ?? 0),
    icon: "lucide:badge-dollar-sign",
    dark: true,
  },
  {
    label: "Outbid",
    value: String(dashboardData.value.stats?.outbid ?? 0),
    icon: "lucide:trending-down",
    dark: false,
  },
  {
    label: "Lelang Dimenangkan",
    value: String(dashboardData.value.stats?.won ?? 0),
    icon: "lucide:trophy",
    dark: false,
  },
]);

// ─── Ending Soon ──────────────────────────────────────────────
const endingSoon = computed(() => dashboardData.value.endingSoon ?? []);

// ─── Filters ──────────────────────────────────────────────────
const filters = computed(() => [
  { label: "Semua", value: "all", count: allBids.value.length },
  { label: "Memimpin", value: "leading", count: allBids.value.filter((b) => b.status === "leading").length },
  { label: "Outbid", value: "outbid", count: allBids.value.filter((b) => b.status === "outbid").length },
  { label: "Menang", value: "won", count: allBids.value.filter((b) => b.status === "won").length },
  { label: "Kalah", value: "lost", count: allBids.value.filter((b) => b.status === "lost").length },
  { label: "Berlangsung", value: "active" },
  { label: "Selesai", value: "ended" },
]);

// ─── Computed filtered list ───────────────────────────────────
const filteredBids = computed(() => {
  let list = [...allBids.value];

  if (search.value.trim()) {
    const q = search.value.toLowerCase();
    list = list.filter(
      (b) =>
        (b.name && b.name.toLowerCase().includes(q)) ||
        (b.artist && b.artist.toLowerCase().includes(q))
    );
  }

  if (activeFilter.value !== "all") {
    if (activeFilter.value === "active") list = list.filter((b) => b.isActive);
    else if (activeFilter.value === "ended") list = list.filter((b) => !b.isActive);
    else list = list.filter((b) => b.status === activeFilter.value);
  }

  if (sortBy.value === "price_high") {
    list.sort((a, b) => (b.topBid ?? 0) - (a.topBid ?? 0));
  } else if (sortBy.value === "price_low") {
    list.sort((a, b) => (a.topBid ?? 0) - (b.topBid ?? 0));
  } else if (sortBy.value === "ending") {
    list.sort((a, b) => {
      if (a.isActive && !b.isActive) return -1;
      if (!a.isActive && b.isActive) return 1;
      if (a.isActive && b.isActive) {
        const dateA = a.endsAt ? new Date(a.endsAt) : new Date(8640000000000000);
        const dateB = b.endsAt ? new Date(b.endsAt) : new Date(8640000000000000);
        return dateA - dateB;
      }
      return 0;
    });
  }

  return list;
});

// ─── Activity feed ────────────────────────────────────────────
const activities = computed(() => dashboardData.value.activities ?? []);

// ─── Quick summary ─────────────────────────────────────────────
const quickSummary = computed(() => [
  { label: "Aktif berlangsung", value: `${dashboardData.value.stats?.active ?? 0} lelang`, dot: "bg-gray-800" },
  { label: "Sudah selesai", value: `${dashboardData.value.stats?.ended ?? 0} lelang`, dot: "bg-gray-300" },
  { label: "Perlu perhatian", value: `${dashboardData.value.stats?.outbid ?? 0} outbid`, dot: "bg-gray-400" },
  { label: "Total menang", value: `${dashboardData.value.stats?.won ?? 0} lelang`, dot: "bg-black" },
]);

// ─── Budget calculation ───────────────────────────────────────
const spentPercent = computed(() => {
  const total = dashboardData.value.totalSpentActive ?? 0;
  const budget = 100000000; // default budget limit Rp 100.000.000
  return Math.min(100, Math.round((total / budget) * 100));
});

// ─── Ticker for Live Countdown ───────────────────────────────
const now = ref(new Date());
let ticker = null;

function updateCountdowns() {
  now.value = new Date();
  
  // 1. Tick endingSoon countdowns
  if (dashboardData.value.endingSoon) {
    dashboardData.value.endingSoon.forEach((item) => {
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

  // 2. Tick main bids list timeLeft
  allBids.value.forEach((bid) => {
    if (bid.isActive && bid.endsAt) {
      const target = new Date(bid.endsAt);
      const diff = Math.max(0, target - now.value);
      if (diff <= 0) {
        bid.isActive = false;
        bid.timeLeft = null;
        bid.status = bid.status === "leading" ? "won" : "lost";
      } else {
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        bid.timeLeft = {
          d: String(d).padStart(2, "0"),
          h: String(h).padStart(2, "0"),
          m: String(m).padStart(2, "0"),
        };
      }
    }
  });
}

async function fetchData(isSilent = false) {
  if (!isSilent) isLoading.value = true;
  isError.value = false;
  try {
    const dashRes = await getMyBidsDashboard();
    dashboardData.value = dashRes.data;

    const bidsRes = await getMyBids({ per_page: 100 });
    allBids.value = bidsRes.data.data ?? [];
    
    updateCountdowns();
  } catch (err) {
    console.error("Gagal mengambil data penawaran:", err);
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

// ─── Helpers ─────────────────────────────────────────────────
function formatRp(val) {
  if (val === undefined || val === null) return "Rp 0";
  return "Rp " + Number(val).toLocaleString("id-ID");
}

function statusStyle(status) {
  const map = {
    leading: {
      wrapper: "bg-gray-50 border-gray-200 text-gray-800",
      dot: "bg-gray-800",
      label: "Anda Saat Ini Memimpin",
    },
    outbid: {
      wrapper: "bg-gray-100 border-gray-200 text-gray-500",
      dot: "bg-gray-400",
      label: "Penawaran Anda Dikalahkan",
    },
    won: {
      wrapper: "bg-black border-black text-white",
      dot: "bg-white",
      label: "Anda Memenangkan Lelang",
    },
    lost: {
      wrapper: "bg-gray-100 border-gray-200 text-gray-400",
      dot: "bg-gray-300",
      label: "Tidak Memenangkan Lelang",
    },
  };
  return map[status] ?? map.lost;
}

function activityStyle(type) {
  const map = {
    bid: {
      bg: "bg-gray-100",
      icon: "text-gray-700",
      path: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
    },
    outbid: {
      bg: "bg-gray-100",
      icon: "text-gray-500",
      path: "M13 17h8m0 0V9m0 8l-8-8-4 4-6-6",
    },
    won: {
      bg: "bg-black",
      icon: "text-white",
      path: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
    },
  };
  return map[type] ?? map.bid;
}
</script>

<template>
  <!-- Loading state -->
  <div v-if="isLoading" class="min-h-screen bg-gray-50 flex items-center justify-center pt-20">
    <div class="flex flex-col items-center">
      <div class="w-8 h-8 border-2 border-black border-t-transparent rounded-full animate-spin mb-4"></div>
      <p class="text-sm text-gray-500 font-medium">Memuat data...</p>
    </div>
  </div>

  <!-- Error state -->
  <div v-else-if="isError" class="min-h-screen bg-gray-50 flex items-center justify-center pt-20">
    <div class="text-center">
      <Icon icon="lucide:alert-circle" class="w-12 h-12 text-gray-400 mx-auto mb-4" />
      <p class="text-lg font-semibold text-gray-800 mb-2">Terjadi Kesalahan</p>
      <p class="text-sm text-gray-500 mb-6">Gagal memuat data penawaran Anda.</p>
      <button @click="fetchData(false)" class="px-6 py-2.5 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
        Coba Lagi
      </button>
    </div>
  </div>

  <div v-else class="min-h-screen bg-gray-50 font-sans pt-20">
    <div class="mx-auto px-6 md:px-10 py-10">
      <!-- ═══════════════════ HEADER ═══════════════════ -->
      <div class="mb-10">
        <span
          class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
          >Akun Saya</span
        >
        <h1 class="text-4xl font-bold text-black mt-2 tracking-tight">
          Tawaran Saya
        </h1>
        <p class="text-gray-500 text-sm mt-3 max-w-2xl leading-relaxed">
          Pantau seluruh lelang yang pernah Anda ikuti, lihat status penawaran
          terbaru, dan ketahui apakah Anda masih menjadi penawar tertinggi atau
          telah dikalahkan oleh peserta lain.
        </p>
      </div>

      <!-- ═══════════════════ STATS ═══════════════════ -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="rounded-2xl px-6 py-6 border card-lift"
          :class="
            stat.dark ? 'bg-black border-black' : 'bg-white border-gray-100'
          "
        >
          <div class="flex items-start justify-between mb-3">
            <div
              class="w-9 h-9 rounded-xl flex items-center justify-center"
              :class="stat.dark ? 'bg-white/15' : 'bg-gray-100'"
            >
              <Icon
                :icon="stat.icon"
                class="w-4 h-4"
                :class="stat.dark ? 'text-white' : 'text-black'"
              />
            </div>
          </div>
          <p
            class="text-2xl font-bold"
            :class="stat.dark ? 'text-white' : 'text-black'"
          >
            {{ stat.value }}
          </p>
          <p
            class="text-xs mt-1"
            :class="stat.dark ? 'text-white/50' : 'text-gray-400'"
          >
            {{ stat.label }}
          </p>
        </div>
      </div>

      <!-- ═══════════════════ ENDING SOON ═══════════════════ -->
      <div v-if="endingSoon.length" class="mb-10">
        <div class="flex items-center gap-2 mb-4">
          <span
            class="live-dot w-2 h-2 rounded-full bg-black inline-block"
          ></span>
          <h2 class="text-sm font-semibold text-black tracking-wide uppercase">
            Akan Segera Berakhir
          </h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="item in endingSoon"
            :key="item.id"
            class="bg-white rounded-2xl border border-gray-100 p-5 card-lift flex items-center justify-between gap-4"
          >
            <div class="flex items-start gap-4 flex-1 min-w-0">
              <div
                class="w-12 h-12 rounded-xl bg-cover bg-center shrink-0 bg-gray-100"
                :style="{ backgroundImage: `url('${item.image}')` }"
              ></div>
              <div class="min-w-0">
                <p class="font-semibold text-sm text-black truncate">
                  {{ item.name }}
                </p>
                <div class="flex items-center gap-1.5 mt-1">
                  <span
                    class="w-1.5 h-1.5 rounded-full shrink-0"
                    :class="
                      item.status === 'leading' ? 'bg-gray-800' : 'bg-gray-400'
                    "
                  ></span>
                  <span
                    class="text-xs"
                    :class="
                      item.status === 'leading'
                        ? 'text-gray-800 font-medium'
                        : 'text-gray-400'
                    "
                  >
                    {{ item.status === "leading" ? "Memimpin" : "Outbid" }}
                  </span>
                </div>
              </div>
            </div>
            <div class="text-right shrink-0">
              <p class="text-xs text-gray-400 mb-1">Berakhir dalam</p>
              <p class="text-sm font-bold text-black font-mono">
                {{ item.countdown }}
              </p>
              <button
                class="mt-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all duration-300"
                :class="
                  item.status === 'leading'
                    ? 'border border-gray-200 text-gray-600 hover:border-black hover:text-black'
                    : 'bg-black text-white hover:bg-gray-800'
                "
                @click="$router.push('/auction/' + item.id)"
              >
                {{ item.status === "leading" ? "Lihat Detail" : "Tawar Lagi" }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ SEARCH & FILTER ═══════════════════ -->
      <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-6">
        <div class="flex flex-col md:flex-row gap-3">
          <!-- Search -->
          <div class="relative flex-1">
            <input
              v-model="search"
              type="text"
              placeholder="Cari berdasarkan nama barang..."
              class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-black placeholder-gray-400 outline-none focus:border-black transition-colors bg-gray-50"
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

          <!-- Sort -->
          <div class="relative">
            <select
              v-model="sortBy"
              class="appearance-none border border-gray-200 rounded-xl pl-4 pr-9 py-2.5 text-sm text-gray-600 outline-none focus:border-black transition-colors bg-gray-50 cursor-pointer"
            >
              <option value="newest">Terbaru</option>
              <option value="ending">Akan Berakhir</option>
              <option value="price_high">Harga Tertinggi</option>
              <option value="price_low">Harga Terendah</option>
            </select>
            <svg
              class="w-3.5 h-3.5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
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
          </div>
        </div>

        <!-- Filter tabs -->
        <div class="flex flex-wrap gap-2 mt-4">
          <button
            v-for="f in filters"
            :key="f.value"
            @click="activeFilter = f.value"
            :class="[
              'px-4 py-1.5 rounded-lg text-xs font-medium transition-all duration-200',
              activeFilter === f.value
                ? 'bg-black text-white'
                : 'bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700',
            ]"
          >
            {{ f.label }}
            <span
              v-if="f.count !== undefined"
              :class="[
                'ml-1.5 text-xs',
                activeFilter === f.value ? 'text-white/60' : 'text-gray-400',
              ]"
              >{{ f.count }}</span
            >
          </button>
        </div>
      </div>

      <!-- ═══════════════════ BID LIST ═══════════════════ -->
      <div class="">
        <!-- Empty state -->
        <div
          v-if="filteredBids.length === 0"
          class="bg-white rounded-2xl border border-gray-100 py-20 text-center"
        >
          <div
            class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-5"
          >
            <svg
              class="w-7 h-7 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
              />
            </svg>
          </div>
          <p class="font-semibold text-gray-800 mb-2">
            Belum ada tawaran ditemukan
          </p>
          <p
            class="text-gray-400 text-sm max-w-xs mx-auto leading-relaxed mb-6"
          >
            {{
              search
                ? "Tidak ada tawaran yang cocok dengan pencarianmu."
                : "Anda belum mengikuti lelang apa pun. Jelajahi berbagai koleksi seni yang tersedia dan mulai ajukan penawaran pertama Anda."
            }}
          </p>
          <button
            @click="$router.push('/auctions')"
            class="px-6 py-2.5 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
          >
            Jelajahi Lelang
          </button>
        </div>

        <!-- Bid cards -->
        <div v-else class="space-y-4">
          <div
            v-for="bid in filteredBids"
            :key="bid.id"
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-lift"
          >
            <div class="flex flex-col md:flex-row">
              <!-- Image -->
              <div class="relative md:w-48 h-48 md:h-auto shrink-0">
                <div
                  class="w-full h-full bg-cover bg-center bg-gray-100"
                  :style="{ backgroundImage: `url('${bid.image}')` }"
                ></div>
                <!-- Category badge -->
                <span
                  class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-gray-700 text-xs px-2.5 py-1 rounded-full font-medium"
                >
                  {{ bid.category }}
                </span>
              </div>

              <!-- Content -->
              <div class="flex-1 p-6 flex flex-col justify-between">
                <div
                  class="flex flex-col md:flex-row md:items-start justify-between gap-4"
                >
                  <!-- Left: name + prices -->
                  <div class="flex-1">
                    <h3 class="font-semibold text-base text-black mb-1">
                      {{ bid.name }}
                    </h3>
                    <p class="text-gray-400 text-xs mb-4">{{ bid.artist }}</p>

                    <div class="flex flex-wrap gap-6">
                      <div>
                        <p class="text-xs text-gray-400 mb-1">Penawaran Saya</p>
                        <p class="font-bold text-lg text-black">
                          {{ formatRp(bid.myBid) }}
                        </p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-400 mb-1">
                          Harga Tertinggi Saat Ini
                        </p>
                        <p
                          class="font-bold text-lg"
                          :class="
                            bid.myBid >= bid.topBid
                              ? 'text-black'
                              : 'text-gray-400'
                          "
                        >
                          {{ formatRp(bid.topBid) }}
                        </p>
                      </div>
                    </div>

                    <!-- Meta info -->
                    <div
                      class="flex flex-wrap gap-5 mt-4 pt-4 border-t border-gray-100"
                    >
                      <div
                        class="flex items-center gap-1.5 text-xs text-gray-400"
                      >
                        <svg
                          class="w-3.5 h-3.5"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                          />
                        </svg>
                        <span>{{ bid.totalBids }} penawaran</span>
                      </div>
                      <div
                        class="flex items-center gap-1.5 text-xs text-gray-400"
                      >
                        <svg
                          class="w-3.5 h-3.5"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                          />
                        </svg>
                        <span>Bid terakhir: {{ bid.lastBidTime }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Right: status + timer + actions -->
                  <div
                    class="flex flex-col items-start md:items-end gap-4 shrink-0"
                  >
                    <!-- Status badge -->
                    <div
                      class="flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-medium"
                      :class="statusStyle(bid.status).wrapper"
                    >
                      <span
                        class="w-1.5 h-1.5 rounded-full shrink-0"
                        :class="statusStyle(bid.status).dot"
                      ></span>
                      {{ statusStyle(bid.status).label }}
                    </div>

                    <!-- Timer / end date -->
                    <div class="text-right">
                      <p class="text-xs text-gray-400 mb-2">
                        {{
                          bid.isActive ? "Berakhir Dalam" : "Lelang Berakhir"
                        }}
                      </p>
                      <div v-if="bid.isActive" class="flex gap-3">
                        <div class="text-center">
                          <p class="text-lg font-bold text-black leading-none">
                            {{ bid.timeLeft.d }}
                          </p>
                          <p class="text-xs text-gray-400 mt-0.5">Hari</p>
                        </div>
                        <div class="text-center">
                          <p class="text-lg font-bold text-black leading-none">
                            {{ bid.timeLeft.h }}
                          </p>
                          <p class="text-xs text-gray-400 mt-0.5">Jam</p>
                        </div>
                        <div class="text-center">
                          <p class="text-lg font-bold text-black leading-none">
                            {{ bid.timeLeft.m }}
                          </p>
                          <p class="text-xs text-gray-400 mt-0.5">Menit</p>
                        </div>
                      </div>
                      <p v-else class="text-sm font-semibold text-gray-600">
                        {{ bid.endDate }}
                      </p>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex gap-2">
                      <button
                        class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-xs font-medium hover:border-black hover:text-black transition-all duration-300"
                        @click="$router.push(`/auction/${bid.id}`)"
                      >
                        Lihat Detail
                      </button>
                      <button
                        v-if="bid.status === 'outbid'"
                        class="px-4 py-2 bg-black text-white rounded-lg text-xs font-medium hover:bg-gray-800 transition-all duration-300"
                        @click="$router.push(`/auction/${bid.id}`)"
                      >
                        Tawar Lagi
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ ACTIVITY FEED ═══════════════════ -->
      <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Activity -->
        <div
          class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6"
        >
          <h2 class="font-semibold text-base text-black mb-5">
            Riwayat Aktivitas Penawaran
          </h2>
          <div class="space-y-1">
            <div
              v-for="(act, i) in activities"
              :key="i"
              class="flex items-start gap-4 py-4"
              :class="
                i < activities.length - 1 ? 'border-b border-gray-50' : ''
              "
            >
              <!-- Icon -->
              <div
                class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                :class="activityStyle(act.type).bg"
              >
                <svg
                  class="w-4 h-4"
                  :class="activityStyle(act.type).icon"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    :d="activityStyle(act.type).path"
                  />
                </svg>
              </div>

              <!-- Text -->
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-700 leading-relaxed">
                  <span v-html="act.text"></span>
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ act.time }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick summary sidebar -->
        <div class="space-y-4">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-base text-black mb-5">
              Ringkasan Cepat
            </h2>
            <div class="space-y-4">
              <div
                v-for="item in quickSummary"
                :key="item.label"
                class="flex items-center justify-between"
              >
                <div class="flex items-center gap-2.5">
                  <div class="w-2 h-2 rounded-full" :class="item.dot"></div>
                  <span class="text-sm text-gray-600">{{ item.label }}</span>
                </div>
                <span class="text-sm font-semibold text-black">{{
                  item.value
                }}</span>
              </div>
            </div>
          </div>

          <div class="bg-black rounded-2xl p-6">
            <p class="text-white/50 text-xs uppercase tracking-widest mb-3">
              Total Pengeluaran Aktif
            </p>
            <p class="text-white font-bold text-3xl">{{ formatRp(dashboardData.totalSpentActive) }}</p>
            <p class="text-white/40 text-xs mt-2">
              Dari {{ dashboardData.activeBidsCount }} lelang yang sedang dipimpin
            </p>
            <div class="mt-5 h-1.5 bg-white/10 rounded-full overflow-hidden">
              <div
                class="h-full bg-white rounded-full"
                :style="{ width: spentPercent + '%' }"
              ></div>
            </div>
            <div class="flex justify-between mt-1.5">
              <span class="text-white/40 text-xs">Anggaran terpakai</span>
              <span class="text-white/60 text-xs font-medium">{{ spentPercent }}%</span>
            </div>
          </div>
        </div>
      </div>
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
</style>
