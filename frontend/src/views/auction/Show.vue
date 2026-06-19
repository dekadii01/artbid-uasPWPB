<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Icon } from "@iconify/vue";
import { useAuthStore } from "../../stores/auth";
import { getEcho } from "../../api/echo";
import {
  getAuction,
  placeBid as placeBidApi,
  buyNow as buyNowApi,
  toggleWatchlist as toggleWatchlistApi
} from "../../api/auctions";

const route = useRoute();
const router = useRouter();

// ─── Helpers ──────────────────────────────────────────────────────
function formatRupiah(v) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(v);
}
function formatRupiahShort(v) {
  if (v >= 1000000) return v / 1000000 + "jt";
  if (v >= 1000) return v / 1000 + "rb";
  return v;
}

// ─── Reactive clock ───────────────────────────────────────────────
const now = ref(new Date());
let ticker;
onMounted(() => {
  ticker = setInterval(() => {
    now.value = new Date();
    
    // Set status to 'live' and refresh data once upcoming starts
    if (
      auction.value &&
      auction.value.status === "upcoming" &&
      auction.value.startsAt
    ) {
      const startTime = new Date(auction.value.startsAt);
      if (now.value >= startTime) {
        auction.value.status = "live";
        fetchAuction(true); // silent fetch to avoid glitch/flicker
      }
    }
  }, 1000);
});
onUnmounted(() => clearInterval(ticker));

// ─── Fetch auction data ──────────────────────────────────────────
const auction = ref(null);
const isLoading = ref(true);
const isError = ref(false);

async function fetchAuction(isSilent = false) {
  if (!isSilent) {
    isLoading.value = true;
  }
  isError.value = false;
  try {
    const { data } = await getAuction(route.params.id);
    auction.value = data.auction;

    // If start time has passed, force status to live locally to prevent loop
    if (auction.value.status === "upcoming" && auction.value.startsAt) {
      if (new Date(auction.value.startsAt) <= new Date()) {
        auction.value.status = "live";
      }
    }

    // Init bidAmount ke minimum bid
    const minB =
      (data.auction.currentPrice ?? data.auction.startPrice) +
      (data.auction.minIncrement ?? 0);
    bidAmount.value = minB;

    // Set watchlist status
    watchlisted.value = data.auction.isWatchlisted;

    // Populate activity feed from bids
    if (data.auction.bids && data.auction.bids.length > 0) {
      activityFeed.value = data.auction.bids.map((b) => ({
        id: b.id,
        icon: "mdi:gavel",
        text: `${b.user} mengajukan penawaran ${formatRupiah(b.amount)}`,
        time: b.time,
      }));

      // Compute user bid status
      const hasUserBid = data.auction.bids.some((b) => b.user === "Anda");
      const isUserLeading = data.auction.bids[0].user === "Anda";

      if (data.auction.status === "ended") {
        userBidStatus.value = isUserLeading
          ? "leading"
          : hasUserBid
            ? "outbid"
            : "none";
      } else {
        userBidStatus.value = isUserLeading
          ? "leading"
          : hasUserBid
            ? "outbid"
            : "none";
      }
    } else {
      activityFeed.value = [];
      userBidStatus.value = "none";
    }
    // Jika lelang berakhir dan user menang (winner info ada)
    if (data.auction.status === "ended" && data.auction.winner) {
      // Cek apakah winner name terdeteksi dari user profile,
      // tapi status leading/won bisa disesuaikan
      if (userBidStatus.value === "leading") {
        userBidStatus.value = "won";
      }
    }
  } catch (err) {
    console.error("Gagal fetch auction:", err);
    if (!isSilent) {
      isError.value = true;
    }
  } finally {
    isLoading.value = false;
  }
}

const authStore = useAuthStore();
let echoChannel = null;
let mountedAuctionId = null; // captured at mount time — survives route changes

onMounted(() => {
  fetchAuction();

  try {
    const echo = getEcho();
    mountedAuctionId = route.params.id;

    // Join the presence channel to track active viewers and listen for bid updates
    echoChannel = echo.join(`auction.${mountedAuctionId}`)
      .here((users) => {
        viewers.value = users.length;
      })
      .joining((user) => {
        viewers.value++;
        addNotif(`${user.name} bergabung menonton`, "info", "mdi:eye");
      })
      .leaving((user) => {
        viewers.value = Math.max(1, viewers.value - 1);
      })
      .listen("BidPlaced", (e) => {
        console.log("Realtime BidPlaced:", e);

        if (auction.value && auction.value.id === e.auction_id) {
          // Update current price, bids count, and ending time
          auction.value.currentPrice = e.highest_bid;
          auction.value.bidCount = e.bids_count;
          if (e.ends_at) {
            auction.value.endsAt = e.ends_at;
          }

          // Format new bid for history list
          const isMe = authStore.user && authStore.user.id === e.bidder_id;
          const displayUser = isMe ? "Anda" : e.bidder_name_masked;

          const newBidItem = {
            id: e.bid.id,
            user: displayUser,
            avatar: e.bid.avatar,
            amount: e.bid.amount,
            time: e.bid.time,
            status: e.bid.status,
          };

          if (!auction.value.bids) {
            auction.value.bids = [];
          }
          const exists = auction.value.bids.some(b => b.id === newBidItem.id);
          if (!exists) {
            auction.value.bids.unshift(newBidItem);
          }

          // Prepend to activity feed
          const activityText = `${displayUser} mengajukan penawaran ${formatRupiah(e.amount)}`;
          activityFeed.value.unshift({
            id: e.bid.id,
            icon: "mdi:gavel",
            text: activityText,
            time: e.bid.time,
          });

          // Update user leading/outbid status
          const hasUserBid = auction.value.bids.some((b) => b.user === "Anda");
          const isUserLeading = auction.value.bids[0]?.user === "Anda";
          userBidStatus.value = isUserLeading ? "leading" : (hasUserBid ? "outbid" : "none");

          // Reset bid amount input
          if (bidAmount.value < minBid.value) {
            bidAmount.value = minBid.value;
          }

          addNotif(
            `${displayUser} menawar ${formatRupiah(e.amount)}`,
            isMe ? "success" : "info",
            "mdi:gavel"
          );
        }
      })
      .listen("AuctionEnded", (e) => {
        console.log("Realtime AuctionEnded:", e);

        if (auction.value && auction.value.id === e.auction_id) {
          auction.value.status = "ended";
          
          if (e.winner_id) {
            const isMe = authStore.user && authStore.user.id === e.winner_id;
            userBidStatus.value = isMe ? "won" : "lost";
            auction.value.winner = {
              name: e.winner_name,
              finalPrice: e.final_price,
              paymentStatus: "pending",
            };
            
            addNotif(
              isMe ? "Selamat! Anda memenangkan lelang!" : `Lelang berakhir. Pemenang: ${e.winner_name}`,
              isMe ? "success" : "info",
              "mdi:trophy"
            );
          } else {
            userBidStatus.value = "none";
            addNotif("Lelang telah berakhir tanpa pemenang.", "info", "mdi:clock-end");
          }
        }
      });
  } catch (err) {
    console.error("Gagal initialize Echo:", err);
  }
});

function leaveAuctionChannel() {
  if (echoChannel && mountedAuctionId) {
    try {
      const echo = getEcho();
      echo.leave(`auction.${mountedAuctionId}`);
      echoChannel = null;
      mountedAuctionId = null;
    } catch (err) {
      console.error("Error leaving Echo channel:", err);
    }
  }
}

onUnmounted(() => {
  leaveAuctionChannel();
});

// ─── Computed dari data auction ───────────────────────────────────
const images = computed(() => auction.value?.images?.map((i) => i.url) ?? []);

const currentPrice = computed(() => {
  if (!auction.value) return 0;
  // Kalau ada bid, ambil amount bid tertinggi; fallback ke current_price
  const topBid = auction.value.bids?.[0]?.amount;
  return topBid ?? auction.value.currentPrice;
});

const minBid = computed(
  () => currentPrice.value + (auction.value?.minIncrement ?? 0),
);

const quickBidIncrements = computed(() => {
  const inc = auction.value?.minIncrement ?? 0;
  return [inc, inc * 2, inc * 4];
});

const countdownUnits = computed(() => {
  if (!auction.value) return [];
  const target =
    auction.value.status === "upcoming"
      ? auction.value.startsAt
      : auction.value.endsAt;
  const diff = Math.max(0, new Date(target) - now.value);
  return [
    {
      label: "Hari",
      value: String(Math.floor(diff / 86400000)).padStart(2, "0"),
    },
    {
      label: "Jam",
      value: String(Math.floor((diff % 86400000) / 3600000)).padStart(2, "0"),
    },
    {
      label: "Menit",
      value: String(Math.floor((diff % 3600000) / 60000)).padStart(2, "0"),
    },
    {
      label: "Detik",
      value: String(Math.floor((diff % 60000) / 1000)).padStart(2, "0"),
    },
  ];
});

const showAntiSnipeBanner = computed(() => {
  if (!auction.value || auction.value.status !== "live") return false;
  const diff = new Date(auction.value.endsAt) - now.value;
  return diff > 0 && diff <= 120000;
});

// ─── UI state ─────────────────────────────────────────────────────
const activePhoto = ref(0);
const watchlisted = ref(false);
const descExpanded = ref(false);
const bidAmount = ref(null);
const bidError = ref("");
const isBidding = ref(false);
const viewers = ref(18);
const userBidStatus = ref("none"); // 'none' | 'leading' | 'outbid' | 'won' | 'lost'

// ─── Activity feed ───────────────────────────────────────────────
const activityFeed = ref([]);

// ─── Notifications ────────────────────────────────────────────────
const notifications = ref([]);
let notifId = 0;
function addNotif(message, type = "success", icon = "mdi:bell") {
  const id = ++notifId;
  notifications.value.unshift({ id, message, type, icon });
  setTimeout(() => {
    notifications.value = notifications.value.filter((n) => n.id !== id);
  }, 4000);
}

// ─── Watchlist toggle ─────────────────────────────────────────────
const isTogglingWatchlist = ref(false);
async function handleToggleWatchlist() {
  if (isTogglingWatchlist.value) return;
  isTogglingWatchlist.value = true;
  try {
    const { data } = await toggleWatchlistApi(auction.value.id);
    watchlisted.value = data.watchlisted;
    addNotif(
      data.watchlisted ? "Ditambahkan ke Watchlist" : "Dihapus dari Watchlist",
      "success",
      data.watchlisted ? "mdi:heart" : "mdi:heart-off"
    );
  } catch (err) {
    console.error("Gagal toggle watchlist:", err);
    addNotif("Gagal merubah watchlist", "warning", "mdi:alert-circle");
  } finally {
    isTogglingWatchlist.value = false;
  }
}

// ─── Bid logic ───────────────────────────────────────────────────
async function placeBid() {
  bidError.value = "";
  const amount = Number(bidAmount.value);
  if (!amount || amount < minBid.value) {
    bidError.value = `Bid minimal ${formatRupiah(minBid.value)}.`;
    return;
  }
  isBidding.value = true;

  try {
    await placeBidApi(auction.value.id, amount);
    addNotif(
      "Bid Berhasil — Anda Sedang Memimpin",
      "success",
      "mdi:check-circle",
    );
    await fetchAuction();
  } catch (err) {
    console.error("Gagal tawar lelang:", err);
    const errMsg = err.response?.data?.message ?? "Gagal mengajukan penawaran.";
    bidError.value = errMsg;
    addNotif(errMsg, "warning", "mdi:alert-circle");
  } finally {
    isBidding.value = false;
  }
}

// ─── Buy Now ─────────────────────────────────────────────────────
const isBuyingNow = ref(false);
const showBuyNowModal = ref(false);

async function executeBuyNow() {
  if (isBuyingNow.value) return;
  isBuyingNow.value = true;
  try {
    await buyNowApi(auction.value.id);
    addNotif(
      "Pembelian Berhasil! Anda memenangkan lelang ini.",
      "success",
      "mdi:trophy"
    );
    showBuyNowModal.value = false;
    await fetchAuction();
  } catch (err) {
    console.error("Gagal buy now:", err);
    const errMsg = err.response?.data?.message ?? "Gagal melakukan Buy Now.";
    addNotif(errMsg, "warning", "mdi:alert-circle");
  } finally {
    isBuyingNow.value = false;
  }
}

// ─── Related auctions (TODO: fetch dari API) ─────────────────────
const relatedAuctions = ref([]);
</script>

<template>
  <div class="bg-white min-h-screen font-sans">
    <!-- ── FLOATING NOTIFICATIONS ────────────────────────────────── -->
    <div
      class="fixed top-24 right-5 z-50 flex flex-col gap-2 pointer-events-none"
    >
      <transition-group
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-x-8"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 translate-x-8"
      >
        <div
          v-for="notif in notifications"
          :key="notif.id"
          :class="[
            'pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium min-w-[260px]',
            notif.type === 'success'
              ? 'bg-white border-gray-200 text-gray-900'
              : notif.type === 'warning'
                ? 'bg-white border-orange-200 text-gray-900'
                : 'bg-black border-black text-white',
          ]"
        >
          <Icon :icon="notif.icon" class="w-5 h-5 shrink-0" />
          <span>{{ notif.message }}</span>
        </div>
      </transition-group>
    </div>

    <!-- ── ANTI-SNIPE BANNER ─────────────────────────────────────── -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-full"
      enter-to-class="opacity-100 translate-y-0"
    >
      <div
        v-if="showAntiSnipeBanner"
        class="bg-amber-50 border-b border-amber-200 px-6 py-3 flex items-center justify-center gap-3 text-sm"
      >
        <span class="text-amber-500">⚠</span>
        <span class="text-amber-800 font-medium"
          >Lelang akan diperpanjang otomatis jika ada bid pada 30 detik
          terakhir.</span
        >
      </div>
    </transition>

    <!-- ── LOADING STATE ─────────────────────────────────────────── -->
    <div v-if="isLoading" class="px-6 md:px-10 py-8 mx-auto">
      <div class="h-6 bg-gray-100 rounded w-64 mb-8 animate-pulse"></div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div class="space-y-4">
          <div class="aspect-[4/3] bg-gray-100 rounded-2xl animate-pulse"></div>
          <div class="h-48 bg-gray-100 rounded-2xl animate-pulse"></div>
        </div>
        <div class="space-y-4">
          <div class="h-64 bg-gray-100 rounded-2xl animate-pulse"></div>
          <div class="h-32 bg-gray-100 rounded-2xl animate-pulse"></div>
        </div>
      </div>
    </div>

    <!-- ── ERROR STATE ───────────────────────────────────────────── -->
    <div
      v-else-if="isError"
      class="flex flex-col items-center justify-center py-32 text-center px-6"
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
      <h3 class="font-semibold text-base mb-2">Gagal memuat lelang</h3>
      <p class="text-gray-400 text-sm mb-6">
        Terjadi kesalahan atau lelang tidak ditemukan.
      </p>
      <div class="flex gap-3">
        <button
          @click="fetchAuction"
          class="px-6 py-2.5 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
        >
          Coba Lagi
        </button>
        <button
          @click="router.push('/auctions')"
          class="px-6 py-2.5 border border-gray-200 rounded-lg text-sm font-medium hover:border-black transition-colors"
        >
          Kembali
        </button>
      </div>
    </div>

    <!-- ── MAIN CONTENT ──────────────────────────────────────────── -->
    <div v-else-if="auction" class="px-6 md:px-10 py-8 mx-auto">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8">
        <router-link to="/" class="hover:text-black transition-colors"
          >Beranda</router-link
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
            d="M9 5l7 7-7 7"
          />
        </svg>
        <router-link to="/auctions" class="hover:text-black transition-colors"
          >Daftar Lelang</router-link
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
            d="M9 5l7 7-7 7"
          />
        </svg>
        <span class="text-gray-700 font-medium truncate max-w-[200px]">{{
          auction.name
        }}</span>
      </nav>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-16">
        <!-- LEFT: Gallery + Info -->
        <div class="flex flex-col gap-6">
          <!-- Gallery -->
          <div
            class="rounded-2xl overflow-hidden bg-gray-50 border border-gray-100"
          >
            <div class="relative aspect-[4/3] overflow-hidden">
              <!-- Foto utama -->
              <img
                v-if="images.length > 0"
                :src="images[activePhoto]"
                :alt="auction.name"
                class="w-full h-full object-cover transition-opacity duration-300"
              />
              <div
                v-else
                class="w-full h-full flex items-center justify-center bg-gray-100"
              >
                <svg
                  class="w-16 h-16 text-gray-200"
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

              <!-- Status badge -->
              <div class="absolute top-4 left-4">
                <span
                  v-if="auction.status === 'live'"
                  class="flex items-center gap-1.5 bg-black text-white text-xs px-3 py-1.5 rounded-full font-medium"
                >
                  <span
                    class="live-dot w-1.5 h-1.5 rounded-full bg-white inline-block"
                  ></span>
                  Sedang Berlangsung
                </span>
                <span
                  v-else-if="auction.status === 'upcoming'"
                  class="flex items-center gap-1.5 bg-amber-400 text-black text-xs px-3 py-1.5 rounded-full font-medium"
                >
                  🟡 Akan Dimulai
                </span>
                <span
                  v-else
                  class="flex items-center gap-1.5 bg-gray-100 text-gray-600 text-xs px-3 py-1.5 rounded-full font-medium"
                >
                  🔴 Selesai
                </span>
              </div>

              <!-- Photo count -->
              <div
                v-if="images.length > 1"
                class="absolute top-4 right-4 bg-black/60 backdrop-blur-sm text-white text-xs px-2.5 py-1 rounded-lg"
              >
                {{ activePhoto + 1 }} / {{ images.length }}
              </div>
            </div>

            <!-- Thumbnails -->
            <div v-if="images.length > 1" class="flex gap-2 p-3">
              <button
                v-for="(img, idx) in images"
                :key="idx"
                @click="activePhoto = idx"
                :class="[
                  'flex-1 aspect-square rounded-lg overflow-hidden border-2 transition-all duration-200',
                  activePhoto === idx
                    ? 'border-black'
                    : 'border-transparent opacity-60 hover:opacity-100',
                ]"
              >
                <img
                  :src="img"
                  :alt="'Foto ' + (idx + 1)"
                  class="w-full h-full object-cover"
                />
              </button>
            </div>
          </div>

          <!-- Item Info -->
          <div class="rounded-2xl border border-gray-100 bg-white p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
              <div>
                <p class="text-xs text-gray-400 mb-1">{{ auction.category }}</p>
                <h1 class="text-xl font-bold leading-snug">
                  {{ auction.name }}
                </h1>
                <div class="flex gap-2 mt-2 flex-wrap">
                  <span
                    v-if="auction.condition"
                    class="text-xs border border-gray-200 text-gray-500 px-2.5 py-1 rounded-full"
                    >{{ auction.condition }}</span
                  >
                  <span
                    v-if="auction.artist"
                    class="text-xs border border-gray-200 text-gray-500 px-2.5 py-1 rounded-full"
                    >{{ auction.artist }}</span
                  >
                  <span
                    v-if="auction.year"
                    class="text-xs border border-gray-200 text-gray-500 px-2.5 py-1 rounded-full"
                    >{{ auction.year }}</span
                  >
                </div>
              </div>
              <button
                @click="handleToggleWatchlist"
                :disabled="isTogglingWatchlist"
                :class="[
                  'shrink-0 w-10 h-10 rounded-xl border-2 flex items-center justify-center transition-all duration-200 disabled:opacity-50',
                  watchlisted
                    ? 'border-black bg-black text-white'
                    : 'border-gray-200 text-gray-400 hover:border-black hover:text-black',
                ]"
              >
                <svg
                  class="w-4 h-4"
                  :fill="watchlisted ? 'currentColor' : 'none'"
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

            <!-- Seller -->
            <div
              class="flex items-center gap-3 mb-5 pb-5 border-b border-gray-100"
            >
              <div
                class="w-9 h-9 rounded-full bg-gray-900 flex items-center justify-center text-white text-sm font-bold shrink-0"
              >
                {{ auction.seller?.name?.charAt(0) ?? "?" }}
              </div>
              <div>
                <p class="text-xs text-gray-400">Dijual oleh</p>
                <p class="text-sm font-medium">{{ auction.seller?.name }}</p>
              </div>
              <span
                class="ml-auto text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full"
                >Terverifikasi ✓</span
              >
            </div>

            <!-- Description -->
            <p
              class="text-xs font-semibold tracking-[0.15em] uppercase text-gray-400 mb-3"
            >
              Deskripsi
            </p>
            <p
              class="text-gray-600 text-sm leading-relaxed"
              :class="{ 'line-clamp-4': !descExpanded }"
            >
              {{ auction.description }}
            </p>
            <button
              @click="descExpanded = !descExpanded"
              class="text-xs text-gray-400 hover:text-black mt-2 transition-colors"
            >
              {{
                descExpanded
                  ? "Tampilkan lebih sedikit ↑"
                  : "Baca selengkapnya ↓"
              }}
            </button>
          </div>

          <!-- Bid History -->
          <div class="rounded-2xl border border-gray-100 bg-white p-6">
            <div class="flex items-center justify-between mb-5">
              <p
                class="text-xs font-semibold tracking-[0.15em] uppercase text-gray-400"
              >
                Riwayat Penawaran
              </p>
              <span
                class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full"
                >{{ auction.bidCount }} penawaran</span
              >
            </div>

            <!-- Empty bids -->
            <div
              v-if="!auction.bids || auction.bids.length === 0"
              class="py-8 text-center"
            >
              <p class="text-sm text-gray-400">
                Belum ada penawaran. Jadilah yang pertama!
              </p>
            </div>

            <div
              v-else
              class="space-y-2 max-h-72 overflow-y-auto pr-1 custom-scroll"
            >
              <transition-group
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
              >
                <div
                  v-for="(bid, idx) in auction.bids"
                  :key="bid.id"
                  :class="[
                    'flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200',
                    idx === 0 ? 'bg-black text-white' : 'bg-gray-50',
                  ]"
                >
                  <div class="flex items-center gap-3">
                    <img
                      :src="bid.avatar"
                      class="w-7 h-7 rounded-full object-cover shrink-0"
                    />
                    <div>
                      <p
                        :class="[
                          'text-xs font-medium',
                          idx === 0 ? 'text-white' : 'text-gray-700',
                        ]"
                      >
                        {{ bid.user }}
                      </p>
                      <p
                        :class="[
                          'text-[10px]',
                          idx === 0 ? 'text-white/50' : 'text-gray-400',
                        ]"
                      >
                        {{ bid.time }}
                      </p>
                    </div>
                  </div>
                  <div class="text-right">
                    <p
                      :class="[
                        'text-sm font-bold',
                        idx === 0 ? 'text-white' : 'text-gray-800',
                      ]"
                    >
                      {{ formatRupiah(bid.amount) }}
                    </p>
                    <p v-if="idx === 0" class="text-[10px] text-white/60">
                      Tertinggi
                    </p>
                  </div>
                </div>
              </transition-group>
            </div>
          </div>

          <!-- Activity Feed -->
          <div
            v-if="activityFeed.length > 0"
            class="rounded-2xl border border-gray-100 bg-white p-6"
          >
            <p
              class="text-xs font-semibold tracking-[0.15em] uppercase text-gray-400 mb-4"
            >
              Aktivitas Realtime
            </p>
            <div class="space-y-2.5 max-h-48 overflow-y-auto custom-scroll">
              <transition-group
                enter-active-class="transition duration-400 ease-out"
                enter-from-class="opacity-0 translate-x-4"
                enter-to-class="opacity-100 translate-x-0"
              >
                <div
                  v-for="act in activityFeed"
                  :key="act.id"
                  class="flex items-start gap-2.5 text-sm text-gray-500"
                >
                  <Icon :icon="act.icon" class="w-4 h-4 mt-0.5 shrink-0" />
                  <p class="leading-relaxed">
                    {{ act.text }}
                    <span class="text-gray-300 text-xs ml-1">{{
                      act.time
                    }}</span>
                  </p>
                </div>
              </transition-group>
            </div>
          </div>
        </div>

        <!-- RIGHT: Auction Panel -->
        <div class="flex flex-col gap-4 lg:sticky lg:top-24 lg:self-start">
          <!-- User status banner -->
          <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
          >
            <div
              v-if="userBidStatus !== 'none'"
              :class="[
                'rounded-xl px-5 py-3.5 flex items-center gap-3 font-medium text-sm',
                userBidStatus === 'leading'
                  ? 'bg-emerald-50 border border-emerald-200 text-emerald-800'
                  : userBidStatus === 'outbid'
                    ? 'bg-red-50 border border-red-200 text-red-800'
                    : 'bg-black text-white',
              ]"
            >
              <Icon
                :icon="
                  userBidStatus === 'leading'
                    ? 'mdi:check-circle'
                    : userBidStatus === 'outbid'
                      ? 'mdi:alert-circle'
                      : 'mdi:trophy'
                "
                class="w-5 h-5"
              />
              <span>
                {{
                  userBidStatus === "leading"
                    ? "Anda Saat Ini Memimpin Lelang"
                    : userBidStatus === "outbid"
                      ? "Penawaran Anda Telah Dikalahkan"
                      : "Selamat! Anda Memenangkan Lelang"
                }}
              </span>
            </div>
          </transition>

          <!-- Main Auction Card -->
          <div
            class="rounded-2xl border border-gray-100 bg-white overflow-hidden"
          >
            <!-- Price header -->
            <div class="bg-black px-6 pt-6 pb-5">
              <div class="flex items-center justify-between mb-1">
                <p class="text-white/50 text-xs">
                  {{
                    auction.status === "upcoming"
                      ? "Harga Awal"
                      : "Harga Tertinggi Saat Ini"
                  }}
                </p>
                <div
                  v-if="auction.status === 'live'"
                  class="flex items-center gap-2 bg-white/10 rounded-full px-2.5 py-1"
                >
                  <span
                    class="live-dot w-1.5 h-1.5 rounded-full bg-white inline-block"
                  ></span>
                  <span class="text-white text-xs">Live</span>
                </div>
              </div>
              <p class="text-white font-bold text-4xl mb-3">
                {{ formatRupiah(currentPrice) }}
              </p>
              <div class="flex gap-5">
                <div>
                  <p class="text-white/40 text-[10px]">Harga Awal</p>
                  <p class="text-white/70 text-sm font-medium">
                    {{ formatRupiah(auction.startPrice) }}
                  </p>
                </div>
                <div>
                  <p class="text-white/40 text-[10px]">Min. Kenaikan</p>
                  <p class="text-white/70 text-sm font-medium">
                    {{ formatRupiah(auction.minIncrement) }}
                  </p>
                </div>
                <div>
                  <p class="text-white/40 text-[10px]">Penawaran</p>
                  <p class="text-white/70 text-sm font-medium">
                    {{ auction.bidCount }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Viewers row -->
            <div
              class="flex items-center gap-2 px-6 py-3 border-b border-gray-100 bg-gray-50"
            >
              <svg
                class="w-3.5 h-3.5 text-gray-400"
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
              <p class="text-xs text-gray-500">
                <span class="font-semibold text-gray-800">{{ viewers }}</span>
                orang sedang melihat lelang ini
              </p>
              <div class="ml-auto flex -space-x-1.5">
                <img
                  v-for="i in Math.min(viewers, 4)"
                  :key="i"
                  :src="`https://i.pravatar.cc/32?img=${i + 10}`"
                  class="w-5 h-5 rounded-full border border-white object-cover"
                />
                <div
                  v-if="viewers > 4"
                  class="w-5 h-5 rounded-full border border-white bg-gray-200 flex items-center justify-center text-[8px] font-bold text-gray-600"
                >
                  +{{ viewers - 4 }}
                </div>
              </div>
            </div>

            <!-- Countdown -->
            <div class="px-6 py-5 border-b border-gray-100">
              <p
                class="text-xs font-semibold tracking-[0.15em] uppercase text-gray-400 mb-3"
              >
                {{
                  auction.status === "live"
                    ? "Lelang Berakhir Dalam"
                    : auction.status === "upcoming"
                      ? "Dimulai Dalam"
                      : "Lelang Telah Berakhir"
                }}
              </p>
              <div v-if="auction.status !== 'ended'" class="flex gap-3">
                <div
                  v-for="unit in countdownUnits"
                  :key="unit.label"
                  class="flex-1 bg-gray-50 rounded-xl py-3 text-center border border-gray-100"
                >
                  <p class="font-bold text-2xl leading-none tabular-nums">
                    {{ unit.value }}
                  </p>
                  <p
                    class="text-gray-400 text-[10px] mt-1.5 uppercase tracking-wider"
                  >
                    {{ unit.label }}
                  </p>
                </div>
              </div>
              <div
                v-else
                class="flex items-center gap-2 bg-gray-50 rounded-xl px-4 py-3"
              >
                <span>🏁</span>
                <p class="text-sm font-medium text-gray-600">
                  Lelang telah berakhir
                </p>
              </div>
            </div>

            <!-- Bid input (live only) -->
            <div
              v-if="auction.status === 'live'"
              class="px-6 py-5 border-b border-gray-100"
            >
              <p
                class="text-xs font-semibold tracking-[0.15em] uppercase text-gray-400 mb-3"
              >
                Ajukan Penawaran
              </p>

              <!-- Quick bid -->
              <div class="flex gap-2 mb-3">
                <button
                  v-for="inc in quickBidIncrements"
                  :key="inc"
                  @click="bidAmount = currentPrice + inc"
                  class="flex-1 py-2 text-xs border border-gray-200 rounded-lg text-gray-600 hover:border-black hover:text-black transition-colors font-medium"
                >
                  +{{ formatRupiahShort(inc) }}
                </button>
              </div>

              <div class="relative mb-2">
                <span
                  class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium"
                  >Rp</span
                >
                <input
                  v-model="bidAmount"
                  type="number"
                  :placeholder="minBid"
                  :class="[
                    'w-full pl-10 pr-4 py-3 border rounded-xl text-sm font-semibold focus:outline-none transition-colors',
                    bidError
                      ? 'border-red-300 bg-red-50 text-red-700'
                      : 'border-gray-200 focus:border-black',
                  ]"
                />
              </div>
              <p
                v-if="bidError"
                class="text-xs text-red-500 mb-2 flex items-center gap-1"
              >
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"
                  />
                </svg>
                {{ bidError }}
              </p>
              <p v-else class="text-xs text-gray-400 mb-3">
                Minimal penawaran:
                <span class="font-semibold text-gray-600">{{
                  formatRupiah(minBid)
                }}</span>
              </p>

              <button
                @click="placeBid"
                :disabled="isBidding"
                class="w-full py-3.5 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                <svg
                  v-if="isBidding"
                  class="w-4 h-4 animate-spin"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  />
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                  />
                </svg>
                {{ isBidding ? "Memproses..." : "Tawar Sekarang" }}
              </button>
            </div>

            <!-- Winner info (ended) -->
            <div
              v-if="auction.status === 'ended' && auction.winner"
              class="px-6 py-5 border-b border-gray-100"
            >
              <div class="flex items-center gap-4">
                <div
                  class="w-12 h-12 bg-black rounded-xl flex items-center justify-center text-2xl shrink-0"
                >
                  🏆
                </div>
                <div>
                  <p class="text-xs text-gray-400 mb-0.5">Pemenang Lelang</p>
                  <p class="font-bold text-base">{{ auction.winner.name }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Harga Akhir:
                    <span class="font-semibold text-gray-700">{{
                      formatRupiah(auction.winner.finalPrice)
                    }}</span>
                  </p>
                </div>
              </div>
            </div>

            <!-- Upcoming — remind me -->
            <div
              v-if="auction.status === 'upcoming'"
              class="px-6 py-5 border-b border-gray-100"
            >
              <button
                @click="handleToggleWatchlist"
                :disabled="isTogglingWatchlist"
                class="w-full py-3.5 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
                :class="watchlisted ? 'bg-gray-100 text-gray-800 hover:bg-gray-200' : 'bg-black text-white hover:bg-gray-800'"
              >
                <Icon :icon="watchlisted ? 'mdi:bell-off' : 'mdi:bell'" class="w-4.5 h-4.5" />
                {{ watchlisted ? "Batalkan Pengingat" : "Ingatkan Saya" }}
              </button>
              <p class="text-xs text-gray-400 text-center mt-2">
                {{ watchlisted ? "Pengingat aktif. Anda akan mendapatkan notifikasi saat lelang dimulai." : "Anda akan diberitahu saat lelang dimulai." }}
              </p>
            </div>
          </div>

          <!-- Buy Now Card -->
          <div
            v-if="auction.status === 'live' && auction.buyNowPrice"
            class="rounded-2xl border border-gray-200 bg-white p-5"
          >
            <div class="flex items-start justify-between mb-3">
              <div>
                <p class="text-xs text-gray-400 mb-0.5">Harga Buy Now</p>
                <p class="text-xl font-bold">
                  {{ formatRupiah(auction.buyNowPrice) }}
                </p>
              </div>
              <span
                class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full font-medium"
                >Langsung Menang</span
              >
            </div>
            <p class="text-xs text-gray-400 leading-relaxed mb-4">
              Mengakhiri lelang secara langsung dan menetapkan Anda sebagai
              pemenang.
            </p>
            <button
              @click="showBuyNowModal = true"
              class="w-full py-3 border-2 border-black text-black rounded-xl text-sm font-semibold hover:bg-black hover:text-white transition-all duration-200 flex items-center justify-center gap-2"
            >
              Beli Sekarang
            </button>
          </div>

          <!-- Share / report -->
          <div class="flex gap-2">
            <button
              class="flex-1 flex items-center justify-center gap-2 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-500 hover:border-black hover:text-black transition-colors"
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
                  d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
                />
              </svg>
              Bagikan
            </button>
            <button
              class="flex-1 flex items-center justify-center gap-2 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-500 hover:border-black hover:text-black transition-colors"
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
                  d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"
                />
              </svg>
              Laporkan
            </button>
          </div>
        </div>
      </div>

      <!-- Related auctions — TODO: fetch dari API -->
      <div v-if="relatedAuctions.length > 0" class="mt-4">
        <div class="flex items-end justify-between mb-8">
          <div>
            <span
              class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
              >Rekomendasi</span
            >
            <h2 class="text-2xl font-bold mt-2">
              Lelang Lain yang Mungkin Anda Sukai
            </h2>
          </div>
          <router-link
            to="/auctions"
            class="text-sm text-gray-500 hover:text-black flex items-center gap-1 transition-colors"
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
          </router-link>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <div
            v-for="rel in relatedAuctions"
            :key="rel.id"
            class="rounded-2xl overflow-hidden bg-white border border-gray-100 card-lift shadow-sm cursor-pointer"
            @click="router.push(`/auction/${rel.id}`)"
          >
            <div class="relative aspect-[4/3] overflow-hidden">
              <img
                :src="rel.image"
                :alt="rel.name"
                class="w-full h-full object-cover"
              />
            </div>
            <div class="p-4">
              <p class="text-xs text-gray-400 mb-0.5">{{ rel.category }}</p>
              <h3 class="font-semibold text-sm leading-snug mb-3">
                {{ rel.name }}
              </h3>
              <div class="flex items-center justify-between">
                <p class="font-bold text-sm">
                  {{ formatRupiah(rel.currentPrice) }}
                </p>
                <span class="text-xs text-gray-400"
                  >{{ rel.bidCount }} bid</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ── BUY NOW MODAL ─────────────────────────────────────── -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="showBuyNowModal"
        class="fixed inset-0 bg-black/55 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        @click.self="showBuyNowModal = false"
      >
        <transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0 translate-y-4 scale-95"
          enter-to-class="opacity-100 translate-y-0 scale-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0 scale-100"
          leave-to-class="opacity-0 translate-y-4 scale-95"
        >
          <div class="relative bg-white rounded-2xl p-7 max-w-sm w-full shadow-2xl border border-gray-100">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-4">
              <div>
                <h3 class="font-bold text-lg text-black">Beli Sekarang</h3>
                <p class="text-xs text-gray-400">Konfirmasi Pembelian Langsung</p>
              </div>
            </div>

            <!-- Content -->
            <div class="bg-gray-50 rounded-xl p-4 mb-6 space-y-3">
              <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Karya Seni</p>
                <p class="text-sm font-bold text-gray-800 truncate">{{ auction.name }}</p>
              </div>
              <div class="border-t border-gray-200/60 pt-2.5">
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Harga Tetap (Buy Now)</p>
                <p class="text-lg font-black text-black">{{ formatRupiah(auction.buyNowPrice) }}</p>
              </div>
            </div>

            <p class="text-xs text-gray-400 leading-relaxed mb-6">
              Tindakan ini akan mengakhiri lelang secara langsung dan menetapkan Anda sebagai pemenang secara permanen.
            </p>

            <!-- Actions -->
            <div class="flex gap-3">
              <button
                @click="showBuyNowModal = false"
                :disabled="isBuyingNow"
                class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors disabled:opacity-50"
              >
                Batal
              </button>
              <button
                @click="executeBuyNow"
                :disabled="isBuyingNow"
                class="flex-1 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
              >
                <svg
                  v-if="isBuyingNow"
                  class="w-4 h-4 animate-spin text-white"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  />
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                  />
                </svg>
                Konfirmasi
              </button>
            </div>
          </div>
        </transition>
      </div>
    </transition>
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

.custom-scroll::-webkit-scrollbar {
  width: 4px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 99px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

.line-clamp-4 {
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.font-sans {
  font-family: "DM Sans", sans-serif;
}
.tabular-nums {
  font-variant-numeric: tabular-nums;
}
</style>
