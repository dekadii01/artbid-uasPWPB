<script setup>
import { ref, computed } from "vue";

// ─── Stats ───────────────────────────────────────────────────
const stats = [
  {
    label: "Total Lelang Dibuat",
    value: "12",
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
    dark: false,
  },
  {
    label: "Sedang Berlangsung",
    value: "4",
    icon: "M13 10V3L4 14h7v7l9-11h-7z",
    dark: true,
  },
  {
    label: "Akan Datang",
    value: "3",
    icon: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
    dark: false,
  },
  {
    label: "Selesai",
    value: "5",
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
    dark: false,
  },
];

// ─── Popular works ───────────────────────────────────────────
const popularWorks = ref([
  {
    id: 3,
    name: "Topeng Barong Antik",
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=600&q=80",
    currentPrice: 12500000,
    totalBids: 52,
    watching: 28,
    badge: "Paling Banyak Ditawar",
  },
  {
    id: 1,
    name: "Lukisan Bali Klasik Tahun 1980",
    image:
      "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=600&q=80",
    currentPrice: 8500000,
    totalBids: 35,
    watching: 42,
    badge: "Paling Banyak Dilihat",
  },
  {
    id: 2,
    name: "Patung Garuda Bali",
    image:
      "https://images.unsplash.com/photo-1578926288207-32356f3e5e93?w=600&q=80",
    currentPrice: 9200000,
    totalBids: 29,
    watching: 19,
    badge: "Harga Tertinggi",
  },
]);

// ─── Filters ─────────────────────────────────────────────────
const filters = [
  { label: "Semua", value: "all", count: 12 },
  { label: "Akan Datang", value: "upcoming", count: 3 },
  { label: "Sedang Berlangsung", value: "active", count: 4 },
  { label: "Selesai", value: "ended", count: 5 },
];

const activeFilter = ref("all");
const search = ref("");
const sortBy = ref("newest");

// ─── Auction list ────────────────────────────────────────────
const auctions = ref([
  {
    id: 1,
    name: "Lukisan Bali Klasik Tahun 1980",
    artist: "I Wayan Sukerta",
    category: "Lukisan",
    image:
      "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=600&q=80",
    startPrice: 5000000,
    currentPrice: 8500000,
    totalBids: 35,
    watching: 42,
    status: "active",
    timeLeft: { d: "01", h: "06", m: "30" },
    endDate: "",
    createdAt: "1 Jun 2026",
    addedAt: 1,
  },
  {
    id: 2,
    name: "Patung Garuda Bali",
    artist: "Ketut Suardana",
    category: "Patung",
    image:
      "https://images.unsplash.com/photo-1578926288207-32356f3e5e93?w=600&q=80",
    startPrice: 6000000,
    currentPrice: 9200000,
    totalBids: 29,
    watching: 19,
    status: "active",
    timeLeft: { d: "02", h: "04", m: "15" },
    endDate: "",
    createdAt: "3 Jun 2026",
    addedAt: 2,
  },
  {
    id: 3,
    name: "Topeng Barong Antik",
    artist: "Ni Luh Eka Sari",
    category: "Kerajinan",
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=600&q=80",
    startPrice: 4000000,
    currentPrice: 12500000,
    totalBids: 52,
    watching: 28,
    status: "active",
    timeLeft: { d: "00", h: "02", m: "47" },
    endDate: "",
    createdAt: "5 Jun 2026",
    addedAt: 3,
  },
  {
    id: 4,
    name: "Harmoni Semesta",
    artist: "I Made Wijaya",
    category: "Lukisan",
    image:
      "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=600&q=80",
    startPrice: 8000000,
    currentPrice: 8000000,
    totalBids: 0,
    watching: 0,
    status: "upcoming",
    timeLeft: { d: "02", h: "04", m: "15" },
    endDate: "",
    createdAt: "10 Jun 2026",
    addedAt: 4,
  },
  {
    id: 5,
    name: "Sunrise Penida",
    artist: "Agung Rai Photography",
    category: "Fotografi",
    image:
      "https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=600&q=80",
    startPrice: 3000000,
    currentPrice: 3000000,
    totalBids: 0,
    watching: 0,
    status: "upcoming",
    timeLeft: { d: "03", h: "11", m: "00" },
    endDate: "",
    createdAt: "12 Jun 2026",
    addedAt: 5,
  },
  {
    id: 6,
    name: "Dewi Kesuburan",
    artist: "Ni Luh Eka Sari",
    category: "Patung",
    image:
      "https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=600&q=80",
    startPrice: 7000000,
    currentPrice: 15000000,
    totalBids: 48,
    watching: 0,
    status: "ended",
    timeLeft: null,
    endDate: "9 Juni 2026",
    createdAt: "28 Mei 2026",
    addedAt: 6,
  },
  {
    id: 7,
    name: "Alam Tak Terbatas",
    artist: "Sang Ayu Putu Riani",
    category: "Lukisan Batik",
    image:
      "https://images.unsplash.com/photo-1578301978018-3005759f48f7?w=600&q=80",
    startPrice: 9500000,
    currentPrice: 18200000,
    totalBids: 41,
    watching: 0,
    status: "ended",
    timeLeft: null,
    endDate: "5 Juni 2026",
    createdAt: "20 Mei 2026",
    addedAt: 7,
  },
]);

// ─── Filtered list ───────────────────────────────────────────
const filteredAuctions = computed(() => {
  let list = [...auctions.value];

  if (search.value.trim()) {
    const q = search.value.toLowerCase();
    list = list.filter(
      (a) =>
        a.name.toLowerCase().includes(q) ||
        a.category.toLowerCase().includes(q) ||
        a.artist.toLowerCase().includes(q),
    );
  }

  if (activeFilter.value !== "all") {
    list = list.filter((a) => a.status === activeFilter.value);
  }

  if (sortBy.value === "oldest") list.sort((a, b) => b.addedAt - a.addedAt);
  else if (sortBy.value === "price_high")
    list.sort((a, b) => b.currentPrice - a.currentPrice);
  else if (sortBy.value === "most_bids")
    list.sort((a, b) => b.totalBids - a.totalBids);
  else if (sortBy.value === "ending")
    list.sort(
      (a, b) =>
        (a.status === "active" ? -1 : 1) - (b.status === "active" ? -1 : 1),
    );
  else if (sortBy.value === "starting")
    list.sort(
      (a, b) =>
        (a.status === "upcoming" ? -1 : 1) - (b.status === "upcoming" ? -1 : 1),
    );
  else list.sort((a, b) => a.addedAt - b.addedAt);

  return list;
});

// ─── Delete modal ────────────────────────────────────────────
const deleteTarget = ref(null);

function confirmDelete(auction) {
  deleteTarget.value = auction;
}

function handleDelete() {
  if (!deleteTarget.value) return;
  auctions.value = auctions.value.filter((a) => a.id !== deleteTarget.value.id);
  deleteTarget.value = null;
}

// ─── Drafts ──────────────────────────────────────────────────
const drafts = ref([
  {
    id: "d1",
    name: "Lukisan Tradisional Bali",
    image:
      "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=100&q=80",
  },
  {
    id: "d2",
    name: "Ukiran Kayu Kecak",
    image:
      "https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=100&q=80",
  },
]);

function deleteDraft(id) {
  drafts.value = drafts.value.filter((d) => d.id !== id);
}

// ─── Recent activities ───────────────────────────────────────
const recentActivities = [
  {
    type: "bid",
    text: 'Penawaran baru sebesar <strong class="text-black">Rp 8.500.000</strong> pada <strong class="text-black">"Lukisan Bali Klasik Tahun 1980"</strong>',
    time: "5 menit yang lalu",
  },
  {
    type: "newbidder",
    text: 'Lelang <strong class="text-black">"Patung Garuda Bali"</strong> mendapatkan penawar baru',
    time: "15 menit yang lalu",
  },
  {
    type: "ended",
    text: 'Lelang <strong class="text-black">"Topeng Barong Antik"</strong> telah berakhir dengan harga akhir <strong class="text-black">Rp 12.500.000</strong>',
    time: "1 jam yang lalu",
  },
  {
    type: "bid",
    text: 'Penawaran baru sebesar <strong class="text-black">Rp 12.500.000</strong> pada <strong class="text-black">"Topeng Barong Antik"</strong>',
    time: "2 jam yang lalu",
  },
  {
    type: "newbidder",
    text: 'Lelang <strong class="text-black">"Harmoni Semesta"</strong> ditambahkan ke watchlist oleh 5 pengguna baru',
    time: "3 jam yang lalu",
  },
];

// ─── Auction results ─────────────────────────────────────────
const auctionResults = [
  {
    id: 6,
    name: "Dewi Kesuburan",
    finalPrice: 15000000,
    winner: "I Putu Arya",
    totalBids: 48,
  },
  {
    id: 7,
    name: "Alam Tak Terbatas",
    finalPrice: 18200000,
    winner: "Budi Santoso",
    totalBids: 41,
  },
];

// ─── Helpers ─────────────────────────────────────────────────
function formatRp(val) {
  return "Rp " + val.toLocaleString("id-ID");
}

function statusStyle(status) {
  const map = {
    active: {
      wrapper: "bg-gray-50 border-gray-200 text-gray-800",
      dot: "bg-gray-800",
      label: "Sedang Berlangsung",
    },
    upcoming: {
      wrapper: "bg-gray-100 border-gray-200 text-gray-500",
      dot: "bg-gray-400",
      label: "Akan Dimulai",
    },
    ended: {
      wrapper: "bg-black border-black text-white",
      dot: "bg-white",
      label: "Selesai",
    },
  };
  return map[status] ?? map.ended;
}

function activityStyle(type) {
  const map = {
    bid: {
      bg: "bg-black",
      icon: "text-white",
      path: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
    },
    newbidder: {
      bg: "bg-gray-100",
      icon: "text-gray-700",
      path: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z",
    },
    ended: {
      bg: "bg-gray-100",
      icon: "text-gray-500",
      path: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
    },
  };
  return map[type] ?? map.bid;
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 font-sans pt-20">
    <div class="mx-auto px-6 md:px-10 py-10">
      <!-- ═══════════════════ HEADER ═══════════════════ -->
      <div
        class="flex flex-col md:flex-row md:items-start md:justify-between gap-5 mb-10"
      >
        <div>
          <span
            class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
            >Akun Saya</span
          >
          <h1 class="text-4xl font-bold text-black mt-2 tracking-tight">
            Karya Saya
          </h1>
          <p class="text-gray-500 text-sm mt-3 max-w-2xl leading-relaxed">
            Kelola seluruh barang yang pernah Anda lelangkan, pantau
            perkembangan penawaran secara realtime, serta lihat hasil akhir dari
            setiap lelang yang telah berlangsung.
          </p>
        </div>
        <button
          @click="$router.push('/auction/create')"
          class="flex-shrink-0 flex items-center gap-2 px-6 py-3 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-all duration-300 self-start"
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
          Buat Lelang Baru
        </button>
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
            class="text-xs mt-1"
            :class="stat.dark ? 'text-white/50' : 'text-gray-400'"
          >
            {{ stat.label }}
          </p>
        </div>
      </div>

      <!-- ═══════════════════ POPULAR WORKS ═══════════════════ -->
      <div v-if="popularWorks.length" class="mb-10">
        <div class="flex items-center gap-2 mb-4">
          <span
            class="live-dot w-2 h-2 rounded-full bg-black inline-block"
          ></span>
          <h2 class="text-sm font-semibold text-black tracking-wide uppercase">
            Karya Sedang Populer
          </h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            v-for="work in popularWorks"
            :key="work.id"
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-lift"
          >
            <div
              class="relative h-36 bg-cover bg-center bg-gray-100"
              :style="{ backgroundImage: `url('${work.image}')` }"
            >
              <div
                class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"
              ></div>
              <span
                class="absolute top-3 left-3 bg-black text-white text-xs px-2.5 py-1 rounded-full font-medium flex items-center gap-1.5"
              >
                <svg
                  class="w-3 h-3"
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
                {{ work.badge }}
              </span>
              <div class="absolute bottom-3 left-3 right-3">
                <p
                  class="text-white font-semibold text-sm leading-tight truncate"
                >
                  {{ work.name }}
                </p>
              </div>
            </div>
            <div class="p-4 grid grid-cols-3 gap-3">
              <div>
                <p class="text-xs text-gray-400 mb-0.5">Harga saat ini</p>
                <p class="font-bold text-sm text-black">
                  {{ formatRp(work.currentPrice) }}
                </p>
              </div>
              <div>
                <p class="text-xs text-gray-400 mb-0.5">Total bid</p>
                <p class="font-bold text-sm text-black">{{ work.totalBids }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400 mb-0.5">Sedang dilihat</p>
                <p class="font-bold text-sm text-black">{{ work.watching }}</p>
              </div>
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
              placeholder="Cari karya atau barang yang pernah Anda lelangkan..."
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
              <option value="newest">Terbaru</option>
              <option value="oldest">Terlama</option>
              <option value="starting">Akan Dimulai</option>
              <option value="ending">Akan Berakhir</option>
              <option value="price_high">Harga Tertinggi</option>
              <option value="most_bids">Jumlah Penawaran</option>
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

      <!-- ═══════════════════ AUCTION LIST ═══════════════════ -->
      <div class="">
        <!-- Empty state -->
        <div
          v-if="filteredAuctions.length === 0"
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
                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
              />
            </svg>
          </div>
          <p class="font-semibold text-gray-800 mb-2">
            {{
              search
                ? "Karya tidak ditemukan"
                : "Anda belum memiliki karya yang dilelangkan"
            }}
          </p>
          <p
            class="text-gray-400 text-sm max-w-sm mx-auto leading-relaxed mb-6"
          >
            {{
              search
                ? "Tidak ada karya yang cocok dengan pencarianmu."
                : "Mulailah menjual karya atau koleksi seni Anda dan temukan penawar terbaik melalui sistem lelang realtime."
            }}
          </p>
          <button
            @click="$router.push('/auction/create')"
            class="px-6 py-2.5 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
          >
            + Buat Lelang Pertama
          </button>
        </div>

        <!-- Auction cards -->
        <div v-else class="space-y-4">
          <div
            v-for="auction in filteredAuctions"
            :key="auction.id"
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden card-lift"
          >
            <div class="flex flex-col md:flex-row">
              <!-- Image -->
              <div class="relative md:w-52 h-52 md:h-auto flex-shrink-0">
                <div
                  class="w-full h-full bg-cover bg-center bg-gray-100"
                  :style="{ backgroundImage: `url('${auction.image}')` }"
                ></div>
                <div
                  class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"
                ></div>
                <span
                  class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-gray-700 text-xs px-2.5 py-1 rounded-full font-medium"
                >
                  {{ auction.category }}
                </span>
                <!-- Viewer count badge -->
                <div
                  v-if="auction.status === 'active'"
                  class="absolute bottom-3 left-3 flex items-center gap-1.5 bg-black/60 backdrop-blur-sm text-white text-xs px-2.5 py-1 rounded-full"
                >
                  <svg
                    class="w-3 h-3"
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
                  {{ auction.watching }} sedang melihat
                </div>
              </div>

              <!-- Content -->
              <div class="flex-1 p-6 flex flex-col justify-between min-w-0">
                <div
                  class="flex flex-col md:flex-row md:items-start justify-between gap-4"
                >
                  <!-- Left -->
                  <div class="flex-1 min-w-0">
                    <h3
                      class="font-semibold text-base text-black mb-0.5 truncate"
                    >
                      {{ auction.name }}
                    </h3>
                    <p class="text-gray-400 text-xs mb-4">
                      {{ auction.artist }}
                    </p>

                    <!-- Price row -->
                    <div class="flex flex-wrap gap-6 mb-4">
                      <div>
                        <p class="text-xs text-gray-400 mb-0.5">Harga Awal</p>
                        <p class="font-semibold text-sm text-gray-600">
                          {{ formatRp(auction.startPrice) }}
                        </p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-400 mb-0.5">
                          Harga Tertinggi
                        </p>
                        <p class="font-bold text-lg text-black">
                          {{
                            auction.status !== "upcoming"
                              ? formatRp(auction.currentPrice)
                              : "—"
                          }}
                        </p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-400 mb-0.5">
                          Total Penawaran
                        </p>
                        <p class="font-semibold text-sm text-black">
                          {{
                            auction.status !== "upcoming"
                              ? auction.totalBids + " Bid"
                              : "—"
                          }}
                        </p>
                      </div>
                    </div>

                    <!-- Meta row -->
                    <div
                      class="flex flex-wrap gap-5 pt-4 border-t border-gray-100"
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
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                          />
                        </svg>
                        Dibuat {{ auction.createdAt }}
                      </div>
                      <div
                        v-if="auction.status === 'active'"
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
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                          />
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                          />
                        </svg>
                        {{ auction.watching }} sedang melihat
                      </div>
                    </div>
                  </div>

                  <!-- Right: status + countdown + actions -->
                  <div
                    class="flex flex-col items-start md:items-end gap-4 flex-shrink-0"
                  >
                    <!-- Status badge -->
                    <div
                      class="flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-medium"
                      :class="statusStyle(auction.status).wrapper"
                    >
                      <span
                        class="w-1.5 h-1.5 rounded-full"
                        :class="statusStyle(auction.status).dot"
                      ></span>
                      {{ statusStyle(auction.status).label }}
                    </div>

                    <!-- Countdown -->
                    <div class="text-right">
                      <p class="text-xs text-gray-400 mb-2">
                        {{
                          auction.status === "active"
                            ? "Berakhir Dalam"
                            : auction.status === "upcoming"
                              ? "Dimulai Dalam"
                              : "Lelang Berakhir"
                        }}
                      </p>
                      <div v-if="auction.status !== 'ended'" class="flex gap-3">
                        <div
                          v-if="auction.timeLeft.d !== '00'"
                          class="text-center"
                        >
                          <p class="text-xl font-bold text-black leading-none">
                            {{ auction.timeLeft.d }}
                          </p>
                          <p class="text-xs text-gray-400 mt-0.5">Hari</p>
                        </div>
                        <div class="text-center">
                          <p class="text-xl font-bold text-black leading-none">
                            {{ auction.timeLeft.h }}
                          </p>
                          <p class="text-xs text-gray-400 mt-0.5">Jam</p>
                        </div>
                        <div class="text-center">
                          <p class="text-xl font-bold text-black leading-none">
                            {{ auction.timeLeft.m }}
                          </p>
                          <p class="text-xs text-gray-400 mt-0.5">Menit</p>
                        </div>
                      </div>
                      <p v-else class="text-sm font-semibold text-gray-600">
                        {{ auction.endDate }}
                      </p>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex flex-wrap gap-2">
                      <!-- Upcoming -->
                      <template v-if="auction.status === 'upcoming'">
                        <button
                          @click="$router.push(`/auctions/${auction.id}`)"
                          class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-xs font-medium hover:border-black hover:text-black transition-all duration-300"
                        >
                          Lihat Detail
                        </button>
                        <button
                          @click="$router.push(`/auction/edit/${auction.id}`)"
                          class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-xs font-medium hover:border-black hover:text-black transition-all duration-300 flex items-center gap-1.5"
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
                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                            />
                          </svg>
                          Edit
                        </button>
                        <button
                          @click="confirmDelete(auction)"
                          class="px-4 py-2 border border-gray-200 text-gray-400 rounded-lg text-xs font-medium hover:border-gray-400 hover:text-gray-700 transition-all duration-300 flex items-center gap-1.5"
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
                          Hapus
                        </button>
                      </template>

                      <!-- Active -->
                      <template v-else-if="auction.status === 'active'">
                        <button
                          @click="$router.push(`/auctions/${auction.id}`)"
                          class="px-4 py-2 bg-black text-white rounded-lg text-xs font-medium hover:bg-gray-800 transition-all duration-300"
                        >
                          Lihat Detail
                        </button>
                      </template>

                      <!-- Ended -->
                      <template v-else>
                        <button
                          @click="
                            $router.push(`/auctions/${auction.id}/result`)
                          "
                          class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-xs font-medium hover:border-black hover:text-black transition-all duration-300"
                        >
                          Lihat Hasil
                        </button>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════ BOTTOM SECTIONS ═══════════════════ -->
      <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Activity feed -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Recent activity -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-base text-black mb-5">
              Aktivitas Terbaru Pada Karya Saya
            </h2>
            <div class="space-y-0">
              <div
                v-for="(act, i) in recentActivities"
                :key="i"
                :class="[
                  'flex items-start gap-4 py-4',
                  i < recentActivities.length - 1
                    ? 'border-b border-gray-50'
                    : '',
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

          <!-- Draft auctions -->
          <div
            v-if="drafts.length"
            class="bg-white rounded-2xl border border-gray-100 p-6"
          >
            <div class="flex items-center justify-between mb-5">
              <h2 class="font-semibold text-base text-black">Draft Lelang</h2>
              <span
                class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full"
                >{{ drafts.length }} draft</span
              >
            </div>
            <div class="space-y-3">
              <div
                v-for="draft in drafts"
                :key="draft.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:border-gray-300 transition-colors"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div
                    class="w-10 h-10 rounded-lg bg-cover bg-center bg-gray-200 flex-shrink-0"
                    :style="{ backgroundImage: `url('${draft.image}')` }"
                  ></div>
                  <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">
                      {{ draft.name }}
                    </p>
                    <span
                      class="text-xs text-gray-400 bg-gray-200 px-2 py-0.5 rounded-full mt-0.5 inline-block"
                      >Draft</span
                    >
                  </div>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                  <button
                    @click="$router.push(`/auction/edit/${draft.id}`)"
                    class="px-3 py-1.5 bg-black text-white rounded-lg text-xs font-medium hover:bg-gray-800 transition-colors"
                  >
                    Lanjutkan
                  </button>
                  <button
                    @click="deleteDraft(draft.id)"
                    class="px-3 py-1.5 border border-gray-200 text-gray-400 rounded-lg text-xs font-medium hover:border-gray-400 hover:text-gray-700 transition-colors"
                  >
                    Hapus
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right sidebar -->
        <div class="space-y-4">
          <!-- Ended auction results -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold text-base text-black mb-5">
              Hasil Lelang Terbaru
            </h2>
            <div class="space-y-4">
              <div
                v-for="(result, i) in auctionResults"
                :key="i"
                :class="[
                  'pb-4',
                  i < auctionResults.length - 1
                    ? 'border-b border-gray-50'
                    : '',
                ]"
              >
                <p class="font-medium text-sm text-black mb-2 leading-snug">
                  {{ result.name }}
                </p>
                <div class="space-y-1.5 mb-3">
                  <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Harga Akhir</span>
                    <span class="text-xs font-bold text-black">{{
                      formatRp(result.finalPrice)
                    }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Pemenang</span>
                    <span class="text-xs font-medium text-gray-700">{{
                      result.winner
                    }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Total Penawaran</span>
                    <span class="text-xs font-medium text-gray-700"
                      >{{ result.totalBids }} Bid</span
                    >
                  </div>
                </div>
                <button
                  @click="$router.push(`/auctions/${result.id}/result`)"
                  class="w-full py-2 border border-gray-200 text-gray-600 rounded-lg text-xs font-medium hover:border-black hover:text-black transition-all duration-300"
                >
                  Lihat Detail Hasil
                </button>
              </div>
            </div>
          </div>

          <!-- Performance card -->
          <div class="bg-black rounded-2xl p-6">
            <p class="text-white/50 text-xs uppercase tracking-widest mb-3">
              Total Pendapatan
            </p>
            <p class="text-white font-bold text-3xl">Rp 72.500.000</p>
            <p class="text-white/40 text-xs mt-2">
              Dari 5 lelang yang telah selesai
            </p>
            <div
              class="mt-5 pt-5 border-t border-white/10 grid grid-cols-2 gap-4"
            >
              <div>
                <p class="text-white/40 text-xs mb-1">Rata-rata harga</p>
                <p class="text-white text-sm font-semibold">Rp 14.500.000</p>
              </div>
              <div>
                <p class="text-white/40 text-xs mb-1">Rata-rata bid</p>
                <p class="text-white text-sm font-semibold">34 Bid</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════ DELETE CONFIRM MODAL ═══════════════════ -->
    <transition name="fade-modal">
      <div
        v-if="deleteTarget"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="deleteTarget = null"
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
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
              />
            </svg>
          </div>
          <h3 class="font-bold text-lg text-black mb-2">Hapus Lelang?</h3>
          <p class="text-gray-500 text-sm leading-relaxed mb-6">
            Anda akan menghapus
            <strong class="text-black">{{ deleteTarget?.name }}</strong
            >. Tindakan ini tidak dapat dibatalkan.
          </p>
          <div class="flex gap-3">
            <button
              @click="deleteTarget = null"
              class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleDelete"
              class="flex-1 py-2.5 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
            >
              Hapus
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
