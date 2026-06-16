<script setup>
import { ref, computed } from "vue";

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

// ─── Stats ────────────────────────────────────────────────────
const stats = [
  {
    label: "Total Pengguna",
    value: "1.245",
    sub: "Seluruh akun terdaftar",
    dark: false,
    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z",
  },
  {
    label: "Pengguna Baru",
    value: "25",
    sub: "Periode yang dipilih",
    dark: false,
    icon: "M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z",
  },
  {
    label: "Total Lelang",
    value: "325",
    sub: "Lelang dibuat",
    dark: true,
    icon: "M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10",
  },
  {
    label: "Total Penawaran",
    value: "5.280",
    sub: "Aktivitas bidding",
    dark: false,
    icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    label: "Nilai Transaksi",
    value: "Rp 1,25M",
    sub: "Akumulasi lelang selesai",
    dark: false,
    icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
  },
  {
    label: "Lelang Aktif",
    value: "78",
    sub: "Sedang berlangsung",
    dark: false,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
];

// ─── Chart data (user growth - line chart) ────────────────────
const userGrowthData = [
  { label: "Jan", value: 80 },
  { label: "Feb", value: 110 },
  { label: "Mar", value: 95 },
  { label: "Apr", value: 140 },
  { label: "Mei", value: 125 },
  { label: "Jun", value: 160 },
];

const auctionChartData = [
  { label: "Jan", new: 30, active: 45, ended: 20 },
  { label: "Feb", new: 42, active: 60, ended: 35 },
  { label: "Mar", new: 28, active: 55, ended: 40 },
  { label: "Apr", new: 55, active: 72, ended: 48 },
  { label: "Mei", new: 48, active: 65, ended: 55 },
  { label: "Jun", new: 62, active: 78, ended: 60 },
];

const bidChartData = [
  { label: "Jan", value: 620 },
  { label: "Feb", value: 840 },
  { label: "Mar", value: 730 },
  { label: "Apr", value: 980 },
  { label: "Mei", value: 890 },
  { label: "Jun", value: 1220 },
];

// ─── Category stats ───────────────────────────────────────────
const categoryStats = [
  { name: "Lukisan", auctions: 125, bids: 1250, pct: 38 },
  { name: "Patung", auctions: 87, bids: 890, pct: 27 },
  { name: "Barang Antik", auctions: 68, bids: 740, pct: 21 },
  { name: "Topeng Tradisional", auctions: 45, bids: 520, pct: 14 },
];

// ─── Top auctions ─────────────────────────────────────────────
const topAuctions = [
  {
    name: "Lukisan Bali Klasik",
    seller: "I Made Sudarma",
    bids: 45,
    price: 12500000,
  },
  {
    name: "Patung Garuda Bali",
    seller: "I Putu Arya",
    bids: 38,
    price: 15000000,
  },
  {
    name: "Topeng Barong Antik",
    seller: "Ni Luh Ratna",
    bids: 35,
    price: 7500000,
  },
  {
    name: "Keris Pusaka Jawa",
    seller: "Budi Santoso",
    bids: 29,
    price: 22000000,
  },
  {
    name: "Ukiran Kayu Garuda",
    seller: "Ketut Wirawan",
    bids: 24,
    price: 18000000,
  },
];

// ─── Top users ────────────────────────────────────────────────
const topUsers = [
  { name: "I Putu Arya", bids: 120, auctions: 8, won: 6 },
  { name: "Ni Luh Ratna", bids: 95, auctions: 5, won: 4 },
  { name: "Kadek Wijaya", bids: 88, auctions: 3, won: 5 },
  { name: "Budi Santoso", bids: 75, auctions: 0, won: 5 },
  { name: "Wayan Sudira", bids: 62, auctions: 3, won: 2 },
];

// ─── Realtime stats ───────────────────────────────────────────
const realtimeStats = [
  {
    label: "Pengguna Online",
    value: "42",
    icon: "M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z",
  },
  {
    label: "Viewer Aktif",
    value: "67",
    icon: "M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z",
  },
  {
    label: "Lelang Berlangsung",
    value: "78",
    icon: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  { label: "Bid (1 jam)", value: "25", icon: "M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" },
];

// ─── System activity ──────────────────────────────────────────
const systemActivity = [
  {
    text: "25 pengguna baru berhasil mendaftar hari ini.",
    time: "Hari ini",
    dark: false,
    icon: "M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z",
  },
  {
    text: "12 lelang baru dibuat dalam 24 jam terakhir.",
    time: "24 jam lalu",
    dark: true,
    icon: "M12 4v16m8-8H4",
  },
  {
    text: 'Lelang "Patung Garuda Bali" berhasil mencapai harga Rp 15.000.000.',
    time: "2 jam lalu",
    dark: false,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
];

// ─── Sales stats ──────────────────────────────────────────────
const salesStats = [
  { label: "Total Barang Terjual", value: "215 Barang" },
  { label: "Nilai Penjualan Tertinggi", value: "Rp 45.000.000" },
  { label: "Nilai Rata-rata", value: "Rp 5.800.000" },
];

// ─── Chart helpers ────────────────────────────────────────────
const lineMax = computed(() => Math.max(...userGrowthData.map((d) => d.value)));
const bidMax = computed(() => Math.max(...bidChartData.map((d) => d.value)));
const barMax = computed(() =>
  Math.max(...auctionChartData.map((d) => d.new + d.active + d.ended)),
);

function lineY(val, max, h = 80) {
  return h - (val / max) * h;
}

function polyline(data, max, h = 80, w = 280) {
  const step = w / (data.length - 1);
  return data.map((d, i) => `${i * step},${lineY(d.value, max, h)}`).join(" ");
}

function formatRp(val) {
  return "Rp " + val.toLocaleString("id-ID");
}

// ─── Export ───────────────────────────────────────────────────
function exportData(format) {
  console.log(`Export ${format}`);
}
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
      <div class="flex gap-2 shrink-0">
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
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
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
            class="text-xs text-gray-400 font-medium bg-gray-100 px-3 py-1 rounded-lg"
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
              :cx="i * (280 / (userGrowthData.length - 1))"
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
            class="text-xs text-gray-400 font-medium bg-gray-100 px-3 py-1 rounded-lg"
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
              :cx="i * (280 / (bidChartData.length - 1))"
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
            class="text-xs text-gray-400 font-medium bg-gray-100 px-3 py-1 rounded-lg"
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
            class="text-xs text-gray-400 font-medium bg-gray-100 px-3 py-1 rounded-lg"
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
            <!-- Lukisan 38% -->
            <circle
              cx="60"
              cy="60"
              r="44"
              fill="none"
              stroke="#111827"
              stroke-width="18"
              stroke-dasharray="104.6 172.0"
              stroke-dashoffset="0"
              transform="rotate(-90 60 60)"
            />
            <!-- Patung 27% -->
            <circle
              cx="60"
              cy="60"
              r="44"
              fill="none"
              stroke="#6b7280"
              stroke-width="18"
              stroke-dasharray="74.4 202.2"
              stroke-dashoffset="-104.6"
              transform="rotate(-90 60 60)"
            />
            <!-- Antik 21% -->
            <circle
              cx="60"
              cy="60"
              r="44"
              fill="none"
              stroke="#d1d5db"
              stroke-width="18"
              stroke-dasharray="57.8 218.8"
              stroke-dashoffset="-179.0"
              transform="rotate(-90 60 60)"
            />
            <!-- Topeng 14% -->
            <circle
              cx="60"
              cy="60"
              r="44"
              fill="none"
              stroke="#e5e7eb"
              stroke-width="18"
              stroke-dasharray="38.6 238.0"
              stroke-dashoffset="-236.8"
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
              325
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
              :class="
                ['bg-gray-800', 'bg-gray-500', 'bg-gray-300', 'bg-gray-200'][i]
              "
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
                  :class="
                    [
                      'bg-gray-800',
                      'bg-gray-500',
                      'bg-gray-300',
                      'bg-gray-200',
                    ][i]
                  "
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
          <p class="text-white font-bold text-3xl">215</p>
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
              <p class="text-xs font-semibold text-black">
                Patung Garuda Emas Bali
              </p>
              <p class="text-sm font-bold text-black mt-0.5">Rp 45.000.000</p>
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
          <div class="flex items-center gap-1.5">
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
        <div class="mt-6 pt-5 border-t border-gray-100">
          <p
            class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-3"
          >
            Export Laporan
          </p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="label in [
                'Laporan Pengguna',
                'Laporan Lelang',
                'Laporan Penawaran',
                'Laporan Transaksi',
                'Laporan Kategori',
              ]"
              :key="label"
              class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs text-gray-600 hover:border-black hover:text-black transition-all font-medium"
            >
              {{ label }}
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
