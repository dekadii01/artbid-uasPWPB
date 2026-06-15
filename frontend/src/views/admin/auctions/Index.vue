<script setup>
import { ref, computed } from "vue";

// ─── System alerts ───────────────────────────────────────────
const systemAlerts = [
  {
    text: "Terdapat 2 lelang yang menunggu verifikasi admin.",
    action: "Verifikasi Sekarang",
    dark: true,
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
  },
  {
    text: "Terdapat 1 lelang yang dilaporkan pengguna.",
    action: "Tinjau Laporan",
    dark: false,
    icon: "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    text: "Terdapat 5 lelang yang akan berakhir dalam 1 jam ke depan.",
    action: "Pantau",
    dark: false,
    icon: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
  },
];

// ─── Stats ───────────────────────────────────────────────────
const stats = [
  {
    label: "Total Lelang",
    value: "325",
    sub: "Seluruh lelang dibuat",
    filter: "all",
    dark: false,
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
  },
  {
    label: "Lelang Aktif",
    value: "78",
    sub: "Sedang berlangsung",
    filter: "active",
    dark: true,
    icon: "M13 10V3L4 14h7v7l9-11h-7z",
  },
  {
    label: "Akan Datang",
    value: "32",
    sub: "Belum dimulai",
    filter: "upcoming",
    dark: false,
    icon: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
  },
  {
    label: "Selesai",
    value: "215",
    sub: "Pemenang sudah ditentukan",
    filter: "ended",
    dark: false,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
];

// ─── Filters ─────────────────────────────────────────────────
const statusFilters = [
  { label: "Semua", value: "all", count: 325 },
  { label: "Akan Datang", value: "upcoming", count: 32 },
  { label: "Sedang Berlangsung", value: "active", count: 78 },
  { label: "Selesai", value: "ended", count: 215 },
  { label: "Dibatalkan", value: "cancelled", count: 0 },
];

const categories = [
  "Lukisan",
  "Patung",
  "Topeng Tradisional",
  "Ukiran Kayu",
  "Barang Antik",
  "Keramik",
  "Fotografi",
];

const activeFilter = ref("all");
const filterCategory = ref("");
const filterPeriod = ref("");
const sortBy = ref("newest");
const searchQuery = ref("");
const currentPage = ref(1);
const perPage = 8;

// ─── Auctions data ───────────────────────────────────────────
const auctions = ref([
  {
    id: 1,
    name: "Lukisan Bali Klasik Tahun 1980",
    seller: "I Made Sudarma",
    sellerEmail: "sudarma@email.com",
    sellerAuctions: 8,
    category: "Lukisan",
    image:
      "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=200&q=80",
    startPrice: 5000000,
    currentPrice: 12500000,
    totalBids: 45,
    watching: 38,
    status: "active",
    startDate: "10 Jun 2026, 09.00",
    endDate: "12 Jun 2026, 21.00 WITA",
    description:
      "Lukisan klasik bergaya Bali tradisional dari seniman terkemuka.",
  },
  {
    id: 2,
    name: "Patung Garuda Bali",
    seller: "I Putu Arya",
    sellerEmail: "arya@email.com",
    sellerAuctions: 12,
    category: "Patung",
    image:
      "https://images.unsplash.com/photo-1578926288207-32356f3e5e93?w=200&q=80",
    startPrice: 8000000,
    currentPrice: 15000000,
    totalBids: 38,
    watching: 21,
    status: "active",
    startDate: "11 Jun 2026, 10.00",
    endDate: "13 Jun 2026, 18.00 WITA",
    description: "Patung Garuda berukir halus dari kayu jati pilihan.",
  },
  {
    id: 3,
    name: "Topeng Barong Antik",
    seller: "Ni Luh Eka Sari",
    sellerEmail: "eka@email.com",
    sellerAuctions: 5,
    category: "Topeng Tradisional",
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=200&q=80",
    startPrice: 4000000,
    currentPrice: 12500000,
    totalBids: 52,
    watching: 29,
    status: "active",
    startDate: "12 Jun 2026, 08.00",
    endDate: "14 Jun 2026, 20.00 WITA",
    description: "Topeng Barong asli dari abad ke-19, kondisi terawat.",
  },
  {
    id: 4,
    name: "Ukiran Kayu Garuda",
    seller: "Dewa Gede Artana",
    sellerEmail: "artana@email.com",
    sellerAuctions: 3,
    category: "Ukiran Kayu",
    image:
      "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=200&q=80",
    startPrice: 6000000,
    currentPrice: 18000000,
    totalBids: 48,
    watching: 64,
    status: "active",
    startDate: "09 Jun 2026, 14.00",
    endDate: "15 Jun 2026, 16.00 WITA",
    description: "Ukiran kayu eboni bergambar Garuda, karya maestro Bali.",
  },
  {
    id: 5,
    name: "Harmoni Semesta",
    seller: "I Made Wijaya",
    sellerEmail: "wijaya@email.com",
    sellerAuctions: 7,
    category: "Lukisan",
    image:
      "https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=200&q=80",
    startPrice: 7500000,
    currentPrice: 7500000,
    totalBids: 0,
    watching: 14,
    status: "upcoming",
    startDate: "18 Jun 2026, 09.00",
    endDate: "22 Jun 2026, 18.00 WITA",
    description: "Lukisan abstrak bernuansa alam semesta, teknik acrylic.",
  },
  {
    id: 6,
    name: "Sunrise Penida",
    seller: "Agung Rai",
    sellerEmail: "agung@email.com",
    sellerAuctions: 15,
    category: "Fotografi",
    image:
      "https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=200&q=80",
    startPrice: 3000000,
    currentPrice: 3000000,
    totalBids: 0,
    watching: 9,
    status: "upcoming",
    startDate: "19 Jun 2026, 08.00",
    endDate: "21 Jun 2026, 20.00 WITA",
    description: "Foto sunrise di Nusa Penida, cetak terbatas edisi 1/10.",
  },
  {
    id: 7,
    name: "Dewi Kesuburan",
    seller: "Ketut Suardana",
    sellerEmail: "suardana2@email.com",
    sellerAuctions: 4,
    category: "Patung",
    image:
      "https://images.unsplash.com/photo-1578301978018-3005759f48f7?w=200&q=80",
    startPrice: 9000000,
    currentPrice: 15000000,
    totalBids: 48,
    watching: 0,
    status: "ended",
    startDate: "1 Jun 2026, 10.00",
    endDate: "9 Jun 2026, 18.00 WITA",
    description: "Patung Dewi Kesuburan berbahan batu andesit hitam.",
  },
  {
    id: 8,
    name: "Alam Tak Terbatas",
    seller: "Sang Ayu Riani",
    sellerEmail: "riani@email.com",
    sellerAuctions: 6,
    category: "Lukisan",
    image:
      "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=200&q=80",
    startPrice: 8500000,
    currentPrice: 18200000,
    totalBids: 41,
    watching: 0,
    status: "ended",
    startDate: "28 Mei 2026, 09.00",
    endDate: "5 Jun 2026, 21.00 WITA",
    description: "Lukisan batik sutra berlapis emas, motif alam semesta.",
  },
  {
    id: 9,
    name: "Jejak Digital",
    seller: "Putu Wiradinata",
    sellerEmail: "wira@email.com",
    sellerAuctions: 2,
    category: "Barang Antik",
    image:
      "https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=200&q=80",
    startPrice: 20000000,
    currentPrice: 20000000,
    totalBids: 0,
    watching: 32,
    status: "upcoming",
    startDate: "20 Jun 2026, 10.00",
    endDate: "25 Jun 2026, 18.00 WITA",
    description: "Instalasi seni digital interaktif, mixed media.",
  },
  {
    id: 10,
    name: "Tanah & Api",
    seller: "Wayan Budarta",
    sellerEmail: "budarta@email.com",
    sellerAuctions: 9,
    category: "Barang Antik",
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=200&q=80",
    startPrice: 4500000,
    currentPrice: 8750000,
    totalBids: 29,
    watching: 18,
    status: "active",
    startDate: "13 Jun 2026, 11.00",
    endDate: "16 Jun 2026, 17.00 WITA",
    description: "Kendi tanah liat tradisional era 1800-an, kondisi utuh.",
  },
]);

// ─── Computed ────────────────────────────────────────────────
const filteredAuctions = computed(() => {
  let list = [...auctions.value];

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(
      (a) =>
        a.name.toLowerCase().includes(q) ||
        a.seller.toLowerCase().includes(q) ||
        a.category.toLowerCase().includes(q),
    );
  }
  if (activeFilter.value !== "all")
    list = list.filter((a) => a.status === activeFilter.value);
  if (filterCategory.value)
    list = list.filter((a) => a.category === filterCategory.value);

  if (sortBy.value === "price_high")
    list.sort((a, b) => b.currentPrice - a.currentPrice);
  else if (sortBy.value === "most_bids")
    list.sort((a, b) => b.totalBids - a.totalBids);
  else if (sortBy.value === "ending")
    list.sort(
      (a, b) =>
        (a.status === "active" ? -1 : 1) - (b.status === "active" ? -1 : 1),
    );
  else if (sortBy.value === "oldest") list.sort((a, b) => a.id - b.id);
  else list.sort((a, b) => b.id - a.id);

  return list;
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredAuctions.value.length / perPage)),
);

const paginatedAuctions = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredAuctions.value.slice(start, start + perPage);
});

// ─── Selection ───────────────────────────────────────────────
const selectedId = ref(null);
const selectedAuction = ref(null);

function selectAuction(auction) {
  if (!auction) return;
  if (selectedId.value === auction.id) {
    selectedId.value = null;
    selectedAuction.value = null;
  } else {
    selectedId.value = auction.id;
    selectedAuction.value = auction;
  }
}

// ─── Bid history modal ───────────────────────────────────────
const bidHistoryTarget = ref(null);

function openBidHistory(auction) {
  bidHistoryTarget.value = auction;
}

const sampleBidHistory = [
  { name: "Budi Santoso", amount: 12500000, time: "12 Jun 2026, 19:35 WITA" },
  { name: "I Putu Gede", amount: 11000000, time: "12 Jun 2026, 18:20 WITA" },
  { name: "Ni Made Ratna", amount: 9500000, time: "12 Jun 2026, 17:05 WITA" },
  { name: "Gede Mahendra", amount: 8000000, time: "11 Jun 2026, 22:10 WITA" },
  { name: "Wayan Sudira", amount: 6500000, time: "11 Jun 2026, 15:45 WITA" },
];

// ─── Confirm modal ───────────────────────────────────────────
const confirmTarget = ref(null);

function confirmAction(action, auction) {
  confirmTarget.value = { action, auction };
}

function handleConfirm() {
  if (!confirmTarget.value) return;
  const { action, auction } = confirmTarget.value;
  if (action === "delete") {
    auctions.value = auctions.value.filter((a) => a.id !== auction.id);
    if (selectedAuction.value?.id === auction.id) {
      selectedAuction.value = null;
      selectedId.value = null;
    }
  } else if (action === "deactivate") {
    const target = auctions.value.find((a) => a.id === auction.id);
    if (target) target.status = "cancelled";
    if (selectedAuction.value?.id === auction.id)
      selectedAuction.value = { ...target };
  }
  confirmTarget.value = null;
}

// ─── Side panels data ────────────────────────────────────────
const endingSoon = [
  {
    id: 1,
    name: "Lukisan Bali Klasik",
    image:
      "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=100&q=80",
    currentPrice: 12500000,
    totalBids: 45,
    countdown: "00:25:08",
  },
  {
    id: 3,
    name: "Topeng Barong Antik",
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=100&q=80",
    currentPrice: 12500000,
    totalBids: 52,
    countdown: "00:09:45",
  },
  {
    id: 10,
    name: "Tanah & Api",
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=100&q=80",
    currentPrice: 8750000,
    totalBids: 29,
    countdown: "00:41:20",
  },
];

const mostActive = [
  {
    id: 3,
    name: "Topeng Barong Antik",
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=100&q=80",
    totalBids: 52,
    watching: 29,
    currentPrice: 12500000,
  },
  {
    id: 4,
    name: "Ukiran Kayu Garuda",
    image:
      "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=100&q=80",
    totalBids: 48,
    watching: 64,
    currentPrice: 18000000,
  },
  {
    id: 1,
    name: "Lukisan Bali Klasik",
    image:
      "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=100&q=80",
    totalBids: 45,
    watching: 38,
    currentPrice: 12500000,
  },
  {
    id: 2,
    name: "Patung Garuda Bali",
    image:
      "https://images.unsplash.com/photo-1578926288207-32356f3e5e93?w=100&q=80",
    totalBids: 38,
    watching: 21,
    currentPrice: 15000000,
  },
];

// ─── Helpers ─────────────────────────────────────────────────
function formatRp(val) {
  return "Rp " + val.toLocaleString("id-ID");
}

function statusBadge(status) {
  const map = {
    active: {
      class: "bg-gray-100 text-gray-800",
      dot: "bg-gray-800",
      label: "Aktif",
    },
    upcoming: {
      class: "bg-gray-100 text-gray-500",
      dot: "bg-gray-400",
      label: "Akan Datang",
    },
    ended: { class: "bg-black text-white", dot: "bg-white", label: "Selesai" },
    cancelled: {
      class: "bg-gray-100 text-gray-400",
      dot: "bg-gray-300",
      label: "Dibatalkan",
    },
  };
  return map[status] ?? map.ended;
}

function exportData() {
  console.log("Export data lelang");
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 font-sans flex flex-col">
    <div class="flex-1 px-8 py-8 space-y-6">
      <!-- ═══════════════════ HEADER ═══════════════════ -->
      <div class="flex justify-between items-center">
        <div>
          <span
            class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
            >Admin Panel</span
          >
          <h1 class="text-3xl font-bold text-black mt-1.5 tracking-tight">
            Manajemen Lelang
          </h1>
          <p class="text-gray-500 text-sm mt-2 max-w-2xl leading-relaxed">
            Kelola seluruh data lelang yang tersedia pada platform, pantau
            aktivitas penawaran, dan pastikan setiap lelang berjalan sesuai
            aturan yang telah ditetapkan.
          </p>
        </div>
        <button
          @click="$router.push('/admin/auctions/create')"
          class="flex items-center gap-2 px-5 py-2 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
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
              d="M12 4v16m8-8H4"
            />
          </svg>
          Tambah Lelang
        </button>
      </div>

      <!-- ═══════════════════ SYSTEM ALERTS ═══════════════════ -->
      <div class="space-y-2">
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
            class="text-xs text-gray-500 hover:text-black font-medium transition-colors shrink-0 whitespace-nowrap"
          >
            {{ alert.action }}
          </button>
        </div>
      </div>

      <!-- ═══════════════════ STATS ═══════════════════ -->
      <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div
          v-for="stat in stats"
          :key="stat.label"
          :class="[
            'rounded-2xl px-6 py-6 border card-lift cursor-pointer transition-all duration-200',
            stat.dark ? 'bg-black border-black' : 'bg-white border-gray-100',
            activeFilter === stat.filter
              ? 'ring-2 ring-black ring-offset-2'
              : '',
          ]"
          @click="activeFilter = stat.filter"
        >
          <div class="flex items-start justify-between mb-3">
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

      <!-- ═══════════════════ FILTERS ═══════════════════ -->
      <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex flex-col xl:flex-row gap-3">
          <!-- Status tabs -->
          <div class="flex flex-wrap gap-2 flex-1">
            <button
              v-for="f in statusFilters"
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
                :class="[
                  'ml-1.5 text-xs',
                  activeFilter === f.value ? 'text-white/60' : 'text-gray-400',
                ]"
                >{{ f.count }}</span
              >
            </button>
          </div>

          <!-- Dropdowns row -->
          <div class="flex flex-wrap gap-2">
            <div class="relative">
              <select
                v-model="filterCategory"
                class="appearance-none border border-gray-200 rounded-xl pl-3 pr-8 py-2 text-xs text-gray-600 outline-none focus:border-black transition-colors bg-gray-50 cursor-pointer"
              >
                <option value="">Semua Kategori</option>
                <option v-for="c in categories" :key="c" :value="c">
                  {{ c }}
                </option>
              </select>
              <svg
                class="w-3 h-3 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
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
            <div class="relative">
              <select
                v-model="filterPeriod"
                class="appearance-none border border-gray-200 rounded-xl pl-3 pr-8 py-2 text-xs text-gray-600 outline-none focus:border-black transition-colors bg-gray-50 cursor-pointer"
              >
                <option value="">Semua Periode</option>
                <option value="today">Hari Ini</option>
                <option value="week">Minggu Ini</option>
                <option value="month">Bulan Ini</option>
                <option value="year">Tahun Ini</option>
              </select>
              <svg
                class="w-3 h-3 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
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
            <div class="relative">
              <select
                v-model="sortBy"
                class="appearance-none border border-gray-200 rounded-xl pl-3 pr-8 py-2 text-xs text-gray-600 outline-none focus:border-black transition-colors bg-gray-50 cursor-pointer"
              >
                <option value="newest">Terbaru</option>
                <option value="oldest">Terlama</option>
                <option value="ending">Akan Berakhir</option>
                <option value="price_high">Harga Tertinggi</option>
                <option value="most_bids">Jumlah Penawaran</option>
              </select>
              <svg
                class="w-3 h-3 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
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
        </div>
      </div>

      <!-- ═══════════════════ MAIN CONTENT ═══════════════════ -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- ── TABLE ── -->
        <div class="xl:col-span-2 space-y-4">
          <!-- Table card -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <div
              class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
            >
              <p class="text-sm font-semibold text-black">
                Menampilkan {{ paginatedAuctions.length }} dari
                {{ filteredAuctions.length }} data lelang
              </p>
              <button
                @click="exportData"
                class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-black transition-colors font-medium"
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
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                  />
                </svg>
                Export
              </button>
            </div>

            <!-- Empty -->
            <div
              v-if="paginatedAuctions.length === 0"
              class="py-20 text-center"
            >
              <div
                class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4"
              >
                <svg
                  class="w-6 h-6 text-gray-400"
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
              <p class="font-medium text-gray-700 text-sm mb-1">
                Tidak ada data ditemukan
              </p>
              <p class="text-gray-400 text-xs">
                Coba ubah filter atau kata kunci pencarian.
              </p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-100">
                    <th
                      class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Barang
                    </th>
                    <th
                      class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Penjual
                    </th>
                    <th
                      class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell"
                    >
                      Kategori
                    </th>
                    <th
                      class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Harga
                    </th>
                    <th
                      class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell"
                    >
                      Bid
                    </th>
                    <th
                      class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Status
                    </th>
                    <th
                      class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden xl:table-cell"
                    >
                      Berakhir
                    </th>
                    <th class="px-4 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr
                    v-for="auction in paginatedAuctions"
                    :key="auction.id"
                    class="hover:bg-gray-50 transition-colors cursor-pointer group"
                    :class="selectedId === auction.id ? 'bg-gray-50' : ''"
                    @click="selectAuction(auction)"
                  >
                    <!-- Barang -->
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-3">
                        <div
                          class="w-10 h-10 rounded-xl bg-cover bg-center bg-gray-100 shrink-0"
                          :style="{
                            backgroundImage: `url('${auction.image}')`,
                          }"
                        ></div>
                        <div class="min-w-0">
                          <p
                            class="text-sm font-medium text-black truncate max-w-40"
                          >
                            {{ auction.name }}
                          </p>
                          <p class="text-xs text-gray-400 mt-0.5">
                            #{{ auction.id }}
                          </p>
                        </div>
                      </div>
                    </td>
                    <!-- Penjual -->
                    <td class="px-4 py-4">
                      <p class="text-sm text-gray-700 whitespace-nowrap">
                        {{ auction.seller }}
                      </p>
                    </td>
                    <!-- Kategori -->
                    <td class="px-4 py-4 hidden lg:table-cell">
                      <span
                        class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full font-medium whitespace-nowrap"
                        >{{ auction.category }}</span
                      >
                    </td>
                    <!-- Harga -->
                    <td class="px-4 py-4 text-right">
                      <p class="text-sm font-bold text-black whitespace-nowrap">
                        {{ formatRp(auction.currentPrice) }}
                      </p>
                      <p class="text-xs text-gray-400 mt-0.5 whitespace-nowrap">
                        Mulai {{ formatRp(auction.startPrice) }}
                      </p>
                    </td>
                    <!-- Bid -->
                    <td class="px-4 py-4 text-center hidden md:table-cell">
                      <p class="text-sm font-semibold text-black">
                        {{ auction.totalBids }}
                      </p>
                    </td>
                    <!-- Status -->
                    <td class="px-4 py-4 text-center">
                      <span
                        :class="[
                          'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap',
                          statusBadge(auction.status).class,
                        ]"
                      >
                        <span
                          class="w-1.5 h-1.5 rounded-full shrink-0"
                          :class="statusBadge(auction.status).dot"
                        ></span>
                        {{ statusBadge(auction.status).label }}
                      </span>
                    </td>
                    <!-- Berakhir -->
                    <td class="px-4 py-4 hidden xl:table-cell">
                      <p class="text-xs text-gray-600 whitespace-nowrap">
                        {{ auction.endDate }}
                      </p>
                    </td>
                    <!-- Aksi -->
                    <td class="px-4 py-4" @click.stop>
                      <div
                        class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                        <button
                          @click="selectAuction(auction)"
                          class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:border-black hover:text-black transition-all"
                          title="Lihat Detail"
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
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            />
                          </svg>
                        </button>
                        <button
                          @click="openBidHistory(auction)"
                          class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:border-black hover:text-black transition-all"
                          title="Riwayat Penawaran"
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
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6m-6 4h4"
                            />
                          </svg>
                        </button>
                        <button
                          v-if="auction.status === 'active'"
                          @click="confirmAction('deactivate', auction)"
                          class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:border-gray-800 hover:text-gray-800 transition-all"
                          title="Nonaktifkan"
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
                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                            />
                          </svg>
                        </button>
                        <button
                          @click="confirmAction('delete', auction)"
                          class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:border-gray-400 hover:text-gray-700 transition-all"
                          title="Hapus"
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
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div
              v-if="filteredAuctions.length > 0"
              class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-4"
            >
              <p class="text-xs text-gray-400">
                Menampilkan {{ (currentPage - 1) * perPage + 1 }}–{{
                  Math.min(currentPage * perPage, filteredAuctions.length)
                }}
                dari {{ filteredAuctions.length }} data lelang
              </p>
              <div class="flex items-center gap-1">
                <button
                  @click="currentPage--"
                  :disabled="currentPage === 1"
                  class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-500 hover:border-black hover:text-black transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  Sebelumnya
                </button>
                <button
                  v-for="p in totalPages"
                  :key="p"
                  @click="currentPage = p"
                  :class="[
                    'w-8 h-8 text-xs rounded-lg border transition-all',
                    currentPage === p
                      ? 'bg-black border-black text-white'
                      : 'border-gray-200 text-gray-500 hover:border-black hover:text-black',
                  ]"
                >
                  {{ p }}
                </button>
                <button
                  @click="currentPage++"
                  :disabled="currentPage === totalPages"
                  class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-500 hover:border-black hover:text-black transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  Berikutnya
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ── RIGHT SIDEBAR ── -->
        <div class="space-y-4">
          <!-- Quick preview -->
          <transition name="slide-fade">
            <div
              v-if="selectedAuction"
              class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
            >
              <div class="relative h-44">
                <div
                  class="w-full h-full bg-cover bg-center"
                  :style="{
                    backgroundImage: `url('${selectedAuction.image}')`,
                  }"
                ></div>
                <div
                  class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent"
                ></div>
                <button
                  @click="selectedAuction = null"
                  class="absolute top-3 right-3 w-7 h-7 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/40 transition-colors"
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
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
                <div class="absolute bottom-3 left-4">
                  <span
                    :class="[
                      'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium',
                      statusBadge(selectedAuction.status).class,
                    ]"
                  >
                    <span
                      class="w-1.5 h-1.5 rounded-full"
                      :class="statusBadge(selectedAuction.status).dot"
                    ></span>
                    {{ statusBadge(selectedAuction.status).label }}
                  </span>
                </div>
              </div>

              <div class="p-5 space-y-4">
                <!-- Barang -->
                <div>
                  <p
                    class="text-xs text-gray-400 uppercase tracking-widest mb-2 font-semibold"
                  >
                    Informasi Barang
                  </p>
                  <p class="font-bold text-sm text-black leading-snug">
                    {{ selectedAuction.name }}
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    {{ selectedAuction.category }}
                  </p>
                  <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    {{ selectedAuction.description }}
                  </p>
                </div>

                <div class="border-t border-gray-100 pt-4">
                  <p
                    class="text-xs text-gray-400 uppercase tracking-widest mb-2 font-semibold"
                  >
                    Penjual
                  </p>
                  <div class="flex items-center gap-2.5">
                    <div
                      class="w-8 h-8 rounded-xl bg-gray-900 flex items-center justify-center text-white text-xs font-bold shrink-0"
                    >
                      {{ selectedAuction.seller.charAt(0) }}
                    </div>
                    <div>
                      <p class="text-sm font-medium text-black">
                        {{ selectedAuction.seller }}
                      </p>
                      <p class="text-xs text-gray-400">
                        {{ selectedAuction.sellerEmail }}
                      </p>
                    </div>
                  </div>
                  <p class="text-xs text-gray-400 mt-2">
                    {{ selectedAuction.sellerAuctions }} lelang dibuat
                  </p>
                </div>

                <div class="border-t border-gray-100 pt-4">
                  <p
                    class="text-xs text-gray-400 uppercase tracking-widest mb-3 font-semibold"
                  >
                    Aktivitas
                  </p>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-xl p-3">
                      <p class="text-xs text-gray-400 mb-0.5">Harga Awal</p>
                      <p class="text-sm font-bold text-black">
                        {{ formatRp(selectedAuction.startPrice) }}
                      </p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                      <p class="text-xs text-gray-400 mb-0.5">
                        Harga Tertinggi
                      </p>
                      <p class="text-sm font-bold text-black">
                        {{ formatRp(selectedAuction.currentPrice) }}
                      </p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                      <p class="text-xs text-gray-400 mb-0.5">Total Bid</p>
                      <p class="text-sm font-bold text-black">
                        {{ selectedAuction.totalBids }}
                      </p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                      <p class="text-xs text-gray-400 mb-0.5">Sedang Dilihat</p>
                      <p class="text-sm font-bold text-black">
                        {{ selectedAuction.watching }}
                      </p>
                    </div>
                  </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                  <p
                    class="text-xs text-gray-400 uppercase tracking-widest mb-3 font-semibold"
                  >
                    Waktu
                  </p>
                  <div class="space-y-1.5">
                    <div class="flex justify-between text-xs">
                      <span class="text-gray-400">Mulai</span>
                      <span class="text-gray-700 font-medium">{{
                        selectedAuction.startDate
                      }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                      <span class="text-gray-400">Berakhir</span>
                      <span class="text-gray-700 font-medium">{{
                        selectedAuction.endDate
                      }}</span>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-2 pt-1">
                  <button
                    @click="
                      $router.push(`/admin/auctions/${selectedAuction.id}`)
                    "
                    class="w-full py-2.5 bg-black text-white rounded-xl text-xs font-medium hover:bg-gray-800 transition-colors"
                  >
                    Lihat Detail Lengkap
                  </button>
                  <button
                    @click="openBidHistory(selectedAuction)"
                    class="w-full py-2.5 border border-gray-200 text-gray-600 rounded-xl text-xs font-medium hover:border-black hover:text-black transition-all duration-300"
                  >
                    Lihat Riwayat Penawaran
                  </button>
                  <button
                    v-if="selectedAuction.status === 'active'"
                    @click="confirmAction('deactivate', selectedAuction)"
                    class="w-full py-2.5 border border-gray-200 text-gray-500 rounded-xl text-xs font-medium hover:border-gray-500 hover:text-gray-700 transition-all duration-300"
                  >
                    Nonaktifkan Lelang
                  </button>
                </div>
              </div>
            </div>
          </transition>

          <!-- Default right panel when nothing selected -->
          <template v-if="!selectedAuction">
            <!-- Ending soon -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
              <div class="flex items-center gap-2 mb-4">
                <span
                  class="live-dot w-1.5 h-1.5 rounded-full bg-black inline-block"
                ></span>
                <h2 class="text-sm font-semibold text-black">
                  Segera Berakhir
                </h2>
              </div>
              <div class="space-y-3">
                <div
                  v-for="item in endingSoon"
                  :key="item.id"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100 hover:border-gray-300 transition-colors cursor-pointer"
                  @click="selectAuction(auctions.find((a) => a.id === item.id))"
                >
                  <div class="flex items-center gap-2.5 min-w-0">
                    <div
                      class="w-9 h-9 rounded-lg bg-cover bg-center bg-gray-200 shrink-0"
                      :style="{ backgroundImage: `url('${item.image}')` }"
                    ></div>
                    <div class="min-w-0">
                      <p class="text-xs font-medium text-black truncate">
                        {{ item.name }}
                      </p>
                      <p class="text-xs text-gray-400">
                        {{ formatRp(item.currentPrice) }}
                      </p>
                      <p class="text-xs text-gray-400">
                        {{ item.totalBids }} bid
                      </p>
                    </div>
                  </div>
                  <span
                    class="text-xs font-mono font-bold text-black shrink-0 ml-2"
                    >{{ item.countdown }}</span
                  >
                </div>
              </div>
            </div>

            <!-- Most active -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
              <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-black">
                  Aktivitas Tertinggi
                </h2>
                <button
                  class="text-xs text-gray-400 hover:text-black transition-colors font-medium"
                >
                  Lihat Semua
                </button>
              </div>
              <div class="space-y-3">
                <div
                  v-for="(item, i) in mostActive"
                  :key="item.id"
                  class="flex items-start gap-3"
                  :class="
                    i < mostActive.length - 1
                      ? 'pb-3 border-b border-gray-50'
                      : ''
                  "
                >
                  <span
                    class="text-xs font-bold text-gray-300 mt-1 w-4 shrink-0"
                    >{{ String(i + 1).padStart(2, "0") }}</span
                  >
                  <div
                    class="w-8 h-8 rounded-lg bg-cover bg-center bg-gray-200 shrink-0"
                    :style="{ backgroundImage: `url('${item.image}')` }"
                  ></div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-black truncate">
                      {{ item.name }}
                    </p>
                    <div class="flex items-center gap-3 mt-1">
                      <span class="text-xs text-gray-400"
                        >{{ item.totalBids }} bid</span
                      >
                      <span class="text-xs text-gray-300">·</span>
                      <span class="text-xs text-gray-400"
                        >{{ item.watching }} lihat</span
                      >
                    </div>
                    <p class="text-xs font-semibold text-black mt-0.5">
                      {{ formatRp(item.currentPrice) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- ═══════════════════ BID HISTORY MODAL ═══════════════════ -->
    <transition name="fade-modal">
      <div
        v-if="bidHistoryTarget"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="bidHistoryTarget = null"
      >
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div
          class="relative bg-white rounded-2xl w-full max-w-lg shadow-xl overflow-hidden"
        >
          <div
            class="px-6 py-5 border-b border-gray-100 flex items-center justify-between"
          >
            <div>
              <p class="font-semibold text-sm text-black">Riwayat Penawaran</p>
              <p class="text-xs text-gray-400 mt-0.5">
                {{ bidHistoryTarget.name }}
              </p>
            </div>
            <button
              @click="bidHistoryTarget = null"
              class="w-8 h-8 flex items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:border-black hover:text-black transition-all"
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
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
          <div class="divide-y divide-gray-50 max-h-96 overflow-y-auto">
            <div
              v-for="(bid, i) in sampleBidHistory"
              :key="i"
              class="flex items-center justify-between px-6 py-3.5"
            >
              <div class="flex items-center gap-3">
                <div
                  class="w-8 h-8 rounded-xl bg-gray-900 flex items-center justify-center text-white text-xs font-bold shrink-0"
                >
                  {{ bid.name.charAt(0) }}
                </div>
                <div>
                  <p class="text-sm font-medium text-black">{{ bid.name }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ bid.time }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold text-black">
                  {{ formatRp(bid.amount) }}
                </p>
                <span
                  v-if="i === 0"
                  class="text-xs bg-black text-white px-2 py-0.5 rounded-full"
                  >Tertinggi</span
                >
              </div>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-gray-100">
            <button
              @click="bidHistoryTarget = null"
              class="w-full py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ═══════════════════ CONFIRM MODAL ═══════════════════ -->
    <transition name="fade-modal">
      <div
        v-if="confirmTarget"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="confirmTarget = null"
      >
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div
          class="relative bg-white rounded-2xl p-7 max-w-sm w-full shadow-xl"
        >
          <div
            class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mb-4"
          >
            <svg
              class="w-6 h-6 text-gray-700"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                :d="
                  confirmTarget.action === 'delete'
                    ? 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'
                    : 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'
                "
              />
            </svg>
          </div>
          <h3 class="font-bold text-lg text-black mb-2">
            {{
              confirmTarget.action === "delete"
                ? "Hapus Lelang?"
                : "Nonaktifkan Lelang?"
            }}
          </h3>
          <p class="text-gray-500 text-sm leading-relaxed mb-6">
            <template v-if="confirmTarget.action === 'delete'">
              Anda akan menghapus
              <strong class="text-black">{{
                confirmTarget.auction.name
              }}</strong
              >. Tindakan ini tidak dapat dibatalkan.
            </template>
            <template v-else>
              Apakah Anda yakin ingin menonaktifkan lelang
              <strong class="text-black">{{
                confirmTarget.auction.name
              }}</strong
              >?
            </template>
          </p>
          <div class="flex gap-3">
            <button
              @click="confirmTarget = null"
              class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleConfirm"
              class="flex-1 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
            >
              {{ confirmTarget.action === "delete" ? "Hapus" : "Nonaktifkan" }}
            </button>
          </div>
        </div>
      </div>
    </transition>
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
    transform 0.25s ease,
    box-shadow 0.25s ease;
}
.card-lift:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
}

.slide-fade-enter-active {
  transition:
    opacity 0.25s ease,
    transform 0.25s ease;
}
.slide-fade-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.fade-modal-enter-active {
  transition: opacity 0.25s ease;
}
.fade-modal-leave-active {
  transition: opacity 0.2s ease;
}
.fade-modal-enter-from,
.fade-modal-leave-to {
  opacity: 0;
}
</style>
