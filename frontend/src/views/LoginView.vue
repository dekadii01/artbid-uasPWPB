<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from "vue";

// ─── Reactive state ───────────────────────────────────────────
const form = reactive({
  email: "",
  password: "",
  remember: false,
});

const showPassword = ref(false);
const isLoading = ref(false);
const errorMessage = ref("");
const fieldErrors = reactive({ email: "", password: "" });

// ─── Bid feed data ────────────────────────────────────────────
const bids = [
  {
    name: "Kol***udi",
    amount: "Rp 12.500.000",
    time: "2 dtk lalu",
    avatar: "https://i.pravatar.cc/32?img=3",
  },
  {
    name: "Bud***ari",
    amount: "Rp 12.000.000",
    time: "1 mnt lalu",
    avatar: "https://i.pravatar.cc/32?img=7",
  },
  {
    name: "Wiy***nto",
    amount: "Rp 11.500.000",
    time: "3 mnt lalu",
    avatar: "https://i.pravatar.cc/32?img=12",
  },
];

// ─── Countdown timer ─────────────────────────────────────────
const timer = reactive({ h: "01", m: "58", s: "44" });
let totalSecs = 1 * 3600 + 58 * 60 + 44;
let timerInterval = null;

function tickTimer() {
  if (totalSecs > 0) totalSecs--;
  timer.h = String(Math.floor(totalSecs / 3600)).padStart(2, "0");
  timer.m = String(Math.floor((totalSecs % 3600) / 60)).padStart(2, "0");
  timer.s = String(totalSecs % 60).padStart(2, "0");
}

// ─── Validation ──────────────────────────────────────────────
function validate() {
  fieldErrors.email = "";
  fieldErrors.password = "";
  let valid = true;

  if (!form.email) {
    fieldErrors.email = "Email wajib diisi.";
    valid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    fieldErrors.email = "Format email tidak valid.";
    valid = false;
  }

  if (!form.password) {
    fieldErrors.password = "Kata sandi wajib diisi.";
    valid = false;
  } else if (form.password.length < 6) {
    fieldErrors.password = "Kata sandi minimal 6 karakter.";
    valid = false;
  }

  return valid;
}

// ─── Submit handler ──────────────────────────────────────────
async function handleLogin() {
  errorMessage.value = "";
  if (!validate()) return;

  isLoading.value = true;

  try {
    // Replace with your actual API call, e.g.:
    // const { data } = await axios.post('/api/login', form)
    // router.push('/dashboard')

    await new Promise((resolve) => setTimeout(resolve, 1500)); // demo delay
    console.log("Login payload:", {
      email: form.email,
      password: form.password,
      remember: form.remember,
    });

    window.location.href = "/auctions"; // Redirect to auctions page after successful login
    // Example: uncomment below to simulate an auth error
    // throw new Error('Email atau kata sandi tidak cocok.')
  } catch (err) {
    errorMessage.value = err.message || "Terjadi kesalahan. Silakan coba lagi.";
  } finally {
    isLoading.value = false;
  }
}

// ─── Lifecycle ───────────────────────────────────────────────
onMounted(() => {
  timerInterval = setInterval(tickTimer, 1000);
});

onBeforeUnmount(() => {
  clearInterval(timerInterval);
});
</script>

<template>
  <div class="min-h-screen bg-white font-sans overflow-x-hidden flex">
    <div
      class="hidden lg:flex lg:w-1/2 relative flex-col justify-between p-12 bg-cover bg-center"
      style="background-image: url(&quot;/img/login.png&quot;)"
    >
      <div
        class="absolute inset-0 bg-linear-to-br from-black/75 via-black/50 to-black/70"
      ></div>

      <div class="relative z-10">
        <router-link
          to="/"
          class="text-2xl font-bold text-white tracking-tight"
        >
          ArtBid<span class="font-light">Bali</span>
        </router-link>
      </div>

      <div class="relative z-10 flex flex-col gap-6">
        <p class="text-white/40 text-xs leading-relaxed max-w-xs">
          "Bergabunglah dengan 500+ kolektor dan seniman Bali yang sudah
          menemukan ekosistem lelang seni yang adil dan aman."
        </p>
      </div>
    </div>

    <div
      class="flex-1 flex flex-col justify-center items-center px-6 py-12 lg:px-16"
    >
      <div class="lg:hidden mb-10 text-center">
        <span class="text-2xl font-bold tracking-tight"
          >ArtBid<span class="font-light">Bali</span></span
        >
        <p class="text-gray-400 text-xs mt-1">Platform Lelang Seni Eksklusif</p>
      </div>

      <div class="w-full max-w-sm">
        <div class="mb-8 fade-up delay-1">
          <h1 class="text-3xl font-bold tracking-tight text-black">
            Masuk ke Akunmu
          </h1>
          <p class="text-gray-500 text-sm mt-2">
            Ikuti lelang favoritmu secara realtime.
          </p>
        </div>

        <transition name="slide-fade">
          <div
            v-if="errorMessage"
            class="mb-6 flex items-start gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3"
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

        <form @submit.prevent="handleLogin" class="space-y-4 fade-up delay-2">
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
                :class="[
                  'w-full bg-white border rounded-lg pl-10 pr-4 py-3 text-sm outline-none transition-colors',
                  fieldErrors.email
                    ? 'border-gray-400 focus:border-black'
                    : 'border-gray-200 focus:border-black',
                ]"
              />
              <svg
                class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
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
            <div class="flex items-center justify-between mb-1.5">
              <label class="text-xs text-gray-500 font-medium"
                >Kata Sandi</label
              >
              <a
                href="#"
                class="text-xs text-gray-500 hover:text-black transition-colors"
                >Lupa kata sandi?</a
              >
            </div>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Masukkan kata sandi"
                autocomplete="current-password"
                :class="[
                  'w-full bg-white border rounded-lg pl-10 pr-10 py-3 text-sm outline-none transition-colors',
                  fieldErrors.password
                    ? 'border-gray-400 focus:border-black'
                    : 'border-gray-200 focus:border-black',
                ]"
              />
              <svg
                class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
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
          </div>

          <div class="flex items-center gap-2.5">
            <button
              type="button"
              @click="form.remember = !form.remember"
              :class="[
                'w-5 h-5 rounded flex items-center justify-center border transition-colors shrink-0',
                form.remember
                  ? 'bg-black border-black'
                  : 'bg-white border-gray-300 hover:border-gray-500',
              ]"
            >
              <svg
                v-if="form.remember"
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
            <span
              class="text-sm text-gray-600 select-none cursor-pointer"
              @click="form.remember = !form.remember"
            >
              Ingat saya selama 30 hari
            </span>
          </div>

          <button
            type="submit"
            :disabled="isLoading"
            class="w-full py-3.5 bg-black text-white rounded-lg font-medium hover:bg-gray-800 transition-all duration-300 text-sm flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
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
            <span>{{ isLoading ? "Memverifikasi..." : "Masuk" }}</span>
          </button>
        </form>

        <div class="relative my-6 fade-up delay-3">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-100"></div>
          </div>
          <div class="relative flex justify-center">
            <span class="bg-white px-3 text-xs text-gray-400"
              >atau masuk dengan</span
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

        <p class="text-center text-sm text-gray-500 mt-8 fade-up delay-4">
          Belum punya akun?
          <router-link
            to="/register"
            class="text-black font-medium hover:underline transition-all"
            >Daftar gratis</router-link
          >
        </p>

        <p class="text-center text-xs text-gray-400 mt-4">
          Dengan masuk, kamu menyetujui
          <a href="#" class="underline hover:text-gray-600 transition-colors"
            >Syarat & Ketentuan</a
          >
          dan
          <a href="#" class="underline hover:text-gray-600 transition-colors"
            >Kebijakan Privasi</a
          >
          kami.
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
</style>
