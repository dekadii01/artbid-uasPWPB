<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import { getAdminReports, exportAdminReport } from "../../../api/admin";
import { getEcho } from "../../../api/echo";

// ─── Period filter ────────────────────────────────────────────
const activePeriod = ref("month");
const dateFrom = ref("");
const dateTo = ref("");

const periods = [
  { label: "Hari Ini", value: "today" },
  { label: "Minggu Ini", value: "week" },
  { label: "Bulan Ini", value: "month" },
  { label: "Tahun Ini", value: "year" },
  { label: "Custom", value: "custom" },
];

const defaultStatsConfig = {
  "Total Pengguna": { dark: false, icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" },
  "Pengguna Baru": { dark: false, icon: "M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" },
  "Total Lelang": { dark: true, icon: "M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" },
  "Total Penawaran": { dark: false, icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" },
  "Nilai Transaksi": { dark: false, icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" },
  "Lelang Aktif": { dark: false, icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" }
};

const defaultRealtimeConfig = {
  "Pengguna Online": { icon: "M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" },
  "Viewer Aktif": { icon: "M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" },
  "Lelang Berlangsung": { icon: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" },
  "Bid (1 jam)": { icon: "M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" }
};

// ─── States ────────────────────────────────────────────────────
const stats = ref([]);
const userGrowthData = ref([]);
const auctionChartData = ref([]);
const bidChartData = ref([]);
const categoryStats = ref([]);
const topAuctions = ref([]);
const topUsers = ref([]);
const realtimeStats = ref([]);
const systemActivity = ref([]);
const salesStats = ref([]);
const topItem = ref({ name: "Tidak ada", price: 0 });

const colorsClass = ["bg-gray-800", "bg-gray-500", "bg-gray-300", "bg-gray-200"];
const exportOptions = [
  { label: "Laporan Pengguna", type: "users" },
  { label: "Laporan Lelang", type: "auctions" },
  { label: "Laporan Penawaran", type: "bids" },
  { label: "Laporan Transaksi", type: "transactions" },
  { label: "Laporan Kategori", type: "categories" },
];

const onlineUsersCount = ref(0);
let echoChannel = null;

// ─── Chart helpers ────────────────────────────────────────────
const lineMax = computed(() => {
  if (userGrowthData.value.length === 0) return 1;
  return Math.max(1, ...userGrowthData.value.map((d) => d.value));
});

const bidMax = computed(() => {
  if (bidChartData.value.length === 0) return 1;
  return Math.max(1, ...bidChartData.value.map((d) => d.value));
});

const barMax = computed(() => {
  if (auctionChartData.value.length === 0) return 1;
  return Math.max(1, ...auctionChartData.value.map((d) => d.new + d.active + d.ended));
});

function lineY(val, max, h = 80) {
  return h - (val / max) * h;
}

function polyline(data, max, h = 80, w = 280) {
  if (!data || data.length < 2) return "0,80 280,80";
  const step = w / (data.length - 1);
  return data.map((d, i) => `${i * step},${lineY(d.value, max, h)}`).join(" ");
}

function formatRp(val) {
  if (val === undefined || val === null) return "Rp 0";
  return "Rp " + Math.round(val).toLocaleString("id-ID");
}

// ─── API Integration ──────────────────────────────────────────
async function loadReports() {
  try {
    const params = {
      period: activePeriod.value,
      date_from: dateFrom.value,
      date_to: dateTo.value,
    };
    const { data } = await getAdminReports(params);

    stats.value = [
      {
        label: "Total Pengguna",
        value: data.stats.total_users,
        sub: "Seluruh akun terdaftar",
        dark: false,
        icon: defaultStatsConfig["Total Pengguna"].icon,
      },
      {
        label: "Pengguna Baru",
        value: data.stats.new_users,
        sub: "Periode yang dipilih",
        dark: false,
        icon: defaultStatsConfig["Pengguna Baru"].icon,
      },
      {
        label: "Total Lelang",
        value: data.stats.total_auctions,
        sub: "Lelang dibuat",
        dark: true,
        icon: defaultStatsConfig["Total Lelang"].icon,
      },
      {
        label: "Total Penawaran",
        value: data.stats.total_bids,
        sub: "Aktivitas bidding",
        dark: false,
        icon: defaultStatsConfig["Total Penawaran"].icon,
      },
      {
        label: "Nilai Transaksi",
        value: data.stats.transaction_value,
        sub: "Akumulasi lelang selesai",
        dark: false,
        icon: defaultStatsConfig["Nilai Transaksi"].icon,
      },
      {
        label: "Lelang Aktif",
        value: data.stats.active_auctions,
        sub: "Sedang berlangsung",
        dark: false,
        icon: defaultStatsConfig["Lelang Aktif"].icon,
      },
    ];

    realtimeStats.value = [
      {
        label: "Pengguna Online",
        value: String(onlineUsersCount.value > 0 ? onlineUsersCount.value : data.realtime.online_users),
        icon: defaultRealtimeConfig["Pengguna Online"].icon,
      },
      {
        label: "Viewer Aktif",
        value: data.realtime.active_viewers,
        icon: defaultRealtimeConfig["Viewer Aktif"].icon,
      },
      {
        label: "Lelang Berlangsung",
        value: data.realtime.active_auctions,
        icon: defaultRealtimeConfig["Lelang Berlangsung"].icon,
      },
      {
        label: "Bid (1 jam)",
        value: data.realtime.bids_last_hour,
        icon: defaultRealtimeConfig["Bid (1 jam)"].icon,
      },
    ];

    userGrowthData.value = data.charts.userGrowth;
    bidChartData.value = data.charts.bids;
    auctionChartData.value = data.charts.auctions;

    categoryStats.value = data.categoryStats;
    topAuctions.value = data.topAuctions;
    topUsers.value = data.topUsers;

    salesStats.value = [
      { label: "Total Barang Terjual", value: data.sales.total_sold },
      { label: "Nilai Penjualan Tertinggi", value: data.sales.highest_sale },
      { label: "Nilai Rata-rata", value: data.sales.average_sale },
    ];

    topItem.value = data.sales.top_item;
    systemActivity.value = data.systemActivities;
  } catch (error) {
    console.error("Gagal memuat laporan:", error);
  }
}

// ─── Export ───────────────────────────────────────────────────
async function exportReport(type) {
  try {
    const params = {
      type,
      period: activePeriod.value,
      date_from: dateFrom.value,
      date_to: dateTo.value,
    };
    const response = await exportAdminReport(params);
    const blob = new Blob([response.data], { type: "text/csv;charset=utf-8;" });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    const dateStr = new Date().toISOString().slice(0, 10);
    link.setAttribute("download", `laporan-${type}-${dateStr}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error("Gagal mengekspor laporan:", error);
  }
}

function exportData(format) {
  if (format === "pdf") {
    window.print();
  } else {
    exportReport("all");
  }
}

// ─── Realtime Presence Channel ────────────────────────────────
function initEcho() {
  const echo = getEcho();
  if (echo) {
    echoChannel = echo.join("online")
      .here((users) => {
        onlineUsersCount.value = users.length;
        const card = realtimeStats.value.find(rt => rt.label === "Pengguna Online");
        if (card) card.value = String(users.length);
      })
      .joining((user) => {
        onlineUsersCount.value++;
        const card = realtimeStats.value.find(rt => rt.label === "Pengguna Online");
        if (card) card.value = String(onlineUsersCount.value);
      })
      .leaving((user) => {
        onlineUsersCount.value = Math.max(1, onlineUsersCount.value - 1);
        const card = realtimeStats.value.find(rt => rt.label === "Pengguna Online");
        if (card) card.value = String(onlineUsersCount.value);
      });
  }
}

// ─── Lifecycle & Watchers ─────────────────────────────────────
watch(activePeriod, (newVal) => {
  if (newVal !== "custom") {
    loadReports();
  }
});

onMounted(() => {
  loadReports();
  initEcho();
});

onUnmounted(() => {
  if (echoChannel) {
    const echo = getEcho();
    if (echo) {
      echo.leave("online");
    }
  }
});

// ─── Donut Pie Calculations ───────────────────────────────────
const totalAuctionsCount = computed(() => {
  return categoryStats.value.reduce((sum, cat) => sum + cat.auctions, 0);
});

const donutSlices = computed(() => {
  const circumference = 2 * Math.PI * 44; // ~276.46
  let accumulatedPercent = 0;
  const colors = ["#111827", "#6b7280", "#d1d5db", "#e5e7eb"];

  return categoryStats.value.slice(0, 4).map((cat, index) => {
    const pct = cat.pct || 0;
    const dashArrayVal = (pct / 100) * circumference;
    const dashArrayOffset = -(accumulatedPercent / 100) * circumference;
    accumulatedPercent += pct;

    return {
      name: cat.name,
      pct: pct,
      stroke: colors[index] ?? "#f3f4f6",
      dashArray: `${dashArrayVal.toFixed(1)} ${(circumference - dashArrayVal).toFixed(1)}`,
      dashOffset: dashArrayOffset.toFixed(1),
    };
  });
});
</script>

<template>
  <div class="flex-1 px-8 py-8 space-y-6 min-h-screen bg-gray-50 font-sans">
    <!-- ═══════════════════ HEADER ═══════════════════ -->
    <div class="flex justify-between items-start">
      <div>
        <span
          class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
          >Admin Panel</span
        >
        <h1 class="text-3xl font-bold text-black mt-1.5 tracking-tight">
          Laporan
        </h1>
        <p class="text-gray-500 text-sm mt-2 max-w-2xl leading-relaxed">
          Pantau performa platform, aktivitas pengguna, statistik lelang, serta
          hasil transaksi yang terjadi pada sistem.
        </p>
      </div>
      <div class="flex gap-2 shrink-0 no-print">
        <button
          @click="exportData('excel')"
          class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:border-black hover:text-black transition-all"
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
              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          Excel
        </button>
        <button
          @click="exportData('pdf')"
          class="flex items-center gap-2 px-4 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
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
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
            />
          </svg>
          Export PDF
        </button>
      </div>
    </div>

    <!-- ═══════════════════ PERIOD FILTER ═══════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 no-print">
      <div class="flex flex-col xl:flex-row gap-4">
        <!-- Period tabs -->
        <div class="flex flex-wrap gap-2">
          <button
            v-for="p in periods"
            :key="p.value"
            @click="activePeriod = p.value"
            :class="[
              'px-4 py-2 rounded-xl text-xs font-medium transition-all duration-200',
              activePeriod === p.value
                ? 'bg-black text-white'
                : 'bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700',
            ]"
          >
            {{ p.label }}
          </button>
        </div>

        <!-- Custom range -->
        <div
          v-if="activePeriod === 'custom'"
          class="flex flex-wrap items-center gap-3 xl:ml-auto"
        >
          <div class="relative">
            <input
              v-model="dateFrom"
              type="date"
              class="border border-gray-200 rounded-xl pl-3 pr-4 py-2 text-sm text-gray-600 outline-none focus:border-black transition-colors bg-gray-50"
            />
          </div>
          <span class="text-xs text-gray-400">s/d</span>
          <div class="relative">
            <input
              v-model="dateTo"
              type="date"
              class="border border-gray-200 rounded-xl pl-3 pr-4 py-2 text-sm text-gray-600 outline-none focus:border-black transition-colors bg-gray-50"
            />
          </div>
          <button
            @click="loadReports"
            class="px-4 py-2 bg-black text-white rounded-xl text-xs font-medium hover:bg-gray-800 transition-colors"
          >
            Terapkan Filter
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════════════════ STATS ═══════════════════ -->
    <div class="grid grid-cols-2 xl:grid-cols-3 gap-4">
      <div
        v-for="stat in stats"
        :key="stat.label"
        :class="[
          'rounded-2xl px-6 py-6 border card-lift',
          stat.dark ? 'bg-black border-black' : 'bg-white border-gray-100',
        ]"
      >
        <div class="mb-3">
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
          class="text-xs mt-0.5"
          :class="stat.dark ? 'text-white/40' : 'text-gray-400'"
        >
          {{ stat.sub }}
        </p>
      </div>
    </div>

    <!-- ═══════════════════ REALTIME STATS ═══════════════════ -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
      <div
        v-for="rt in realtimeStats"
        :key="rt.label"
        class="bg-white rounded-2xl border border-gray-100 px-5 py-4 flex items-center gap-4"
      >
        <div
          class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center shrink-0"
        >
          <svg
            class="w-4 h-4 text-black"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="rt.icon"
            />
          </svg>
        </div>
        <div>
          <p class="text-xl font-bold text-black">{{ rt.value }}</p>
          <p class="text-xs text-gray-400 mt-0.5 leading-tight">
            {{ rt.label }}
          </p>
        </div>
        <div class="ml-auto">
          <span
            class="live-dot w-2 h-2 rounded-full bg-black inline-block"
          ></span>
        </div>
      </div>
    </div>

    <!-- ═══════════════════ CHARTS ROW ═══════════════════ -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <!-- User Growth Line Chart -->
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-sm font-semibold text-black">
              Pertumbuhan Pengguna
            </h2>
            <p class="text-xs text-gray-400 mt-0.5">Registrasi per bulan</p>
          </div>
          <span
            class="text-xs text-gray-400 font-medium bg-gray-100 px-3 py-1 rounded-lg no-print"
            >Line Chart</span
          >
        </div>
        <div class="relative">
          <svg
            viewBox="0 0 300 110"
            class="w-full"
            preserveAspectRatio="none"
            style="height: 120px"
          >
            <!-- Grid lines -->
            <line
              x1="0"
              y1="0"
              x2="300"
              y2="0"
              stroke="#f3f4f6"
              stroke-width="1"
            />
            <line
              x1="0"
              y1="26"
              x2="300"
              y2="26"
              stroke="#f3f4f6"
              stroke-width="1"
            />
            <line
              x1="0"
              y1="53"
              x2="300"
              y2="53"
              stroke="#f3f4f6"
              stroke-width="1"
            />
            <line
              x1="0"
              y1="80"
              x2="300"
              y2="80"
              stroke="#f3f4f6"
              stroke-width="1"
            />
            <!-- Fill area -->
            <defs>
              <linearGradient id="fillGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#000" stop-opacity="0.08" />
                <stop offset="100%" stop-color="#000" stop-opacity="0" />
              </linearGradient>
            </defs>
            <polygon
              :points="`0,80 ${polyline(userGrowthData, lineMax, 80, 280)} 280,80`"
              fill="url(#fillGrad)"
            />
            <!-- Line -->
            <polyline
              :points="polyline(userGrowthData, lineMax, 80, 280)"
              fill="none"
              stroke="#111827"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <!-- Dots -->
            <circle
              v-for="(d, i) in userGrowthData"
              :key="i"
              :cx="i * (280 / Math.max(1, userGrowthData.length - 1))"
              :cy="lineY(d.value, lineMax)"
              r="3"
              fill="#111827"
            />
          </svg>
          <!-- X labels -->
          <div class="flex justify-between mt-2">
            <span
              v-for="d in userGrowthData"
              :key="d.label"
              class="text-xs text-gray-400"
              >{{ d.label }}</span
            >
          </div>
        </div>
      </div>

      <!-- Bid Activity Line Chart -->
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-sm font-semibold text-black">
              Aktivitas Penawaran
            </h2>
            <p class="text-xs text-gray-400 mt-0.5">Jumlah bid per bulan</p>
          </div>
          <span
            class="text-xs text-gray-400 font-medium bg-gray-100 px-3 py-1 rounded-lg no-print"
            >Line Chart</span
          >
        </div>
        <div class="relative">
          <svg
            viewBox="0 0 300 110"
            class="w-full"
            preserveAspectRatio="none"
            style="height: 120px"
          >
            <line
              x1="0"
              y1="0"
              x2="300"
              y2="0"
              stroke="#f3f4f6"
              stroke-width="1"
            />
            <line
              x1="0"
              y1="26"
              x2="300"
              y2="26"
              stroke="#f3f4f6"
              stroke-width="1"
            />
            <line
              x1="0"
              y1="53"
              x2="300"
              y2="53"
              stroke="#f3f4f6"
              stroke-width="1"
            />
            <line
              x1="0"
              y1="80"
              x2="300"
              y2="80"
              stroke="#f3f4f6"
              stroke-width="1"
            />
            <defs>
              <linearGradient id="bidGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#374151" stop-opacity="0.1" />
                <stop offset="100%" stop-color="#374151" stop-opacity="0" />
              </linearGradient>
            </defs>
            <polygon
              :points="`0,80 ${polyline(bidChartData, bidMax, 80, 280)} 280,80`"
              fill="url(#bidGrad)"
            />
            <polyline
              :points="polyline(bidChartData, bidMax, 80, 280)"
              fill="none"
              stroke="#374151"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <circle
              v-for="(d, i) in bidChartData"
              :key="i"
              :cx="i * (280 / Math.max(1, bidChartData.length - 1))"
              :cy="lineY(d.value, bidMax)"
              r="3"
              fill="#374151"
            />
          </svg>
          <div class="flex justify-between mt-2">
            <span
              v-for="d in bidChartData"
              :key="d.label"
              class="text-xs text-gray-400"
              >{{ d.label }}</span
            >
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════ AUCTION BAR CHART ═══════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-sm font-semibold text-black">Aktivitas Lelang</h2>
          <p class="text-xs text-gray-400 mt-0.5">
            Lelang baru, aktif, dan selesai per bulan
          </p>
        </div>
        <div class="flex items-center gap-4 text-xs text-gray-400">
          <span class="flex items-center gap-1.5"
            ><span class="w-2.5 h-2.5 rounded bg-gray-800 inline-block"></span
            >Baru</span
          >
          <span class="flex items-center gap-1.5"
            ><span class="w-2.5 h-2.5 rounded bg-gray-400 inline-block"></span
            >Aktif</span
          >
          <span class="flex items-center gap-1.5"
            ><span class="w-2.5 h-2.5 rounded bg-gray-200 inline-block"></span
            >Selesai</span
          >
          <span
            class="text-xs text-gray-400 font-medium bg-gray-100 px-3 py-1 rounded-lg no-print"
            >Bar Chart</span
          >
        </div>
      </div>
      <div class="flex items-end justify-between gap-3" style="height: 140px">
        <div
          v-for="(d, i) in auctionChartData"
          :key="i"
          class="flex-1 flex flex-col items-center gap-0.5"
        >
          <div
            class="w-full flex flex-col gap-0.5"
            :style="{ height: '120px' }"
          >
            <div
              class="w-full rounded-t-lg bg-gray-800 transition-all duration-500"
              :style="{ height: `${(d.new / barMax) * 120}px` }"
            ></div>
            <div
              class="w-full bg-gray-400 transition-all duration-500"
              :style="{ height: `${(d.active / barMax) * 120}px` }"
            ></div>
            <div
              class="w-full rounded-b-lg bg-gray-200 transition-all duration-500"
              :style="{ height: `${(d.ended / barMax) * 120}px` }"
            ></div>
          </div>
          <span class="text-xs text-gray-400 mt-1">{{ d.label }}</span>
        </div>
      </div>
    </div>

    <!-- ═══════════════════ BOTTOM SECTION ═══════════════════ -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- ── Category stats + pie ── -->
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
          <div>
            <h2 class="text-sm font-semibold text-black">Statistik Kategori</h2>
            <p class="text-xs text-gray-400 mt-0.5">Performa per kategori</p>
          </div>
          <span
            class="text-xs text-gray-400 font-medium bg-gray-100 px-3 py-1 rounded-lg no-print"
            >Pie Chart</span
          >
        </div>

        <!-- Donut pie -->
        <div class="flex justify-center mb-5">
          <svg viewBox="0 0 120 120" class="w-28 h-28">
            <circle
              cx="60"
              cy="60"
              r="44"
              fill="none"
              stroke="#f3f4f6"
              stroke-width="18"
            />
            <circle
              v-for="(slice, idx) in donutSlices"
              :key="idx"
              cx="60"
              cy="60"
              r="44"
              fill="none"
              :stroke="slice.stroke"
              stroke-width="18"
              :stroke-dasharray="slice.dashArray"
              :stroke-dashoffset="slice.dashOffset"
              transform="rotate(-90 60 60)"
            />
            <text
              x="60"
              y="57"
              text-anchor="middle"
              class="text-xs"
              font-size="10"
              font-weight="700"
              fill="#111827"
            >
              {{ totalAuctionsCount }}
            </text>
            <text
              x="60"
              y="69"
              text-anchor="middle"
              font-size="6.5"
              fill="#9ca3af"
            >
              lelang
            </text>
          </svg>
        </div>

        <div class="space-y-3">
          <div
            v-for="(cat, i) in categoryStats"
            :key="i"
            class="flex items-center gap-3"
          >
            <span
              class="w-2.5 h-2.5 rounded-full shrink-0"
              :class="colorsClass[i] || 'bg-gray-100'"
            ></span>
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-center mb-1">
                <p class="text-xs font-medium text-black truncate">
                  {{ cat.name }}
                </p>
                <p class="text-xs text-gray-400 shrink-0 ml-2">
                  {{ cat.pct }}%
                </p>
              </div>
              <div class="h-1 bg-gray-100 rounded-full">
                <div
                  class="h-1 rounded-full transition-all duration-700"
                  :class="colorsClass[i] || 'bg-gray-100'"
                  :style="{ width: cat.pct + '%' }"
                ></div>
              </div>
              <p class="text-xs text-gray-400 mt-1">
                {{ cat.auctions }} lelang ·
                {{ cat.bids.toLocaleString("id-ID") }} bid
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Top auctions ── -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
          <h2 class="text-sm font-semibold text-black">Lelang Terpopuler</h2>
          <p class="text-xs text-gray-400 mt-0.5">Berdasarkan aktivitas bid</p>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-50">
                <th
                  class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                >
                  Barang
                </th>
                <th
                  class="text-center px-3 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell"
                >
                  Bid
                </th>
                <th
                  class="text-right px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                >
                  Harga
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr
                v-for="(item, i) in topAuctions"
                :key="i"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-3.5">
                  <div class="flex items-center gap-3">
                    <span
                      class="text-xs font-bold text-gray-300 w-4 shrink-0"
                      >{{ String(i + 1).padStart(2, "0") }}</span
                    >
                    <div class="min-w-0">
                      <p
                        class="text-xs font-medium text-black truncate max-w-[120px]"
                      >
                        {{ item.name }}
                      </p>
                      <p class="text-xs text-gray-400 mt-0.5">
                        {{ item.seller }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-3 py-3.5 text-center hidden lg:table-cell">
                  <span class="text-xs font-semibold text-black">{{
                    item.bids
                  }}</span>
                  <span class="text-xs text-gray-400 ml-1">bid</span>
                </td>
                <td class="px-6 py-3.5 text-right">
                  <p class="text-xs font-semibold text-black whitespace-nowrap">
                    {{ formatRp(item.price) }}
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── Top users ── -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
          <h2 class="text-sm font-semibold text-black">
            Pengguna Paling Aktif
          </h2>
          <p class="text-xs text-gray-400 mt-0.5">
            Berdasarkan total aktivitas
          </p>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-50">
                <th
                  class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                >
                  Pengguna
                </th>
                <th
                  class="text-center px-3 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                >
                  Bid
                </th>
                <th
                  class="text-center px-3 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell"
                >
                  Menang
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr
                v-for="(user, i) in topUsers"
                :key="i"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-3.5">
                  <div class="flex items-center gap-3">
                    <span
                      class="text-xs font-bold text-gray-300 w-4 shrink-0"
                      >{{ String(i + 1).padStart(2, "0") }}</span
                    >
                    <div>
                      <p class="text-xs font-medium text-black">
                        {{ user.name }}
                      </p>
                      <p class="text-xs text-gray-400 mt-0.5">
                        {{ user.auctions }} lelang dibuat
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-3 py-3.5 text-center">
                  <p class="text-xs font-semibold text-black">
                    {{ user.bids }}
                  </p>
                </td>
                <td class="px-3 py-3.5 text-center hidden lg:table-cell">
                  <span
                    class="inline-flex items-center gap-1 text-xs font-medium bg-black text-white px-2 py-0.5 rounded-full"
                  >
                    {{ user.won }} ✓
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ═══════════════════ SALES + ACTIVITY ═══════════════════ -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Sales stats -->
      <div class="space-y-4">
        <!-- Summary black card -->
        <div class="bg-black rounded-2xl p-5">
          <p class="text-white/50 text-xs uppercase tracking-widest mb-3">
            Statistik Penjualan
          </p>
          <p class="text-white font-bold text-3xl">
            {{ salesStats.find(s => s.label === "Total Barang Terjual")?.value.replace(" Barang", "") || "0" }}
          </p>
          <p class="text-white/40 text-xs mt-1.5">Barang terjual</p>
          <div class="mt-4 pt-4 border-t border-white/10 space-y-3">
            <div v-for="s in salesStats" :key="s.label">
              <p class="text-white/40 text-xs mb-0.5">{{ s.label }}</p>
              <p class="text-white text-sm font-semibold">{{ s.value }}</p>
            </div>
          </div>
        </div>

        <!-- Top item -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <p
            class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-3"
          >
            Nilai Tertinggi
          </p>
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center shrink-0"
            >
              <svg
                class="w-5 h-5 text-gray-700"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                />
              </svg>
            </div>
            <div>
              <p class="text-xs font-semibold text-black truncate max-w-[180px]">
                {{ topItem.name }}
              </p>
              <p class="text-sm font-bold text-black mt-0.5">
                {{ formatRp(topItem.price) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- System activity -->
      <div
        class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 p-6"
      >
        <div class="flex items-center justify-between mb-5">
          <div>
            <h2 class="text-sm font-semibold text-black">Aktivitas Sistem</h2>
            <p class="text-xs text-gray-400 mt-0.5">
              Kejadian penting terbaru pada platform
            </p>
          </div>
          <div class="flex items-center gap-1.5 no-print">
            <span
              class="live-dot w-1.5 h-1.5 rounded-full bg-black inline-block"
            ></span>
            <span class="text-xs text-gray-400">Live</span>
          </div>
        </div>
        <div class="space-y-4">
          <div
            v-for="(act, i) in systemActivity"
            :key="i"
            class="flex items-start gap-3 pb-4"
            :class="
              i < systemActivity.length - 1 ? 'border-b border-gray-50' : ''
            "
          >
            <div
              :class="[
                'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5',
                act.dark ? 'bg-black' : 'bg-gray-100',
              ]"
            >
              <svg
                class="w-3.5 h-3.5"
                :class="act.dark ? 'text-white' : 'text-gray-600'"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  :d="act.icon"
                />
              </svg>
            </div>
            <div class="flex-1">
              <p class="text-sm text-gray-700 leading-relaxed">
                {{ act.text }}
              </p>
              <p class="text-xs text-gray-400 mt-1">{{ act.time }}</p>
            </div>
          </div>
        </div>

        <!-- Export options -->
        <div class="mt-6 pt-5 border-t border-gray-100 no-print">
          <p
            class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-3"
          >
            Export Laporan
          </p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="opt in exportOptions"
              :key="opt.label"
              @click="exportReport(opt.type)"
              class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs text-gray-600 hover:border-black hover:text-black transition-all font-medium"
            >
              {{ opt.label }}
            </button>
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

.card-lift {
  transition:
    transform 0.25s ease,
    box-shadow 0.25s ease;
}
.card-lift:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
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
</style>

<style>
@media print {
  /* Hide sidebar, topbar, and elements with no-print class */
  aside,
  header,
  .no-print,
  button,
  input,
  .gap-2,
  .xl\:ml-auto {
    display: none !important;
  }
  
  /* Reset left margins and backgrounds for print */
  .min-h-screen.flex > div,
  .flex-1 {
    margin-left: 0 !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    background-color: white !important;
  }
  
  body, html {
    background-color: white !important;
    color: black !important;
  }
}
</style>
