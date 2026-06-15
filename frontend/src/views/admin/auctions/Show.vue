<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

// ─── Auction Data ─────────────────────────────────────────────
const auction = ref({
  id: 1,
  name: "Lukisan Bali Klasik Tahun 1980",
  category: "Lukisan",
  description:
    "Lukisan tradisional Bali karya seniman lokal yang dibuat pada tahun 1980 dengan kondisi sangat baik dan memiliki nilai sejarah tinggi.",
  images: [
    "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=800&q=80",
    "https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=800&q=80",
    "https://images.unsplash.com/photo-1578301978018-3005759f48f7?w=800&q=80",
    "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=800&q=80",
  ],
  startPrice: 5000000,
  currentPrice: 12500000,
  minIncrement: 100000,
  totalBids: 45,
  status: "active",
  startDate: "12 Jun 2026, 09.00 WITA",
  endDate: "15 Jun 2026, 21.00 WITA",
  endTimestamp: new Date("2026-06-15T21:00:00+08:00").getTime(),
  seller: {
    name: "I Made Sudarma",
    email: "madesudarma@gmail.com",
    phone: "0812xxxxxxxx",
    joinDate: "10 Januari 2026",
    status: "active",
    totalAuctions: 18,
    completedAuctions: 15,
    activeAuctions: 3,
    totalSold: 15,
    avatar: null,
  },
  stats: {
    totalViewers: 32,
    currentViewers: 12,
    bidsLastHour: 8,
    bidsLast24h: 27,
    lowestBid: 5100000,
    uniqueBidders: 18,
    lastActivity:
      "Penawaran baru sebesar Rp 12.500.000 oleh I Putu Arya pada 14 Juni 2026 pukul 19.25 WITA.",
  },
  antiSniping: {
    active: true,
    lastExtension: "+2 Menit",
    note: "Lelang diperpanjang secara otomatis karena terdapat penawaran pada 30 detik terakhir sebelum penutupan.",
  },
  buyNow: {
    used: false,
    price: 20000000,
  },
  notifications: [
    {
      type: "warning",
      text: "Terdapat 2 laporan pengguna terkait lelang ini.",
    },
    {
      type: "info",
      text: "Lelang sedang dalam proses peninjauan administrator.",
    },
  ],
  winner: null,
});

const bidHistory = ref([
  {
    name: "I Putu Arya",
    email: "arya@email.com",
    amount: 12500000,
    status: "highest",
    time: "14 Jun 2026, 19.25 WITA",
  },
  {
    name: "Ni Luh Ratna",
    email: "ratna@email.com",
    amount: 12300000,
    status: "outbid",
    time: "14 Jun 2026, 19.20 WITA",
  },
  {
    name: "Kadek Wijaya",
    email: "wijaya@email.com",
    amount: 12000000,
    status: "outbid",
    time: "14 Jun 2026, 19.10 WITA",
  },
  {
    name: "Budi Santoso",
    email: "budi@email.com",
    amount: 11500000,
    status: "outbid",
    time: "14 Jun 2026, 18.55 WITA",
  },
  {
    name: "I Gede Putra",
    email: "putra@email.com",
    amount: 11000000,
    status: "outbid",
    time: "14 Jun 2026, 18.30 WITA",
  },
  {
    name: "Wayan Sudira",
    email: "sudira@email.com",
    amount: 10500000,
    status: "outbid",
    time: "14 Jun 2026, 17.45 WITA",
  },
  {
    name: "Ni Made Ayu",
    email: "ayu@email.com",
    amount: 9500000,
    status: "outbid",
    time: "14 Jun 2026, 16.00 WITA",
  },
  {
    name: "Gede Mahendra",
    email: "mahendra@email.com",
    amount: 8000000,
    status: "outbid",
    time: "13 Jun 2026, 22.10 WITA",
  },
  {
    name: "Komang Sari",
    email: "sari@email.com",
    amount: 6500000,
    status: "outbid",
    time: "13 Jun 2026, 15.30 WITA",
  },
  {
    name: "Putu Ariasa",
    email: "ariasa@email.com",
    amount: 5100000,
    status: "outbid",
    time: "12 Jun 2026, 09.15 WITA",
  },
]);

const systemActivity = ref([
  {
    text: "Lelang dibuat oleh I Made Sudarma",
    time: "12 Jun 2026, 08.30 WITA",
    icon: "M12 4v16m8-8H4",
  },
  {
    text: "Penawaran pertama masuk sebesar Rp 5.100.000",
    time: "12 Jun 2026, 09.15 WITA",
    icon: "M13 10V3L4 14h7v7l9-11h-7z",
  },
  {
    text: "Lelang diperpanjang otomatis karena Anti-Sniping",
    time: "15 Jun 2026, 20.59 WITA",
    icon: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    text: "Pemenang berhasil ditentukan",
    time: "15 Jun 2026, 21.02 WITA",
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
]);

// ─── Gallery ──────────────────────────────────────────────────
const activeImage = ref(0);

// ─── Countdown ────────────────────────────────────────────────
const countdown = ref({ days: 0, hours: 0, minutes: 0, seconds: 0 });
let countdownInterval = null;

function updateCountdown() {
  const now = Date.now();
  const diff = auction.value.endTimestamp - now;
  if (diff <= 0) {
    countdown.value = { days: 0, hours: 0, minutes: 0, seconds: 0 };
    return;
  }
  countdown.value = {
    days: Math.floor(diff / 86400000),
    hours: Math.floor((diff % 86400000) / 3600000),
    minutes: Math.floor((diff % 3600000) / 60000),
    seconds: Math.floor((diff % 60000) / 1000),
  };
}

onMounted(() => {
  updateCountdown();
  countdownInterval = setInterval(updateCountdown, 1000);
});
onUnmounted(() => clearInterval(countdownInterval));

// ─── Modals ───────────────────────────────────────────────────
const modal = ref(null); // 'deactivate' | 'delete' | 'cancel'

function openModal(type) {
  modal.value = type;
}
function closeModal() {
  modal.value = null;
}

function handleModalConfirm() {
  if (modal.value === "deactivate") auction.value.status = "cancelled";
  if (modal.value === "cancel") auction.value.status = "cancelled";
  if (modal.value === "delete") router.push("/admin/auctions");
  closeModal();
}

// ─── Helpers ──────────────────────────────────────────────────
function formatRp(val) {
  return "Rp " + val.toLocaleString("id-ID");
}

function pad(n) {
  return String(n).padStart(2, "0");
}

function statusBadge(status) {
  const map = {
    active: {
      class: "bg-gray-100 text-gray-800",
      dot: "bg-gray-800",
      label: "Sedang Berlangsung",
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

function bidStatusBadge(status) {
  if (status === "highest") return "bg-black text-white";
  return "bg-gray-100 text-gray-500";
}

function bidStatusLabel(status) {
  if (status === "highest") return "Tertinggi";
  return "Outbid";
}

const priceMultiplier = computed(() => {
  if (!auction.value.startPrice) return 1;
  return (
    (auction.value.currentPrice / auction.value.startPrice - 1) *
    100
  ).toFixed(0);
});

const modalConfig = computed(() => {
  const configs = {
    deactivate: {
      title: "Nonaktifkan Lelang",
      body: "Apakah Anda yakin ingin menonaktifkan lelang ini? Lelang tidak akan menerima penawaran baru sampai diaktifkan kembali.",
      confirm: "Nonaktifkan",
      icon: "M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636",
    },
    delete: {
      title: "Hapus Lelang",
      body: "Tindakan ini akan menghapus data lelang secara permanen dan tidak dapat dibatalkan.",
      confirm: "Hapus Permanen",
      icon: "M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16",
    },
    cancel: {
      title: "Batalkan Lelang",
      body: "Apakah Anda yakin ingin membatalkan lelang ini? Seluruh penawaran yang masuk akan dibatalkan.",
      confirm: "Batalkan Lelang",
      icon: "M6 18L18 6M6 6l12 12",
    },
  };
  return configs[modal.value] ?? configs.deactivate;
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 font-sans flex flex-col">
    <div class="flex-1 px-8 py-8 space-y-6 max-w-screen-2xl mx-auto w-full">
      <!-- ══ HEADER ══ -->
      <div class="space-y-1">
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
          <span
            class="hover:text-black cursor-pointer transition-colors"
            @click="$router.push('/admin/dashboard')"
            >Dashboard</span
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
              d="M9 5l7 7-7 7"
            />
          </svg>
          <span
            class="hover:text-black cursor-pointer transition-colors"
            @click="$router.push('/admin/auctions')"
            >Manajemen Lelang</span
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
              d="M9 5l7 7-7 7"
            />
          </svg>
          <span class="text-black font-medium">Detail Lelang</span>
        </nav>
        <div class="flex items-start justify-between gap-4 pt-1">
          <div>
            <h1 class="text-3xl font-bold text-black tracking-tight">
              Detail Lelang
            </h1>
            <p class="text-gray-500 text-sm mt-1.5 max-w-2xl leading-relaxed">
              Lihat informasi lengkap mengenai lelang, aktivitas penawaran, dan
              data penjual untuk memastikan proses lelang berjalan dengan aman
              dan sesuai aturan platform.
            </p>
          </div>
          <button
            @click="$router.push('/admin/auctions')"
            class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-black font-medium transition-colors shrink-0 mt-1 border border-gray-200 rounded-xl px-3 py-2 hover:border-black"
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
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              />
            </svg>
            Kembali
          </button>
        </div>
      </div>

      <!-- ══ NOTIFICATIONS ══ -->
      <div v-if="auction.notifications.length" class="space-y-2">
        <div
          v-for="notif in auction.notifications"
          :key="notif.text"
          class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3"
        >
          <div
            :class="[
              'w-7 h-7 rounded-lg flex items-center justify-center shrink-0',
              notif.type === 'warning' ? 'bg-black' : 'bg-gray-100',
            ]"
          >
            <svg
              class="w-3.5 h-3.5"
              :class="notif.type === 'warning' ? 'text-white' : 'text-gray-600'"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <p class="text-sm text-gray-700 flex-1">{{ notif.text }}</p>
          <button
            class="text-xs text-gray-400 hover:text-black font-medium transition-colors shrink-0"
          >
            Tinjau
          </button>
        </div>
      </div>

      <!-- ══ MAIN GRID ══ -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- ── LEFT COLUMN ── -->
        <div class="xl:col-span-2 space-y-6">
          <!-- GALLERY + ITEM INFO -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <!-- Gallery -->
            <div class="relative bg-gray-100 h-80 overflow-hidden">
              <img
                :src="auction.images[activeImage]"
                :alt="auction.name"
                class="w-full h-full object-cover transition-opacity duration-300"
              />
              <div
                class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent pointer-events-none"
              ></div>
              <!-- Status badge -->
              <div class="absolute top-4 left-4">
                <span
                  :class="[
                    'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium backdrop-blur-sm',
                    statusBadge(auction.status).class,
                  ]"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full"
                    :class="statusBadge(auction.status).dot"
                  ></span>
                  {{ statusBadge(auction.status).label }}
                </span>
              </div>
              <!-- Image counter -->
              <div
                class="absolute top-4 right-4 bg-black/50 backdrop-blur-sm text-white text-xs px-2.5 py-1 rounded-full"
              >
                {{ activeImage + 1 }} / {{ auction.images.length }}
              </div>
            </div>
            <!-- Thumbnails -->
            <div class="flex gap-2 p-4 border-b border-gray-100">
              <div
                v-for="(img, i) in auction.images"
                :key="i"
                @click="activeImage = i"
                :class="[
                  'w-16 h-16 rounded-xl bg-cover bg-center cursor-pointer border-2 transition-all shrink-0',
                  activeImage === i
                    ? 'border-black'
                    : 'border-transparent opacity-60 hover:opacity-100',
                ]"
                :style="{ backgroundImage: `url('${img}')` }"
              ></div>
            </div>
            <!-- Item details -->
            <div class="p-6 space-y-5">
              <div>
                <p
                  class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-1"
                >
                  Informasi Barang
                </p>
                <h2 class="text-xl font-bold text-black leading-snug">
                  {{ auction.name }}
                </h2>
                <span
                  class="inline-block mt-1.5 text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full font-medium"
                  >{{ auction.category }}</span
                >
                <p class="text-sm text-gray-500 mt-3 leading-relaxed">
                  {{ auction.description }}
                </p>
              </div>

              <!-- Auction info grid -->
              <div class="border-t border-gray-100 pt-5">
                <p
                  class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-3"
                >
                  Informasi Lelang
                </p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                  <div class="bg-gray-50 rounded-xl p-3.5">
                    <p class="text-xs text-gray-400 mb-1">Harga Awal</p>
                    <p class="text-sm font-bold text-black">
                      {{ formatRp(auction.startPrice) }}
                    </p>
                  </div>
                  <div class="bg-black rounded-xl p-3.5">
                    <p class="text-xs text-white/50 mb-1">Harga Tertinggi</p>
                    <p class="text-sm font-bold text-white">
                      {{ formatRp(auction.currentPrice) }}
                    </p>
                    <p class="text-xs text-white/40 mt-0.5">
                      +{{ priceMultiplier }}% dari awal
                    </p>
                  </div>
                  <div class="bg-gray-50 rounded-xl p-3.5">
                    <p class="text-xs text-gray-400 mb-1">Kelipatan Min. Bid</p>
                    <p class="text-sm font-bold text-black">
                      {{ formatRp(auction.minIncrement) }}
                    </p>
                  </div>
                  <div class="bg-gray-50 rounded-xl p-3.5">
                    <p class="text-xs text-gray-400 mb-1">Total Penawaran</p>
                    <p class="text-sm font-bold text-black">
                      {{ auction.totalBids }} Bid
                    </p>
                  </div>
                  <div class="bg-gray-50 rounded-xl p-3.5">
                    <p class="text-xs text-gray-400 mb-1">Mulai</p>
                    <p class="text-xs font-semibold text-black leading-tight">
                      {{ auction.startDate }}
                    </p>
                  </div>
                  <div class="bg-gray-50 rounded-xl p-3.5">
                    <p class="text-xs text-gray-400 mb-1">Berakhir</p>
                    <p class="text-xs font-semibold text-black leading-tight">
                      {{ auction.endDate }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Countdown -->
              <div
                v-if="auction.status === 'active'"
                class="border-t border-gray-100 pt-5"
              >
                <p
                  class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-3"
                >
                  Sisa Waktu
                </p>
                <div class="flex gap-2">
                  <div
                    v-for="(unit, label) in {
                      Hari: countdown.days,
                      Jam: countdown.hours,
                      Menit: countdown.minutes,
                      Detik: countdown.seconds,
                    }"
                    :key="label"
                    class="flex-1 bg-gray-900 rounded-xl p-3 text-center"
                  >
                    <p
                      class="text-2xl font-bold text-white font-mono tabular-nums"
                    >
                      {{ pad(unit) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ label }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- BID HISTORY -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <div
              class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
            >
              <div>
                <p class="text-sm font-semibold text-black">
                  Aktivitas Penawaran
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                  Riwayat seluruh bid yang masuk
                </p>
              </div>
              <div class="flex gap-4 text-right">
                <div>
                  <p class="text-xs text-gray-400">Total Penawar</p>
                  <p class="text-sm font-bold text-black">
                    {{ auction.stats.uniqueBidders }} Pengguna
                  </p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Total Bid</p>
                  <p class="text-sm font-bold text-black">
                    {{ auction.totalBids }}
                  </p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Bid Terendah</p>
                  <p class="text-sm font-bold text-black">
                    {{ formatRp(auction.stats.lowestBid) }}
                  </p>
                </div>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-50">
                    <th
                      class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      #
                    </th>
                    <th
                      class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Penawar
                    </th>
                    <th
                      class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Nominal Bid
                    </th>
                    <th
                      class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Status
                    </th>
                    <th
                      class="text-right px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Waktu
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr
                    v-for="(bid, i) in bidHistory"
                    :key="i"
                    class="hover:bg-gray-50 transition-colors"
                  >
                    <td class="px-6 py-3.5">
                      <span class="text-xs font-bold text-gray-300">{{
                        pad(i + 1)
                      }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                      <div class="flex items-center gap-2.5">
                        <div
                          class="w-7 h-7 rounded-lg bg-gray-900 flex items-center justify-center text-white text-xs font-bold shrink-0"
                        >
                          {{ bid.name.charAt(0) }}
                        </div>
                        <div>
                          <p class="text-sm font-medium text-black">
                            {{ bid.name }}
                          </p>
                          <p class="text-xs text-gray-400">{{ bid.email }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                      <p class="text-sm font-bold text-black">
                        {{ formatRp(bid.amount) }}
                      </p>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                      <span
                        :class="[
                          'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                          bidStatusBadge(bid.status),
                        ]"
                      >
                        {{ bidStatusLabel(bid.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-3.5 text-right">
                      <p class="text-xs text-gray-500 whitespace-nowrap">
                        {{ bid.time }}
                      </p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
              <button
                class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-black font-medium transition-colors"
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
                Lihat Riwayat Lengkap
              </button>
            </div>
          </div>

          <!-- SYSTEM ACTIVITY -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <p class="text-sm font-semibold text-black mb-4">
              Aktivitas Sistem
            </p>
            <div class="space-y-0">
              <div
                v-for="(act, i) in systemActivity"
                :key="i"
                class="flex gap-4 relative"
              >
                <!-- timeline line -->
                <div class="flex flex-col items-center">
                  <div
                    class="w-7 h-7 rounded-xl bg-gray-100 flex items-center justify-center shrink-0 z-10"
                  >
                    <svg
                      class="w-3.5 h-3.5 text-gray-600"
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
                  <div
                    v-if="i < systemActivity.length - 1"
                    class="w-px flex-1 bg-gray-100 my-1"
                  ></div>
                </div>
                <div class="pb-5">
                  <p class="text-sm text-gray-700">{{ act.text }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ act.time }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── RIGHT COLUMN ── -->
        <div class="space-y-4">
          <!-- SELLER INFO -->
          <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <p
              class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-4"
            >
              Informasi Penjual
            </p>
            <!-- Profile -->
            <div class="flex items-center gap-3 mb-4">
              <div
                class="w-12 h-12 rounded-xl bg-gray-900 flex items-center justify-center text-white text-lg font-bold shrink-0"
              >
                {{ auction.seller.name.charAt(0) }}
              </div>
              <div>
                <p class="text-sm font-bold text-black">
                  {{ auction.seller.name }}
                </p>
                <p class="text-xs text-gray-400">{{ auction.seller.email }}</p>
                <p class="text-xs text-gray-400">{{ auction.seller.phone }}</p>
              </div>
            </div>
            <div class="space-y-2 text-xs border-t border-gray-100 pt-4 mb-4">
              <div class="flex justify-between">
                <span class="text-gray-400">Bergabung</span>
                <span class="text-gray-700 font-medium">{{
                  auction.seller.joinDate
                }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Status Akun</span>
                <span
                  class="inline-flex items-center gap-1 font-medium"
                  :class="
                    auction.seller.status === 'active'
                      ? 'text-gray-800'
                      : 'text-gray-400'
                  "
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full"
                    :class="
                      auction.seller.status === 'active'
                        ? 'bg-gray-800'
                        : 'bg-gray-300'
                    "
                  ></span>
                  {{
                    auction.seller.status === "active" ? "Aktif" : "Nonaktif"
                  }}
                </span>
              </div>
            </div>
            <!-- Seller stats -->
            <div class="grid grid-cols-2 gap-2 mb-4">
              <div class="bg-gray-50 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-black">
                  {{ auction.seller.totalAuctions }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Total Lelang</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-black">
                  {{ auction.seller.completedAuctions }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Selesai</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-black">
                  {{ auction.seller.activeAuctions }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Aktif</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-3 text-center">
                <p class="text-lg font-bold text-black">
                  {{ auction.seller.totalSold }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Terjual</p>
              </div>
            </div>
            <button
              class="w-full py-2.5 border border-gray-200 text-gray-600 rounded-xl text-xs font-medium hover:border-black hover:text-black transition-all duration-300"
            >
              Lihat Profil Penjual
            </button>
          </div>

          <!-- REALTIME STATS -->
          <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-4">
              <span
                class="live-dot w-1.5 h-1.5 rounded-full bg-black inline-block"
              ></span>
              <p class="text-sm font-semibold text-black">Statistik Realtime</p>
            </div>
            <div class="grid grid-cols-2 gap-2 mb-4">
              <div class="bg-gray-50 rounded-xl p-3.5">
                <p class="text-xs text-gray-400 mb-0.5">Total Viewer</p>
                <p class="text-xl font-bold text-black">
                  {{ auction.stats.totalViewers }}
                </p>
                <p class="text-xs text-gray-400">pengguna</p>
              </div>
              <div class="bg-black rounded-xl p-3.5">
                <p class="text-xs text-white/50 mb-0.5">Viewer Saat Ini</p>
                <p class="text-xl font-bold text-white">
                  {{ auction.stats.currentViewers }}
                </p>
                <p class="text-xs text-white/40">online</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-3.5">
                <p class="text-xs text-gray-400 mb-0.5">Bid 1 Jam</p>
                <p class="text-xl font-bold text-black">
                  {{ auction.stats.bidsLastHour }}
                </p>
                <p class="text-xs text-gray-400">penawaran</p>
              </div>
              <div class="bg-gray-50 rounded-xl p-3.5">
                <p class="text-xs text-gray-400 mb-0.5">Bid 24 Jam</p>
                <p class="text-xl font-bold text-black">
                  {{ auction.stats.bidsLast24h }}
                </p>
                <p class="text-xs text-gray-400">penawaran</p>
              </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3.5">
              <p class="text-xs text-gray-400 mb-1.5">Aktivitas Terakhir</p>
              <p class="text-xs text-gray-700 leading-relaxed">
                {{ auction.stats.lastActivity }}
              </p>
            </div>
          </div>

          <!-- WINNER (shown if ended) -->
          <div
            v-if="auction.status === 'ended' && auction.winner"
            class="bg-white rounded-2xl border border-gray-100 p-5"
          >
            <p
              class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-4"
            >
              Informasi Pemenang
            </p>
            <div class="flex items-center gap-3 mb-4">
              <div
                class="w-10 h-10 rounded-xl bg-gray-900 flex items-center justify-center text-white text-sm font-bold shrink-0"
              >
                {{ auction.winner.name.charAt(0) }}
              </div>
              <div>
                <p class="text-sm font-bold text-black">
                  {{ auction.winner.name }}
                </p>
                <p class="text-xs text-gray-400">Pemenang Lelang</p>
              </div>
            </div>
            <div class="space-y-2 text-xs border-t border-gray-100 pt-4">
              <div class="flex justify-between">
                <span class="text-gray-400">Harga Akhir</span>
                <span class="font-bold text-black">{{
                  formatRp(auction.winner.finalPrice)
                }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Waktu Penutupan</span>
                <span class="text-gray-700">{{ auction.winner.closedAt }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-400">Status Pembayaran</span>
                <span
                  class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium"
                  >{{ auction.winner.paymentStatus }}</span
                >
              </div>
            </div>
          </div>

          <!-- FITUR BONUS -->
          <div
            class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4"
          >
            <p
              class="text-xs text-gray-400 uppercase tracking-widest font-semibold"
            >
              Fitur Bonus
            </p>
            <!-- Anti-sniping -->
            <div class="border border-gray-100 rounded-xl p-4">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold text-black">Anti-Sniping</p>
                <span
                  :class="[
                    'text-xs px-2.5 py-1 rounded-full font-medium',
                    auction.antiSniping.active
                      ? 'bg-black text-white'
                      : 'bg-gray-100 text-gray-500',
                  ]"
                >
                  {{ auction.antiSniping.active ? "Aktif" : "Nonaktif" }}
                </span>
              </div>
              <div class="space-y-1.5 text-xs">
                <div class="flex justify-between">
                  <span class="text-gray-400">Perpanjangan Terakhir</span>
                  <span class="font-medium text-gray-700">{{
                    auction.antiSniping.lastExtension
                  }}</span>
                </div>
              </div>
              <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                {{ auction.antiSniping.note }}
              </p>
            </div>
            <!-- Buy Now -->
            <div class="border border-gray-100 rounded-xl p-4">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold text-black">Buy Now</p>
                <span
                  :class="[
                    'text-xs px-2.5 py-1 rounded-full font-medium',
                    auction.buyNow.used
                      ? 'bg-black text-white'
                      : 'bg-gray-100 text-gray-500',
                  ]"
                >
                  {{ auction.buyNow.used ? "Digunakan" : "Belum Digunakan" }}
                </span>
              </div>
              <div class="flex justify-between text-xs">
                <span class="text-gray-400">Harga Buy Now</span>
                <span class="font-bold text-black">{{
                  formatRp(auction.buyNow.price)
                }}</span>
              </div>
            </div>
          </div>

          <!-- ADMIN ACTIONS -->
          <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <p
              class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-4"
            >
              Tindakan Administrator
            </p>
            <div class="space-y-2">
              <button
                class="w-full py-2.5 bg-black text-white rounded-xl text-xs font-medium hover:bg-gray-800 transition-colors flex items-center justify-center gap-1.5"
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
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  />
                </svg>
                Edit Informasi Lelang
              </button>
              <button
                v-if="auction.status === 'active'"
                @click="openModal('deactivate')"
                class="w-full py-2.5 border border-gray-200 text-gray-600 rounded-xl text-xs font-medium hover:border-gray-500 hover:text-gray-800 transition-all duration-300 flex items-center justify-center gap-1.5"
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
                Nonaktifkan Lelang
              </button>
              <button
                v-if="auction.status === 'cancelled'"
                @click="auction.status = 'active'"
                class="w-full py-2.5 border border-gray-200 text-gray-600 rounded-xl text-xs font-medium hover:border-black hover:text-black transition-all duration-300 flex items-center justify-center gap-1.5"
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
                Aktifkan Kembali Lelang
              </button>
              <button
                @click="openModal('cancel')"
                class="w-full py-2.5 border border-gray-200 text-gray-500 rounded-xl text-xs font-medium hover:border-gray-400 hover:text-gray-700 transition-all duration-300 flex items-center justify-center gap-1.5"
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
                Batalkan Lelang
              </button>
              <button
                @click="openModal('delete')"
                class="w-full py-2.5 border border-gray-200 text-gray-400 rounded-xl text-xs font-medium hover:border-gray-400 hover:text-gray-600 transition-all duration-300 flex items-center justify-center gap-1.5"
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
                Hapus Lelang
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ CONFIRM MODAL ══ -->
    <transition name="fade-modal">
      <div
        v-if="modal"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="closeModal"
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
                :d="modalConfig.icon"
              />
            </svg>
          </div>
          <h3 class="font-bold text-lg text-black mb-2">
            {{ modalConfig.title }}
          </h3>
          <p class="text-gray-500 text-sm leading-relaxed mb-6">
            {{ modalConfig.body }}
          </p>
          <div class="flex gap-3">
            <button
              @click="closeModal"
              class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleModalConfirm"
              class="flex-1 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
            >
              {{ modalConfig.confirm }}
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
