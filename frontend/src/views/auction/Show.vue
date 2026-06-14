<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Icon } from "@iconify/vue";

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
function hoursFromNow(h) {
  return new Date(Date.now() + h * 3600000);
}

// ─── Reactive clock ───────────────────────────────────────────────
const now = ref(new Date());
let ticker;
onMounted(() => {
  ticker = setInterval(() => {
    now.value = new Date();
  }, 1000);
});
onUnmounted(() => clearInterval(ticker));

// ─── Dummy auction data (ganti dengan API) ────────────────────────
const auction = ref({
  id: 1,
  name: "Lukisan Bali Klasik Tahun 1980",
  category: "Lukisan",
  status: "live", // 'live' | 'upcoming' | 'ended'
  seller: "I Made Surya",
  sellerAvatar: "https://i.pravatar.cc/80?img=33",
  description:
    "Lukisan karya seniman Bali terkemuka tahun 1980, dikerjakan dengan teknik cat minyak di atas kanvas berukuran 120×90 cm. Menampilkan pemandangan desa Bali klasik dengan nuansa alam yang autentik — sawah hijau berlapis, pepohonan kelapa, dan langit senja keemasan. Karya ini merupakan bagian dari koleksi pribadi yang disimpan selama lebih dari 40 tahun. Kondisi sangat baik, telah melewati proses restorasi profesional pada tahun 2019. Disertai sertifikat keaslian dari Yayasan Seni Bali.",
  tags: ["Cat Minyak", "Kanvas", "1980an", "Realis", "Pemandangan"],
  images: [
    "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=800&q=80",
    "https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=800&q=80",
    "https://images.unsplash.com/photo-1501472312651-726afe119ff1?w=800&q=80",
    "https://images.unsplash.com/photo-1565367777-8879b08cd5b6?w=800&q=80",
  ],
  startPrice: 5000000,
  minIncrement: 250000,
  buyNowPrice: 25000000,
  endsAt: hoursFromNow(1.8),
  winner: { name: "I Putu Arya", finalPrice: 12500000 },
});

// ─── State ────────────────────────────────────────────────────────
const activePhoto = ref(0);
const watchlisted = ref(false);
const descExpanded = ref(false);
const bidAmount = ref(null);
const bidError = ref("");
const isBidding = ref(false);
const viewers = ref(18);
const userBidStatus = ref("leading"); // 'none' | 'leading' | 'outbid' | 'won'

// ─── Dummy bid history (newest first) ────────────────────────────
const bidHistory = ref([
  {
    id: 7,
    user: "Kol***udi",
    avatar: "https://i.pravatar.cc/32?img=3",
    amount: 8500000,
    time: "10:15:32",
  },
  {
    id: 6,
    user: "Bud***ari",
    avatar: "https://i.pravatar.cc/32?img=7",
    amount: 8000000,
    time: "10:12:10",
  },
  {
    id: 5,
    user: "Wiy***nto",
    avatar: "https://i.pravatar.cc/32?img=12",
    amount: 7750000,
    time: "10:08:55",
  },
  {
    id: 4,
    user: "Sar***ewi",
    avatar: "https://i.pravatar.cc/32?img=15",
    amount: 7500000,
    time: "10:05:22",
  },
  {
    id: 3,
    user: "Ptu***ya",
    avatar: "https://i.pravatar.cc/32?img=20",
    amount: 7000000,
    time: "09:58:44",
  },
  {
    id: 2,
    user: "Agu***rai",
    avatar: "https://i.pravatar.cc/32?img=25",
    amount: 6500000,
    time: "09:45:10",
  },
  {
    id: 1,
    user: "Ket***ana",
    avatar: "https://i.pravatar.cc/32?img=30",
    amount: 6000000,
    time: "09:30:00",
  },
]);

const currentPrice = computed(
  () => bidHistory.value[0]?.amount ?? auction.value.startPrice,
);
const minBid = computed(() => currentPrice.value + auction.value.minIncrement);
const quickBidIncrements = computed(() => [
  auction.value.minIncrement,
  auction.value.minIncrement * 2,
  auction.value.minIncrement * 4,
]);

// ─── Activity feed ────────────────────────────────────────────────
const activityFeed = ref([
  {
    id: 5,
    icon: "mdi:gavel",
    text: "Kol***udi menaikkan bid menjadi Rp 8.500.000",
    time: "baru saja",
  },
  {
    id: 4,
    icon: "mdi:alert-circle",
    text: "Bud***ari telah di-outbid",
    time: "3 mnt lalu",
  },
  {
    id: 3,
    icon: "mdi:gavel",
    text: "Bud***ari menaikkan bid menjadi Rp 8.000.000",
    time: "3 mnt lalu",
  },
  {
    id: 2,
    icon: "mdi:eye",
    text: "18 pengguna sedang menyaksikan lelang ini",
    time: "5 mnt lalu",
  },
  {
    id: 1,
    icon: "mdi:gavel",
    text: "Wiy***nto menaikkan bid menjadi Rp 7.750.000",
    time: "6 mnt lalu",
  },
]);

// ─── Notifications ────────────────────────────────────────────────
const notifications = ref([]);
let notifId = 0;
function addNotif(message, type = "success", icon = "🔔") {
  const id = ++notifId;
  notifications.value.unshift({ id, message, type, icon });
  setTimeout(() => {
    notifications.value = notifications.value.filter((n) => n.id !== id);
  }, 4000);
}

// ─── Bid logic ────────────────────────────────────────────────────
function placeBid() {
  bidError.value = "";
  const amount = Number(bidAmount.value);
  if (!amount || amount < minBid.value) {
    bidError.value = `Bid harus lebih besar dari ${formatRupiah(currentPrice.value)} + kenaikan minimum.`;
    return;
  }
  isBidding.value = true;
  setTimeout(() => {
    const newBid = {
      id: bidHistory.value.length + 1,
      user: "Anda",
      avatar: "https://i.pravatar.cc/32?img=45",
      amount,
      time: new Date().toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
      }),
    };
    bidHistory.value.unshift(newBid);
    activityFeed.value.unshift({
      id: Date.now(),
      icon: "mdi:gavel",
      text: `Anda menaikkan bid menjadi ${formatRupiah(amount)}`,
      time: "baru saja",
    });
    userBidStatus.value = "leading";
    bidAmount.value = null;
    isBidding.value = false;
    addNotif(
      "Bid Berhasil — Anda Sedang Memimpin",
      "success",
      "mdi:alert-circle",
    );
  }, 1200);
}

// ─── Countdown ───────────────────────────────────────────────────
const countdownUnits = computed(() => {
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

// ─── Anti-snipe banner ───────────────────────────────────────────
const showAntiSnipeBanner = computed(() => {
  if (auction.value.status !== "live") return false;
  const diff = new Date(auction.value.endsAt) - now.value;
  return diff > 0 && diff <= 120000;
});
const antiSnipeExtended = ref(false);

// ─── Simulate outbid after 8s (demo) ────────────────────────────
let demoTimer;
onMounted(() => {
  demoTimer = setTimeout(() => {
    if (userBidStatus.value === "leading") {
      userBidStatus.value = "outbid";
      bidHistory.value.unshift({
        id: 99,
        user: "Pnw***ari",
        avatar: "https://i.pravatar.cc/32?img=50",
        amount: currentPrice.value + auction.value.minIncrement,
        time: new Date().toLocaleTimeString("id-ID", {
          hour: "2-digit",
          minute: "2-digit",
          second: "2-digit",
        }),
      });
      activityFeed.value.unshift(
        {
          id: Date.now() + 1,
          icon: "mdi:gavel",
          text: `Pnw***ari menaikkan bid menjadi ${formatRupiah(bidHistory.value[0].amount)}`,
          time: "baru saja",
        },
        {
          id: Date.now() + 2,
          icon: "mdi:alert-circle",
          text: "Anda telah di-outbid",
          time: "baru saja",
        },
      );
      addNotif("Anda Telah Di-Outbid", "error", "mdi:alert-circle");
    }
  }, 8000);
});
onUnmounted(() => clearTimeout(demoTimer));

// ─── Simulated viewer fluctuation ───────────────────────────────
onMounted(() => {
  setInterval(() => {
    const delta = Math.random() > 0.5 ? 1 : -1;
    viewers.value = Math.max(5, Math.min(40, viewers.value + delta));
  }, 5000);
});

// ─── Related auctions dummy ──────────────────────────────────────
const relatedAuctions = [
  {
    id: 2,
    name: "Penjaga Bali",
    category: "Patung",
    status: "live",
    image:
      "https://images.unsplash.com/photo-1578301978069-3c23ee44e8dc?w=400&q=80",
    currentPrice: 12500000,
    bidCount: 18,
  },
  {
    id: 3,
    name: "Dewi Kesuburan",
    category: "Patung",
    status: "live",
    image:
      "https://images.unsplash.com/photo-1569091791842-7cfb64e04797?w=400&q=80",
    currentPrice: 28000000,
    bidCount: 31,
  },
  {
    id: 7,
    name: "Sunrise Penida",
    category: "Koleksi Langka",
    status: "upcoming",
    image:
      "https://images.unsplash.com/photo-1518544866330-4e716499f800?w=400&q=80",
    currentPrice: 5000000,
    bidCount: 0,
  },
  {
    id: 5,
    name: "Alam Tak Terbatas",
    category: "Lukisan",
    status: "live",
    image:
      "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=400&q=80",
    currentPrice: 19200000,
    bidCount: 22,
  },
];
</script>

<template>
  <div class="bg-white min-h-screen font-sans">
    <!-- ── FLOATING NOTIFICATIONS ─────────────────────────────────── -->
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
            'pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-medium min-w-65',
            notif.type === 'success'
              ? 'bg-white border-gray-200 text-gray-900'
              : notif.type === 'warning'
                ? 'bg-white border-orange-200 text-gray-900'
                : 'bg-black border-black text-white',
          ]"
        >
          <div
            class="flex gap-x-2 items-center"
            v-for="notif in notifications"
            :key="notif.id"
          >
            <Icon :icon="notif.icon" class="w-5 h-5" />
            <span>{{ notif.message }}</span>
          </div>
        </div>
      </transition-group>
    </div>

    <!-- ── ANTI-SNIPE BANNER ──────────────────────────────────────── -->
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
        <span class="text-amber-800 font-medium">
          {{
            antiSnipeExtended
              ? "⏰ Waktu lelang diperpanjang 2 menit karena terdapat bid pada detik terakhir."
              : "Lelang akan diperpanjang otomatis jika ada bid pada 30 detik terakhir."
          }}
        </span>
      </div>
    </transition>

    <div class="px-6 md:px-10 py-8 mx-auto">
      <!-- ── BREADCRUMB ─────────────────────────────────────────────── -->
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

      <!-- ── MAIN GRID ──────────────────────────────────────────────── -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-16">
        <!-- LEFT: Gallery + Info ────────────────────────────────────── -->
        <div class="flex flex-col gap-6">
          <!-- Gallery -->
          <div
            class="rounded-2xl overflow-hidden bg-gray-50 border border-gray-100"
          >
            <!-- Main photo -->
            <div class="relative aspect-[4/3] overflow-hidden">
              <img
                :src="auction.images[activePhoto]"
                :alt="auction.name"
                class="w-full h-full object-cover transition-opacity duration-300"
              />
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
              <!-- Photo count badge -->
              <div
                class="absolute top-4 right-4 bg-black/60 backdrop-blur-sm text-white text-xs px-2.5 py-1 rounded-lg"
              >
                {{ activePhoto + 1 }} / {{ auction.images.length }}
              </div>
            </div>

            <!-- Thumbnails -->
            <div class="flex gap-2 p-3">
              <button
                v-for="(img, idx) in auction.images"
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

          <!-- Item Info card -->
          <div class="rounded-2xl border border-gray-100 bg-white p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
              <div>
                <p class="text-xs text-gray-400 mb-1">{{ auction.category }}</p>
                <h1 class="text-xl font-bold leading-snug">
                  {{ auction.name }}
                </h1>
              </div>
              <!-- Watchlist -->
              <button
                @click="watchlisted = !watchlisted"
                :class="[
                  'flex-shrink-0 w-10 h-10 rounded-xl border-2 flex items-center justify-center transition-all duration-200',
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

            <div
              class="flex items-center gap-3 mb-5 pb-5 border-b border-gray-100"
            >
              <img
                :src="auction.sellerAvatar"
                class="w-9 h-9 rounded-full object-cover"
              />
              <div>
                <p class="text-xs text-gray-400">Dijual oleh</p>
                <p class="text-sm font-medium">{{ auction.seller }}</p>
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

            <!-- Meta tags -->
            <div class="flex flex-wrap gap-2 mt-5">
              <span
                v-for="tag in auction.tags"
                :key="tag"
                class="text-xs border border-gray-200 text-gray-500 px-3 py-1 rounded-full"
              >
                {{ tag }}
              </span>
            </div>
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
                >{{ bidHistory.length }} penawaran</span
              >
            </div>

            <div class="space-y-2 max-h-72 overflow-y-auto pr-1 custom-scroll">
              <transition-group
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
              >
                <div
                  v-for="(bid, idx) in bidHistory"
                  :key="bid.id"
                  :class="[
                    'flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200',
                    idx === 0 ? 'bg-black text-white' : 'bg-gray-50',
                  ]"
                >
                  <div class="flex items-center gap-3">
                    <img
                      :src="bid.avatar"
                      class="w-7 h-7 rounded-full object-cover flex-shrink-0"
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

          <!-- Live Activity Feed -->
          <div class="rounded-2xl border border-gray-100 bg-white p-6">
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
                  <span class="mt-0.5 flex-shrink-0">{{ act.icon }}</span>
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

        <!-- RIGHT: Auction Panel ──────────────────────────────────── -->
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
                    : userBidStatus === 'won'
                      ? 'bg-black text-white'
                      : '',
              ]"
            >
              <div class="flex items-center gap-2">
                <Icon
                  :icon="
                    userBidStatus === 'leading'
                      ? 'mdi:check-circle'
                      : userBidStatus === 'outbid'
                        ? 'mdi:alert-circle'
                        : 'mdi:trophy'
                  "
                  :class="
                    userBidStatus === 'leading'
                      ? 'text-green-500'
                      : userBidStatus === 'outbid'
                        ? 'text-red-500'
                        : 'text-yellow-500'
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
            </div>
          </transition>

          <!-- Main Auction Card -->
          <div
            class="rounded-2xl border border-gray-100 bg-white overflow-hidden"
          >
            <!-- Current price header -->
            <div class="bg-black px-6 pt-6 pb-5">
              <div class="flex items-center justify-between mb-1">
                <p class="text-white/50 text-xs">Harga Tertinggi Saat Ini</p>
                <div
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
                    {{ bidHistory.length }}
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

            <!-- Bid input area -->
            <div
              v-if="auction.status === 'live'"
              class="px-6 py-5 border-b border-gray-100"
            >
              <p
                class="text-xs font-semibold tracking-[0.15em] uppercase text-gray-400 mb-3"
              >
                Ajukan Penawaran
              </p>

              <!-- Quick bid buttons -->
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
                      ? 'border-red-300 bg-red-50 text-red-700 focus:border-red-400'
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
              v-if="auction.status === 'ended'"
              class="px-6 py-5 border-b border-gray-100"
            >
              <div class="flex items-center gap-4">
                <div
                  class="w-12 h-12 bg-black rounded-xl flex items-center justify-center text-2xl flex-shrink-0"
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

            <!-- Upcoming - remind me -->
            <div
              v-if="auction.status === 'upcoming'"
              class="px-6 py-5 border-b border-gray-100"
            >
              <button
                class="w-full py-3.5 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors"
              >
                🔔 Ingatkan Saya
              </button>
              <p class="text-xs text-gray-400 text-center mt-2">
                Anda akan diberitahu saat lelang dimulai
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
              pemenang tanpa perlu menunggu.
            </p>
            <button
              class="w-full py-3 border-2 border-black text-black rounded-xl text-sm font-semibold hover:bg-black hover:text-white transition-all duration-200"
            >
              Beli Sekarang
            </button>
          </div>

          <!-- Share / report row -->
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
        <!-- end right panel -->
      </div>
      <!-- end main grid -->

      <!-- ── RELATED AUCTIONS ─────────────────────────────────────── -->
      <div class="mt-4">
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
          >
            <div class="relative aspect-[4/3] overflow-hidden">
              <img
                :src="rel.image"
                :alt="rel.name"
                class="w-full h-full object-cover"
              />
              <div v-if="rel.status === 'live'" class="absolute top-3 left-3">
                <span
                  class="flex items-center gap-1.5 bg-black text-white text-xs px-2.5 py-1 rounded-full"
                >
                  <span
                    class="live-dot w-1.5 h-1.5 rounded-full bg-white inline-block"
                  ></span>
                  Live
                </span>
              </div>
            </div>
            <div class="p-4">
              <p class="text-xs text-gray-400 mb-0.5">{{ rel.category }}</p>
              <h3 class="font-semibold text-sm leading-snug mb-3">
                {{ rel.name }}
              </h3>
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-[10px] text-gray-400">Harga saat ini</p>
                  <p class="font-bold text-sm">
                    {{ formatRupiah(rel.currentPrice) }}
                  </p>
                </div>
                <span class="text-xs text-gray-400"
                  >{{ rel.bidCount }} bid</span
                >
              </div>
            </div>
          </div>
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
