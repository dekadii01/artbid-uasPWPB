<script setup>
import { ref, onMounted } from "vue";
import { useAuthStore } from "../../stores/auth";
import * as authApi from "../../api/auth";

// ─── Active section ───────────────────────────────────────────
const activeTab = ref("profil");

const tabs = [
  {
    label: "Profil",
    value: "profil",
    icon: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
  },
  {
    label: "Statistik",
    value: "statistik",
    icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
  },
  {
    label: "Prestasi",
    value: "prestasi",
    icon: "M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z",
  },
  {
    label: "Keamanan",
    value: "keamanan",
    icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
  },
  {
    label: "Preferensi",
    value: "preferensi",
    icon: "M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9",
  },
];

// ─── User data ────────────────────────────────────────────────
const user = ref({
  fullName: "",
  username: "",
  email: "",
  phone: "",
  address: "Jl. Raya Denpasar No. 45",
  city: "Denpasar",
  province: "Bali",
  bio: "Kolektor dan pecinta seni tradisional Bali sejak tahun 2018.",
  joinedAt: "10 Januari 2026",
  status: "active",
  location: "Denpasar, Bali",
});

const auth = useAuthStore();
const selectedFile = ref(null);
const errors = ref({});
const saving = ref(false);
const photoPreview = ref(null);

onMounted(async () => {
  try {
    await auth.fetchUser();
  } catch (err) {
    console.error("Gagal memuat data user:", err);
  }
  
  if (auth.user) {
    user.value.fullName = auth.user.full_name || "";
    user.value.username = auth.user.email ? auth.user.email.split("@")[0] : "user";
    user.value.email = auth.user.email || "";
    user.value.phone = auth.user.phone || "";
    
    if (auth.user.avatar) {
      photoPreview.value = auth.user.avatar.startsWith("http")
        ? auth.user.avatar
        : `http://localhost:8000/storage/${auth.user.avatar}`;
    }
    
    if (auth.user.created_at) {
      const date = new Date(auth.user.created_at);
      const options = { day: "numeric", month: "long", year: "numeric" };
      user.value.joinedAt = date.toLocaleDateString("id-ID", options);
    }
  }
});

// ─── Stats summary ────────────────────────────────────────────
const summaryStats = [
  {
    label: "Lelang Dibuat",
    value: "18",
    icon: "M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10",
    dark: false,
  },
  {
    label: "Total Penawaran",
    value: "52",
    icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
    dark: true,
  },
  {
    label: "Lelang Menang",
    value: "6",
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
    dark: false,
  },
  {
    label: "Watchlist",
    value: "15",
    icon: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z",
    dark: false,
  },
];

// ─── Recent activity ──────────────────────────────────────────
const recentActivity = [
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
    text: "Menambahkan barang ke Watchlist",
    time: "15 Jun 2026, 18.30 WITA",
    dark: false,
    icon: "M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z",
  },
  {
    text: "Membuat lelang baru",
    time: "14 Jun 2026, 10.00 WITA",
    dark: false,
    icon: "M12 4v16m8-8H4",
  },
];

// ─── Auction stats ────────────────────────────────────────────
const auctionStats = [
  { label: "Total Dibuat", value: "18" },
  { label: "Aktif", value: "3" },
  { label: "Selesai", value: "15" },
  { label: "Barang Terjual", value: "15" },
];

// ─── Bid stats ────────────────────────────────────────────────
const bidStats = [
  { label: "Total Bid", value: "52" },
  { label: "Bid Menang", value: "6" },
  { label: "Bid Kalah", value: "31" },
  { label: "Outbid", value: "15" },
];

// ─── Achievements ─────────────────────────────────────────────
const achievements = [
  {
    name: "Penawar Aktif",
    desc: "Melakukan lebih dari 50 penawaran.",
    icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
    unlocked: true,
  },
  {
    name: "Kolektor Seni",
    desc: "Memenangkan lebih dari 5 lelang.",
    icon: "M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z",
    unlocked: true,
  },
  {
    name: "Penjual Terpercaya",
    desc: "Berhasil menjual lebih dari 10 barang.",
    icon: "M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z",
    unlocked: true,
  },
  {
    name: "Anggota Aktif",
    desc: "Aktif menggunakan platform selama lebih dari 6 bulan.",
    icon: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
    unlocked: true,
  },
];

// ─── Account history ──────────────────────────────────────────
const accountHistory = [
  { label: "Bergabung Sejak", value: "10 Januari 2026" },
  { label: "Total Hari Aktif", value: "356 Hari" },
  { label: "Total Aktivitas", value: "128 Aktivitas" },
  { label: "Barang di Watchlist", value: "15 Barang" },
];

// ─── Preferences ─────────────────────────────────────────────
const prefs = ref({
  notifOutbid: true,
  notifWinner: true,
  notifAuctionEnd: true,
  newsletter: true,
});

// ─── Password modal ───────────────────────────────────────────
const showPasswordModal = ref(false);
const passwords = ref({ current: "", newPass: "", confirm: "" });

// ─── Photo upload ─────────────────────────────────────────────

function handlePhotoChange(e) {
  const file = e.target.files[0];
  if (!file) return;
  selectedFile.value = file;
  const reader = new FileReader();
  reader.onload = (ev) => (photoPreview.value = ev.target.result);
  reader.readAsDataURL(file);
}

// ─── Save toast ───────────────────────────────────────────────
const saved = ref(false);

async function save() {
  saving.value = true;
  errors.value = {};
  
  try {
    const formData = new FormData();
    
    // Pecah nama lengkap menjadi firstName & lastName
    const names = user.value.fullName.trim().split(/\s+/);
    const firstName = names[0] || "";
    const lastName = names.slice(1).join(" ") || "-";
    
    formData.append("firstName", firstName);
    formData.append("lastName", lastName);
    formData.append("email", user.value.email);
    formData.append("phone", user.value.phone);
    
    // Method spoofing agar Laravel membaca request ini sebagai PUT
    formData.append("_method", "PUT");
    
    // Lampirkan avatar jika ada file baru yang dipilih
    if (selectedFile.value) {
      formData.append("avatar", selectedFile.value);
    }
    
    // Lampirkan password baru jika diisi di modal keamanan
    if (passwords.value.newPass) {
      formData.append("password", passwords.value.newPass);
      formData.append("password_confirmation", passwords.value.confirm);
    }
    
    // Mengirim data ke API backend
    const { data } = await authApi.updateProfile(formData);
    
    // Sinkronisasi data user baru ke store Pinia agar navbar/halaman lain ikut terupdate
    auth.user = data.user;
    
    // Perbarui state lokal halaman dengan data terbaru dari database
    user.value.fullName = data.user.full_name || "";
    user.value.email = data.user.email || "";
    user.value.phone = data.user.phone || "";
    
    if (data.user.avatar) {
      photoPreview.value = data.user.avatar.startsWith("http")
        ? data.user.avatar
        : `http://localhost:8000/storage/${data.user.avatar}`;
    }
    
    // Reset file yang dipilih & inputan password
    selectedFile.value = null;
    passwords.value = { current: "", newPass: "", confirm: "" };
    
    // Tampilkan notifikasi toast sukses
    saved.value = true;
    setTimeout(() => (saved.value = false), 2500);
  } catch (err) {
    console.error("Gagal mengupdate profil:", err);
    if (err.response && err.response.data && err.response.data.errors) {
      errors.value = err.response.data.errors;
      const messages = Object.values(errors.value).flat().join("\n");
      alert(messages);
    } else {
      alert(err.response?.data?.message || "Terjadi kesalahan saat menyimpan profil.");
    }
  } finally {
    saving.value = false;
  }
}

// ─── Avatar helpers ───────────────────────────────────────────
function initials(name) {
  if (!name) return "U";
  return name
    .split(" ")
    .filter(Boolean)
    .map((w) => w[0])
    .slice(0, 2)
    .join("")
    .toUpperCase();
}
</script>

<template>
  <div
    class="flex-1 px-6 md:px-8 py-8 space-y-6 min-h-screen bg-gray-50 font-sans"
  >
    <!-- ═══════════════════ SAVE TOAST ═══════════════════ -->
    <transition name="slide-toast">
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
        Perubahan berhasil disimpan.
      </div>
    </transition>

    <!-- ═══════════════════ PROFILE HERO ═══════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <!-- Cover strip -->

      <div class="px-6 pb-6 pt-20">
        <div
          class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 -mt-10"
        >
          <!-- Avatar -->
          <div class="flex items-end gap-4">
            <div class="relative shrink-0">
              <div
                class="w-20 h-20 rounded-2xl border-4 border-white shadow-lg overflow-hidden bg-gray-900 flex items-center justify-center"
              >
                <img
                  v-if="photoPreview"
                  :src="photoPreview"
                  alt="avatar"
                  class="w-full h-full object-cover"
                />
                <span v-else class="text-white text-xl font-bold">{{
                  initials(user.fullName)
                }}</span>
              </div>
              <!-- Upload trigger -->
              <label
                class="absolute -bottom-1 -right-1 w-6 h-6 bg-black rounded-lg flex items-center justify-center cursor-pointer hover:bg-gray-700 transition-colors shadow"
              >
                <svg
                  class="w-3 h-3 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
                <input
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="handlePhotoChange"
                />
              </label>
            </div>
            <div class="mb-1">
              <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-black tracking-tight">
                  {{ user.fullName }}
                </h1>
                <span
                  class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full bg-gray-800 inline-block"
                  ></span>
                  Aktif
                </span>
              </div>
              <p class="text-sm text-gray-400 mt-0.5">
                @{{ user.username }} · {{ user.location }}
              </p>
            </div>
          </div>
          <!-- Meta right -->
          <div class="flex items-center gap-3 mb-1">
            <div class="text-right hidden md:block">
              <p class="text-xs text-gray-400">Bergabung sejak</p>
              <p class="text-sm font-semibold text-black">
                {{ user.joinedAt }}
              </p>
            </div>
            <button
              @click="activeTab = 'profil'"
              class="flex items-center gap-2 px-4 py-2 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
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
              Edit Profil
            </button>
          </div>
        </div>

        <!-- Bio -->
        <p
          v-if="user.bio"
          class="text-sm text-gray-500 mt-4 max-w-xl leading-relaxed"
        >
          {{ user.bio }}
        </p>

        <!-- Quick meta row -->
        <div class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-gray-100">
          <div class="flex items-center gap-1.5 text-xs text-gray-500">
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
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
              />
            </svg>
            {{ user.email }}
          </div>
          <div class="flex items-center gap-1.5 text-xs text-gray-500">
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
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
              />
            </svg>
            {{ user.phone }}
          </div>
          <div class="flex items-center gap-1.5 text-xs text-gray-500">
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
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
              />
            </svg>
            {{ user.location }}
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════ SUMMARY STATS ═══════════════════ -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
      <div
        v-for="stat in summaryStats"
        :key="stat.label"
        :class="[
          'rounded-2xl px-6 py-5 border card-lift',
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
          class="text-2xl font-bold"
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

    <!-- ═══════════════════ MAIN GRID ═══════════════════ -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- ── TAB CONTENT (2/3) ── -->
      <div class="xl:col-span-2 space-y-5">
        <!-- Tab nav -->
        <div
          class="bg-white rounded-2xl border border-gray-100 p-1.5 flex flex-wrap gap-1"
        >
          <button
            v-for="tab in tabs"
            :key="tab.value"
            @click="activeTab = tab.value"
            :class="[
              'flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-medium transition-all duration-200',
              activeTab === tab.value
                ? 'bg-black text-white'
                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800',
            ]"
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
                :d="tab.icon"
              />
            </svg>
            {{ tab.label }}
          </button>
        </div>

        <!-- ════ PROFIL ════ -->
        <div v-if="activeTab === 'profil'" class="space-y-5">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">Informasi Pribadi</h2>
            <p class="text-xs text-gray-400 mb-6">
              Perbarui data pribadi yang ditampilkan di profil kamu.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Nama Lengkap</label
                >
                <input v-model="user.fullName" type="text" class="field" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Username</label
                >
                <div class="relative">
                  <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none"
                    >@</span
                  >
                  <input
                    v-model="user.username"
                    type="text"
                    class="field pl-7"
                  />
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Email</label
                >
                <input v-model="user.email" type="email" class="field" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Nomor Telepon</label
                >
                <input v-model="user.phone" type="text" class="field" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Kota</label
                >
                <input v-model="user.city" type="text" class="field" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Provinsi</label
                >
                <input v-model="user.province" type="text" class="field" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Alamat</label
                >
                <textarea
                  v-model="user.address"
                  rows="2"
                  class="field resize-none"
                ></textarea>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                  >Bio Singkat</label
                >
                <textarea
                  v-model="user.bio"
                  rows="3"
                  class="field resize-none"
                  placeholder="Ceritakan sedikit tentang diri kamu..."
                ></textarea>
              </div>
            </div>

            <div class="flex justify-end mt-6">
              <button @click="save" class="btn-primary">
                Simpan Perubahan
              </button>
            </div>
          </div>
        </div>

        <!-- ════ STATISTIK ════ -->
        <div v-if="activeTab === 'statistik'" class="space-y-5">
          <!-- Auction stats -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">Statistik Lelang</h2>
            <p class="text-xs text-gray-400 mb-5">
              Performa kamu sebagai penjual di platform.
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
              <div
                v-for="s in auctionStats"
                :key="s.label"
                class="bg-gray-50 rounded-xl p-4 border border-gray-100"
              >
                <p class="text-2xl font-bold text-black">{{ s.value }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ s.label }}</p>
              </div>
            </div>
            <div
              class="bg-gray-50 rounded-xl p-4 border border-gray-100 flex items-center justify-between"
            >
              <div>
                <p class="text-xs text-gray-400 mb-0.5">Nilai Penjualan</p>
                <p class="text-lg font-bold text-black">Rp 87.000.000</p>
              </div>
              <div
                class="w-10 h-10 bg-black rounded-xl flex items-center justify-center"
              >
                <svg
                  class="w-5 h-5 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                  />
                </svg>
              </div>
            </div>
          </div>

          <!-- Bid stats -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">
              Statistik Penawaran
            </h2>
            <p class="text-xs text-gray-400 mb-5">
              Performa kamu sebagai penawar di platform.
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
              <div
                v-for="(s, i) in bidStats"
                :key="s.label"
                :class="[
                  'rounded-xl p-4 border',
                  i === 0
                    ? 'bg-black border-black'
                    : 'bg-gray-50 border-gray-100',
                ]"
              >
                <p
                  class="text-2xl font-bold"
                  :class="i === 0 ? 'text-white' : 'text-black'"
                >
                  {{ s.value }}
                </p>
                <p
                  class="text-xs mt-0.5"
                  :class="i === 0 ? 'text-white/50' : 'text-gray-400'"
                >
                  {{ s.label }}
                </p>
              </div>
            </div>

            <!-- Win rate bar -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
              <div class="flex justify-between items-center mb-2">
                <p class="text-xs font-semibold text-gray-700">
                  Tingkat Kemenangan
                </p>
                <p class="text-xs font-bold text-black">
                  {{ Math.round((6 / 52) * 100) }}%
                </p>
              </div>
              <div class="h-2 bg-gray-200 rounded-full">
                <div
                  class="h-2 bg-black rounded-full transition-all duration-700"
                  :style="{ width: Math.round((6 / 52) * 100) + '%' }"
                ></div>
              </div>
              <div class="flex justify-between mt-2">
                <p class="text-xs text-gray-400">Total Nilai Penawaran</p>
                <p class="text-xs font-semibold text-black">Rp 125.000.000</p>
              </div>
            </div>
          </div>
        </div>

        <!-- ════ PRESTASI ════ -->
        <div v-if="activeTab === 'prestasi'" class="space-y-5">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">Pencapaian</h2>
            <p class="text-xs text-gray-400 mb-5">
              Lencana yang kamu raih di platform ArtBid Bali.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="(ach, i) in achievements"
                :key="ach.name"
                :class="[
                  'flex items-center gap-4 p-4 rounded-xl border transition-all',
                  i === 0
                    ? 'bg-black border-black'
                    : 'bg-gray-50 border-gray-100',
                ]"
              >
                <div
                  :class="[
                    'w-11 h-11 rounded-xl flex items-center justify-center shrink-0',
                    i === 0 ? 'bg-white/15' : 'bg-white border border-gray-200',
                  ]"
                >
                  <svg
                    class="w-5 h-5"
                    :class="i === 0 ? 'text-white' : 'text-gray-700'"
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
                <div>
                  <p
                    class="text-sm font-semibold"
                    :class="i === 0 ? 'text-white' : 'text-black'"
                  >
                    {{ ach.name }}
                  </p>
                  <p
                    class="text-xs mt-0.5"
                    :class="i === 0 ? 'text-white/50' : 'text-gray-400'"
                  >
                    {{ ach.desc }}
                  </p>
                </div>
                <svg
                  class="w-4 h-4 ml-auto shrink-0"
                  :class="i === 0 ? 'text-white/60' : 'text-gray-300'"
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
            </div>
          </div>

          <!-- Account history -->
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">Riwayat Akun</h2>
            <p class="text-xs text-gray-400 mb-5">
              Ringkasan penggunaan akun kamu di platform.
            </p>
            <div class="grid grid-cols-2 gap-3">
              <div
                v-for="h in accountHistory"
                :key="h.label"
                class="bg-gray-50 rounded-xl p-4 border border-gray-100"
              >
                <p class="text-xs text-gray-400 mb-1">{{ h.label }}</p>
                <p class="text-sm font-bold text-black">{{ h.value }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- ════ KEAMANAN ════ -->
        <div v-if="activeTab === 'keamanan'" class="space-y-5">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">Keamanan Akun</h2>
            <p class="text-xs text-gray-400 mb-6">
              Kelola password dan pantau aktivitas login akun kamu.
            </p>

            <div class="space-y-4">
              <!-- Password row -->
              <div
                class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center justify-center"
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
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                      />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-black">Password</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      Terakhir diubah 3 bulan lalu
                    </p>
                  </div>
                </div>
                <button
                  @click="showPasswordModal = true"
                  class="px-3 py-1.5 border border-gray-200 text-gray-600 rounded-lg text-xs font-medium hover:border-black hover:text-black transition-all"
                >
                  Ubah Password
                </button>
              </div>

              <!-- Email verification -->
              <div
                class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center justify-center"
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
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                      />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-black">
                      Verifikasi Email
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ user.email }}</p>
                  </div>
                </div>
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full bg-gray-800 inline-block"
                  ></span>
                  Terverifikasi
                </span>
              </div>

              <!-- Last login -->
              <div
                class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center justify-center"
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
                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"
                      />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-black">
                      Perangkat Terakhir
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      Windows 11 – Google Chrome
                    </p>
                  </div>
                </div>
                <p class="text-xs text-gray-400 text-right">
                  15 Jun 2026<br />20.15 WITA
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- ════ PREFERENSI ════ -->
        <div v-if="activeTab === 'preferensi'" class="space-y-5">
          <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="text-sm font-bold text-black mb-1">
              Preferensi Notifikasi
            </h2>
            <p class="text-xs text-gray-400 mb-6">
              Atur notifikasi yang kamu terima dari platform.
            </p>

            <div class="space-y-4">
              <div
                v-for="(item, key) in {
                  notifOutbid: {
                    label: 'Notifikasi Outbid',
                    desc: 'Beritahu saat penawaranmu dilampaui pengguna lain.',
                  },
                  notifWinner: {
                    label: 'Notifikasi Pemenang Lelang',
                    desc: 'Beritahu saat kamu memenangkan lelang.',
                  },
                  notifAuctionEnd: {
                    label: 'Notifikasi Lelang Berakhir',
                    desc: 'Beritahu saat lelang yang kamu ikuti berakhir.',
                  },
                  newsletter: {
                    label: 'Newsletter Platform',
                    desc: 'Terima informasi terbaru dan penawaran eksklusif.',
                  },
                }"
                :key="key"
                class="flex items-start justify-between gap-4 py-3.5 border-b border-gray-50 last:border-0"
              >
                <div>
                  <p class="text-sm font-medium text-black">{{ item.label }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ item.desc }}</p>
                </div>
                <button
                  @click="prefs[key] = !prefs[key]"
                  :class="[
                    'toggle shrink-0 mt-0.5',
                    prefs[key] ? 'bg-black' : 'bg-gray-200',
                  ]"
                >
                  <span
                    :class="[
                      'toggle-dot',
                      prefs[key] ? 'translate-x-5' : 'translate-x-1',
                    ]"
                  ></span>
                </button>
              </div>
            </div>

            <div class="flex justify-end mt-6">
              <button @click="save" class="btn-primary">
                Simpan Preferensi
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── RIGHT SIDEBAR (1/3) ── -->
      <div class="space-y-4">
        <!-- Recent activity -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-black">Aktivitas Terbaru</h2>
            <div class="flex items-center gap-1.5">
              <span
                class="live-dot w-1.5 h-1.5 rounded-full bg-black inline-block"
              ></span>
              <span class="text-xs text-gray-400">Live</span>
            </div>
          </div>
          <div class="space-y-3">
            <div
              v-for="(act, i) in recentActivity"
              :key="i"
              class="flex items-start gap-3"
              :class="
                i < recentActivity.length - 1
                  ? 'pb-3 border-b border-gray-50'
                  : ''
              "
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

        <!-- Tingkat keaktifan card -->
        <div class="bg-black rounded-2xl p-5">
          <p class="text-white/50 text-xs uppercase tracking-widest mb-3">
            Tingkat Keaktifan
          </p>
          <p class="text-white font-bold text-2xl">Sangat Aktif</p>
          <p class="text-white/40 text-xs mt-1.5">
            Berdasarkan aktivitas 30 hari terakhir
          </p>
          <div
            class="mt-4 pt-4 border-t border-white/10 grid grid-cols-2 gap-3"
          >
            <div>
              <p class="text-white/40 text-xs mb-1">Total Penjualan</p>
              <p class="text-white text-sm font-semibold">15 Barang</p>
            </div>
            <div>
              <p class="text-white/40 text-xs mb-1">Bergabung</p>
              <p class="text-white text-sm font-semibold">356 Hari</p>
            </div>
          </div>
        </div>

        <!-- Quick actions -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
          <h2 class="text-sm font-semibold text-black mb-3">Aksi Cepat</h2>
          <div class="space-y-2">
            <button
              @click="activeTab = 'profil'"
              class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-all text-left"
            >
              <svg
                class="w-4 h-4 text-gray-400"
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
              Edit Profil
            </button>
            <button
              @click="showPasswordModal = true"
              class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-all text-left"
            >
              <svg
                class="w-4 h-4 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                />
              </svg>
              Ubah Password
            </button>
            <button
              @click="activeTab = 'preferensi'"
              class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-all text-left"
            >
              <svg
                class="w-4 h-4 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
              </svg>
              Kelola Preferensi
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════ CHANGE PASSWORD MODAL ═══════════════════ -->
    <transition name="fade-modal">
      <div
        v-if="showPasswordModal"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="showPasswordModal = false"
      >
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div
          class="relative bg-white rounded-2xl p-7 max-w-sm w-full shadow-xl"
        >
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="font-bold text-lg text-black">Ubah Password</h3>
              <p class="text-xs text-gray-400 mt-0.5">
                Pastikan password baru kamu cukup kuat.
              </p>
            </div>
            <button
              @click="showPasswordModal = false"
              class="w-8 h-8 flex items-center justify-center rounded-xl border border-gray-200 text-gray-400 hover:border-black hover:text-black transition-all"
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
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                >Password Saat Ini</label
              >
              <input
                v-model="passwords.current"
                type="password"
                class="field"
                placeholder="••••••••"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                >Password Baru</label
              >
              <input
                v-model="passwords.newPass"
                type="password"
                class="field"
                placeholder="••••••••"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                >Konfirmasi Password Baru</label
              >
              <input
                v-model="passwords.confirm"
                type="password"
                class="field"
                placeholder="••••••••"
              />
            </div>
          </div>
          <div class="flex gap-3 mt-6">
            <button
              @click="showPasswordModal = false"
              class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="
                showPasswordModal = false;
                save();
              "
              class="flex-1 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
            >
              Simpan
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

.field {
  width: 100%;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  padding: 0.625rem 1rem;
  font-size: 0.875rem;
  color: #111827;
  background: #f9fafb;
  outline: none;
  transition: border-color 0.15s;
}
.field:focus {
  border-color: #111827;
}
.field::placeholder {
  color: #9ca3af;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: #000;
  color: #fff;
  border-radius: 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background 0.15s;
  cursor: pointer;
  border: none;
}
.btn-primary:hover {
  background: #1f2937;
}

.toggle {
  position: relative;
  display: inline-flex;
  width: 2.75rem;
  height: 1.5rem;
  align-items: center;
  border-radius: 9999px;
  transition: background-color 0.3s;
  cursor: pointer;
  border: none;
}
.toggle-dot {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  background: white;
  border-radius: 9999px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s;
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

.slide-toast-enter-active {
  transition:
    opacity 0.25s ease,
    transform 0.25s ease;
}
.slide-toast-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}
.slide-toast-enter-from {
  opacity: 0;
  transform: translateY(-8px);
}
.slide-toast-leave-to {
  opacity: 0;
  transform: translateY(-8px);
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
