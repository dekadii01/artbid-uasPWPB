<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const features = [
  {
    icon: 'M13 10V3L4 14h7v7l9-11h-7z',
    title: 'Update Harga Instan',
    description: 'Harga berubah secara langsung di layar semua peserta saat tawaran baru masuk.',
  },
  {
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    title: 'Countdown Otomatis',
    description: 'Timer mundur presisi hingga detik terakhir — diperpanjang otomatis saat tawaran terakhir masuk.',
  },
  {
    icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
    title: 'Notifikasi Tawaran Baru',
    description: 'Terima notifikasi push dan in-app setiap ada tawaran yang mengalahkan penawaranmu.',
  },
  {
    icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    title: 'Pengumuman Pemenang Otomatis',
    description: 'Pemenang diumumkan secara otomatis dan langsung mendapatkan instruksi transaksi.',
  },
]

const bidActivities = [
  { avatar: 'https://i.pravatar.cc/32?img=3', name: 'Kol***udi', amount: 'Rp 12.500.000', amountClass: 'text-white', time: '2 dtk lalu' },
  { avatar: 'https://i.pravatar.cc/32?img=7', name: 'Bud***ari', amount: 'Rp 12.000.000', amountClass: 'text-white/60', time: '1 mnt lalu' },
  { avatar: 'https://i.pravatar.cc/32?img=12', name: 'Wiy***nto', amount: 'Rp 11.500.000', amountClass: 'text-white/60', time: '3 mnt lalu' },
  { avatar: 'https://i.pravatar.cc/32?img=15', name: 'Sar***dewi', amount: 'Rp 11.000.000', amountClass: 'text-white/60', time: '5 mnt lalu' },
]

const rtSecs = ref(44)
let timer = null

onMounted(() => {
  timer = setInterval(() => {
    rtSecs.value = rtSecs.value > 0 ? rtSecs.value - 1 : 59
  }, 1000)
})

onUnmounted(() => {
  clearInterval(timer)
})
</script>

<template>
  <section class="px-10 py-20 bg-black rounded-3xl mx-6 overflow-hidden relative">
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
      <div>
        <span class="text-xs font-semibold tracking-[0.2em] uppercase text-white/40">Pengalaman Realtime</span>
        <h2 class="text-4xl font-bold mt-3 text-white leading-tight">Bersaing Secara<br>Langsung, Tanpa Jeda</h2>
        <p class="text-white/50 mt-5 leading-relaxed text-sm">
          Teknologi WebSocket kami memastikan setiap tawaran, pergerakan harga, dan setiap detik countdown terjadi
          secara instan — tanpa perlu menyegarkan halaman.
        </p>

        <div class="mt-10 space-y-5">
          <div v-for="feature in features" :key="feature.title" class="flex items-start gap-4">
            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center shrink-0 mt-0.5">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon" />
              </svg>
            </div>
            <div>
              <p class="text-white font-medium text-sm">{{ feature.title }}</p>
              <p class="text-white/40 text-xs mt-1 leading-relaxed">{{ feature.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Live bid panel mockup -->
      <div class="bg-white/8 border border-white/10 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-5">
          <div>
            <p class="text-white font-semibold text-sm">Harmoni Semesta</p>
            <p class="text-white/40 text-xs">I Made Wijaya · Lelang #AB-2847</p>
          </div>
          <div class="flex items-center gap-2 bg-white/10 rounded-full px-3 py-1">
            <span class="live-dot w-1.5 h-1.5 rounded-full bg-white inline-block"></span>
            <span class="text-white text-xs">Live</span>
          </div>
        </div>

        <div class="bg-white/8 rounded-xl p-4 mb-4 text-center">
          <p class="text-white/40 text-xs mb-1">Tawaran Tertinggi</p>
          <p class="text-white font-bold text-4xl">Rp 12.500.000</p>
          <div class="flex justify-center gap-6 mt-3">
            <div class="text-center">
              <p class="text-white font-bold text-xl">01</p>
              <p class="text-white/40 text-xs">Jam</p>
            </div>
            <div class="text-center">
              <p class="text-white font-bold text-xl">58</p>
              <p class="text-white/40 text-xs">Menit</p>
            </div>
            <div class="text-center">
              <p class="text-white font-bold text-xl">{{ String(rtSecs).padStart(2, '0') }}</p>
              <p class="text-white/40 text-xs">Detik</p>
            </div>
          </div>
        </div>

        <p class="text-white/40 text-xs mb-3 uppercase tracking-widest">Aktivitas Tawaran</p>
        <div class="space-y-2">
          <div
            v-for="bid in bidActivities"
            :key="bid.name"
            class="flex items-center justify-between bg-white/5 rounded-lg px-3 py-2.5"
          >
            <div class="flex items-center gap-2.5">
              <div
                class="w-7 h-7 rounded-full bg-gray-500 bg-cover bg-center"
                :style="{ backgroundImage: `url('${bid.avatar}')` }"
              ></div>
              <span class="text-white/70 text-xs">{{ bid.name }}</span>
            </div>
            <span :class="[bid.amountClass, 'text-xs font-semibold']">{{ bid.amount }}</span>
            <span class="text-white/30 text-xs">{{ bid.time }}</span>
          </div>
        </div>

        <button class="mt-5 w-full py-3 bg-white text-black rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
          Ajukan Tawaran
        </button>
      </div>
    </div>
  </section>
</template>
