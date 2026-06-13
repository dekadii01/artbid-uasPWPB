<template>
  <div class="bg-white min-h-screen font-sans overflow-x-hidden mt-20">
    <Navbar />
    <!-- ─── HEADER ─────────────────────────────────────────────────── -->
    <div class="px-6 md:px-10 pt-10 pb-8">
      <span
        class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
      >
        Jelajahi Koleksi
      </span>
      <h1 class="text-3xl md:text-4xl font-bold mt-2 leading-tight">
        Temukan dan Ikuti<br class="hidden md:block" />
        Lelang Barang Seni Terbaik
      </h1>
      <p class="text-gray-500 mt-3 text-sm max-w-lg leading-relaxed">
        Jelajahi berbagai koleksi seni unik dari para seniman Bali dan ajukan
        penawaran secara realtime.
      </p>
    </div>

    <!-- ─── LAYOUT WRAPPER (sidebar + main) ────────────────────────── -->
    <div class="px-6 md:px-10 pb-20 flex gap-8 items-start">
      <!-- ── SIDEBAR STATISTIK (desktop only) ─────────────────────── -->
      <aside class="hidden xl:flex flex-col gap-4 w-56 shrink-0 sticky top-24">
        <p
          class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400 mb-1"
        >
          Statistik Platform
        </p>
        <div
          v-for="stat in platformStats"
          :key="stat.label"
          class="rounded-xl border border-gray-100 px-5 py-4 bg-white hover:shadow-sm transition-shadow"
        >
          <p class="text-2xl font-bold">{{ stat.value }}</p>
          <p class="text-gray-400 text-xs mt-0.5">{{ stat.label }}</p>
        </div>

        <!-- Pengguna Online — dark card -->
        <div class="rounded-xl bg-black px-5 py-4">
          <div class="flex items-center gap-2 mb-1">
            <span
              class="live-dot w-2 h-2 rounded-full bg-white inline-block"
            ></span>
            <p class="text-white text-xl font-bold">
              {{ platformStats.find((s) => s.key === "online")?.value }}
            </p>
          </div>
          <p class="text-white/50 text-xs">Pengguna Online</p>
        </div>
      </aside>

      <!-- ── MAIN CONTENT ──────────────────────────────────────────── -->
      <div class="flex-1 min-w-0">
        <!-- Search + Filter bar -->
        <div class="flex flex-col md:flex-row gap-3 mb-6">
          <!-- Search -->
          <div class="relative flex-1">
            <svg
              class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
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
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari nama barang, kategori, atau penjual…"
              class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-black transition-colors bg-white"
            />
          </div>

          <!-- Category -->
          <select
            v-model="selectedCategory"
            class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-black transition-colors bg-white text-gray-700 cursor-pointer"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in categories" :key="cat" :value="cat">
              {{ cat }}
            </option>
          </select>

          <!-- Sort -->
          <select
            v-model="sortBy"
            class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-black transition-colors bg-white text-gray-700 cursor-pointer"
          >
            <option value="latest">Terbaru</option>
            <option value="price_high">Harga Tertinggi</option>
            <option value="price_low">Harga Terendah</option>
            <option value="ending_soon">Waktu Berakhir Terdekat</option>
            <option value="most_bids">Paling Banyak Ditawar</option>
          </select>
        </div>

        <!-- Status Tabs -->
        <div class="flex gap-2 mb-8 flex-wrap">
          <button
            v-for="tab in statusTabs"
            :key="tab.value"
            @click="selectedStatus = tab.value"
            :class="[
              'px-5 py-2 rounded-lg text-sm font-medium transition-all duration-200',
              selectedStatus === tab.value
                ? 'bg-black text-white'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
            ]"
          >
            {{ tab.label }}
            <span
              v-if="tab.count !== undefined"
              :class="[
                'ml-1.5 text-xs px-1.5 py-0.5 rounded-full',
                selectedStatus === tab.value
                  ? 'bg-white/20 text-white'
                  : 'bg-gray-200 text-gray-500',
              ]"
              >{{ tab.count }}</span
            >
          </button>
        </div>

        <!-- Result count -->
        <p class="text-sm text-gray-400 mb-5">
          Menampilkan
          <span class="text-black font-semibold">{{
            filteredAuctions.length
          }}</span>
          lelang
        </p>

        <!-- ── AUCTION GRID ──────────────────────────────────────── -->
        <div
          v-if="filteredAuctions.length > 0"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
        >
          <div
            v-for="auction in paginatedAuctions"
            :key="auction.id"
            class="rounded-2xl overflow-hidden bg-white border border-gray-100 card-lift shadow-sm flex flex-col"
          >
            <!-- Image area -->
            <div class="relative aspect-4/3 overflow-hidden bg-gray-100">
              <img
                :src="auction.image"
                :alt="auction.name"
                class="w-full h-full object-cover"
              />

              <!-- Multi-photo badge -->
              <div
                v-if="auction.photoCount > 1"
                class="absolute top-3 right-3 bg-black/60 backdrop-blur-sm text-white text-xs px-2.5 py-1 rounded-lg flex items-center gap-1.5"
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
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                  />
                </svg>
                {{ auction.photoCount }} Foto
              </div>

              <!-- Status badge -->
              <div class="absolute top-3 left-3">
                <span
                  v-if="auction.status === 'live'"
                  class="bg-black text-white text-xs px-3 py-1 rounded-full flex items-center gap-1.5"
                >
                  <span
                    class="live-dot w-1.5 h-1.5 rounded-full bg-white inline-block"
                  ></span>
                  LIVE
                </span>
                <span
                  v-else-if="auction.status === 'upcoming'"
                  class="bg-white text-black text-xs px-3 py-1 rounded-full border border-gray-200 font-medium"
                >
                  AKAN DATANG
                </span>
                <span
                  v-else
                  class="bg-gray-100 text-gray-500 text-xs px-3 py-1 rounded-full font-medium"
                >
                  SELESAI
                </span>
              </div>

              <!-- Viewers (presence channel) -->
              <div
                v-if="auction.status === 'live' && auction.viewers"
                class="absolute bottom-3 left-3 bg-black/60 backdrop-blur-sm text-white text-xs px-2.5 py-1 rounded-lg flex items-center gap-1.5"
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
                {{ auction.viewers }} sedang melihat
              </div>
            </div>

            <!-- Card body -->
            <div class="p-5 flex flex-col flex-1">
              <p class="text-xs text-gray-400 mb-1">{{ auction.category }}</p>
              <h3 class="font-semibold text-sm leading-snug mb-0.5">
                {{ auction.name }}
              </h3>
              <p class="text-gray-400 text-xs mb-4">
                Dijual oleh: {{ auction.seller }}
              </p>

              <!-- Price -->
              <div class="flex items-end justify-between mb-3">
                <div>
                  <p class="text-xs text-gray-400 mb-0.5">
                    {{
                      auction.status === "upcoming"
                        ? "Harga Awal"
                        : "Harga Tertinggi"
                    }}
                  </p>
                  <p class="font-bold text-base">
                    {{ formatRupiah(auction.currentPrice) }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-xs text-gray-400 mb-0.5">Penawaran</p>
                  <p class="font-semibold text-sm">{{ auction.bidCount }}</p>
                </div>
              </div>

              <!-- Countdown / status block -->
              <div
                :class="[
                  'rounded-xl px-4 py-3 mb-4 flex items-center gap-3',
                  auction.status === 'live'
                    ? 'bg-gray-50 border border-gray-100'
                    : auction.status === 'upcoming'
                      ? 'bg-gray-50 border border-gray-100'
                      : 'bg-gray-50 border border-gray-100',
                ]"
              >
                <!-- Live countdown -->
                <template v-if="auction.status === 'live'">
                  <svg
                    class="w-4 h-4 text-gray-500 shrink-0"
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
                  <div class="flex-1">
                    <p class="text-xs text-gray-400 mb-1">Berakhir dalam</p>
                    <div class="flex gap-3">
                      <div
                        class="text-center"
                        v-if="parseCountdown(auction.endsAt).days > 0"
                      >
                        <p class="font-bold text-sm leading-none">
                          {{
                            String(
                              parseCountdown(auction.endsAt).days,
                            ).padStart(2, "0")
                          }}
                        </p>
                        <p class="text-gray-400 text-[10px] mt-0.5">Hari</p>
                      </div>
                      <div class="text-center">
                        <p class="font-bold text-sm leading-none">
                          {{
                            String(
                              parseCountdown(auction.endsAt).hours,
                            ).padStart(2, "0")
                          }}
                        </p>
                        <p class="text-gray-400 text-[10px] mt-0.5">Jam</p>
                      </div>
                      <div class="text-center">
                        <p class="font-bold text-sm leading-none">
                          {{
                            String(
                              parseCountdown(auction.endsAt).minutes,
                            ).padStart(2, "0")
                          }}
                        </p>
                        <p class="text-gray-400 text-[10px] mt-0.5">Menit</p>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Upcoming countdown -->
                <template v-else-if="auction.status === 'upcoming'">
                  <svg
                    class="w-4 h-4 text-gray-500 shrink-0"
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
                  <div class="flex-1">
                    <p class="text-xs text-gray-400 mb-1">Dimulai dalam</p>
                    <div class="flex gap-3">
                      <div
                        class="text-center"
                        v-if="parseCountdown(auction.startsAt).days > 0"
                      >
                        <p class="font-bold text-sm leading-none">
                          {{
                            String(
                              parseCountdown(auction.startsAt).days,
                            ).padStart(2, "0")
                          }}
                        </p>
                        <p class="text-gray-400 text-[10px] mt-0.5">Hari</p>
                      </div>
                      <div class="text-center">
                        <p class="font-bold text-sm leading-none">
                          {{
                            String(
                              parseCountdown(auction.startsAt).hours,
                            ).padStart(2, "0")
                          }}
                        </p>
                        <p class="text-gray-400 text-[10px] mt-0.5">Jam</p>
                      </div>
                      <div class="text-center">
                        <p class="font-bold text-sm leading-none">
                          {{
                            String(
                              parseCountdown(auction.startsAt).minutes,
                            ).padStart(2, "0")
                          }}
                        </p>
                        <p class="text-gray-400 text-[10px] mt-0.5">Menit</p>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Ended -->
                <template v-else>
                  <svg
                    class="w-4 h-4 text-gray-400 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
                    />
                  </svg>
                  <div>
                    <p class="text-xs text-gray-400">Lelang Berakhir</p>
                    <p class="text-sm font-semibold text-gray-700 mt-0.5">
                      {{ formatRupiah(auction.currentPrice) }}
                    </p>
                  </div>
                </template>
              </div>

              <!-- Action button — pushed to bottom -->
              <div class="mt-auto">
                <button
                  v-if="auction.status === 'live'"
                  class="w-full py-2.5 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
                >
                  Tawar Sekarang
                </button>
                <button
                  v-else-if="auction.status === 'upcoming'"
                  class="w-full py-2.5 border border-black text-black rounded-lg text-sm font-medium hover:bg-black hover:text-white transition-colors"
                >
                  Lihat Detail
                </button>
                <button
                  v-else
                  class="w-full py-2.5 border border-gray-200 text-gray-500 rounded-lg text-sm font-medium hover:border-black hover:text-black transition-colors"
                >
                  Lihat Hasil
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- ── EMPTY STATE ──────────────────────────────────────── -->
        <div
          v-else
          class="flex flex-col items-center justify-center py-24 text-center"
        >
          <div
            class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-5"
          >
            <svg
              class="w-8 h-8 text-gray-300"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <h3 class="font-semibold text-base mb-2">
            Tidak ada lelang ditemukan
          </h3>
          <p class="text-gray-400 text-sm max-w-xs leading-relaxed">
            Belum ada lelang yang sesuai dengan pencarian Anda. Coba gunakan
            kata kunci lain atau ubah filter pencarian.
          </p>
          <button
            @click="resetFilters"
            class="mt-6 px-6 py-2.5 border border-gray-200 rounded-lg text-sm font-medium hover:border-black hover:text-black transition-colors"
          >
            Reset Filter
          </button>
        </div>

        <!-- ── PAGINATION ──────────────────────────────────────── -->
        <div
          v-if="totalPages > 1"
          class="flex items-center justify-center gap-2 mt-10"
        >
          <button
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:border-black hover:text-black transition-colors disabled:opacity-30 disabled:pointer-events-none"
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
                d="M15 19l-7-7 7-7"
              />
            </svg>
          </button>

          <button
            v-for="p in totalPages"
            :key="p"
            @click="currentPage = p"
            :class="[
              'w-9 h-9 rounded-lg text-sm font-medium transition-all duration-200',
              currentPage === p
                ? 'bg-black text-white'
                : 'border border-gray-200 text-gray-600 hover:border-black hover:text-black',
            ]"
          >
            {{ p }}
          </button>

          <button
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:border-black hover:text-black transition-colors disabled:opacity-30 disabled:pointer-events-none"
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
                d="M9 5l7 7-7 7"
              />
            </svg>
          </button>
        </div>

        <!-- ── CTA BUAT LELANG ──────────────────────────────────── -->
        <div
          class="bg-black rounded-3xl mt-16 px-8 py-12 flex flex-col md:flex-row md:items-center justify-between gap-6 overflow-hidden relative"
        >
          <div
            class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"
          ></div>
          <div class="relative">
            <span
              class="text-xs font-semibold tracking-[0.2em] uppercase text-white/40"
              >Untuk Seniman & Kolektor</span
            >
            <h2 class="text-2xl font-bold text-white mt-2 leading-tight">
              Punya Koleksi Seni<br class="hidden sm:block" />
              yang Ingin Dijual?
            </h2>
            <p class="text-white/50 text-sm mt-3 leading-relaxed max-w-sm">
              Buat lelang Anda sendiri dan temukan penawar terbaik secara
              realtime. Gratis listing, komisi hanya saat terjual.
            </p>
          </div>
          <button
            class="relative shrink-0 px-8 py-3.5 bg-white text-black rounded-xl text-sm font-medium hover:bg-gray-100 transition-colors whitespace-nowrap"
          >
            Buat Lelang
          </button>
        </div>
      </div>
      <!-- end main -->
    </div>
    <Footer />
    <!-- end layout wrapper -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import Footer from "../../components/TheFooter.vue";
import Navbar from "../../components/Appnavbar.vue";

// ─── Helpers ─────────────────────────────────────────────────────
function formatRupiah(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(value);
}

function parseCountdown(targetIso) {
  const diff = Math.max(0, new Date(targetIso) - now.value);
  const days = Math.floor(diff / 86400000);
  const hours = Math.floor((diff % 86400000) / 3600000);
  const minutes = Math.floor((diff % 3600000) / 60000);
  const seconds = Math.floor((diff % 60000) / 1000);
  return { days, hours, minutes, seconds };
}

// ─── Reactive clock ──────────────────────────────────────────────
const now = ref(new Date());
let ticker;
onMounted(() => {
  ticker = setInterval(() => {
    now.value = new Date();
  }, 1000);
});
onUnmounted(() => clearInterval(ticker));

// ─── Filters ─────────────────────────────────────────────────────
const searchQuery = ref("");
const selectedCategory = ref("");
const selectedStatus = ref("all");
const sortBy = ref("latest");
const currentPage = ref(1);
const perPage = 9;

const categories = [
  "Lukisan",
  "Patung",
  "Topeng Tradisional",
  "Ukiran Kayu",
  "Kerajinan Perak",
  "Barang Antik",
  "Koleksi Langka",
];

const statusTabs = computed(() => [
  { label: "Semua", value: "all", count: auctions.length },
  {
    label: "Sedang Berlangsung",
    value: "live",
    count: auctions.filter((a) => a.status === "live").length,
  },
  {
    label: "Akan Datang",
    value: "upcoming",
    count: auctions.filter((a) => a.status === "upcoming").length,
  },
  {
    label: "Selesai",
    value: "ended",
    count: auctions.filter((a) => a.status === "ended").length,
  },
]);

function resetFilters() {
  searchQuery.value = "";
  selectedCategory.value = "";
  selectedStatus.value = "all";
  sortBy.value = "latest";
  currentPage.value = 1;
}

// ─── Platform stats sidebar ───────────────────────────────────────
const platformStats = [
  { key: "active", label: "Lelang Aktif", value: "128" },
  { key: "bids", label: "Total Penawaran Hari Ini", value: "1.245" },
  { key: "online", label: "Pengguna Online", value: "86" },
  { key: "sold", label: "Barang Terjual", value: "532" },
].filter((s) => s.key !== "online");

// ─── Dummy Data ───────────────────────────────────────────────────
// endsAt / startsAt — relative to page load time so countdown is always meaningful in demo
function hoursFromNow(h) {
  return new Date(Date.now() + h * 3600000).toISOString();
}
function daysFromNow(d) {
  return new Date(Date.now() + d * 86400000).toISOString();
}

const auctions = [
  {
    id: 1,
    status: "live",
    name: "Karma Tanah Lot",
    category: "Lukisan",
    seller: "I Wayan Sukerta",
    currentPrice: 48500000,
    bidCount: 24,
    viewers: 18,
    photoCount: 4,
    image:
      "https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=600&q=80",
    endsAt: hoursFromNow(2.8),
  },
  {
    id: 2,
    status: "live",
    name: "Penjaga Bali",
    category: "Patung",
    seller: "I Made Wijaya",
    currentPrice: 12500000,
    bidCount: 18,
    viewers: 9,
    photoCount: 2,
    image:
      "https://images.unsplash.com/photo-1578301978069-3c23ee44e8dc?w=600&q=80",
    endsAt: hoursFromNow(2),
  },
  {
    id: 3,
    status: "live",
    name: "Dewi Kesuburan",
    category: "Patung",
    seller: "Ni Luh Eka Sari",
    currentPrice: 28000000,
    bidCount: 31,
    viewers: 22,
    photoCount: 3,
    image:
      "https://images.unsplash.com/photo-1569091791842-7cfb64e04797?w=600&q=80",
    endsAt: hoursFromNow(5.4),
  },
  {
    id: 4,
    status: "live",
    name: "Tanah & Api",
    category: "Barang Antik",
    seller: "Ketut Suardana",
    currentPrice: 8750000,
    bidCount: 9,
    viewers: 4,
    photoCount: 1,
    image:
      "https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=600&q=80",
    endsAt: hoursFromNow(0.75),
  },
  {
    id: 5,
    status: "live",
    name: "Alam Tak Terbatas",
    category: "Lukisan",
    seller: "Sang Ayu Putu Riani",
    currentPrice: 19200000,
    bidCount: 22,
    viewers: 15,
    photoCount: 2,
    image:
      "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=600&q=80",
    endsAt: hoursFromNow(1.2),
  },
  {
    id: 6,
    status: "live",
    name: "Harmoni Semesta",
    category: "Lukisan",
    seller: "I Made Wijaya",
    currentPrice: 15000000,
    bidCount: 14,
    viewers: 7,
    photoCount: 3,
    image:
      "https://images.unsplash.com/photo-1501472312651-726afe119ff1?w=600&q=80",
    endsAt: hoursFromNow(3.5),
  },
  {
    id: 7,
    status: "upcoming",
    name: "Sunrise Penida",
    category: "Koleksi Langka",
    seller: "Agung Rai Photography",
    currentPrice: 5000000,
    bidCount: 0,
    viewers: 0,
    photoCount: 5,
    image:
      "https://images.unsplash.com/photo-1518544866330-4e716499f800?w=600&q=80",
    startsAt: hoursFromNow(6),
  },
  {
    id: 8,
    status: "upcoming",
    name: "Jejak Digital",
    category: "Koleksi Langka",
    seller: "Dewa Gede Artana",
    currentPrice: 35000000,
    bidCount: 0,
    viewers: 0,
    photoCount: 2,
    image:
      "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80",
    startsAt: daysFromNow(1),
  },
  {
    id: 9,
    status: "upcoming",
    name: "Topeng Rangda Antik",
    category: "Topeng Tradisional",
    seller: "I Nyoman Kariasa",
    currentPrice: 22000000,
    bidCount: 0,
    viewers: 0,
    photoCount: 3,
    image:
      "https://images.unsplash.com/photo-1576487236230-eaa4afe68192?w=600&q=80",
    startsAt: daysFromNow(2),
  },
  {
    id: 10,
    status: "upcoming",
    name: "Ukiran Barong Gianyar",
    category: "Ukiran Kayu",
    seller: "Wayan Raka Sandi",
    currentPrice: 17500000,
    bidCount: 0,
    viewers: 0,
    photoCount: 4,
    image:
      "https://images.unsplash.com/photo-1597696929736-6d13bed8e6a8?w=600&q=80",
    startsAt: daysFromNow(3),
  },
  {
    id: 11,
    status: "ended",
    name: "Bali Sunrise 1985",
    category: "Lukisan",
    seller: "I Gusti Nyoman Lempad Jr.",
    currentPrice: 87500000,
    bidCount: 47,
    viewers: 0,
    photoCount: 2,
    image:
      "https://images.unsplash.com/photo-1565367777-8879b08cd5b6?w=600&q=80",
  },
  {
    id: 12,
    status: "ended",
    name: "Keris Pusaka Abad XVIII",
    category: "Barang Antik",
    seller: "Puri Agung Karangasem",
    currentPrice: 125000000,
    bidCount: 61,
    viewers: 0,
    photoCount: 6,
    image:
      "https://images.unsplash.com/photo-1531804055935-76f44d7c3621?w=600&q=80",
  },
  {
    id: 13,
    status: "ended",
    name: "Gelang Perak Celuk",
    category: "Kerajinan Perak",
    seller: "Perak Bali Craft",
    currentPrice: 4200000,
    bidCount: 11,
    viewers: 0,
    photoCount: 3,
    image:
      "https://images.unsplash.com/photo-1611085583191-a3b181a88401?w=600&q=80",
  },
  {
    id: 14,
    status: "ended",
    name: "Lontar Kuno Koleksi",
    category: "Koleksi Langka",
    seller: "Gedong Kirtya Archive",
    currentPrice: 56000000,
    bidCount: 33,
    viewers: 0,
    photoCount: 4,
    image:
      "https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=600&q=80",
  },
];

// ─── Computed ─────────────────────────────────────────────────────
const filteredAuctions = computed(() => {
  let result = [...auctions];

  // Status filter
  if (selectedStatus.value !== "all") {
    result = result.filter((a) => a.status === selectedStatus.value);
  }

  // Category filter
  if (selectedCategory.value) {
    result = result.filter((a) => a.category === selectedCategory.value);
  }

  // Search
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(
      (a) =>
        a.name.toLowerCase().includes(q) ||
        a.category.toLowerCase().includes(q) ||
        a.seller.toLowerCase().includes(q),
    );
  }

  // Sort
  switch (sortBy.value) {
    case "price_high":
      result.sort((a, b) => b.currentPrice - a.currentPrice);
      break;
    case "price_low":
      result.sort((a, b) => a.currentPrice - b.currentPrice);
      break;
    case "most_bids":
      result.sort((a, b) => b.bidCount - a.bidCount);
      break;
    case "ending_soon":
      result.sort((a, b) => {
        const aTime = a.endsAt
          ? new Date(a.endsAt)
          : new Date(a.startsAt || "9999");
        const bTime = b.endsAt
          ? new Date(b.endsAt)
          : new Date(b.startsAt || "9999");
        return aTime - bTime;
      });
      break;
    default:
      break; // latest = original order
  }

  return result;
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredAuctions.value.length / perPage)),
);

const paginatedAuctions = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredAuctions.value.slice(start, start + perPage);
});

// Reset to page 1 whenever filters change
import { watch } from "vue";
watch([searchQuery, selectedCategory, selectedStatus, sortBy], () => {
  currentPage.value = 1;
});
</script>

<style scoped>
@keyframes pulse-dot {
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
  animation: pulse-dot 1.4s ease-in-out infinite;
}

.card-lift {
  transition:
    transform 0.3s ease,
    box-shadow 0.3s ease;
}
.card-lift:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* font-family harus sesuai dengan project Vue kamu (import DM Sans di main.css / index.html) */
.font-sans {
  font-family: "DM Sans", sans-serif;
}
</style>
