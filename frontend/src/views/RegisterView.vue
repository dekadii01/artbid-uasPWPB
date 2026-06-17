<script setup>
import { ref, reactive, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";

const router = useRouter();
const auth = useAuthStore();

// ─── Constants ────────────────────────────────────────────────
const totalSteps = 3;

const benefits = [
  {
    label: "Lelang Realtime",
    desc: "Harga berubah langsung tanpa refresh halaman.",
    icon: "M13 10V3L4 14h7v7l9-11h-7z",
  },
  {
    label: "Transaksi Aman",
    desc: "Dana dilindungi escrow hingga karya diterima.",
    icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
  },
  {
    label: "0% Biaya Listing",
    desc: "Daftar gratis, komisi hanya saat terjual.",
    icon: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
];

const passwordRules = [
  { label: "Minimal 8 karakter", pass: (p) => p.length >= 8 },
  { label: "Mengandung huruf besar", pass: (p) => /[A-Z]/.test(p) },
  { label: "Mengandung angka", pass: (p) => /[0-9]/.test(p) },
  {
    label: "Mengandung karakter spesial (!@#)",
    pass: (p) => /[^A-Za-z0-9]/.test(p),
  },
];

const avatars = [
  "https://i.pravatar.cc/40?img=1",
  "https://i.pravatar.cc/40?img=5",
  "https://i.pravatar.cc/40?img=9",
  "https://i.pravatar.cc/40?img=14",
];

const panelStyle = {
  backgroundImage: "url('img/register.png')",
};

const liveCount = ref(12);

// ─── Step meta ───────────────────────────────────────────────
const stepMeta = {
  1: {
    title: "Buat Akun Baru",
    subtitle: "Isi informasi dasar untuk memulai.",
  },
  2: {
    title: "Buat Kata Sandi",
    subtitle: "Pilih kata sandi yang kuat dan aman.",
  },
  3: {
    title: "Hampir Selesai!",
    subtitle: "Tinjau dan setujui ketentuan kami.",
  },
  4: { title: "Akun Siap!", subtitle: "" },
};

// ─── State ───────────────────────────────────────────────────
const currentStep = ref(1);
const isLoading = ref(false);
const errorMessage = ref("");
const showPassword = ref(false);
const showConfirm = ref(false);

// Catatan: field 'role' & checkbox 'agreeTerms/agreeAge/agreeNewsletter'
// dihapus dari payload yang dikirim ke API — backend (tabel users)
// tidak punya kolom untuk ini. Checkbox tetap ada di UI untuk consent,
// tapi cukup divalidasi di frontend saja, tidak dikirim ke server.
const form = reactive({
  firstName: "",
  lastName: "",
  email: "",
  phone: "",
  password: "",
  confirmPassword: "",
  agreeTerms: false,
  agreeAge: false,
  agreeNewsletter: false,
});

const fieldErrors = reactive({
  firstName: "",
  lastName: "",
  email: "",
  phone: "",
  password: "",
  confirmPassword: "",
  agreeTerms: "",
  agreeAge: "",
});

// ─── Computed ────────────────────────────────────────────────
const stepTitle = computed(() => stepMeta[currentStep.value]?.title ?? "");
const stepSubtitle = computed(
  () => stepMeta[currentStep.value]?.subtitle ?? "",
);

const initials = computed(() =>
  `${form.firstName.charAt(0)}${form.lastName.charAt(0)}`.toUpperCase(),
);

const passwordStrength = computed(() => {
  const p = form.password;
  let score = 0;
  if (p.length >= 8) score++;
  if (/[A-Z]/.test(p)) score++;
  if (/[0-9]/.test(p)) score++;
  if (/[^A-Za-z0-9]/.test(p)) score++;
  return score;
});

const strengthColor = computed(() => {
  const map = {
    1: "bg-gray-400",
    2: "bg-gray-500",
    3: "bg-gray-700",
    4: "bg-black",
  };
  return map[passwordStrength.value] ?? "bg-gray-100";
});

const strengthTextColor = computed(() => {
  const map = {
    1: "text-gray-400",
    2: "text-gray-500",
    3: "text-gray-700",
    4: "text-black",
  };
  return map[passwordStrength.value] ?? "text-gray-300";
});

const strengthLabel = computed(() => {
  const map = { 1: "Lemah", 2: "Cukup", 3: "Kuat", 4: "Sangat Kuat" };
  return map[passwordStrength.value] ?? "";
});

// ─── Helpers ─────────────────────────────────────────────────
function inputClass(error) {
  return [
    "w-full bg-white border rounded-lg px-4 py-3 text-sm outline-none transition-colors",
    error
      ? "border-gray-400 focus:border-black"
      : "border-gray-200 focus:border-black",
  ].join(" ");
}

function clearErrors() {
  errorMessage.value = "";
  Object.keys(fieldErrors).forEach((k) => (fieldErrors[k] = ""));
}

// ─── Validation per step ─────────────────────────────────────
function validateStep1() {
  let ok = true;
  if (!form.firstName.trim()) {
    fieldErrors.firstName = "Nama depan wajib diisi.";
    ok = false;
  }
  if (!form.lastName.trim()) {
    fieldErrors.lastName = "Nama belakang wajib diisi.";
    ok = false;
  }
  if (!form.email) {
    fieldErrors.email = "Email wajib diisi.";
    ok = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    fieldErrors.email = "Format email tidak valid.";
    ok = false;
  }
  if (!form.phone.trim()) {
    fieldErrors.phone = "Nomor telepon wajib diisi.";
    ok = false;
  } else if (!/^[+\d\s-]{8,}$/.test(form.phone)) {
    fieldErrors.phone = "Format nomor telepon tidak valid.";
    ok = false;
  }
  return ok;
}

function validateStep2() {
  let ok = true;
  if (!form.password) {
    fieldErrors.password = "Kata sandi wajib diisi.";
    ok = false;
  } else if (form.password.length < 8) {
    fieldErrors.password = "Kata sandi minimal 8 karakter.";
    ok = false;
  }
  if (!form.confirmPassword) {
    fieldErrors.confirmPassword = "Konfirmasi kata sandi wajib diisi.";
    ok = false;
  } else if (form.password !== form.confirmPassword) {
    fieldErrors.confirmPassword = "Kata sandi tidak cocok.";
    ok = false;
  }
  return ok;
}

function validateStep3() {
  let ok = true;
  if (!form.agreeTerms) {
    fieldErrors.agreeTerms = "Kamu harus menyetujui syarat & ketentuan.";
    ok = false;
  }
  if (!form.agreeAge) {
    fieldErrors.agreeAge = "Kamu harus mengkonfirmasi usia minimal.";
    ok = false;
  }
  return ok;
}

// ─── Navigation ──────────────────────────────────────────────
function goNext() {
  clearErrors();
  const validators = { 1: validateStep1, 2: validateStep2 };
  const valid = validators[currentStep.value]?.() ?? true;
  if (valid) currentStep.value++;
}

function goBack() {
  clearErrors();
  if (currentStep.value > 1) currentStep.value--;
}

// ─── Submit ──────────────────────────────────────────────────
async function handleRegister() {
  clearErrors();
  if (!validateStep3()) return;

  isLoading.value = true;
  try {
    // Mapping field UI -> field yang diharapkan API (lihat RegisterRequest):
    // - confirmPassword (UI) -> password_confirmation (konvensi Laravel)
    // - role & checkbox consent TIDAK dikirim, backend tidak punya kolomnya
    await auth.register({
      firstName: form.firstName,
      lastName: form.lastName,
      email: form.email,
      phone: form.phone,
      password: form.password,
      password_confirmation: form.confirmPassword,
    });

    currentStep.value = 4;
  } catch (err) {
    if (err.response?.status === 422) {
      // Error validasi dari Laravel — map per-field ke fieldErrors lokal
      const errors = err.response.data.errors;

      if (errors?.firstName) fieldErrors.firstName = errors.firstName[0];
      if (errors?.lastName) fieldErrors.lastName = errors.lastName[0];
      if (errors?.email) fieldErrors.email = errors.email[0];
      if (errors?.phone) fieldErrors.phone = errors.phone[0];
      if (errors?.password) fieldErrors.password = errors.password[0];

      errorMessage.value = "Periksa kembali data yang kamu isi.";
    } else {
      errorMessage.value =
        err.response?.data?.message || "Terjadi kesalahan. Silakan coba lagi.";
    }
  } finally {
    isLoading.value = false;
  }
}

function goToDashboard() {
  router.push({ name: "Auctions" });
}
</script>

<template>
  <div class="min-h-screen bg-white font-sans overflow-x-hidden flex">
    <!-- ═══════════════════ LEFT PANEL — Brand / Visual ═══════════════════ -->
    <div
      class="hidden lg:flex lg:w-1/2 relative flex-col justify-between p-12 bg-cover bg-center bg-no-repeat"
      :style="panelStyle"
    >
      <div
        class="absolute inset-0 bg-linear-to-br from-black/75 via-black/50 to-black/70"
      ></div>

      <!-- Logo -->
      <div class="relative z-10">
        <router-link
          to="/"
          class="text-2xl font-bold text-white tracking-tight"
        >
          ArtBid<span class="font-light">Bali</span>
        </router-link>
      </div>

      <!-- Bottom content -->
      <div class="relative z-10 flex flex-col gap-6">
        <!-- Social proof -->
        <div class="flex items-center gap-3">
          <div class="flex -space-x-2">
            <div
              v-for="(avatar, i) in avatars"
              :key="i"
              class="w-8 h-8 rounded-full border-2 border-white/30 bg-gray-500 bg-cover bg-center"
              :style="{ backgroundImage: `url('${avatar}')` }"
            ></div>
          </div>
          <p class="text-white/50 text-xs">+500 kolektor sudah bergabung</p>
        </div>
      </div>
    </div>

    <!-- ═══════════════════ RIGHT PANEL — Register Form ═══════════════════ -->
    <div
      class="flex-1 flex flex-col justify-center items-center px-6 py-12 lg:px-16 overflow-y-auto"
    >
      <!-- Mobile logo -->
      <div class="lg:hidden mb-8 text-center">
        <span class="text-2xl font-bold tracking-tight"
          >ArtBid<span class="font-light">Bali</span></span
        >
        <p class="text-gray-400 text-xs mt-1">Platform Lelang Seni Eksklusif</p>
      </div>

      <div class="w-full max-w-sm">
        <!-- Step indicator -->
        <div class="flex items-center gap-2 mb-8 fade-up delay-1">
          <div
            v-for="n in totalSteps"
            :key="n"
            :class="[
              'h-1 rounded-full transition-all duration-500',
              n <= currentStep ? 'bg-black' : 'bg-gray-200',
              n === currentStep ? 'flex-2' : 'flex-1',
            ]"
          ></div>
        </div>

        <!-- Header -->
        <div class="mb-7 fade-up delay-1">
          <p
            class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400 mb-2"
          >
            Langkah {{ currentStep }} dari {{ totalSteps }}
          </p>
          <h1 class="text-3xl font-bold tracking-tight text-black">
            {{ stepTitle }}
          </h1>
          <p class="text-gray-500 text-sm mt-2">{{ stepSubtitle }}</p>
        </div>

        <!-- Error alert -->
        <transition name="slide-fade">
          <div
            v-if="errorMessage"
            class="mb-5 flex items-start gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3"
          >
            <svg
              class="w-4 h-4 text-gray-600 shrink-0 mt-0.5"
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
            <p class="text-gray-700 text-sm">{{ errorMessage }}</p>
          </div>
        </transition>

        <!-- ── STEP 1 — Informasi Akun ── -->
        <transition name="step-fade" mode="out-in">
          <form
            v-if="currentStep === 1"
            key="step1"
            @submit.prevent="goNext"
            class="space-y-4 fade-up delay-2"
          >
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-xs text-gray-500 mb-1.5 block font-medium"
                  >Nama Depan</label
                >
                <input
                  v-model="form.firstName"
                  type="text"
                  placeholder="Wayan"
                  autocomplete="given-name"
                  :class="inputClass(fieldErrors.firstName)"
                />
                <p
                  v-if="fieldErrors.firstName"
                  class="text-gray-500 text-xs mt-1"
                >
                  {{ fieldErrors.firstName }}
                </p>
              </div>
              <div>
                <label class="text-xs text-gray-500 mb-1.5 block font-medium"
                  >Nama Belakang</label
                >
                <input
                  v-model="form.lastName"
                  type="text"
                  placeholder="Sudira"
                  autocomplete="family-name"
                  :class="inputClass(fieldErrors.lastName)"
                />
                <p
                  v-if="fieldErrors.lastName"
                  class="text-gray-500 text-xs mt-1"
                >
                  {{ fieldErrors.lastName }}
                </p>
              </div>
            </div>

            <div>
              <label class="text-xs text-gray-500 mb-1.5 block font-medium"
                >Alamat Email</label
              >
              <div class="relative">
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="kamu@email.com"
                  autocomplete="email"
                  :class="inputClass(fieldErrors.email) + ' pl-10'"
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
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                  />
                </svg>
              </div>
              <p v-if="fieldErrors.email" class="text-gray-500 text-xs mt-1">
                {{ fieldErrors.email }}
              </p>
            </div>

            <div>
              <label class="text-xs text-gray-500 mb-1.5 block font-medium"
                >Nomor Telepon</label
              >
              <div class="relative">
                <input
                  v-model="form.phone"
                  type="tel"
                  placeholder="+62 812 0000 0000"
                  autocomplete="tel"
                  :class="inputClass(fieldErrors.phone) + ' pl-10'"
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
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                  />
                </svg>
              </div>
              <p v-if="fieldErrors.phone" class="text-gray-500 text-xs mt-1">
                {{ fieldErrors.phone }}
              </p>
            </div>

            <button
              type="submit"
              class="w-full py-3.5 bg-black text-white rounded-lg font-medium hover:bg-gray-800 transition-all duration-300 text-sm flex items-center justify-center gap-2"
            >
              Lanjut
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
            </button>
          </form>
        </transition>

        <!-- ── STEP 2 — Buat Kata Sandi ── -->
        <transition name="step-fade" mode="out-in">
          <form
            v-if="currentStep === 2"
            key="step2"
            @submit.prevent="goNext"
            class="space-y-4 fade-up delay-2"
          >
            <div>
              <label class="text-xs text-gray-500 mb-1.5 block font-medium"
                >Kata Sandi</label
              >
              <div class="relative">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Minimal 8 karakter"
                  autocomplete="new-password"
                  :class="inputClass(fieldErrors.password) + ' pl-10 pr-10'"
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
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                  />
                </svg>
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 transition-colors"
                >
                  <svg
                    v-if="!showPassword"
                    class="w-4 h-4"
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
                  <svg
                    v-else
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                    />
                  </svg>
                </button>
              </div>
              <p v-if="fieldErrors.password" class="text-gray-500 text-xs mt-1">
                {{ fieldErrors.password }}
              </p>

              <!-- Password strength -->
              <div v-if="form.password" class="mt-3 space-y-2">
                <div class="flex gap-1">
                  <div
                    v-for="n in 4"
                    :key="n"
                    :class="[
                      'h-1 flex-1 rounded-full transition-all duration-300',
                      passwordStrength >= n ? strengthColor : 'bg-gray-100',
                    ]"
                  ></div>
                </div>
                <p class="text-xs text-gray-400">
                  Kekuatan:
                  <span :class="strengthTextColor" class="font-medium">{{
                    strengthLabel
                  }}</span>
                </p>
              </div>
            </div>

            <div>
              <label class="text-xs text-gray-500 mb-1.5 block font-medium"
                >Konfirmasi Kata Sandi</label
              >
              <div class="relative">
                <input
                  v-model="form.confirmPassword"
                  :type="showConfirm ? 'text' : 'password'"
                  placeholder="Ulangi kata sandi"
                  autocomplete="new-password"
                  :class="
                    inputClass(fieldErrors.confirmPassword) + ' pl-10 pr-10'
                  "
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
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                  />
                </svg>
                <button
                  type="button"
                  @click="showConfirm = !showConfirm"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 transition-colors"
                >
                  <svg
                    v-if="!showConfirm"
                    class="w-4 h-4"
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
                  <svg
                    v-else
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                    />
                  </svg>
                </button>
              </div>
              <p
                v-if="fieldErrors.confirmPassword"
                class="text-gray-500 text-xs mt-1"
              >
                {{ fieldErrors.confirmPassword }}
              </p>
            </div>

            <!-- Password rules -->
            <div class="bg-gray-50 rounded-xl p-4 space-y-2">
              <p class="text-xs text-gray-400 font-medium mb-1">
                Syarat kata sandi:
              </p>
              <div
                v-for="rule in passwordRules"
                :key="rule.label"
                class="flex items-center gap-2"
              >
                <svg
                  :class="[
                    'w-3.5 h-3.5 shrink-0 transition-colors',
                    rule.pass(form.password)
                      ? 'text-gray-800'
                      : 'text-gray-300',
                  ]"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.5"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
                <span
                  :class="[
                    'text-xs transition-colors',
                    rule.pass(form.password)
                      ? 'text-gray-700'
                      : 'text-gray-400',
                  ]"
                >
                  {{ rule.label }}
                </span>
              </div>
            </div>

            <div class="flex gap-3">
              <button
                type="button"
                @click="goBack"
                class="flex-1 py-3.5 border border-gray-200 text-gray-600 rounded-lg font-medium hover:bg-gray-50 hover:border-gray-400 transition-all duration-300 text-sm"
              >
                Kembali
              </button>
              <button
                type="submit"
                class="flex-2 py-3.5 bg-black text-white rounded-lg font-medium hover:bg-gray-800 transition-all duration-300 text-sm flex items-center justify-center gap-2"
              >
                Lanjut
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
              </button>
            </div>
          </form>
        </transition>

        <!-- ── STEP 3 — Persetujuan & Submit ── -->
        <transition name="step-fade" mode="out-in">
          <form
            v-if="currentStep === 3"
            key="step3"
            @submit.prevent="handleRegister"
            class="space-y-4 fade-up delay-2"
          >
            <!-- Summary card -->
            <div
              class="bg-gray-50 rounded-2xl p-5 border border-gray-100 space-y-3"
            >
              <p
                class="text-xs text-gray-400 uppercase tracking-widest font-medium"
              >
                Ringkasan Akun
              </p>
              <div class="flex items-center gap-3">
                <div
                  class="w-12 h-12 bg-black rounded-xl flex items-center justify-center shrink-0"
                >
                  <span class="text-white font-bold text-lg">{{
                    initials
                  }}</span>
                </div>
                <div>
                  <p class="font-semibold text-sm text-black">
                    {{ form.firstName }} {{ form.lastName }}
                  </p>
                  <p class="text-gray-500 text-xs">{{ form.email }}</p>
                </div>
              </div>
            </div>

            <!-- Agreements -->
            <div class="space-y-3">
              <div class="flex items-start gap-3">
                <button
                  type="button"
                  @click="form.agreeTerms = !form.agreeTerms"
                  :class="[
                    'w-5 h-5 rounded flex items-center justify-center border transition-colors shrink-0 mt-0.5',
                    form.agreeTerms
                      ? 'bg-black border-black'
                      : 'bg-white border-gray-300 hover:border-gray-500',
                  ]"
                >
                  <svg
                    v-if="form.agreeTerms"
                    class="w-3 h-3 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </button>
                <p class="text-sm text-gray-600 leading-relaxed">
                  Saya menyetujui
                  <a href="#" class="text-black font-medium hover:underline"
                    >Syarat & Ketentuan</a
                  >
                  dan
                  <a href="#" class="text-black font-medium hover:underline"
                    >Kebijakan Privasi</a
                  >
                  ArtBid Bali.
                </p>
              </div>
              <p
                v-if="fieldErrors.agreeTerms"
                class="text-gray-500 text-xs pl-8"
              >
                {{ fieldErrors.agreeTerms }}
              </p>

              <div class="flex items-start gap-3">
                <button
                  type="button"
                  @click="form.agreeAge = !form.agreeAge"
                  :class="[
                    'w-5 h-5 rounded flex items-center justify-center border transition-colors shrink-0 mt-0.5',
                    form.agreeAge
                      ? 'bg-black border-black'
                      : 'bg-white border-gray-300 hover:border-gray-500',
                  ]"
                >
                  <svg
                    v-if="form.agreeAge"
                    class="w-3 h-3 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </button>
                <p class="text-sm text-gray-600 leading-relaxed">
                  Saya menyatakan bahwa saya berusia minimal 17 tahun dan
                  memiliki kapasitas hukum untuk mengikuti lelang.
                </p>
              </div>
              <p v-if="fieldErrors.agreeAge" class="text-gray-500 text-xs pl-8">
                {{ fieldErrors.agreeAge }}
              </p>

              <div class="flex items-start gap-3">
                <button
                  type="button"
                  @click="form.agreeNewsletter = !form.agreeNewsletter"
                  :class="[
                    'w-5 h-5 rounded flex items-center justify-center border transition-colors shrink-0 mt-0.5',
                    form.agreeNewsletter
                      ? 'bg-black border-black'
                      : 'bg-white border-gray-300 hover:border-gray-500',
                  ]"
                >
                  <svg
                    v-if="form.agreeNewsletter"
                    class="w-3 h-3 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </button>
                <p class="text-sm text-gray-600 leading-relaxed">
                  Kirimkan saya notifikasi lelang baru dan penawaran eksklusif.
                  <span class="text-gray-400">(Opsional)</span>
                </p>
              </div>
            </div>

            <div class="flex gap-3">
              <button
                type="button"
                @click="goBack"
                class="flex-1 py-3.5 border border-gray-200 text-gray-600 rounded-lg font-medium hover:bg-gray-50 hover:border-gray-400 transition-all duration-300 text-sm"
              >
                Kembali
              </button>
              <button
                type="submit"
                :disabled="isLoading"
                class="flex-2 py-3.5 bg-black text-white rounded-lg font-medium hover:bg-gray-800 transition-all duration-300 text-sm flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                <svg
                  v-if="isLoading"
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
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                  ></path>
                </svg>
                <span>{{ isLoading ? "Mendaftarkan..." : "Buat Akun" }}</span>
              </button>
            </div>
          </form>
        </transition>

        <!-- ── STEP 4 — Success ── -->
        <transition name="step-fade" mode="out-in">
          <div
            v-if="currentStep === 4"
            key="step4"
            class="text-center fade-up delay-2"
          >
            <div
              class="w-16 h-16 bg-black rounded-2xl flex items-center justify-center mx-auto mb-6"
            >
              <svg
                class="w-8 h-8 text-white"
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
            </div>
            <h2 class="text-2xl font-bold text-black mb-2">
              Akun Berhasil Dibuat!
            </h2>
            <p class="text-gray-500 text-sm leading-relaxed mb-2">
              Selamat datang,
              <span class="font-semibold text-black">{{ form.firstName }}</span
              >!
            </p>
            <p class="text-gray-400 text-xs mb-8">
              Kami mengirim email verifikasi ke
              <span class="text-gray-600 font-medium">{{ form.email }}</span
              >. Periksa kotak masukmu.
            </p>
            <button
              type="button"
              class="w-full py-3.5 bg-black text-white rounded-lg font-medium hover:bg-gray-800 transition-all duration-300 text-sm"
              @click="goToDashboard"
            >
              Mulai Menawar
            </button>
            <p class="text-gray-400 text-xs mt-4">
              Belum menerima email?
              <button
                type="button"
                class="text-black font-medium hover:underline"
              >
                Kirim ulang
              </button>
            </p>
          </div>
        </transition>

        <!-- Divider + social (step 1 only) -->
        <template v-if="currentStep === 1">
          <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-100"></div>
            </div>
            <div class="relative flex justify-center">
              <span class="bg-white px-3 text-xs text-gray-400"
                >atau daftar dengan</span
              >
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 fade-up delay-3">
            <button
              type="button"
              class="flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all duration-300"
            >
              <svg class="w-4 h-4" viewBox="0 0 24 24">
                <path
                  fill="#4285F4"
                  d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                />
                <path
                  fill="#34A853"
                  d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                />
                <path
                  fill="#FBBC05"
                  d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                />
                <path
                  fill="#EA4335"
                  d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                />
              </svg>
              Google
            </button>
            <button
              type="button"
              class="flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all duration-300"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                />
              </svg>
              Facebook
            </button>
          </div>
        </template>

        <!-- Login link (hidden on success) -->
        <p
          v-if="currentStep < 4"
          class="text-center text-sm text-gray-500 mt-8 fade-up delay-4"
        >
          Sudah punya akun?
          <router-link
            to="/login"
            class="text-black font-medium hover:underline transition-all"
            >Masuk di sini</router-link
          >
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.font-sans {
  font-family: "DM Sans", sans-serif;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(24px);
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
  animation-delay: 0.1s;
  opacity: 0;
}
.delay-2 {
  animation-delay: 0.22s;
  opacity: 0;
}
.delay-3 {
  animation-delay: 0.34s;
  opacity: 0;
}
.delay-4 {
  animation-delay: 0.46s;
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

.slide-fade-enter-active {
  transition: all 0.3s ease;
}
.slide-fade-leave-active {
  transition: all 0.2s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.step-fade-enter-active {
  transition:
    opacity 0.25s ease,
    transform 0.25s ease;
}
.step-fade-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}
.step-fade-enter-from {
  opacity: 0;
  transform: translateX(16px);
}
.step-fade-leave-to {
  opacity: 0;
  transform: translateX(-16px);
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
.animate-spin {
  animation: spin 0.8s linear infinite;
}
</style>
