<script setup>
import { ref, computed } from "vue";

// ─── Stats ───────────────────────────────────────────────────
const stats = [
  {
    label: "Total Watchlist",
    value: "24",
    icon: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z",
    dark: false,
  },
  {
    label: "Sedang Berlangsung",
    value: "15",
    icon: "M13 10V3L4 14h7v7l9-11h-7z",
    dark: true,
  },
  {
    label: "Akan Datang",
    value: "6",
    icon: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
    dark: false,
  },
  {
    label: "Segera Berakhir",
    value: "3",
    icon: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
    dark: false,
  },
];

// ─── Ending soon strip ───────────────────────────────────────
const endingSoon = ref([
  {
    id: 1,
    name: "Lukisan Bali Klasik Tahun 1980",
    category: "Lukisan",
    image:
      "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=600&q=80",
    currentPrice: 12500000,
    countdown: "00:25:08",
  },
  {
    id: 4,
    name: "Topeng Barong Antik",
    category: "Kerajinan",
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=600&q=80",
    currentPrice: 8750000,
    countdown: "00:09:45",
  },
  {
    id: 5,
    name: "Patung Dewi Saraswati",
    category: "Patung",
    image:
      "https://images.unsplash.com/photo-1578926288207-32356f3e5e93?w=600&q=80",
    currentPrice: 6200000,
    countdown: "00:17:33",
  },
]);

// ─── Filters ─────────────────────────────────────────────────
const filters = [
  { label: "Semua", value: "all", count: 24 },
  { label: "Sedang Berlangsung", value: "active", count: 15 },
  { label: "Akan Datang", value: "upcoming", count: 6 },
  { label: "Selesai", value: "ended", count: 3 },
];

const activeFilter = ref("all");
const search = ref("");
const sortBy = ref("newest");

// ─── Watchlist items ─────────────────────────────────────────
const watchlist = ref([
  {
    id: 1,
    name: "Patung Garuda Bali",
    artist: "Ketut Suardana",
    category: "Patung",
    image:
      "https://images.unsplash.com/photo-1578926288207-32356f3e5e93?w=600&q=80",
    currentPrice: 8500000,
    totalBids: 25,
    watching: 18,
    hasBid: false,
    status: "active",
    timeLeft: { d: "01", h: "05", m: "12" },
    endDate: "",
    addedAt: 1,
  },
  {
    id: 2,
    name: "Lukisan Bali Klasik Tahun 1980",
    artist: "I Wayan Sukerta",
    category: "Lukisan",
    image:
      "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=600&q=80",
    currentPrice: 12500000,
    totalBids: 35,
    watching: 42,
    hasBid: true,
    status: "active",
    timeLeft: { d: "00", h: "00", m: "25" },
    endDate: "",
    addedAt: 2,
  },
  {
    id: 3,
    name: "Topeng Tradisional Bali",
    artist: "Ni Luh Eka Sari",
    category: "Kerajinan",
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=600&q=80",
    currentPrice: 4200000,
    totalBids: 11,
    watching: 9,
    hasBid: false,
    status: "upcoming",
    timeLeft: { d: "00", h: "03", m: "25" },
    endDate: "",
    addedAt: 3,
  },
  {
    id: 4,
    name: "Ukiran Kayu Garuda",
    artist: "Dewa Gede Artana",
    category: "Patung",
    image:
      "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=600&q=80",
    currentPrice: 18000000,
    totalBids: 48,
    watching: 63,
    hasBid: true,
    status: "active",
    timeLeft: { d: "02", h: "11", m: "40" },
    endDate: "",
    addedAt: 4,
  },
  {
    id: 5,
    name: "Harmoni Semesta",
    artist: "I Made Wijaya",
    category: "Lukisan",
    image:
      "https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=600&q=80",
    currentPrice: 19200000,
    totalBids: 52,
    watching: 77,
    hasBid: false,
    status: "ended",
    timeLeft: null,
    endDate: "12 Juni 2026",
    addedAt: 5,
  },
  {
    id: 6,
    name: "Alam Tak Terbatas",
    artist: "Sang Ayu Putu Riani",
    category: "Lukisan Batik",
    image:
      "https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=600&q=80",
    currentPrice: 7800000,
    totalBids: 19,
    watching: 24,
    hasBid: false,
    status: "upcoming",
    timeLeft: { d: "00", h: "07", m: "50" },
    endDate: "",
    addedAt: 6,
  },
]);

// ─── Filtered + sorted list ──────────────────────────────────
const filteredList = computed(() => {
  let list = [...watchlist.value];

  if (search.value.trim()) {
    const q = search.value.toLowerCase();
    list = list.filter(
      (i) =>
        i.name.toLowerCase().includes(q) ||
        i.artist.toLowerCase().includes(q) ||
        i.category.toLowerCase().includes(q),
    );
  }

  if (activeFilter.value !== "all") {
    list = list.filter((i) => i.status === activeFilter.value);
  }

  if (sortBy.value === "price_high")
    list.sort((a, b) => b.currentPrice - a.currentPrice);
  else if (sortBy.value === "price_low")
    list.sort((a, b) => a.currentPrice - b.currentPrice);
  else if (sortBy.value === "most_bids")
    list.sort((a, b) => b.totalBids - a.totalBids);
  else if (sortBy.value === "ending")
    list.sort(
      (a, b) =>
        (a.status === "active" ? -1 : 1) - (b.status === "active" ? -1 : 1),
    );
  else list.sort((a, b) => a.addedAt - b.addedAt);

  return list;
});

// ─── Remove from watchlist ───────────────────────────────────
function removeFromWatchlist(id) {
  watchlist.value = watchlist.value.filter((i) => i.id !== id);
}

function addToWatchlist(id) {
  // TODO: API call to add to watchlist
  console.log("Add to watchlist:", id);
}

// ─── Price activity feed ─────────────────────────────────────
const priceActivities = [
  {
    type: "price",
    text: 'Harga <strong class="text-black">"Patung Garuda Bali"</strong> naik menjadi <strong class="text-black">Rp 8.500.000</strong>',
    time: "15 menit yang lalu",
  },
  {
    type: "bids",
    text: 'Lelang <strong class="text-black">"Topeng Barong Antik"</strong> menerima 5 penawaran baru',
    time: "35 menit yang lalu",
  },
  {
    type: "upcoming",
    text: 'Lelang <strong class="text-black">"Ukiran Kayu Garuda"</strong> akan dimulai dalam 1 jam',
    time: "1 jam yang lalu",
  },
  {
    type: "price",
    text: 'Harga <strong class="text-black">"Harmoni Semesta"</strong> naik menjadi <strong class="text-black">Rp 19.200.000</strong>',
    time: "2 jam yang lalu",
  },
  {
    type: "bids",
    text: 'Lelang <strong class="text-black">"Lukisan Bali Klasik"</strong> menerima 12 penawaran baru dalam 30 menit terakhir',
    time: "3 jam yang lalu",
  },
];

// ─── Category breakdown ──────────────────────────────────────
const categoryBreakdown = [
  { label: "Lukisan", count: 9, pct: 75 },
  { label: "Patung", count: 7, pct: 58 },
  { label: "Kerajinan", count: 5, pct: 42 },
  { label: "Fotografi", count: 2, pct: 17 },
  { label: "Lukisan Batik", count: 1, pct: 8 },
];

// ─── Recommendations ─────────────────────────────────────────
const recommendations = ref([
  {
    id: 10,
    name: "Sunrise Penida",
    category: "Fotografi",
    image:
      "https://images.unsplash.com/photo-1519125323398-675f0ddb6308?w=400&q=80",
    startPrice: 5000000,
    status: "active",
  },
  {
    id: 11,
    name: "Tanah & Api",
    category: "Keramik",
    image:
      "https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=400&q=80",
    startPrice: 3500000,
    status: "upcoming",
  },
  {
    id: 12,
    name: "Jejak Digital",
    category: "Instalasi",
    image:
      "https://images.unsplash.com/photo-1578301978018-3005759f48f7?w=400&q=80",
    startPrice: 35000000,
    status: "upcoming",
  },
  {
    id: 13,
    name: "Dewi Kesuburan",
    category: "Patung",
    image:
      "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=400&q=80",
    startPrice: 18000000,
    status: "active",
  },
]);

// ─── Helpers ─────────────────────────────────────────────────
function formatRp(val) {
  return "Rp " + val.toLocaleString("id-ID");
}

function statusBadge(status) {
  const map = {
    active: {
      class: "bg-white/90 backdrop-blur-sm text-gray-800",
      dot: "bg-gray-800",
      label: "Berlangsung",
    },
    upcoming: {
      class: "bg-white/90 backdrop-blur-sm text-gray-500",
      dot: "bg-gray-400",
      label: "Akan Datang",
    },
    ended: {
      class: "bg-black/70 backdrop-blur-sm text-white",
      dot: "bg-white/60",
      label: "Selesai",
    },
  };
  return map[status] ?? map.ended;
}

function activityStyle(type) {
  const map = {
    price: {
      bg: "bg-black",
      icon: "text-white",
      path: "M13 7h8m0 0V3m0 4l-8 8-4-4-6 6",
    },
    bids: {
      bg: "bg-gray-100",
      icon: "text-gray-700",
      path: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z",
    },
    upcoming: {
      bg: "bg-gray-100",
      icon: "text-gray-500",
      path: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
    },
  };
  return map[type] ?? map.bids;
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 font-sans pt-20">
    <div class="mx-auto px-6 md:px-10 py-10">
      <!-- ═══════════════════ HEADER ═══════════════════ -->
      <div class="mb-10">
        <span
          class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
          >Akun Saya</span
        >
        <h1 class="text-4xl font-bold text-black mt-2 tracking-tight">
          Watchlist
        </h1>
        <p class="text-gray-500 text-sm mt-3 max-w-2xl leading-relaxed">
          Simpan dan pantau lelang yang menarik perhatian Anda. Watchlist
          membantu Anda mengikuti perkembangan harga, jumlah penawaran, dan
          waktu lelang tanpa harus langsung melakukan penawaran.
        </p>
      </div>

      <!-- ═══════════════════ STATS ═══════════════════ -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <div
          v-for="stat in stats"
          :key="stat.label"
          :class="[
            'rounded-2xl px-6 py-6 border card-lift',
            stat.dark ? 'bg-black border-black' : 'bg-white border-gray-100',
          ]"
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
            Segera Berakhir
          </h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            v-for="item in endingSoon"
            :key="item.id"
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-lift"
          >
            <div
              class="relative h-36 bg-cover bg-center bg-gray-100"
              :style="{ backgroundImage: `url('${item.image}')` }"
            >
              <div
                class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"
              ></div>
              <div
                class="absolute bottom-3 left-3 right-3 flex items-end justify-between"
              >
                <div>
                  <p class="text-white font-semibold text-sm leading-tight">
                    {{ item.name }}
                  </p>
                  <p class="text-white/60 text-xs mt-0.5">
                    {{ item.category }}
                  </p>
                </div>
              </div>
            </div>
            <div class="p-4 flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-400 mb-0.5">Harga saat ini</p>
                <p class="font-bold text-base text-black">
                  {{ formatRp(item.currentPrice) }}
                </p>
                <div class="flex items-center gap-1.5 mt-1.5">
                  <span
                    class="w-1.5 h-1.5 rounded-full bg-gray-800 inline-block"
                  ></span>
                  <p class="text-xs text-gray-500 font-mono font-medium">
                    {{ item.countdown }}
                  </p>
                </div>
              </div>
              <button
                class="px-3 py-2 border border-gray-200 text-gray-600 rounded-lg text-xs font-medium hover:border-black hover:text-black transition-all duration-300"
                @click="$router.push(`/auctions/${item.id}`)"
              >
                Lihat Detail
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ SEARCH & FILTER ═══════════════════ -->
      <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-6">
        <div class="flex flex-col md:flex-row gap-3">
          <div class="relative flex-1">
            <input
              v-model="search"
              type="text"
              placeholder="Cari barang dalam watchlist..."
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
          <div class="relative">
            <select
              v-model="sortBy"
              class="appearance-none border border-gray-200 rounded-xl pl-4 pr-9 py-2.5 text-sm text-gray-600 outline-none focus:border-black transition-colors bg-gray-50 cursor-pointer"
            >
              <option value="newest">Terbaru Ditambahkan</option>
              <option value="ending">Akan Berakhir</option>
              <option value="price_high">Harga Tertinggi</option>
              <option value="price_low">Harga Terendah</option>
              <option value="most_bids">Paling Banyak Ditawar</option>
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

      <!-- ═══════════════════ WATCHLIST GRID ═══════════════════ -->
      <div class="">
        <!-- Empty state -->
        <div
          v-if="filteredList.length === 0"
          class="bg-white rounded-2xl border border-gray-100 py-24 text-center"
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
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
              />
            </svg>
          </div>
          <p class="font-semibold text-gray-800 mb-2">
            {{
              search ? "Barang tidak ditemukan" : "Watchlist Anda masih kosong"
            }}
          </p>
          <p
            class="text-gray-400 text-sm max-w-xs mx-auto leading-relaxed mb-6"
          >
            {{
              search
                ? "Tidak ada barang yang cocok dengan pencarianmu."
                : "Simpan barang yang menarik perhatian Anda dengan menekan ikon ❤️ pada halaman daftar lelang atau detail lelang."
            }}
          </p>
          <button
            @click="$router.push('/auctions')"
            class="px-6 py-2.5 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
          >
            Jelajahi Lelang
          </button>
        </div>

        <!-- Cards grid -->
        <div
          v-else
          class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5"
        >
          <div
            v-for="item in filteredList"
            :key="item.id"
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-lift flex flex-col"
          >
            <!-- Image -->
            <div class="relative h-52 flex-shrink-0">
              <div
                class="w-full h-full bg-cover bg-center bg-gray-100"
                :style="{ backgroundImage: `url('${item.image}')` }"
              ></div>
              <div
                class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"
              ></div>

              <!-- Status badge -->
              <div class="absolute top-3 left-3">
                <span
                  :class="[
                    'flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium',
                    statusBadge(item.status).class,
                  ]"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full"
                    :class="statusBadge(item.status).dot"
                  ></span>
                  {{ statusBadge(item.status).label }}
                </span>
              </div>

              <!-- Remove button -->
              <button
                @click="removeFromWatchlist(item.id)"
                class="absolute top-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm hover:bg-gray-100 transition-colors group"
                title="Hapus dari Watchlist"
              >
                <svg
                  class="w-4 h-4 text-gray-400 group-hover:text-black transition-colors"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                  />
                </svg>
              </button>

              <!-- Category -->
              <span
                class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-sm text-gray-700 text-xs px-2.5 py-1 rounded-full font-medium"
              >
                {{ item.category }}
              </span>
            </div>

            <!-- Body -->
            <div class="p-5 flex flex-col flex-1">
              <h3
                class="font-semibold text-base text-black mb-0.5 leading-snug"
              >
                {{ item.name }}
              </h3>
              <p class="text-gray-400 text-xs mb-4">{{ item.artist }}</p>

              <!-- Price + bids row -->
              <div class="flex items-end justify-between mb-4">
                <div>
                  <p class="text-xs text-gray-400 mb-0.5">
                    Harga Tertinggi Saat Ini
                  </p>
                  <p class="font-bold text-xl text-black">
                    {{ formatRp(item.currentPrice) }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-xs text-gray-400 mb-0.5">Penawaran</p>
                  <p class="font-semibold text-sm text-black">
                    {{ item.totalBids }}
                  </p>
                </div>
              </div>

              <!-- Activity row -->
              <div
                class="flex items-center gap-4 pb-4 border-b border-gray-100 mb-4"
              >
                <div class="flex items-center gap-1.5 text-xs text-gray-400">
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
                  {{ item.watching }} melihat
                </div>
                <div
                  class="flex items-center gap-1.5 text-xs"
                  :class="
                    item.hasBid ? 'text-gray-700 font-medium' : 'text-gray-400'
                  "
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
                      :d="
                        item.hasBid
                          ? 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                          : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'
                      "
                    />
                  </svg>
                  {{
                    item.hasBid
                      ? "Sudah mengikuti lelang ini"
                      : "Belum ada penawaran dari Anda"
                  }}
                </div>
              </div>

              <!-- Countdown / end date -->
              <div class="mb-5 flex-1">
                <p class="text-xs text-gray-400 mb-2">
                  {{
                    item.status === "active"
                      ? "Berakhir Dalam"
                      : item.status === "upcoming"
                        ? "Dimulai Dalam"
                        : "Lelang Berakhir"
                  }}
                </p>
                <div v-if="item.status === 'active'" class="flex gap-4">
                  <div v-if="item.timeLeft.d !== '00'" class="text-center">
                    <p class="text-xl font-bold text-black leading-none">
                      {{ item.timeLeft.d }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Hari</p>
                  </div>
                  <div class="text-center">
                    <p class="text-xl font-bold text-black leading-none">
                      {{ item.timeLeft.h }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Jam</p>
                  </div>
                  <div class="text-center">
                    <p class="text-xl font-bold text-black leading-none">
                      {{ item.timeLeft.m }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Menit</p>
                  </div>
                </div>
                <div v-else-if="item.status === 'upcoming'" class="flex gap-4">
                  <div class="text-center">
                    <p class="text-xl font-bold text-black leading-none">
                      {{ item.timeLeft.h }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Jam</p>
                  </div>
                  <div class="text-center">
                    <p class="text-xl font-bold text-black leading-none">
                      {{ item.timeLeft.m }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Menit</p>
                  </div>
                </div>
                <p v-else class="text-sm font-semibold text-gray-500">
                  {{ item.endDate }}
                </p>
              </div>

              <!-- Actions -->
              <div class="flex gap-2 mt-auto">
                <button
                  @click="$router.push(`/auctions/${item.id}`)"
                  class="flex-1 py-2.5 border border-gray-200 text-gray-700 rounded-lg text-xs font-medium hover:border-black hover:text-black transition-all duration-300"
                >
                  Lihat Detail
                </button>
                <button
                  @click="removeFromWatchlist(item.id)"
                  class="w-10 h-10 flex items-center justify-center border border-gray-200 rounded-lg text-gray-400 hover:border-black hover:text-black transition-all duration-300 flex-shrink-0"
                  title="Hapus dari Watchlist"
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
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ BOTTOM SECTIONS ═══════════════════ -->
      <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Price activity feed -->
        <div
          class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6"
        >
          <h2 class="font-semibold text-base text-black mb-5">
            Perubahan Harga Terbaru
          </h2>
          <div class="space-y-0">
            <div
              v-for="(act, i) in priceActivities"
              :key="i"
              :class="[
                'flex items-start gap-4 py-4',
                i < priceActivities.length - 1 ? 'border-b border-gray-50' : '',
              ]"
            >
              <div
                :class="[
                  'w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5',
                  activityStyle(act.type).bg,
                ]"
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
              <div class="flex-1 min-w-0">
                <p
                  class="text-sm text-gray-700 leading-relaxed"
                  v-html="act.text"
                ></p>
                <p class="text-xs text-gray-400 mt-1">{{ act.time }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick stats sidebar -->
        <div class="space-y-4">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-base text-black mb-5">
              Kategori Favorit
            </h2>
            <div class="space-y-3">
              <div v-for="cat in categoryBreakdown" :key="cat.label">
                <div class="flex items-center justify-between mb-1.5">
                  <span class="text-sm text-gray-600">{{ cat.label }}</span>
                  <span class="text-xs font-semibold text-black">{{
                    cat.count
                  }}</span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-black rounded-full transition-all duration-700"
                    :style="{ width: cat.pct + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-black rounded-2xl p-6">
            <p class="text-white/50 text-xs uppercase tracking-widest mb-3">
              Total Nilai Watchlist
            </p>
            <p class="text-white font-bold text-3xl">Rp 187.500.000</p>
            <p class="text-white/40 text-xs mt-2">
              Akumulasi harga tertinggi 24 lelang
            </p>
            <div
              class="mt-5 pt-5 border-t border-white/10 flex items-center justify-between"
            >
              <span class="text-white/50 text-xs">Rata-rata per barang</span>
              <span class="text-white text-sm font-semibold">Rp 7.812.500</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ RECOMMENDATIONS ═══════════════════ -->
      <div class="mt-12">
        <div class="flex items-end justify-between mb-6">
          <div>
            <span
              class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
              >Berdasarkan Watchlist Anda</span
            >
            <h2 class="text-2xl font-bold mt-2">Mungkin Anda Juga Menyukai</h2>
          </div>
          <a
            href="#"
            class="text-sm font-medium text-gray-500 hover:text-black flex items-center gap-1 transition-colors"
          >
            Lihat Semua
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
                d="M17 8l4 4m0 0l-4 4m4-4H3"
              />
            </svg>
          </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div
            v-for="rec in recommendations"
            :key="rec.id"
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-lift"
          >
            <div class="relative h-40">
              <div
                class="w-full h-full bg-cover bg-center bg-gray-100"
                :style="{ backgroundImage: `url('${rec.image}')` }"
              ></div>
              <div
                class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"
              ></div>
              <span
                :class="[
                  'absolute top-3 left-3 flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium',
                  statusBadge(rec.status).class,
                ]"
              >
                <span
                  class="w-1.5 h-1.5 rounded-full"
                  :class="statusBadge(rec.status).dot"
                ></span>
                {{ statusBadge(rec.status).label }}
              </span>
              <button
                @click="addToWatchlist(rec.id)"
                class="absolute top-3 right-3 w-7 h-7 bg-white rounded-full flex items-center justify-center shadow-sm hover:bg-gray-100 transition-colors"
              >
                <svg
                  class="w-3.5 h-3.5 text-gray-400 hover:text-black transition-colors"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                  />
                </svg>
              </button>
            </div>
            <div class="p-4">
              <span class="text-xs text-gray-400">{{ rec.category }}</span>
              <h3
                class="font-semibold text-sm text-black mt-0.5 mb-3 leading-snug line-clamp-2"
              >
                {{ rec.name }}
              </h3>
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs text-gray-400 mb-0.5">Harga awal</p>
                  <p class="font-bold text-sm text-black">
                    {{ formatRp(rec.startPrice) }}
                  </p>
                </div>
              </div>
              <button
                @click="$router.push(`/auctions/${rec.id}`)"
                class="mt-3 w-full py-2 border border-gray-200 text-gray-600 rounded-lg text-xs font-medium hover:border-black hover:text-black transition-all duration-300"
              >
                Lihat Detail
              </button>
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

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
