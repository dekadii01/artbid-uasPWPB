<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { useRouter } from "vue-router";
import Navbar from "../../components/Appnavbar.vue";
import { getAuctions } from "../../api/auctions";
import { useAuthStore } from "../../stores/auth";

const router = useRouter();
const authStore = useAuthStore();

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
    
    // Transisi status lelang dari upcoming -> live saat waktu mulai tercapai
    if (auctions.value && auctions.value.length > 0) {
      auctions.value.forEach((auc) => {
        if (auc.status === "upcoming" && auc.startsAt) {
          const startTime = new Date(auc.startsAt);
          if (now.value >= startTime) {
            auc.status = "live";
          }
        }
      });
    }
  }, 1000);
  fetchAuctions();
});
onUnmounted(() => clearInterval(ticker));

// ─── Filters ─────────────────────────────────────────────────────
const searchQuery = ref("");
const selectedCategory = ref("");
const selectedStatus = ref("all");
const sortBy = ref("latest");
const currentPage = ref(1);

const categories = [
  "Lukisan",
  "Patung",
  "Topeng Tradisional",
  "Ukiran Kayu",
  "Kerajinan Perak",
  "Barang Antik",
  "Koleksi Langka",
];

// ─── API state ───────────────────────────────────────────────────
const auctions = ref([]);
const isLoading = ref(false);
const isError = ref(false);
const meta = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 12,
});

// Status counts — dihitung dari total per-status lewat API terpisah
// Untuk kesederhanaan, kita fetch semua lalu hitung di sini
const statusCounts = ref({ all: 0, live: 0, upcoming: 0, ended: 0 });
const todayBidsCount = ref(0);

async function fetchAuctions() {
  isLoading.value = true;
  isError.value = false;

  try {
    const params = {
      page: currentPage.value,
      per_page: 12,
    };

    // Status filter — kirim ke backend
    if (selectedStatus.value !== "all") {
      params.status = selectedStatus.value;
    }

    // Category filter
    if (selectedCategory.value) {
      params.category = selectedCategory.value;
    }

    // Search
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim();
    }

    // Sort
    if (sortBy.value !== "latest") {
      params.sort = sortBy.value;
    }

    const { data } = await getAuctions(params);

    auctions.value = (data.data ?? []).map((auc) => {
      if (auc.status === "upcoming" && auc.startsAt) {
        if (new Date(auc.startsAt) <= new Date()) {
          auc.status = "live";
        }
      }
      return auc;
    });
    meta.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      total: data.total,
      per_page: data.per_page,
    };
    todayBidsCount.value = data.today_bids_count ?? 0;

    // Hitung status counts saat filter = all (untuk badge di tab)
    if (
      selectedStatus.value === "all" &&
      !selectedCategory.value &&
      !searchQuery.value
    ) {
      statusCounts.value.all = data.total;
      // Fetch counts per status di background
      fetchStatusCounts();
    }
  } catch (err) {
    console.error("Gagal fetch auctions:", err);
    isError.value = true;
  } finally {
    isLoading.value = false;
  }
}

async function fetchStatusCounts() {
  try {
    const [liveRes, upcomingRes, endedRes] = await Promise.all([
      getAuctions({ status: "live", per_page: 1 }),
      getAuctions({ status: "upcoming", per_page: 1 }),
      getAuctions({ status: "ended", per_page: 1 }),
    ]);
    statusCounts.value.live = liveRes.data.total;
    statusCounts.value.upcoming = upcomingRes.data.total;
    statusCounts.value.ended = endedRes.data.total;
    statusCounts.value.all =
      statusCounts.value.live +
      statusCounts.value.upcoming +
      statusCounts.value.ended;
  } catch {
    // silent fail — counts tidak kritis
  }
}

// Reset ke halaman 1 saat filter berubah, lalu fetch ulang
watch([selectedStatus, selectedCategory, sortBy], () => {
  currentPage.value = 1;
  fetchAuctions();
});

// Debounce search agar tidak spam request
let searchTimer;
watch(searchQuery, () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    currentPage.value = 1;
    fetchAuctions();
  }, 400);
});

watch(currentPage, () => {
  fetchAuctions();
  window.scrollTo({ top: 0, behavior: "smooth" });
});

function resetFilters() {
  searchQuery.value = "";
  selectedCategory.value = "";
  selectedStatus.value = "all";
  sortBy.value = "latest";
  currentPage.value = 1;
}

// ─── Status tabs ─────────────────────────────────────────────────
const statusTabs = computed(() => [
  { label: "Semua", value: "all", count: statusCounts.value.all },
  {
    label: "Sedang Berlangsung",
    value: "live",
    count: statusCounts.value.live,
  },
  {
    label: "Akan Datang",
    value: "upcoming",
    count: statusCounts.value.upcoming,
  },
  { label: "Selesai", value: "ended", count: statusCounts.value.ended },
]);

// ─── Platform stats sidebar (static — bisa diganti API nanti) ────
const platformStats = [
  {
    key: "active",
    label: "Lelang Aktif",
    value: computed(() => statusCounts.value.live.toString()),
  },
  { key: "bids", label: "Total Penawaran Hari Ini", value: todayBidsCount },
  {
    key: "sold",
    label: "Barang Terjual",
    value: computed(() => statusCounts.value.ended.toString()),
  },
];

const goToAuction = (id) => router.push(`/auction/${id}`);
</script>

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

    <!-- ─── LAYOUT WRAPPER ──────────────────────────────────────────── -->
    <div class="px-6 md:px-10 pb-20 flex gap-8 items-start">
      <!-- ── SIDEBAR STATISTIK ─────────────────────────────────────── -->
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
          <p class="text-2xl font-bold">
            {{ typeof stat.value === "object" ? stat.value.value : stat.value }}
          </p>
          <p class="text-gray-400 text-xs mt-0.5">{{ stat.label }}</p>
        </div>

        <!-- Pengguna Online — dark card -->
        <div class="rounded-xl bg-black px-5 py-4">
          <div class="flex items-center gap-2 mb-1">
            <span
              class="live-dot w-2 h-2 rounded-full bg-white inline-block"
            ></span>
            <p class="text-white text-xl font-bold">{{ statusCounts.live }}</p>
          </div>
          <p class="text-white/50 text-xs">Lelang Berlangsung</p>
        </div>
      </aside>

      <!-- ── MAIN CONTENT ──────────────────────────────────────────── -->
      <div class="flex-1 min-w-0">
        <!-- Search + Filter bar -->
        <div class="flex flex-col md:flex-row gap-3 mb-6">
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

          <select
            v-model="selectedCategory"
            class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-black transition-colors bg-white text-gray-700 cursor-pointer"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in categories" :key="cat" :value="cat">
              {{ cat }}
            </option>
          </select>

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
              :class="[
                'ml-1.5 text-xs px-1.5 py-0.5 rounded-full',
                selectedStatus === tab.value
                  ? 'bg-white/20 text-white'
                  : 'bg-gray-200 text-gray-500',
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </div>

        <!-- Result count -->
        <p class="text-sm text-gray-400 mb-5">
          Menampilkan
          <span class="text-black font-semibold">{{ meta.total }}</span> lelang
        </p>

        <!-- ── LOADING STATE ─────────────────────────────────────── -->
        <div
          v-if="isLoading"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
        >
          <div
            v-for="i in 6"
            :key="i"
            class="rounded-2xl border border-gray-100 overflow-hidden animate-pulse"
          >
            <div class="aspect-4/3 bg-gray-100"></div>
            <div class="p-5 space-y-3">
              <div class="h-3 bg-gray-100 rounded w-1/3"></div>
              <div class="h-4 bg-gray-100 rounded w-3/4"></div>
              <div class="h-3 bg-gray-100 rounded w-1/2"></div>
              <div class="h-10 bg-gray-100 rounded-xl mt-4"></div>
              <div class="h-10 bg-gray-100 rounded-lg mt-2"></div>
            </div>
          </div>
        </div>

        <!-- ── ERROR STATE ───────────────────────────────────────── -->
        <div
          v-else-if="isError"
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
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <h3 class="font-semibold text-base mb-2">Gagal memuat data</h3>
          <p class="text-gray-400 text-sm max-w-xs leading-relaxed">
            Terjadi kesalahan saat mengambil data lelang.
          </p>
          <button
            @click="fetchAuctions"
            class="mt-6 px-6 py-2.5 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
          >
            Coba Lagi
          </button>
        </div>

        <!-- ── AUCTION GRID ──────────────────────────────────────── -->
        <div
          v-else-if="auctions.length > 0"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
        >
          <div
            v-for="auction in auctions"
            :key="auction.id"
            class="rounded-2xl overflow-hidden bg-white border border-gray-100 card-lift shadow-sm flex flex-col"
          >
            <!-- Image area -->
            <div class="relative aspect-4/3 overflow-hidden bg-gray-100">
              <img
                v-if="auction.image"
                :src="auction.image"
                :alt="auction.name"
                class="w-full h-full object-cover"
              />
              <!-- Fallback jika tidak ada foto -->
              <div
                v-else
                class="w-full h-full flex items-center justify-center"
              >
                <svg
                  class="w-12 h-12 text-gray-200"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>

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
                class="rounded-xl px-4 py-3 mb-4 flex items-center gap-3 bg-gray-50 border border-gray-100"
              >
                <!-- Live countdown -->
                <template v-if="auction.status === 'live' && auction.endsAt">
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
                        v-if="parseCountdown(auction.endsAt).days > 0"
                        class="text-center"
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
                <template
                  v-else-if="auction.status === 'upcoming' && auction.startsAt"
                >
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
                        v-if="parseCountdown(auction.startsAt).days > 0"
                        class="text-center"
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

              <!-- Action button -->
              <div class="mt-auto">
                <button
                  v-if="auction.status === 'live'"
                  @click="goToAuction(auction.id)"
                  class="w-full py-2.5 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
                >
                  {{ authStore.user && authStore.user.id === auction.seller_id ? "Lelang Saya" : "Tawar Sekarang" }}
                </button>
                <button
                  v-else-if="auction.status === 'upcoming'"
                  @click="goToAuction(auction.id)"
                  class="w-full py-2.5 border border-black text-black rounded-lg text-sm font-medium hover:bg-black hover:text-white transition-colors"
                >
                  {{ authStore.user && authStore.user.id === auction.seller_id ? "Lelang Saya" : "Lihat Detail" }}
                </button>
                <button
                  v-else
                  @click="goToAuction(auction.id)"
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
          v-if="meta.last_page > 1"
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
            v-for="p in meta.last_page"
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
            :disabled="currentPage === meta.last_page"
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
              Punya Koleksi Seni<br class="hidden sm:block" />yang Ingin Dijual?
            </h2>
            <p class="text-white/50 text-sm mt-3 leading-relaxed max-w-sm">
              Buat lelang Anda sendiri dan temukan penawar terbaik secara
              realtime. Gratis listing, komisi hanya saat terjual.
            </p>
          </div>
          <button
            @click="router.push('/auction/create')"
            class="relative shrink-0 px-8 py-3.5 bg-white text-black rounded-xl text-sm font-medium hover:bg-gray-100 transition-colors whitespace-nowrap"
          >
            Buat Lelang
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

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

.font-sans {
  font-family: "DM Sans", sans-serif;
}
</style>
