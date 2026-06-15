<script setup>
import { ref, computed } from "vue";

// ─── System alerts ───────────────────────────────────────────
const systemAlerts = [
  {
    text: "Terdapat 5 pengguna baru yang mendaftar hari ini.",
    action: "Lihat Semua",
    dark: false,
    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z",
  },
  {
    text: "Terdapat 2 akun yang dilaporkan oleh pengguna lain.",
    action: "Tinjau Laporan",
    dark: true,
    icon: "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    text: "Terdapat 1 akun yang memerlukan verifikasi tambahan.",
    action: "Verifikasi",
    dark: false,
    icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
  },
];

// ─── Stats ───────────────────────────────────────────────────
const stats = [
  {
    label: "Total Pengguna",
    value: "1.245",
    sub: "Seluruh akun terdaftar",
    filter: "all",
    dark: false,
    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z",
  },
  {
    label: "Pengguna Aktif",
    value: "1.120",
    sub: "Aktif menggunakan platform",
    filter: "active",
    dark: true,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    label: "Baru Bulan Ini",
    value: "25",
    sub: "Registrasi bulan berjalan",
    filter: "new",
    dark: false,
    icon: "M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z",
  },
  {
    label: "Pengguna Diblokir",
    value: "8",
    sub: "Dinonaktifkan administrator",
    filter: "blocked",
    dark: false,
    icon: "M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636",
  },
];

// ─── Filters ─────────────────────────────────────────────────
const statusFilters = [
  { label: "Semua", value: "all", count: 1245 },
  { label: "Aktif", value: "active", count: 1120 },
  { label: "Diblokir", value: "blocked", count: 8 },
];

const activeFilter = ref("all");
const filterActivity = ref("");
const sortBy = ref("newest");
const searchQuery = ref("");
const currentPage = ref(1);
const perPage = 8;

// ─── Users data ──────────────────────────────────────────────
const users = ref([
  {
    id: 1,
    name: "I Putu Arya",
    email: "putuarya@gmail.com",
    phone: "+62 812-0001-0001",
    role: "Kolektor",
    joinedAt: "10 Jan 2026",
    status: "active",
    totalAuctions: 8,
    totalBids: 52,
    wonAuctions: 6,
    watchlist: 15,
    recentActivity: [
      {
        text: 'Membuat lelang baru "Patung Garuda Bali"',
        time: "2 jam lalu",
        dark: false,
        icon: "M12 4v16m8-8H4",
      },
      {
        text: "Melakukan penawaran Rp 12.500.000 pada lelang",
        time: "5 jam lalu",
        dark: true,
        icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
      },
      {
        text: 'Memenangkan lelang "Topeng Barong Antik"',
        time: "1 hari lalu",
        dark: false,
        icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
      },
    ],
    auctionHistory: [
      {
        name: "Patung Garuda Bali",
        finalPrice: 15000000,
        totalBids: 38,
        status: "active",
      },
      {
        name: "Ukiran Kayu Garuda",
        finalPrice: 18000000,
        totalBids: 48,
        status: "ended",
      },
    ],
    bidHistory: [
      {
        name: "Lukisan Bali Klasik",
        amount: 12500000,
        time: "12 Jun 2026",
        bidStatus: "leading",
      },
      {
        name: "Harmoni Semesta",
        amount: 9500000,
        time: "10 Jun 2026",
        bidStatus: "outbid",
      },
      {
        name: "Topeng Barong",
        amount: 8750000,
        time: "9 Jun 2026",
        bidStatus: "won",
      },
    ],
  },
  {
    id: 2,
    name: "Ni Luh Ratna",
    email: "niluhratna@gmail.com",
    phone: "+62 813-0002-0002",
    role: "Kolektor",
    joinedAt: "15 Feb 2026",
    status: "active",
    totalAuctions: 5,
    totalBids: 47,
    wonAuctions: 4,
    watchlist: 22,
    recentActivity: [],
    auctionHistory: [],
    bidHistory: [],
  },
  {
    id: 3,
    name: "Ketut Wirawan",
    email: "ketutwirawan@gmail.com",
    phone: "+62 814-0003-0003",
    role: "Seniman",
    joinedAt: "3 Mar 2026",
    status: "active",
    totalAuctions: 12,
    totalBids: 8,
    wonAuctions: 1,
    watchlist: 5,
    recentActivity: [],
    auctionHistory: [],
    bidHistory: [],
  },
  {
    id: 4,
    name: "Wayan Sudira",
    email: "wayansudira@gmail.com",
    phone: "+62 815-0004-0004",
    role: "Kolektor",
    joinedAt: "22 Jan 2026",
    status: "active",
    totalAuctions: 3,
    totalBids: 29,
    wonAuctions: 2,
    watchlist: 18,
    recentActivity: [],
    auctionHistory: [],
    bidHistory: [],
  },
  {
    id: 5,
    name: "Gede Mahendra",
    email: "gedemahendra@gmail.com",
    phone: "+62 816-0005-0005",
    role: "Kolektor",
    joinedAt: "5 Apr 2026",
    status: "active",
    totalAuctions: 1,
    totalBids: 34,
    wonAuctions: 3,
    watchlist: 12,
    recentActivity: [],
    auctionHistory: [],
    bidHistory: [],
  },
  {
    id: 6,
    name: "Made Ayu Sari",
    email: "madeayusari@gmail.com",
    phone: "+62 817-0006-0006",
    role: "Seniman",
    joinedAt: "19 Feb 2026",
    status: "active",
    totalAuctions: 9,
    totalBids: 2,
    wonAuctions: 0,
    watchlist: 3,
    recentActivity: [],
    auctionHistory: [],
    bidHistory: [],
  },
  {
    id: 7,
    name: "Putu Hartawan",
    email: "putuhartawan@gmail.com",
    phone: "+62 818-0007-0007",
    role: "Kolektor",
    joinedAt: "8 Mei 2026",
    status: "blocked",
    totalAuctions: 0,
    totalBids: 6,
    wonAuctions: 0,
    watchlist: 4,
    recentActivity: [],
    auctionHistory: [],
    bidHistory: [],
  },
  {
    id: 8,
    name: "Ni Made Dewi",
    email: "nimadedewi@gmail.com",
    phone: "+62 819-0008-0008",
    role: "Kolektor",
    joinedAt: "2 Jun 2026",
    status: "active",
    totalAuctions: 0,
    totalBids: 3,
    wonAuctions: 0,
    watchlist: 8,
    recentActivity: [],
    auctionHistory: [],
    bidHistory: [],
  },
  {
    id: 9,
    name: "Komang Bayu",
    email: "komangbayu@gmail.com",
    phone: "+62 820-0009-0009",
    role: "Kolektor",
    joinedAt: "11 Jun 2026",
    status: "active",
    totalAuctions: 2,
    totalBids: 15,
    wonAuctions: 1,
    watchlist: 9,
    recentActivity: [],
    auctionHistory: [],
    bidHistory: [],
  },
  {
    id: 10,
    name: "Sang Ayu Riani",
    email: "sangriani@gmail.com",
    phone: "+62 821-0010-0010",
    role: "Seniman",
    joinedAt: "14 Jun 2026",
    status: "active",
    totalAuctions: 6,
    totalBids: 0,
    wonAuctions: 0,
    watchlist: 0,
    recentActivity: [],
    auctionHistory: [],
    bidHistory: [],
  },
  {
    id: 11,
    name: "Budi Santoso",
    email: "budisantoso@gmail.com",
    phone: "+62 822-0011-0011",
    role: "Kolektor",
    joinedAt: "28 Jan 2026",
    status: "active",
    totalAuctions: 0,
    totalBids: 41,
    wonAuctions: 5,
    watchlist: 28,
    recentActivity: [],
    auctionHistory: [],
    bidHistory: [],
  },
  {
    id: 12,
    name: "I Made Sudarma",
    email: "imadesudarma@gmail.com",
    phone: "+62 823-0012-0012",
    role: "Seniman",
    joinedAt: "7 Mar 2026",
    status: "blocked",
    totalAuctions: 8,
    totalBids: 5,
    wonAuctions: 0,
    watchlist: 2,
    recentActivity: [],
    auctionHistory: [],
    bidHistory: [],
  },
]);

// ─── Computed ────────────────────────────────────────────────
const filteredUsers = computed(() => {
  let list = [...users.value];

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(
      (u) =>
        u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q),
    );
  }

  if (activeFilter.value === "active")
    list = list.filter((u) => u.status === "active");
  if (activeFilter.value === "blocked")
    list = list.filter((u) => u.status === "blocked");
  if (activeFilter.value === "new")
    list = list.filter((u) => u.joinedAt.includes("2026"));

  if (filterActivity.value === "has_auction")
    list = list.filter((u) => u.totalAuctions > 0);
  if (filterActivity.value === "has_bid")
    list = list.filter((u) => u.totalBids > 0);
  if (filterActivity.value === "inactive")
    list = list.filter((u) => u.totalAuctions === 0 && u.totalBids === 0);

  if (sortBy.value === "oldest") list.sort((a, b) => a.id - b.id);
  else if (sortBy.value === "most_active")
    list.sort(
      (a, b) => b.totalBids + b.totalAuctions - (a.totalBids + a.totalAuctions),
    );
  else if (sortBy.value === "most_auctions")
    list.sort((a, b) => b.totalAuctions - a.totalAuctions);
  else if (sortBy.value === "most_bids")
    list.sort((a, b) => b.totalBids - a.totalBids);
  else list.sort((a, b) => b.id - a.id);

  return list;
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredUsers.value.length / perPage)),
);

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredUsers.value.slice(start, start + perPage);
});

const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const cur = currentPage.value;
  if (total <= 5) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    pages.push(1);
    if (cur > 3) pages.push("...");
    for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++)
      pages.push(i);
    if (cur < total - 2) pages.push("...");
    pages.push(total);
  }
  return pages;
});

// ─── Selection ───────────────────────────────────────────────
const selectedId = ref(null);
const selectedUser = ref(null);

function selectUser(user) {
  if (!user) return;
  if (selectedId.value === user.id) {
    selectedId.value = null;
    selectedUser.value = null;
  } else {
    selectedId.value = user.id;
    selectedUser.value = user;
  }
}

// ─── Confirm modal ───────────────────────────────────────────
const confirmTarget = ref(null);

function confirmAction(action, user) {
  confirmTarget.value = { action, user };
}

function handleConfirm() {
  if (!confirmTarget.value) return;
  const { action, user } = confirmTarget.value;
  const target = users.value.find((u) => u.id === user.id);
  if (target) {
    target.status = action === "block" ? "blocked" : "active";
    if (selectedUser.value?.id === target.id)
      selectedUser.value = { ...target };
  }
  confirmTarget.value = null;
}

// ─── Side panel data ─────────────────────────────────────────
const mostActiveUsers = computed(() =>
  [...users.value]
    .sort(
      (a, b) => b.totalBids + b.totalAuctions - (a.totalBids + a.totalAuctions),
    )
    .slice(0, 5),
);

const newUsers = computed(() =>
  [...users.value].sort((a, b) => b.id - a.id).slice(0, 5),
);

// ─── Helpers ─────────────────────────────────────────────────
function formatRp(val) {
  return "Rp " + val.toLocaleString("id-ID");
}

function initials(name) {
  return name
    .split(" ")
    .map((w) => w[0])
    .slice(0, 2)
    .join("")
    .toUpperCase();
}

const palette = [
  "#1a1a1a",
  "#374151",
  "#4b5563",
  "#6b7280",
  "#374151",
  "#111827",
  "#1f2937",
  "#374151",
];
function avatarColor(name) {
  const idx = name.charCodeAt(0) % palette.length;
  return palette[idx];
}

function userStatusBadge(status) {
  const map = {
    active: {
      class: "bg-gray-100 text-gray-800",
      dot: "bg-gray-800",
      label: "Aktif",
    },
    blocked: {
      class: "bg-gray-100 text-gray-400",
      dot: "bg-gray-300",
      label: "Diblokir",
    },
  };
  return map[status] ?? map.active;
}

function bidStatusStyle(status) {
  const map = {
    leading: { class: "bg-gray-100 text-gray-800", label: "Memimpin" },
    outbid: { class: "bg-gray-100 text-gray-400", label: "Outbid" },
    won: { class: "bg-black text-white", label: "Menang" },
    lost: { class: "bg-gray-100 text-gray-400", label: "Kalah" },
  };
  return map[status] ?? map.outbid;
}

function exportData() {
  console.log("Export data pengguna");
}
</script>

<template>
  <div class="flex-1 px-8 py-8 space-y-6 min-h-screen bg-gray-50 font-sans">
    <!-- ═══════════════════ HEADER ═══════════════════ -->
    <div class="flex justify-between items-center">
      <div>
        <span
          class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
          >Admin Panel</span
        >
        <h1 class="text-3xl font-bold text-black mt-1.5 tracking-tight">
          Manajemen Pengguna
        </h1>
        <p class="text-gray-500 text-sm mt-2 max-w-2xl leading-relaxed">
          Kelola seluruh pengguna yang terdaftar pada platform, pantau aktivitas
          mereka dalam proses lelang, serta pastikan penggunaan sistem berjalan
          sesuai aturan yang berlaku.
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
        Tambah User
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
          activeFilter === stat.filter ? 'ring-2 ring-black ring-offset-2' : '',
        ]"
        @click="activeFilter = stat.filter"
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

    <!-- ═══════════════════ FILTERS ═══════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
      <div class="flex flex-col xl:flex-row gap-3">
        <!-- Search -->
        <div class="relative flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama atau email pengguna..."
            class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm text-black placeholder-gray-400 outline-none focus:border-black transition-colors bg-gray-50"
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

        <div class="flex flex-wrap gap-2">
          <!-- Status filter -->
          <div class="relative">
            <select
              v-model="filterActivity"
              class="appearance-none border border-gray-200 rounded-xl pl-3 pr-8 py-2.5 text-sm text-gray-600 outline-none focus:border-black transition-colors bg-gray-50 cursor-pointer"
            >
              <option value="">Semua Aktivitas</option>
              <option value="has_auction">Pernah Membuat Lelang</option>
              <option value="has_bid">Pernah Melakukan Penawaran</option>
              <option value="inactive">Tidak Aktif</option>
            </select>
            <svg
              class="w-3.5 h-3.5 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
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
          <!-- Sort -->
          <div class="relative">
            <select
              v-model="sortBy"
              class="appearance-none border border-gray-200 rounded-xl pl-3 pr-8 py-2.5 text-sm text-gray-600 outline-none focus:border-black transition-colors bg-gray-50 cursor-pointer"
            >
              <option value="newest">Terbaru</option>
              <option value="oldest">Terlama</option>
              <option value="most_active">Paling Aktif</option>
              <option value="most_auctions">Lelang Terbanyak</option>
              <option value="most_bids">Penawaran Terbanyak</option>
            </select>
            <svg
              class="w-3.5 h-3.5 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
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
          <!-- Export -->
          <button
            @click="exportData"
            class="flex items-center gap-1.5 px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:border-black hover:text-black transition-all duration-200 font-medium"
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
      </div>

      <!-- Status tabs -->
      <div class="flex flex-wrap gap-2 mt-4">
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
    </div>

    <!-- ═══════════════════ MAIN CONTENT ═══════════════════ -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- ── TABLE ── -->
      <div class="xl:col-span-2 space-y-4">
        <div
          class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
        >
          <!-- Table header -->
          <div
            class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
          >
            <p class="text-sm font-semibold text-black">
              Menampilkan {{ paginatedUsers.length }} dari
              {{ filteredUsers.length }} pengguna
            </p>
          </div>

          <!-- Empty -->
          <div v-if="paginatedUsers.length === 0" class="py-20 text-center">
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
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
            </div>
            <p class="font-medium text-gray-700 text-sm mb-1">
              Tidak ada pengguna ditemukan
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
                    Pengguna
                  </th>
                  <th
                    class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell"
                  >
                    Email
                  </th>
                  <th
                    class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell"
                  >
                    Lelang
                  </th>
                  <th
                    class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell"
                  >
                    Bid
                  </th>
                  <th
                    class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden xl:table-cell"
                  >
                    Bergabung
                  </th>
                  <th
                    class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                  >
                    Status
                  </th>
                  <th class="px-4 py-3"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr
                  v-for="user in paginatedUsers"
                  :key="user.id"
                  class="hover:bg-gray-50 transition-colors cursor-pointer group"
                  :class="selectedId === user.id ? 'bg-gray-50' : ''"
                  @click="selectUser(user)"
                >
                  <!-- Pengguna -->
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0"
                        :style="{ backgroundColor: avatarColor(user.name) }"
                      >
                        {{ initials(user.name) }}
                      </div>
                      <div class="min-w-0">
                        <p
                          class="text-sm font-medium text-black truncate max-w-[140px]"
                        >
                          {{ user.name }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                          {{ user.role }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <!-- Email -->
                  <td class="px-4 py-4 hidden md:table-cell">
                    <p class="text-sm text-gray-600 truncate max-w-[180px]">
                      {{ user.email }}
                    </p>
                  </td>
                  <!-- Lelang -->
                  <td class="px-4 py-4 text-center hidden lg:table-cell">
                    <p class="text-sm font-semibold text-black">
                      {{ user.totalAuctions }}
                    </p>
                  </td>
                  <!-- Bid -->
                  <td class="px-4 py-4 text-center hidden lg:table-cell">
                    <p class="text-sm font-semibold text-black">
                      {{ user.totalBids }}
                    </p>
                  </td>
                  <!-- Bergabung -->
                  <td class="px-4 py-4 hidden xl:table-cell">
                    <p class="text-xs text-gray-500 whitespace-nowrap">
                      {{ user.joinedAt }}
                    </p>
                  </td>
                  <!-- Status -->
                  <td class="px-4 py-4 text-center">
                    <span
                      :class="[
                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap',
                        userStatusBadge(user.status).class,
                      ]"
                    >
                      <span
                        class="w-1.5 h-1.5 rounded-full shrink-0"
                        :class="userStatusBadge(user.status).dot"
                      ></span>
                      {{ userStatusBadge(user.status).label }}
                    </span>
                  </td>
                  <!-- Aksi -->
                  <td class="px-4 py-4" @click.stop>
                    <div
                      class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <button
                        @click="selectUser(user)"
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
                        v-if="user.status === 'active'"
                        @click="confirmAction('block', user)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:border-gray-700 hover:text-gray-700 transition-all"
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
                        v-else
                        @click="confirmAction('activate', user)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:border-gray-700 hover:text-gray-700 transition-all"
                        title="Aktifkan"
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
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
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
            v-if="filteredUsers.length > 0"
            class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-4"
          >
            <p class="text-xs text-gray-400">
              Menampilkan {{ (currentPage - 1) * perPage + 1 }}–{{
                Math.min(currentPage * perPage, filteredUsers.length)
              }}
              dari {{ filteredUsers.length }} pengguna
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
                v-for="p in visiblePages"
                :key="p"
                @click="p !== '...' && (currentPage = p)"
                :class="[
                  'w-8 h-8 text-xs rounded-lg border transition-all',
                  p === '...'
                    ? 'border-transparent text-gray-400 cursor-default'
                    : currentPage === p
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
        <!-- User detail panel -->
        <transition name="slide-fade">
          <div
            v-if="selectedUser"
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <!-- Profile header -->
            <div class="p-5 border-b border-gray-100">
              <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                  <div
                    class="w-12 h-12 rounded-2xl flex items-center justify-center text-white text-base font-bold shrink-0"
                    :style="{ backgroundColor: avatarColor(selectedUser.name) }"
                  >
                    {{ initials(selectedUser.name) }}
                  </div>
                  <div>
                    <p class="font-bold text-sm text-black">
                      {{ selectedUser.name }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ selectedUser.role }}
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-1.5">
                  <span
                    :class="[
                      'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium',
                      userStatusBadge(selectedUser.status).class,
                    ]"
                  >
                    <span
                      class="w-1.5 h-1.5 rounded-full"
                      :class="userStatusBadge(selectedUser.status).dot"
                    ></span>
                    {{ userStatusBadge(selectedUser.status).label }}
                  </span>
                  <button
                    @click="
                      selectedUser = null;
                      selectedId = null;
                    "
                    class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:border-black hover:text-black transition-all"
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
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Info rows -->
              <div class="space-y-2">
                <div class="flex items-center gap-2.5 text-xs text-gray-500">
                  <svg
                    class="w-3.5 h-3.5 text-gray-400 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                    />
                  </svg>
                  {{ selectedUser.email }}
                </div>
                <div class="flex items-center gap-2.5 text-xs text-gray-500">
                  <svg
                    class="w-3.5 h-3.5 text-gray-400 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                    />
                  </svg>
                  {{ selectedUser.phone }}
                </div>
                <div class="flex items-center gap-2.5 text-xs text-gray-500">
                  <svg
                    class="w-3.5 h-3.5 text-gray-400 shrink-0"
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
                  Bergabung {{ selectedUser.joinedAt }}
                </div>
              </div>
            </div>

            <!-- Activity stats -->
            <div class="p-5 border-b border-gray-100">
              <p
                class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-3"
              >
                Statistik Aktivitas
              </p>
              <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-xl p-3">
                  <p class="text-xs text-gray-400 mb-0.5">Lelang Dibuat</p>
                  <p class="text-lg font-bold text-black">
                    {{ selectedUser.totalAuctions }}
                  </p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                  <p class="text-xs text-gray-400 mb-0.5">Total Bid</p>
                  <p class="text-lg font-bold text-black">
                    {{ selectedUser.totalBids }}
                  </p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                  <p class="text-xs text-gray-400 mb-0.5">Lelang Menang</p>
                  <p class="text-lg font-bold text-black">
                    {{ selectedUser.wonAuctions }}
                  </p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                  <p class="text-xs text-gray-400 mb-0.5">Watchlist</p>
                  <p class="text-lg font-bold text-black">
                    {{ selectedUser.watchlist }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Recent activity -->
            <div class="p-5 border-b border-gray-100">
              <p
                class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-3"
              >
                Aktivitas Terbaru
              </p>
              <div class="space-y-3">
                <div
                  v-for="(act, i) in selectedUser.recentActivity"
                  :key="i"
                  class="flex items-start gap-3"
                >
                  <div
                    :class="[
                      'w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5',
                      act.dark ? 'bg-black' : 'bg-gray-100',
                    ]"
                  >
                    <svg
                      class="w-3 h-3"
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
                  <div>
                    <p class="text-xs text-gray-700 leading-relaxed">
                      {{ act.text }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ act.time }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Auction history -->
            <div class="p-5 border-b border-gray-100">
              <p
                class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-3"
              >
                Riwayat Lelang
              </p>
              <div class="space-y-2.5">
                <div
                  v-for="(lel, i) in selectedUser.auctionHistory"
                  :key="i"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100"
                >
                  <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-black truncate">
                      {{ lel.name }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ formatRp(lel.finalPrice) }} · {{ lel.totalBids }} bid
                    </p>
                  </div>
                  <span
                    :class="[
                      'ml-2 shrink-0 text-xs px-2 py-0.5 rounded-full font-medium',
                      lel.status === 'ended'
                        ? 'bg-black text-white'
                        : 'bg-gray-100 text-gray-500',
                    ]"
                  >
                    {{ lel.status === "ended" ? "Selesai" : "Aktif" }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Bid history -->
            <div class="p-5 border-b border-gray-100">
              <p
                class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-3"
              >
                Riwayat Penawaran
              </p>
              <div class="space-y-2.5">
                <div
                  v-for="(bid, i) in selectedUser.bidHistory"
                  :key="i"
                  class="flex items-center justify-between"
                >
                  <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-black truncate">
                      {{ bid.name }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ formatRp(bid.amount) }} · {{ bid.time }}
                    </p>
                  </div>
                  <span
                    :class="[
                      'ml-2 shrink-0 text-xs px-2 py-0.5 rounded-full font-medium',
                      bidStatusStyle(bid.bidStatus).class,
                    ]"
                  >
                    {{ bidStatusStyle(bid.bidStatus).label }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="p-5 flex flex-col gap-2">
              <button
                @click="$router.push(`/admin/users/${selectedUser.id}`)"
                class="w-full py-2.5 bg-black text-white rounded-xl text-xs font-medium hover:bg-gray-800 transition-colors"
              >
                Lihat Profil Lengkap
              </button>
              <button
                v-if="selectedUser.status === 'active'"
                @click="confirmAction('block', selectedUser)"
                class="w-full py-2.5 border border-gray-200 text-gray-600 rounded-xl text-xs font-medium hover:border-gray-700 hover:text-gray-700 transition-all duration-300"
              >
                Nonaktifkan Pengguna
              </button>
              <button
                v-else
                @click="confirmAction('activate', selectedUser)"
                class="w-full py-2.5 border border-gray-200 text-gray-600 rounded-xl text-xs font-medium hover:border-black hover:text-black transition-all duration-300"
              >
                Aktifkan Pengguna
              </button>
            </div>
          </div>
        </transition>

        <!-- Default panels when nothing selected -->
        <template v-if="!selectedUser">
          <!-- Most active users -->
          <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-sm font-semibold text-black">
                Pengguna Paling Aktif
              </h2>
              <button
                class="text-xs text-gray-400 hover:text-black transition-colors font-medium"
              >
                Lihat Semua
              </button>
            </div>
            <div class="space-y-3">
              <div
                v-for="(user, i) in mostActiveUsers"
                :key="user.id"
                class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-xl p-2 -mx-2 transition-colors"
                :class="
                  i < mostActiveUsers.length - 1
                    ? 'pb-3 border-b border-gray-50'
                    : ''
                "
                @click="selectUser(users.find((u) => u.id === user.id))"
              >
                <span class="text-xs font-bold text-gray-300 w-4 shrink-0">{{
                  String(i + 1).padStart(2, "0")
                }}</span>
                <div
                  class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold shrink-0"
                  :style="{ backgroundColor: avatarColor(user.name) }"
                >
                  {{ initials(user.name) }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold text-black truncate">
                    {{ user.name }}
                  </p>
                  <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs text-gray-400"
                      >{{ user.totalBids }} bid</span
                    >
                    <span class="text-xs text-gray-300">·</span>
                    <span class="text-xs text-gray-400"
                      >{{ user.totalAuctions }} lelang</span
                    >
                    <span class="text-xs text-gray-300">·</span>
                    <span class="text-xs text-gray-400"
                      >{{ user.wonAuctions }} menang</span
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- New users -->
          <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-sm font-semibold text-black">Pengguna Baru</h2>
              <div class="flex items-center gap-1.5">
                <span
                  class="live-dot w-1.5 h-1.5 rounded-full bg-black inline-block"
                ></span>
                <span class="text-xs text-gray-400">Bulan ini</span>
              </div>
            </div>
            <div class="space-y-3">
              <div
                v-for="(user, i) in newUsers"
                :key="user.id"
                class="flex items-center gap-3"
                :class="
                  i < newUsers.length - 1 ? 'pb-3 border-b border-gray-50' : ''
                "
              >
                <div
                  class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold shrink-0"
                  :style="{ backgroundColor: avatarColor(user.name) }"
                >
                  {{ initials(user.name) }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold text-black truncate">
                    {{ user.name }}
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5 truncate">
                    {{ user.email }}
                  </p>
                </div>
                <span
                  class="text-xs text-gray-400 shrink-0 whitespace-nowrap"
                  >{{ user.joinedAt }}</span
                >
              </div>
            </div>
          </div>

          <!-- Summary card -->
          <div class="bg-black rounded-2xl p-5">
            <p class="text-white/50 text-xs uppercase tracking-widest mb-3">
              Pertumbuhan Pengguna
            </p>
            <p class="text-white font-bold text-3xl">+25</p>
            <p class="text-white/40 text-xs mt-1.5">Pengguna baru bulan ini</p>
            <div
              class="mt-4 pt-4 border-t border-white/10 grid grid-cols-2 gap-3"
            >
              <div>
                <p class="text-white/40 text-xs mb-1">Total aktif</p>
                <p class="text-white text-sm font-semibold">1.120</p>
              </div>
              <div>
                <p class="text-white/40 text-xs mb-1">Diblokir</p>
                <p class="text-white text-sm font-semibold">8</p>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

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
                  confirmTarget.action === 'block'
                    ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'
                    : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                "
              />
            </svg>
          </div>
          <h3 class="font-bold text-lg text-black mb-2">
            {{
              confirmTarget.action === "block"
                ? "Nonaktifkan Akun?"
                : "Aktifkan Kembali Akun?"
            }}
          </h3>
          <p class="text-gray-500 text-sm leading-relaxed mb-6">
            <template v-if="confirmTarget.action === 'block'">
              Apakah Anda yakin ingin menonaktifkan akun
              <strong class="text-black">{{ confirmTarget.user.name }}</strong
              >? Pengguna tidak akan dapat mengakses platform.
            </template>
            <template v-else>
              Apakah Anda yakin ingin mengaktifkan kembali akun
              <strong class="text-black">{{ confirmTarget.user.name }}</strong
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
              {{
                confirmTarget.action === "block" ? "Nonaktifkan" : "Aktifkan"
              }}
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
  transition: opacity 0.2s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.slide-fade-leave-to {
  opacity: 0;
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
