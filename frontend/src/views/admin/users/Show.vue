<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

// ─── User data (ganti dengan API call) ───────────────────────
const user = ref({
  name: "I Putu Arya Pratama",
  username: "putuarya",
  email: "putuarya@gmail.com",
  phone: "+62 812-0001-0001",
  address: "Denpasar, Bali",
  joinedAt: "10 Januari 2026",
  lastLogin: "15 Juni 2026, 20.15 WITA",
  role: "Kolektor",
  status: "active",
});

const profileInfo = computed(() => [
  {
    label: "Email",
    value: user.value.email,
    icon: "M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
  },
  {
    label: "Telepon",
    value: user.value.phone,
    icon: "M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z",
  },
  {
    label: "Alamat",
    value: user.value.address,
    icon: "M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z",
  },
  {
    label: "Bergabung",
    value: user.value.joinedAt,
    icon: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
  },
  {
    label: "Terakhir Login",
    value: user.value.lastLogin,
    icon: "M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1",
  },
]);

// ─── Stats ───────────────────────────────────────────────────
const userStats = [
  {
    label: "Total Lelang",
    value: "18",
    dark: false,
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
  },
  {
    label: "Total Penawaran",
    value: "52",
    dark: true,
    icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    label: "Lelang Menang",
    value: "6",
    dark: false,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    label: "Watchlist",
    value: "15",
    dark: false,
    icon: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z",
  },
  {
    label: "Total Nilai Bid",
    value: "Rp 125 Jt",
    dark: false,
    icon: "M13 7h8m0 0V3m0 4l-8 8-4-4-6 6",
  },
  {
    label: "Total Nilai Jual",
    value: "Rp 87 Jt",
    dark: false,
    icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
  },
];

const achievements = [
  {
    label: "Lelang Dimenangkan",
    value: "6",
    icon: "M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z",
  },
  {
    label: "Lelang Terjual",
    value: "15",
    icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10",
  },
  {
    label: "Total Bid Berhasil",
    value: "52",
    icon: "M13 10V3L4 14h7v7l9-11h-7z",
  },
];

// ─── Alerts ──────────────────────────────────────────────────
const alerts = [
  {
    text: "Tidak ditemukan aktivitas mencurigakan pada akun ini.",
    dark: false,
    icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
  },
  {
    text: "Terdapat 1 laporan pengguna terkait akun ini yang sedang ditinjau.",
    dark: true,
    icon: "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
];

// ─── Tabs ────────────────────────────────────────────────────
const tabs = [
  { label: "Aktivitas Terbaru", value: "activity" },
  { label: "Riwayat Lelang", value: "auctions" },
  { label: "Riwayat Penawaran", value: "bids" },
  { label: "Watchlist", value: "watchlist" },
  { label: "Login", value: "login" },
  { label: "Aktivitas Sistem", value: "system" },
];
const activeTab = ref("activity");

// ─── Tab data ────────────────────────────────────────────────
const recentActivities = [
  {
    text: 'Mengikuti lelang "Patung Garuda Bali"',
    time: "15 Jun 2026, 20.10 WITA",
    dark: false,
    icon: "M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z",
  },
  {
    text: "Melakukan penawaran sebesar Rp 12.500.000",
    time: "15 Jun 2026, 19.25 WITA",
    dark: true,
    icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    text: 'Menambahkan "Harmoni Semesta" ke Watchlist',
    time: "15 Jun 2026, 18.30 WITA",
    dark: false,
    icon: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z",
  },
  {
    text: 'Membuat lelang baru "Lukisan Bali Klasik"',
    time: "14 Jun 2026, 10.00 WITA",
    dark: false,
    icon: "M12 4v16m8-8H4",
  },
  {
    text: 'Memenangkan lelang "Topeng Barong Antik"',
    time: "12 Jun 2026, 20.00 WITA",
    dark: true,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
];

const auctionMiniStats = [
  { label: "Total Lelang", value: "18" },
  { label: "Lelang Aktif", value: "3" },
  { label: "Selesai", value: "15" },
];

const auctionHistory = [
  {
    id: 1,
    name: "Lukisan Bali Klasik Tahun 1980",
    image:
      "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=100&q=80",
    startPrice: 5000000,
    finalPrice: 12500000,
    status: "ended",
  },
  {
    id: 2,
    name: "Patung Garuda Bali",
    image:
      "https://images.unsplash.com/photo-1578926288207-32356f3e5e93?w=100&q=80",
    startPrice: 8000000,
    finalPrice: 15000000,
    status: "active",
  },
  {
    id: 3,
    name: "Topeng Barong Antik",
    image:
      "https://images.unsplash.com/photo-1567359781514-3b964e2b04d6?w=100&q=80",
    startPrice: 3000000,
    finalPrice: 7500000,
    status: "ended",
  },
  {
    id: 4,
    name: "Harmoni Semesta",
    image:
      "https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=100&q=80",
    startPrice: 7500000,
    finalPrice: 7500000,
    status: "upcoming",
  },
];

const bidMiniStats = [
  { label: "Total Bid", value: "52" },
  { label: "Bid Menang", value: "6" },
  { label: "Bid Kalah", value: "31" },
  { label: "Outbid", value: "15" },
];

const bidHistory = [
  {
    id: 1,
    name: "Patung Garuda Bali",
    amount: 12500000,
    status: "leading",
    time: "15 Jun 2026, 19.25 WITA",
  },
  {
    id: 2,
    name: "Topeng Barong Antik",
    amount: 7500000,
    status: "won",
    time: "12 Jun 2026, 20.00 WITA",
  },
  {
    id: 3,
    name: "Lukisan Bali Klasik",
    amount: 10000000,
    status: "outbid",
    time: "10 Jun 2026, 18.45 WITA",
  },
  {
    id: 4,
    name: "Ukiran Kayu Garuda",
    amount: 14000000,
    status: "lost",
    time: "8 Jun 2026, 17.00 WITA",
  },
  {
    id: 5,
    name: "Harmoni Semesta",
    amount: 9500000,
    status: "outbid",
    time: "7 Jun 2026, 15.30 WITA",
  },
];

const watchlistItems = [
  {
    id: 1,
    name: "Patung Garuda Bali",
    image:
      "https://images.unsplash.com/photo-1578926288207-32356f3e5e93?w=100&q=80",
    category: "Patung",
    currentPrice: 15000000,
    status: "active",
  },
  {
    id: 2,
    name: "Lukisan Tradisional Bali",
    image:
      "https://images.unsplash.com/photo-1541701494587-cb58502866ab?w=100&q=80",
    category: "Lukisan",
    currentPrice: 9000000,
    status: "active",
  },
  {
    id: 3,
    name: "Sunrise Penida",
    image:
      "https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=100&q=80",
    category: "Foto",
    currentPrice: 5000000,
    status: "upcoming",
  },
];

const loginHistory = [
  {
    device: "Windows 11",
    browser: "Google Chrome",
    ip: "103.xxx.xxx.xxx",
    time: "15 Jun 2026, 20.15 WITA",
    icon: "M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
  },
  {
    device: "Android",
    browser: "Chrome Mobile",
    ip: "103.xxx.xxx.xxx",
    time: "14 Jun 2026, 09.30 WITA",
    icon: "M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z",
  },
  {
    device: "MacOS",
    browser: "Safari",
    ip: "202.xxx.xxx.xxx",
    time: "13 Jun 2026, 14.00 WITA",
    icon: "M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z",
  },
];

const systemActivity = [
  {
    text: "Akun berhasil dibuat",
    time: "10 Jan 2026, 08.00 WITA",
    dark: false,
    icon: "M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z",
  },
  {
    text: "Membuat lelang pertama",
    time: "15 Jan 2026, 14.30 WITA",
    dark: false,
    icon: "M12 4v16m8-8H4",
  },
  {
    text: "Memenangkan lelang pertama",
    time: "20 Jan 2026, 20.00 WITA",
    dark: true,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    text: "Role diubah menjadi Kolektor oleh Admin",
    time: "10 Mar 2026, 09.15 WITA",
    dark: false,
    icon: "M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z",
  },
  {
    text: "Total 50 penawaran tercapai",
    time: "1 Jun 2026, 16.00 WITA",
    dark: false,
    icon: "M13 10V3L4 14h7v7l9-11h-7z",
  },
];

const notifications = [
  {
    text: "Tidak ditemukan aktivitas mencurigakan pada akun ini.",
    dark: false,
    icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
  },
  {
    text: "Pengguna memiliki reputasi baik di komunitas platform.",
    dark: false,
    icon: "M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z",
  },
  {
    text: "Terdapat 1 laporan pengguna terkait akun ini yang sedang ditinjau.",
    dark: true,
    icon: "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
];

// ─── Modals ──────────────────────────────────────────────────
const activeModal = ref(null);
const selectedRole = ref(user.value.role);
const roles = ["User", "Kolektor", "Seniman", "Admin", "Super Admin"];

const genericModalTitle = computed(() => {
  if (activeModal.value === "resetPassword") return "Reset Password";
  if (activeModal.value === "sendWarning") return "Kirim Peringatan";
  return "";
});
const genericModalDesc = computed(() => {
  if (activeModal.value === "resetPassword")
    return `Link reset password akan dikirimkan ke email ${user.value.email}. Pengguna harus mengatur ulang kata sandi sebelum dapat login kembali.`;
  if (activeModal.value === "sendWarning")
    return `Peringatan resmi akan dikirimkan ke ${user.value.name}. Pengguna akan menerima notifikasi melalui email dan platform.`;
  return "";
});

function openModal(name) {
  activeModal.value = name;
}

function handleRoleChange() {
  user.value.role = selectedRole.value;
  activeModal.value = null;
}
function handleStatusChange(status) {
  user.value.status = status;
  activeModal.value = null;
}
function handleDelete() {
  activeModal.value = null;
  router.push("/admin/users");
}

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

function statusBadge(status) {
  return (
    {
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
    }[status] ?? {
      class: "bg-gray-100 text-gray-400",
      dot: "bg-gray-300",
      label: status,
    }
  );
}

function auctionStatusBadge(status) {
  return (
    {
      active: {
        class: "bg-gray-100 text-gray-800",
        dot: "bg-gray-700",
        label: "Aktif",
      },
      upcoming: {
        class: "bg-gray-100 text-gray-500",
        dot: "bg-gray-400",
        label: "Akan Datang",
      },
      ended: {
        class: "bg-black text-white",
        dot: "bg-white",
        label: "Selesai",
      },
    }[status] ?? {
      class: "bg-gray-100 text-gray-400",
      dot: "bg-gray-300",
      label: status,
    }
  );
}

function bidStatusBadge(status) {
  return (
    {
      leading: {
        class: "bg-gray-100 text-gray-800",
        dot: "bg-gray-800",
        label: "Memimpin",
      },
      won: { class: "bg-black text-white", dot: "bg-white", label: "Menang" },
      outbid: {
        class: "bg-gray-100 text-gray-500",
        dot: "bg-gray-400",
        label: "Outbid",
      },
      lost: {
        class: "bg-gray-100 text-gray-400",
        dot: "bg-gray-300",
        label: "Kalah",
      },
    }[status] ?? {
      class: "bg-gray-100 text-gray-400",
      dot: "bg-gray-300",
      label: status,
    }
  );
}
</script>

<template>
  <div class="flex-1 px-8 py-8 min-h-screen bg-gray-50 font-sans">
    <!-- ═══════════════════ BREADCRUMB + HEADER ═══════════════════ -->
    <div class="mb-8">
      <div class="flex items-center gap-2 text-xs text-gray-400 mb-3">
        <router-link
          to="/admin/dashboard"
          class="hover:text-black transition-colors"
          >Dashboard</router-link
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
        <router-link
          to="/admin/users"
          class="hover:text-black transition-colors"
          >Manajemen Pengguna</router-link
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
        <span class="text-gray-600 font-medium">Detail Pengguna</span>
      </div>
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-black tracking-tight">
            Detail Pengguna
          </h1>
          <p class="text-gray-500 text-sm mt-2 max-w-2xl leading-relaxed">
            Lihat informasi lengkap pengguna, aktivitas lelang, riwayat
            penawaran, serta statistik penggunaan platform untuk memastikan
            aktivitas berjalan sesuai ketentuan.
          </p>
        </div>
        <button
          @click="$router.back()"
          class="shrink-0 flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-600 hover:border-black hover:text-black transition-all duration-200 font-medium"
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
              d="M11 17l-5-5m0 0l5-5m-5 5h12"
            />
          </svg>
          Kembali
        </button>
      </div>
    </div>

    <!-- ═══════════════════ ALERT BANNERS ═══════════════════ -->
    <div class="space-y-2 mb-6">
      <div
        v-for="alert in alerts"
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
      </div>
    </div>

    <!-- ═══════════════════ MAIN GRID ═══════════════════ -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- ── LEFT COL: Profile + Actions ── -->
      <div class="space-y-4">
        <!-- Profile card -->
        <div
          class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
        >
          <!-- Cover -->
          <div class="h-24 bg-black relative">
            <div
              class="absolute inset-0 opacity-10"
              style="
                background: repeating-linear-gradient(
                    45deg,
                    #fff 0,
                    #fff 1px,
                    transparent 0,
                    transparent 50%
                  )
                  0/12px 12px;
              "
            ></div>
          </div>
          <!-- Avatar -->
          <div class="px-6 pb-6">
            <div class="flex items-end justify-between -mt-8 mb-4 relative">
              <div
                class="w-16 h-16 rounded-2xl bg-gray-900 flex items-center justify-center text-white text-2xl font-bold border-4 border-white shadow-sm shrink-0"
              >
                {{ initials(user.name) }}
              </div>
              <span
                :class="[
                  'flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium',
                  statusBadge(user.status).class,
                ]"
              >
                <span
                  class="w-1.5 h-1.5 rounded-full shrink-0"
                  :class="statusBadge(user.status).dot"
                ></span>
                {{ statusBadge(user.status).label }}
              </span>
            </div>
            <p class="font-bold text-lg text-black leading-tight">
              {{ user.name }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">@{{ user.username }}</p>
            <span
              class="inline-block mt-2 text-xs bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full font-medium"
              >{{ user.role }}</span
            >

            <div class="mt-5 space-y-2.5">
              <div
                v-for="info in profileInfo"
                :key="info.label"
                class="flex items-center gap-2.5"
              >
                <div
                  class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center shrink-0"
                >
                  <svg
                    class="w-3.5 h-3.5 text-gray-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      :d="info.icon"
                    />
                  </svg>
                </div>
                <div class="min-w-0">
                  <p class="text-xs text-gray-400">{{ info.label }}</p>
                  <p class="text-xs font-medium text-gray-800 truncate">
                    {{ info.value }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Admin actions -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <p
            class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-4"
          >
            Tindakan Administrator
          </p>
          <div class="space-y-2">
            <button
              @click="openModal('editRole')"
              class="w-full flex items-center gap-3 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:border-black hover:text-black transition-all duration-200 text-left"
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
                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                />
              </svg>
              Edit Profil
            </button>
            <button
              @click="openModal('editRole')"
              class="w-full flex items-center gap-3 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:border-black hover:text-black transition-all duration-200 text-left"
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
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
              Ubah Role
            </button>
            <button
              @click="openModal('resetPassword')"
              class="w-full flex items-center gap-3 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:border-black hover:text-black transition-all duration-200 text-left"
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
                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
                />
              </svg>
              Reset Password
            </button>
            <button
              @click="openModal('sendWarning')"
              class="w-full flex items-center gap-3 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:border-black hover:text-black transition-all duration-200 text-left"
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
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                />
              </svg>
              Kirim Peringatan
            </button>
            <div class="border-t border-gray-100 pt-2 mt-2 space-y-2">
              <button
                v-if="user.status === 'active'"
                @click="openModal('deactivate')"
                class="w-full flex items-center gap-3 px-4 py-2.5 border border-gray-200 text-gray-500 rounded-xl text-sm font-medium hover:border-gray-600 hover:text-gray-700 transition-all duration-200 text-left"
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
                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                  />
                </svg>
                Nonaktifkan Akun
              </button>
              <button
                v-else
                @click="openModal('activate')"
                class="w-full flex items-center gap-3 px-4 py-2.5 border border-gray-200 text-gray-500 rounded-xl text-sm font-medium hover:border-black hover:text-black transition-all duration-200 text-left"
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
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                Aktifkan Akun
              </button>
              <button
                @click="openModal('delete')"
                class="w-full flex items-center gap-3 px-4 py-2.5 border border-gray-200 text-gray-400 rounded-xl text-sm font-medium hover:border-gray-400 hover:text-gray-600 transition-all duration-200 text-left"
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
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
                Hapus Akun
              </button>
            </div>
          </div>
        </div>

        <!-- Achievements -->
        <div class="bg-black rounded-2xl p-5">
          <p
            class="text-white/50 text-xs uppercase tracking-widest font-semibold mb-4"
          >
            Prestasi
          </p>
          <div class="space-y-3">
            <div
              v-for="ach in achievements"
              :key="ach.label"
              class="flex items-center justify-between"
            >
              <div class="flex items-center gap-2.5">
                <div
                  class="w-7 h-7 bg-white/10 rounded-lg flex items-center justify-center"
                >
                  <svg
                    class="w-3.5 h-3.5 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      :d="ach.icon"
                    />
                  </svg>
                </div>
                <span class="text-white/70 text-xs">{{ ach.label }}</span>
              </div>
              <span class="text-white text-sm font-bold">{{ ach.value }}</span>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-white/10">
            <div class="flex items-center justify-between">
              <span class="text-white/50 text-xs">Tingkat Keaktifan</span>
              <span
                class="text-white text-xs font-semibold bg-white/10 px-2.5 py-1 rounded-full"
                >Sangat Aktif</span
              >
            </div>
          </div>
        </div>
      </div>

      <!-- ── RIGHT COL: Stats + Tabs ── -->
      <div class="xl:col-span-2 space-y-5">
        <!-- Stats grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div
            v-for="stat in userStats"
            :key="stat.label"
            :class="[
              'rounded-2xl px-5 py-5 border card-lift',
              stat.dark ? 'bg-black border-black' : 'bg-white border-gray-100',
            ]"
          >
            <div
              :class="[
                'w-8 h-8 rounded-xl flex items-center justify-center mb-3',
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
            <p
              class="text-xl font-bold"
              :class="stat.dark ? 'text-white' : 'text-black'"
            >
              {{ stat.value }}
            </p>
            <p
              class="text-xs mt-0.5"
              :class="stat.dark ? 'text-white/50' : 'text-gray-400'"
            >
              {{ stat.label }}
            </p>
          </div>
        </div>

        <!-- Tabs -->
        <div
          class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
        >
          <!-- Tab nav -->
          <div class="flex border-b border-gray-100 overflow-x-auto">
            <button
              v-for="tab in tabs"
              :key="tab.value"
              @click="activeTab = tab.value"
              :class="[
                'px-5 py-3.5 text-sm font-medium whitespace-nowrap transition-all duration-200 border-b-2',
                activeTab === tab.value
                  ? 'border-black text-black'
                  : 'border-transparent text-gray-400 hover:text-gray-700',
              ]"
            >
              {{ tab.label }}
            </button>
          </div>

          <!-- ── TAB: Aktivitas Terbaru ── -->
          <div v-if="activeTab === 'activity'" class="p-6">
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
                    'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5',
                    act.dark ? 'bg-black' : 'bg-gray-100',
                  ]"
                >
                  <svg
                    class="w-4 h-4"
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
                <div class="flex-1">
                  <p class="text-sm text-gray-700 leading-relaxed">
                    {{ act.text }}
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ act.time }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- ── TAB: Riwayat Lelang ── -->
          <div v-if="activeTab === 'auctions'">
            <!-- Mini stats -->
            <div
              class="grid grid-cols-3 divide-x divide-gray-100 border-b border-gray-100"
            >
              <div
                v-for="s in auctionMiniStats"
                :key="s.label"
                class="px-6 py-4 text-center"
              >
                <p class="text-xl font-bold text-black">{{ s.value }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ s.label }}</p>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-100">
                    <th
                      class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Nama Barang
                    </th>
                    <th
                      class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Harga Awal
                    </th>
                    <th
                      class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Harga Akhir
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
                    v-for="auction in auctionHistory"
                    :key="auction.id"
                    class="hover:bg-gray-50 transition-colors"
                  >
                    <td class="px-6 py-3.5">
                      <div class="flex items-center gap-3">
                        <div
                          class="w-9 h-9 rounded-xl bg-cover bg-center bg-gray-100 shrink-0"
                          :style="{
                            backgroundImage: `url('${auction.image}')`,
                          }"
                        ></div>
                        <p class="text-sm font-medium text-black">
                          {{ auction.name }}
                        </p>
                      </div>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                      <p class="text-sm text-gray-500">
                        {{ formatRp(auction.startPrice) }}
                      </p>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                      <p class="text-sm font-semibold text-black">
                        {{ formatRp(auction.finalPrice) }}
                      </p>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                      <span
                        :class="[
                          'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium',
                          auctionStatusBadge(auction.status).class,
                        ]"
                      >
                        <span
                          class="w-1.5 h-1.5 rounded-full"
                          :class="auctionStatusBadge(auction.status).dot"
                        ></span>
                        {{ auctionStatusBadge(auction.status).label }}
                      </span>
                    </td>
                    <td class="px-4 py-3.5">
                      <button
                        @click="$router.push(`/admin/auctions/${auction.id}`)"
                        class="text-xs text-gray-500 hover:text-black font-medium transition-colors whitespace-nowrap"
                      >
                        Lihat Detail
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ── TAB: Riwayat Penawaran ── -->
          <div v-if="activeTab === 'bids'">
            <!-- Mini stats -->
            <div
              class="grid grid-cols-4 divide-x divide-gray-100 border-b border-gray-100"
            >
              <div
                v-for="s in bidMiniStats"
                :key="s.label"
                class="px-4 py-4 text-center"
              >
                <p class="text-xl font-bold text-black">{{ s.value }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ s.label }}</p>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-100">
                    <th
                      class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Barang
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
                      class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Waktu
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr
                    v-for="bid in bidHistory"
                    :key="bid.id"
                    class="hover:bg-gray-50 transition-colors"
                  >
                    <td class="px-6 py-3.5">
                      <p class="text-sm font-medium text-black">
                        {{ bid.name }}
                      </p>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                      <p class="text-sm font-bold text-black">
                        {{ formatRp(bid.amount) }}
                      </p>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                      <span
                        :class="[
                          'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium',
                          bidStatusBadge(bid.status).class,
                        ]"
                      >
                        <span
                          class="w-1.5 h-1.5 rounded-full shrink-0"
                          :class="bidStatusBadge(bid.status).dot"
                        ></span>
                        {{ bidStatusBadge(bid.status).label }}
                      </span>
                    </td>
                    <td class="px-4 py-3.5">
                      <p class="text-xs text-gray-500">{{ bid.time }}</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ── TAB: Watchlist ── -->
          <div v-if="activeTab === 'watchlist'">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b border-gray-100">
                    <th
                      class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Nama Barang
                    </th>
                    <th
                      class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Kategori
                    </th>
                    <th
                      class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                      Harga Saat Ini
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
                    v-for="item in watchlistItems"
                    :key="item.id"
                    class="hover:bg-gray-50 transition-colors"
                  >
                    <td class="px-6 py-3.5">
                      <div class="flex items-center gap-3">
                        <div
                          class="w-9 h-9 rounded-xl bg-cover bg-center bg-gray-100 shrink-0"
                          :style="{ backgroundImage: `url('${item.image}')` }"
                        ></div>
                        <p class="text-sm font-medium text-black">
                          {{ item.name }}
                        </p>
                      </div>
                    </td>
                    <td class="px-4 py-3.5">
                      <span
                        class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full font-medium"
                        >{{ item.category }}</span
                      >
                    </td>
                    <td class="px-4 py-3.5 text-right">
                      <p class="text-sm font-semibold text-black">
                        {{ formatRp(item.currentPrice) }}
                      </p>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                      <span
                        :class="[
                          'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium',
                          auctionStatusBadge(item.status).class,
                        ]"
                      >
                        <span
                          class="w-1.5 h-1.5 rounded-full"
                          :class="auctionStatusBadge(item.status).dot"
                        ></span>
                        {{ auctionStatusBadge(item.status).label }}
                      </span>
                    </td>
                    <td class="px-4 py-3.5">
                      <button
                        @click="$router.push(`/admin/auctions/${item.id}`)"
                        class="text-xs text-gray-500 hover:text-black font-medium transition-colors whitespace-nowrap"
                      >
                        Lihat Detail
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ── TAB: Login History ── -->
          <div v-if="activeTab === 'login'" class="p-6">
            <div class="space-y-3">
              <div
                v-for="(log, i) in loginHistory"
                :key="i"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-9 h-9 bg-white rounded-xl border border-gray-100 flex items-center justify-center shrink-0"
                  >
                    <svg
                      class="w-4 h-4 text-gray-500"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        :d="log.icon"
                      />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-black">
                      {{ log.device }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ log.browser }} · {{ log.ip }}
                    </p>
                  </div>
                </div>
                <p
                  class="text-xs text-gray-500 text-right whitespace-nowrap ml-4"
                >
                  {{ log.time }}
                </p>
              </div>
            </div>
          </div>

          <!-- ── TAB: Aktivitas Sistem ── -->
          <div v-if="activeTab === 'system'" class="p-6">
            <div class="relative">
              <!-- Timeline line -->
              <div
                class="absolute left-[15px] top-0 bottom-0 w-px bg-gray-100"
              ></div>
              <div class="space-y-5">
                <div
                  v-for="(sys, i) in systemActivity"
                  :key="i"
                  class="flex items-start gap-4 relative"
                >
                  <div
                    :class="[
                      'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 z-10 border-2 border-white',
                      sys.dark ? 'bg-black' : 'bg-gray-100',
                    ]"
                  >
                    <svg
                      class="w-3.5 h-3.5"
                      :class="sys.dark ? 'text-white' : 'text-gray-500'"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        :d="sys.icon"
                      />
                    </svg>
                  </div>
                  <div class="flex-1 pb-1">
                    <p class="text-sm text-gray-700 leading-relaxed">
                      {{ sys.text }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ sys.time }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notifikasi & Laporan -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
          <h2 class="font-semibold text-sm text-black mb-4">
            Notifikasi & Laporan
          </h2>
          <div class="space-y-3">
            <div
              v-for="note in notifications"
              :key="note.text"
              class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50"
            >
              <div
                :class="[
                  'w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5',
                  note.dark ? 'bg-black' : 'bg-gray-200',
                ]"
              >
                <svg
                  class="w-3.5 h-3.5"
                  :class="note.dark ? 'text-white' : 'text-gray-600'"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    :d="note.icon"
                  />
                </svg>
              </div>
              <p class="text-sm text-gray-700 leading-relaxed">
                {{ note.text }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════ MODALS ═══════════════════ -->
    <transition name="fade-modal">
      <div
        v-if="activeModal"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="activeModal = null"
      >
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

        <!-- Edit Role -->
        <div
          v-if="activeModal === 'editRole'"
          class="relative bg-white rounded-2xl p-7 max-w-sm w-full shadow-xl"
        >
          <h3 class="font-bold text-lg text-black mb-1">Ubah Role</h3>
          <p class="text-gray-400 text-xs mb-5">
            Role saat ini:
            <span class="text-black font-semibold">{{ user.role }}</span>
          </p>
          <div class="space-y-2 mb-6">
            <label
              v-for="r in roles"
              :key="r"
              class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all duration-200"
              :class="
                selectedRole === r
                  ? 'border-black bg-gray-50'
                  : 'border-gray-200 hover:border-gray-400'
              "
            >
              <div
                :class="[
                  'w-4 h-4 rounded-full border-2 flex items-center justify-center',
                  selectedRole === r ? 'border-black' : 'border-gray-300',
                ]"
              >
                <div
                  v-if="selectedRole === r"
                  class="w-2 h-2 rounded-full bg-black"
                ></div>
              </div>
              <span
                class="text-sm font-medium text-black"
                @click="selectedRole = r"
                >{{ r }}</span
              >
            </label>
          </div>
          <div class="flex gap-3">
            <button
              @click="activeModal = null"
              class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleRoleChange"
              class="flex-1 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
            >
              Simpan Perubahan
            </button>
          </div>
        </div>

        <!-- Deactivate -->
        <div
          v-else-if="activeModal === 'deactivate'"
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
                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
              />
            </svg>
          </div>
          <h3 class="font-bold text-lg text-black mb-2">Nonaktifkan Akun</h3>
          <p class="text-gray-500 text-sm leading-relaxed mb-6">
            Apakah Anda yakin ingin menonaktifkan akun ini? Pengguna tidak akan
            dapat mengakses sistem sampai akun diaktifkan kembali.
          </p>
          <div class="flex gap-3">
            <button
              @click="activeModal = null"
              class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleStatusChange('blocked')"
              class="flex-1 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
            >
              Nonaktifkan
            </button>
          </div>
        </div>

        <!-- Activate -->
        <div
          v-else-if="activeModal === 'activate'"
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
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <h3 class="font-bold text-lg text-black mb-2">
            Aktifkan Kembali Akun
          </h3>
          <p class="text-gray-500 text-sm leading-relaxed mb-6">
            Apakah Anda yakin ingin mengaktifkan kembali akun
            <strong class="text-black">{{ user.name }}</strong
            >?
          </p>
          <div class="flex gap-3">
            <button
              @click="activeModal = null"
              class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleStatusChange('active')"
              class="flex-1 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
            >
              Aktifkan
            </button>
          </div>
        </div>

        <!-- Delete -->
        <div
          v-else-if="activeModal === 'delete'"
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
          <h3 class="font-bold text-lg text-black mb-2">Hapus Akun</h3>
          <p class="text-gray-500 text-sm leading-relaxed mb-6">
            Tindakan ini akan menghapus akun pengguna
            <strong class="text-black">{{ user.name }}</strong> secara permanen
            dan tidak dapat dibatalkan.
          </p>
          <div class="flex gap-3">
            <button
              @click="activeModal = null"
              class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleDelete"
              class="flex-1 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
            >
              Hapus Permanen
            </button>
          </div>
        </div>

        <!-- Reset Password / Send Warning (generic confirm) -->
        <div
          v-else
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
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <h3 class="font-bold text-lg text-black mb-2">
            {{ genericModalTitle }}
          </h3>
          <p class="text-gray-500 text-sm leading-relaxed mb-6">
            {{ genericModalDesc }}
          </p>
          <div class="flex gap-3">
            <button
              @click="activeModal = null"
              class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="activeModal = null"
              class="flex-1 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
            >
              Konfirmasi
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

.card-lift {
  transition:
    transform 0.25s ease,
    box-shadow 0.25s ease;
}
.card-lift:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
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
