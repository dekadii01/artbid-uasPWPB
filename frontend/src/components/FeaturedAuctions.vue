<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { getAuctions } from "../api/auctions";

const router = useRouter();

const auctions = ref([]);
const isLoading = ref(true);
const isError = ref(false);
const now = ref(new Date());
let timer = null;

function formatRupiah(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(value);
}

function getTimerText(auction, nowVal) {
  if (auction.status === "ended") {
    return "Selesai";
  }
  if (auction.status === "upcoming") {
    if (!auction.startsAt) return "Segera";
    const startDate = new Date(auction.startsAt);
    const hours = String(startDate.getHours()).padStart(2, "0");
    const minutes = String(startDate.getMinutes()).padStart(2, "0");
    return `Mulai ${hours}:${minutes}`;
  }
  if (auction.status === "live") {
    if (!auction.endsAt) return "--:--:--";
    const diff = Math.max(0, new Date(auction.endsAt) - nowVal);
    if (diff === 0) {
      return "Selesai";
    }
    const hours = Math.floor(diff / 3600000);
    const minutes = Math.floor((diff % 3600000) / 60000);
    const seconds = Math.floor((diff % 60000) / 1000);
    return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
  }
  return "";
}

async function fetchFeaturedAuctions() {
  isLoading.value = true;
  isError.value = false;
  try {
    const { data } = await getAuctions({ per_page: 6, status: "all" });
    auctions.value = data.data ?? [];
  } catch (err) {
    console.error("Gagal mengambil data lelang unggulan:", err);
    isError.value = true;
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  fetchFeaturedAuctions();
  timer = setInterval(() => {
    now.value = new Date();
  }, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>

<template>
  <section id="auctions" class="px-4 md:px-10 py-20">
    <div class="flex items-end justify-between mb-12">
      <div>
        <span class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400">Sedang Berlangsung</span>
        <h2 class="text-4xl font-bold mt-3">Lelang Unggulan</h2>
      </div>
      <router-link
        to="/auctions"
        class="text-sm font-medium text-gray-600 hover:text-black flex items-center gap-1 transition-colors"
      >
        Lihat Semua
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
        </svg>
      </router-link>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-black mb-3"></div>
      <p class="text-sm text-gray-500">Memuat lelang unggulan...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="isError" class="text-center py-20">
      <p class="text-red-500 mb-4">Gagal memuat lelang unggulan.</p>
      <button @click="fetchFeaturedAuctions" class="px-4 py-2 bg-black text-white rounded-lg text-sm">
        Coba Lagi
      </button>
    </div>

    <!-- Empty State -->
    <div v-else-if="auctions.length === 0" class="text-center py-20 text-gray-500">
      Tidak ada lelang unggulan saat ini.
    </div>

    <!-- Auctions Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="auction in auctions"
        :key="auction.id"
        class="rounded-2xl overflow-hidden bg-white shadow-sm card-lift border border-gray-100 flex flex-col cursor-pointer"
        @click="router.push(`/auction/${auction.id}`)"
      >
        <!-- Image Area -->
        <div class="relative aspect-4/3 overflow-hidden bg-gray-100">
          <div
            v-if="auction.image"
            class="h-full w-full bg-cover bg-center transition-transform duration-300 hover:scale-105"
            :style="{ backgroundImage: `url('${auction.image}')` }"
          ></div>
          <div v-else class="h-full w-full flex items-center justify-center">
            <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>

          <!-- Status Badge -->
          <span
            v-if="auction.status === 'live'"
            class="absolute top-4 left-4 bg-black text-white text-[10px] uppercase font-bold tracking-wider px-3 py-1.5 rounded-full flex items-center gap-1.5"
          >
            <span class="live-dot w-1.5 h-1.5 rounded-full bg-white inline-block animate-pulse"></span>LIVE
          </span>
          <span
            v-else-if="auction.status === 'upcoming'"
            class="absolute top-4 left-4 bg-white text-black text-[10px] uppercase font-bold tracking-wider px-3 py-1.5 rounded-full border border-gray-200 shadow-xs"
          >
            AKAN DATANG
          </span>
          <span
            v-else
            class="absolute top-4 left-4 bg-gray-100 text-gray-500 text-[10px] uppercase font-bold tracking-wider px-3 py-1.5 rounded-full"
          >
            SELESAI
          </span>

          <!-- Timer Badge -->
          <div
            v-if="auction.status !== 'ended'"
            class="absolute bottom-4 right-4 bg-black/70 backdrop-blur-sm text-white text-xs px-3 py-1.5 rounded-lg font-medium"
          >
            {{ getTimerText(auction, now) }}
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-5 flex flex-col flex-1">
          <p class="text-xs text-gray-400 mb-1 truncate">{{ auction.category || 'Lelang Seni' }}</p>
          <h3 class="font-semibold text-base line-clamp-1">{{ auction.name }}</h3>
          <p class="text-gray-500 text-sm mt-0.5 truncate">{{ auction.artist || 'Seniman Bali' }}</p>

          <!-- Price & Count Info -->
          <div class="flex items-end justify-between mt-4 pt-4 border-t border-gray-100">
            <div>
              <p class="text-xs text-gray-400 mb-0.5">
                {{ auction.status === 'upcoming' ? 'Harga Pembuka' : 'Tawaran Tertinggi' }}
              </p>
              <p class="font-bold text-lg text-black">
                {{ formatRupiah(auction.currentPrice || auction.startPrice) }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-xs text-gray-400 mb-0.5">
                {{ auction.status === 'upcoming' ? 'Diminati' : 'Penawaran' }}
              </p>
              <p class="font-semibold text-sm text-black">
                {{ auction.status === 'upcoming' ? `${auction.watching || 0} orang` : `${auction.bidCount || 0} bid` }}
              </p>
            </div>
          </div>

          <!-- Button -->
          <button
            :class="[
              'mt-4 w-full py-2.5 rounded-lg text-sm font-medium transition-colors',
              auction.status === 'live'
                ? 'bg-black text-white hover:bg-gray-800'
                : 'border border-black text-black hover:bg-black hover:text-white'
            ]"
          >
            {{ auction.status === 'live' ? 'Ikuti Lelang' : auction.status === 'upcoming' ? 'Lihat Detail' : 'Lelang Selesai' }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
