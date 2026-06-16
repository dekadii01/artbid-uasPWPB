<script setup>
import { ref } from "vue";

// ─── Active tab ───────────────────────────────────────────────
const activeTab = ref("umum");

const tabs = [
  {
    label: "Umum",
    value: "umum",
    icon: "M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z",
  },
  {
    label: "Lelang",
    value: "lelang",
    icon: "M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10",
  },
  { label: "Realtime", value: "realtime", icon: "M13 10V3L4 14h7v7l9-11h-7z" },
  {
    label: "Keamanan",
    value: "keamanan",
    icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
  },
  {
    label: "Notifikasi",
    value: "notifikasi",
    icon: "M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9",
  },
  {
    label: "Penyimpanan",
    value: "penyimpanan",
    icon: "M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4",
  },
  {
    label: "Backup",
    value: "backup",
    icon: "M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12",
  },
];

// ─── General settings ─────────────────────────────────────────
const general = ref({
  platformName: "ArtBid Bali",
  description:
    "Platform lelang daring untuk komunitas kolektor barang seni di Bali.",
  adminEmail: "admin@artbidbali.com",
  contact: "+62 812 3456 7890",
});

// ─── Auction settings ─────────────────────────────────────────
const auction = ref({
  defaultDuration: "7",
  minBidIncrement: 100000,
  maxPhotos: 10,
  requireApproval: true,
  antiSnipingEnabled: true,
  antiSnipingWindow: 30,
  antiSnipingExtension: 2,
  buyNowEnabled: true,
  sellerSetBuyNow: true,
});

const durationOptions = [
  { label: "1 Hari", value: "1" },
  { label: "3 Hari", value: "3" },
  { label: "7 Hari", value: "7" },
  { label: "14 Hari", value: "14" },
  { label: "30 Hari", value: "30" },
];

// ─── Realtime settings ────────────────────────────────────────
const realtime = ref({
  serverStatus: "Online",
  activeConnections: 42,
  broadcastDriver: "Reverb",
  presenceChannelEnabled: true,
  channels: [
    { name: "Auction Channels", count: 78, active: true },
    { name: "Presence Channels", count: 42, active: true },
    { name: "Notification Channels", count: 120, active: true },
  ],
});

// ─── Notification settings ────────────────────────────────────
const notifications = ref({
  outbid: true,
  winner: true,
  auctionEnded: true,
  adminAlert: true,
  methodInApp: true,
  methodEmail: true,
});

// ─── Security settings ────────────────────────────────────────
const security = ref({
  sessionExpiry: 7,
  maxLoginAttempts: 5,
  allowPasswordReset: true,
  requireEmailVerification: true,
});

const roles = [
  {
    name: "Super Admin",
    desc: "Akses penuh ke seluruh sistem.",
    icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
    dark: true,
  },
  {
    name: "Admin",
    desc: "Mengelola pengguna, lelang, kategori, dan laporan.",
    icon: "M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z",
    dark: false,
  },
  {
    name: "User",
    desc: "Membuat lelang, mengikuti lelang, dan mengelola akun pribadi.",
    icon: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
    dark: false,
  },
];

// ─── Storage settings ─────────────────────────────────────────
const storage = ref({
  driver: "local",
  maxFileSize: 5,
  allowedFormats: ["jpg", "jpeg", "png", "webp"],
});

const storageOptions = [
  { label: "Local Storage", value: "local" },
  { label: "Amazon S3", value: "s3" },
  { label: "Cloudflare R2", value: "r2" },
];

// ─── Backup settings ──────────────────────────────────────────
const backup = ref({
  autoBackup: true,
  frequency: "daily",
  lastBackup: "14 Juni 2026, 23.59 WITA",
});

const freqOptions = [
  { label: "Harian", value: "daily" },
  { label: "Mingguan", value: "weekly" },
  { label: "Bulanan", value: "monthly" },
];

// ─── System info ──────────────────────────────────────────────
const sysInfo = [
  { label: "Backend", value: "Laravel 12" },
  { label: "Frontend", value: "Vue 3" },
  { label: "Database", value: "MySQL 8" },
  { label: "Realtime Engine", value: "Laravel Reverb" },
  { label: "Versi Aplikasi", value: "v1.0.0" },
  { label: "Status Sistem", value: "Online", highlight: true },
];

// ─── Save toast ───────────────────────────────────────────────
const saved = ref(false);
function save() {
  saved.value = true;
  setTimeout(() => (saved.value = false), 2500);
}

function toggleFormat(fmt) {
  const idx = storage.value.allowedFormats.indexOf(fmt);
  if (idx === -1) storage.value.allowedFormats.push(fmt);
  else storage.value.allowedFormats.splice(idx, 1);
}

function formatRp(val) {
  return "Rp " + Number(val).toLocaleString("id-ID");
}
</script>

<template>
  <div class="flex-1 px-8 py-8 space-y-6 min-h-screen bg-gray-50 font-sans">
    <!-- ═══════════════════ HEADER ═══════════════════ -->
    <div class="flex justify-between items-start">
      <div>
        <span
          class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
          >Admin Panel</span
        >
        <h1 class="text-3xl font-bold text-black mt-1.5 tracking-tight">
          Pengaturan Sistem
        </h1>
        <p class="text-gray-500 text-sm mt-2 max-w-2xl leading-relaxed">
          Kelola konfigurasi platform, fitur lelang, keamanan, dan berbagai
          pengaturan sistem lainnya.
        </p>
      </div>
      <div class="flex gap-2 shrink-0">
        <button
          @click="save"
          class="flex items-center gap-2 px-5 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
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
              d="M5 13l4 4L19 7"
            />
          </svg>
          Simpan Semua
        </button>
      </div>
    </div>

    <!-- ═══════════════════ TOAST ═══════════════════ -->
    <transition name="fade-modal">
      <div
        v-if="saved"
        class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-black text-white px-5 py-3 rounded-2xl shadow-xl text-sm font-medium"
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
            d="M5 13l4 4L19 7"
          />
        </svg>
        Pengaturan berhasil disimpan.
      </div>
    </transition>

    <!-- ═══════════════════ LAYOUT ═══════════════════ -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
      <!-- ── SIDEBAR NAV ── -->
      <div class="xl:col-span-1 space-y-1.5">
        <div class="bg-white rounded-2xl border border-gray-100 p-3">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            @click="activeTab = tab.value"
            :class="[
              'w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 text-left',
              activeTab === tab.value
                ? 'bg-black text-white'
                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800',
            ]"
          >
            <svg
              class="w-4 h-4 shrink-0"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                :d="tab.icon"
              />
            </svg>
            {{ tab.label }}
          </button>
        </div>

        <!-- System info card -->
        <div class="bg-black rounded-2xl p-5">
          <p class="text-white/50 text-xs uppercase tracking-widest mb-4">
            Informasi Sistem
          </p>
          <div class="space-y-3">
            <div v-for="info in sysInfo" :key="info.label">
              <p class="text-white/40 text-xs mb-0.5">{{ info.label }}</p>
              <p
                class="text-sm font-semibold"
                :class="info.highlight ? 'text-white' : 'text-white/80'"
              >
                <span
                  v-if="info.highlight"
                  class="inline-flex items-center gap-1.5"
                >
                  <span
                    class="live-dot w-1.5 h-1.5 rounded-full bg-white inline-block"
                  ></span>
                  {{ info.value }}
                </span>
                <span v-else>{{ info.value }}</span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── MAIN CONTENT ── -->
      <div class="xl:col-span-3 space-y-5">
        <!-- ════ UMUM ════ -->
        <div v-if="activeTab === 'umum'" class="space-y-5">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">
              Informasi Platform
            </h2>
            <p class="text-xs text-gray-400 mb-6">
              Atur identitas dan informasi kontak platform.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Nama Platform</label
                >
                <input
                  v-model="general.platformName"
                  type="text"
                  class="field"
                  placeholder="ArtBid Bali"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Email Administrator</label
                >
                <input
                  v-model="general.adminEmail"
                  type="email"
                  class="field"
                  placeholder="admin@artbidbali.com"
                />
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Deskripsi Platform</label
                >
                <textarea
                  v-model="general.description"
                  rows="3"
                  class="field resize-none"
                  placeholder="Deskripsi singkat platform..."
                ></textarea>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Nomor Kontak</label
                >
                <input
                  v-model="general.contact"
                  type="text"
                  class="field"
                  placeholder="+62 812 3456 7890"
                />
              </div>
              <div></div>
              <!-- Logo upload -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Logo Platform
                  <span class="text-gray-400 font-normal ml-1"
                    >PNG, JPG, SVG</span
                  ></label
                >
                <div class="upload-zone">
                  <svg
                    class="w-5 h-5 text-gray-300 mx-auto mb-1.5"
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
                  <p class="text-xs text-gray-400">Klik untuk unggah logo</p>
                </div>
              </div>
              <!-- Favicon upload -->
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Favicon
                  <span class="text-gray-400 font-normal ml-1"
                    >ICO, PNG</span
                  ></label
                >
                <div class="upload-zone">
                  <svg
                    class="w-5 h-5 text-gray-300 mx-auto mb-1.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                    />
                  </svg>
                  <p class="text-xs text-gray-400">Klik untuk unggah favicon</p>
                </div>
              </div>
            </div>

            <div class="flex justify-end mt-6">
              <button @click="save" class="btn-primary">
                Simpan Perubahan
              </button>
            </div>
          </div>
        </div>

        <!-- ════ LELANG ════ -->
        <div v-if="activeTab === 'lelang'" class="space-y-5">
          <!-- Default config -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">
              Konfigurasi Default Lelang
            </h2>
            <p class="text-xs text-gray-400 mb-6">
              Pengaturan awal yang diterapkan pada setiap lelang baru.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Durasi Lelang Default</label
                >
                <div class="relative">
                  <select
                    v-model="auction.defaultDuration"
                    class="field appearance-none pr-8"
                  >
                    <option
                      v-for="opt in durationOptions"
                      :key="opt.value"
                      :value="opt.value"
                    >
                      {{ opt.label }}
                    </option>
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
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Kelipatan Bid Minimum</label
                >
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none"
                    >Rp</span
                  >
                  <input
                    v-model="auction.minBidIncrement"
                    type="number"
                    class="field pl-8"
                    placeholder="100000"
                  />
                </div>
                <p class="text-xs text-gray-400 mt-1">
                  {{ formatRp(auction.minBidIncrement) }}
                </p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Jumlah Maksimum Foto</label
                >
                <input
                  v-model="auction.maxPhotos"
                  type="number"
                  class="field"
                  placeholder="10"
                />
              </div>
            </div>

            <!-- Approval toggle -->
            <div class="mt-5 pt-5 border-t border-gray-100">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <p class="text-sm font-medium text-black">
                    Persetujuan Lelang
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5 max-w-sm">
                    Lelang baru harus diverifikasi administrator sebelum
                    dipublikasikan.
                  </p>
                </div>
                <button
                  @click="auction.requireApproval = !auction.requireApproval"
                  :class="[
                    'toggle',
                    auction.requireApproval ? 'bg-black' : 'bg-gray-200',
                  ]"
                >
                  <span
                    :class="[
                      'toggle-dot',
                      auction.requireApproval
                        ? 'translate-x-5'
                        : 'translate-x-1',
                    ]"
                  ></span>
                </button>
              </div>
            </div>
          </div>

          <!-- Anti-sniping -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <div class="flex items-start justify-between gap-4 mb-5">
              <div>
                <h2 class="text-sm font-bold text-black mb-1">
                  Fitur Anti-Sniping
                </h2>
                <p class="text-xs text-gray-400">
                  Perpanjang waktu lelang otomatis jika ada bid di detik-detik
                  akhir.
                </p>
              </div>
              <button
                @click="
                  auction.antiSnipingEnabled = !auction.antiSnipingEnabled
                "
                :class="[
                  'toggle',
                  auction.antiSnipingEnabled ? 'bg-black' : 'bg-gray-200',
                ]"
              >
                <span
                  :class="[
                    'toggle-dot',
                    auction.antiSnipingEnabled
                      ? 'translate-x-5'
                      : 'translate-x-1',
                  ]"
                ></span>
              </button>
            </div>
            <div
              v-if="auction.antiSnipingEnabled"
              class="grid grid-cols-2 gap-5"
            >
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Batas Waktu (detik)</label
                >
                <input
                  v-model="auction.antiSnipingWindow"
                  type="number"
                  class="field"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Durasi Perpanjangan (menit)</label
                >
                <input
                  v-model="auction.antiSnipingExtension"
                  type="number"
                  class="field"
                />
              </div>
              <div class="col-span-2">
                <div
                  class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100"
                >
                  <p class="text-xs text-gray-600 leading-relaxed">
                    Apabila terdapat penawaran pada
                    <strong class="text-black"
                      >{{ auction.antiSnipingWindow }} detik</strong
                    >
                    terakhir, waktu lelang akan diperpanjang otomatis selama
                    <strong class="text-black"
                      >{{ auction.antiSnipingExtension }} menit</strong
                    >.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Buy Now -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <div class="flex items-start justify-between gap-4 mb-5">
              <div>
                <h2 class="text-sm font-bold text-black mb-1">Fitur Buy Now</h2>
                <p class="text-xs text-gray-400">
                  Pembeli dapat langsung memenangkan lelang dengan harga yang
                  telah ditentukan.
                </p>
              </div>
              <button
                @click="auction.buyNowEnabled = !auction.buyNowEnabled"
                :class="[
                  'toggle',
                  auction.buyNowEnabled ? 'bg-black' : 'bg-gray-200',
                ]"
              >
                <span
                  :class="[
                    'toggle-dot',
                    auction.buyNowEnabled ? 'translate-x-5' : 'translate-x-1',
                  ]"
                ></span>
              </button>
            </div>
            <div
              v-if="auction.buyNowEnabled"
              class="pt-4 border-t border-gray-100"
            >
              <div class="flex items-start justify-between gap-4">
                <div>
                  <p class="text-sm font-medium text-black">
                    Izinkan Penjual Menentukan Harga Buy Now
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Penjual dapat menetapkan harga beli langsung pada lelang
                    mereka.
                  </p>
                </div>
                <button
                  @click="auction.sellerSetBuyNow = !auction.sellerSetBuyNow"
                  :class="[
                    'toggle',
                    auction.sellerSetBuyNow ? 'bg-black' : 'bg-gray-200',
                  ]"
                >
                  <span
                    :class="[
                      'toggle-dot',
                      auction.sellerSetBuyNow
                        ? 'translate-x-5'
                        : 'translate-x-1',
                    ]"
                  ></span>
                </button>
              </div>
            </div>
          </div>

          <div class="flex justify-end">
            <button @click="save" class="btn-primary">Simpan Perubahan</button>
          </div>
        </div>

        <!-- ════ REALTIME ════ -->
        <div v-if="activeTab === 'realtime'" class="space-y-5">
          <!-- Server status -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">Laravel Reverb</h2>
            <p class="text-xs text-gray-400 mb-6">
              Status dan konfigurasi layanan komunikasi realtime.
            </p>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
              <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-gray-400 mb-1">Status Server</p>
                <div class="flex items-center gap-2">
                  <span
                    class="live-dot w-2 h-2 rounded-full bg-black inline-block"
                  ></span>
                  <p class="text-sm font-bold text-black">
                    {{ realtime.serverStatus }}
                  </p>
                </div>
              </div>
              <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-gray-400 mb-1">Koneksi Aktif</p>
                <p class="text-sm font-bold text-black">
                  {{ realtime.activeConnections }} Koneksi
                </p>
              </div>
              <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-gray-400 mb-1">Broadcast Driver</p>
                <p class="text-sm font-bold text-black">
                  {{ realtime.broadcastDriver }}
                </p>
              </div>
            </div>

            <!-- Channel monitoring -->
            <h3
              class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3"
            >
              Channel Monitoring
            </h3>
            <div class="space-y-2.5">
              <div
                v-for="ch in realtime.channels"
                :key="ch.name"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100"
              >
                <div class="flex items-center gap-3">
                  <span
                    class="live-dot w-1.5 h-1.5 rounded-full bg-black inline-block"
                  ></span>
                  <p class="text-sm font-medium text-black">{{ ch.name }}</p>
                </div>
                <span class="text-xs text-gray-400 font-medium"
                  >{{ ch.count }} aktif</span
                >
              </div>
            </div>

            <div class="mt-5 pt-5 border-t border-gray-100">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <p class="text-sm font-medium text-black">Presence Channel</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Menampilkan jumlah pengguna yang sedang melihat halaman
                    lelang secara realtime.
                  </p>
                </div>
                <button
                  @click="
                    realtime.presenceChannelEnabled =
                      !realtime.presenceChannelEnabled
                  "
                  :class="[
                    'toggle',
                    realtime.presenceChannelEnabled
                      ? 'bg-black'
                      : 'bg-gray-200',
                  ]"
                >
                  <span
                    :class="[
                      'toggle-dot',
                      realtime.presenceChannelEnabled
                        ? 'translate-x-5'
                        : 'translate-x-1',
                    ]"
                  ></span>
                </button>
              </div>
            </div>

            <div class="flex justify-end mt-5">
              <button
                class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:border-black hover:text-black transition-all"
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
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                  />
                </svg>
                Restart Realtime Service
              </button>
            </div>
          </div>
        </div>

        <!-- ════ KEAMANAN ════ -->
        <div v-if="activeTab === 'keamanan'" class="space-y-5">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">Autentikasi</h2>
            <p class="text-xs text-gray-400 mb-6">
              Konfigurasi keamanan login dan sesi pengguna.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Masa Berlaku Login (hari)</label
                >
                <input
                  v-model="security.sessionExpiry"
                  type="number"
                  class="field"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Batas Percobaan Login</label
                >
                <input
                  v-model="security.maxLoginAttempts"
                  type="number"
                  class="field"
                />
                <p class="text-xs text-gray-400 mt-1">
                  Akun akan dikunci setelah {{ security.maxLoginAttempts }} kali
                  percobaan gagal.
                </p>
              </div>
            </div>

            <div class="space-y-4 pt-5 border-t border-gray-100">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <p class="text-sm font-medium text-black">Reset Password</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Izinkan pengguna melakukan reset password melalui email.
                  </p>
                </div>
                <button
                  @click="
                    security.allowPasswordReset = !security.allowPasswordReset
                  "
                  :class="[
                    'toggle',
                    security.allowPasswordReset ? 'bg-black' : 'bg-gray-200',
                  ]"
                >
                  <span
                    :class="[
                      'toggle-dot',
                      security.allowPasswordReset
                        ? 'translate-x-5'
                        : 'translate-x-1',
                    ]"
                  ></span>
                </button>
              </div>
              <div
                class="flex items-start justify-between gap-4 pt-4 border-t border-gray-50"
              >
                <div>
                  <p class="text-sm font-medium text-black">Verifikasi Email</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Pengguna baru wajib memverifikasi email sebelum menggunakan
                    platform.
                  </p>
                </div>
                <button
                  @click="
                    security.requireEmailVerification =
                      !security.requireEmailVerification
                  "
                  :class="[
                    'toggle',
                    security.requireEmailVerification
                      ? 'bg-black'
                      : 'bg-gray-200',
                  ]"
                >
                  <span
                    :class="[
                      'toggle-dot',
                      security.requireEmailVerification
                        ? 'translate-x-5'
                        : 'translate-x-1',
                    ]"
                  ></span>
                </button>
              </div>
            </div>
          </div>

          <!-- Role & Access -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">
              Role dan Hak Akses
            </h2>
            <p class="text-xs text-gray-400 mb-5">
              Role yang tersedia pada platform.
            </p>
            <div class="space-y-3">
              <div
                v-for="role in roles"
                :key="role.name"
                :class="[
                  'flex items-center gap-4 p-4 rounded-xl border',
                  role.dark
                    ? 'bg-black border-black'
                    : 'bg-gray-50 border-gray-100',
                ]"
              >
                <div
                  :class="[
                    'w-9 h-9 rounded-xl flex items-center justify-center shrink-0',
                    role.dark
                      ? 'bg-white/15'
                      : 'bg-white border border-gray-200',
                  ]"
                >
                  <svg
                    class="w-4 h-4"
                    :class="role.dark ? 'text-white' : 'text-gray-700'"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      :d="role.icon"
                    />
                  </svg>
                </div>
                <div>
                  <p
                    class="text-sm font-semibold"
                    :class="role.dark ? 'text-white' : 'text-black'"
                  >
                    {{ role.name }}
                  </p>
                  <p
                    class="text-xs mt-0.5"
                    :class="role.dark ? 'text-white/50' : 'text-gray-400'"
                  >
                    {{ role.desc }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end">
            <button @click="save" class="btn-primary">Simpan Perubahan</button>
          </div>
        </div>

        <!-- ════ NOTIFIKASI ════ -->
        <div v-if="activeTab === 'notifikasi'" class="space-y-5">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">Notifikasi Sistem</h2>
            <p class="text-xs text-gray-400 mb-6">
              Atur jenis notifikasi yang dikirimkan kepada pengguna.
            </p>

            <div class="space-y-4">
              <div
                v-for="(item, key) in {
                  outbid: 'Notifikasi Outbid',
                  winner: 'Notifikasi Pemenang',
                  auctionEnded: 'Notifikasi Lelang Berakhir',
                  adminAlert: 'Notifikasi Admin',
                }"
                :key="key"
                class="flex items-center justify-between py-3.5 border-b border-gray-50 last:border-0"
              >
                <div>
                  <p class="text-sm font-medium text-black">{{ item }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    {{
                      key === "outbid"
                        ? "Dikirim saat penawaran pengguna dilampaui."
                        : key === "winner"
                          ? "Dikirim saat pengguna memenangkan lelang."
                          : key === "auctionEnded"
                            ? "Dikirim saat lelang yang diikuti berakhir."
                            : "Dikirim ke administrator untuk aktivitas penting."
                    }}
                  </p>
                </div>
                <button
                  @click="notifications[key] = !notifications[key]"
                  :class="[
                    'toggle',
                    notifications[key] ? 'bg-black' : 'bg-gray-200',
                  ]"
                >
                  <span
                    :class="[
                      'toggle-dot',
                      notifications[key] ? 'translate-x-5' : 'translate-x-1',
                    ]"
                  ></span>
                </button>
              </div>
            </div>
          </div>

          <!-- Method -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">Metode Notifikasi</h2>
            <p class="text-xs text-gray-400 mb-5">
              Pilih saluran pengiriman notifikasi.
            </p>
            <div class="space-y-3">
              <label
                class="flex items-center gap-3 p-3.5 bg-gray-50 rounded-xl border border-gray-100 cursor-pointer hover:border-gray-300 transition-colors"
              >
                <input
                  type="checkbox"
                  v-model="notifications.methodInApp"
                  class="w-4 h-4 accent-black rounded"
                />
                <div>
                  <p class="text-sm font-medium text-black">Dalam Aplikasi</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Notifikasi muncul langsung di dalam platform.
                  </p>
                </div>
              </label>
              <label
                class="flex items-center gap-3 p-3.5 bg-gray-50 rounded-xl border border-gray-100 cursor-pointer hover:border-gray-300 transition-colors"
              >
                <input
                  type="checkbox"
                  v-model="notifications.methodEmail"
                  class="w-4 h-4 accent-black rounded"
                />
                <div>
                  <p class="text-sm font-medium text-black">Email</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Notifikasi dikirim ke alamat email pengguna.
                  </p>
                </div>
              </label>
            </div>
          </div>

          <div class="flex justify-end">
            <button @click="save" class="btn-primary">Simpan Perubahan</button>
          </div>
        </div>

        <!-- ════ PENYIMPANAN ════ -->
        <div v-if="activeTab === 'penyimpanan'" class="space-y-5">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">Penyimpanan File</h2>
            <p class="text-xs text-gray-400 mb-6">
              Konfigurasi penyimpanan foto dan file yang diunggah pengguna.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Storage Driver</label
                >
                <div class="relative">
                  <select
                    v-model="storage.driver"
                    class="field appearance-none pr-8"
                  >
                    <option
                      v-for="opt in storageOptions"
                      :key="opt.value"
                      :value="opt.value"
                    >
                      {{ opt.label }}
                    </option>
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
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Maksimum Ukuran File (MB)</label
                >
                <input
                  v-model="storage.maxFileSize"
                  type="number"
                  class="field"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-3"
                >Format File yang Diizinkan</label
              >
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="fmt in ['jpg', 'jpeg', 'png', 'webp']"
                  :key="fmt"
                  @click="toggleFormat(fmt)"
                  :class="[
                    'px-4 py-1.5 rounded-lg text-xs font-semibold uppercase border transition-all',
                    storage.allowedFormats.includes(fmt)
                      ? 'bg-black border-black text-white'
                      : 'bg-gray-50 border-gray-200 text-gray-500 hover:border-gray-400',
                  ]"
                >
                  {{ fmt }}
                </button>
              </div>
              <p class="text-xs text-gray-400 mt-2">
                Digunakan untuk menyimpan foto barang yang diunggah pengguna.
              </p>
            </div>

            <div class="flex justify-end mt-6">
              <button @click="save" class="btn-primary">
                Simpan Perubahan
              </button>
            </div>
          </div>
        </div>

        <!-- ════ BACKUP ════ -->
        <div v-if="activeTab === 'backup'" class="space-y-5">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">Backup Database</h2>
            <p class="text-xs text-gray-400 mb-6">
              Kelola jadwal dan proses backup data platform.
            </p>

            <div
              class="flex items-start justify-between gap-4 pb-5 border-b border-gray-100 mb-5"
            >
              <div>
                <p class="text-sm font-medium text-black">Backup Otomatis</p>
                <p class="text-xs text-gray-400 mt-0.5">
                  Sistem akan membuat backup secara otomatis sesuai jadwal yang
                  ditentukan.
                </p>
              </div>
              <button
                @click="backup.autoBackup = !backup.autoBackup"
                :class="[
                  'toggle',
                  backup.autoBackup ? 'bg-black' : 'bg-gray-200',
                ]"
              >
                <span
                  :class="[
                    'toggle-dot',
                    backup.autoBackup ? 'translate-x-5' : 'translate-x-1',
                  ]"
                ></span>
              </button>
            </div>

            <div v-if="backup.autoBackup" class="mb-5">
              <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                >Frekuensi Backup</label
              >
              <div class="relative w-48">
                <select
                  v-model="backup.frequency"
                  class="field appearance-none pr-8"
                >
                  <option
                    v-for="opt in freqOptions"
                    :key="opt.value"
                    :value="opt.value"
                  >
                    {{ opt.label }}
                  </option>
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

            <!-- Last backup info -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 mb-5">
              <div class="flex items-center gap-3">
                <div
                  class="w-8 h-8 bg-white rounded-lg border border-gray-200 flex items-center justify-center shrink-0"
                >
                  <svg
                    class="w-4 h-4 text-gray-600"
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
                </div>
                <div>
                  <p class="text-xs font-semibold text-black">
                    Backup Terakhir
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    {{ backup.lastBackup }}
                  </p>
                </div>
              </div>
            </div>

            <div class="flex gap-3">
              <button
                class="flex items-center gap-2 px-4 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
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
                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                  />
                </svg>
                Buat Backup Sekarang
              </button>
              <button
                class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:border-black hover:text-black transition-all"
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
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                  />
                </svg>
                Unduh Backup
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

.card-lift {
  transition:
    transform 0.25s ease,
    box-shadow 0.25s ease;
}
.card-lift:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
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
  transition:
    opacity 0.25s ease,
    transform 0.25s ease;
}
.fade-modal-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}
.fade-modal-enter-from {
  opacity: 0;
  transform: translateY(-8px);
}
.fade-modal-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
