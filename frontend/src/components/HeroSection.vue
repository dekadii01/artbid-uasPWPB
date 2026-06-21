<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";

const heroTotalSecs = ref(2 * 3600 + 47 * 60 + 33);
const heroH = computed(() =>
  String(Math.floor(heroTotalSecs.value / 3600)).padStart(2, "0"),
);
const heroM = computed(() =>
  String(Math.floor((heroTotalSecs.value % 3600) / 60)).padStart(2, "0"),
);
const heroS = computed(() => String(heroTotalSecs.value % 60).padStart(2, "0"));

const bidderAvatars = [
  "https://i.pravatar.cc/40?img=1",
  "https://i.pravatar.cc/40?img=5",
  "https://i.pravatar.cc/40?img=9",
];

const stats = [
  {
    value: "500+",
    label: "Kolektor Aktif",
    bg: "bg-gray-100",
    text: "text-black",
    labelColor: "text-gray-500",
  },
  {
    value: "1.200+",
    label: "Barang Terjual",
    bg: "bg-black",
    text: "text-white",
    labelColor: "text-white/60",
  },
  {
    value: "300+",
    label: "Lelang Berhasil",
    bg: "bg-gray-100",
    text: "text-black",
    labelColor: "text-gray-500",
  },
  {
    value: "95%",
    label: "Tingkat Kepuasan",
    bg: "bg-gray-200",
    text: "text-black",
    labelColor: "text-gray-500",
  },
];

let timer = null;

onMounted(() => {
  timer = setInterval(() => {
    if (heroTotalSecs.value > 0) heroTotalSecs.value--;
  }, 1000);
});

onUnmounted(() => {
  clearInterval(timer);
});
</script>

<template>
  <section id="home" class="pt-[72px] md:pt-[96px]">
    <div class="px-4 md:px-10 mx-auto min-h-[calc(100dvh-72px)] md:h-[calc(100dvh-96px)]">
      <div
        class="bg-cover bg-center min-h-[calc(100dvh-72px)] md:h-full rounded-lg relative overflow-hidden"
        style="background-image: url(&quot;/img/hero.png&quot;)"
      >
        <div
          class="absolute inset-0 bg-linear-to-br from-black/70 via-black/40 to-black/60 rounded-lg"
        ></div>

        <div class="relative p-4 md:p-10 min-h-[calc(100dvh-72px)] md:min-h-0 md:h-full flex flex-col justify-between">
          <div class="my-auto max-w-2xl">
            <h1
              class="text-white font-bold text-5xl md:text-7xl leading-none tracking-tight fade-up delay-2 mt-6"
            >
              Menangkan<br />Karya Seni<br />Terbaik Bali
            </h1>
            <p
              class="text-white/80 mt-6 text-lg leading-relaxed fade-up delay-3 max-w-md"
            >
              Platform lelang online eksklusif untuk kolektor dan seniman Bali.
              Ajukan penawaran secara realtime, tanpa refresh, dari mana saja.
            </p>
            <div class="flex flex-wrap gap-3 mt-8 fade-up delay-4">
              <button
                class="px-8 py-3.5 bg-white text-black rounded-lg font-medium hover:bg-gray-100 transition-colors text-sm"
              >
                Mulai Menawar
              </button>
              <button
                class="px-8 py-3.5 border border-white text-white rounded-lg font-medium hover:bg-white/10 transition-colors text-sm"
              >
                Lihat Lelang Aktif
              </button>
            </div>
          </div>

          <!-- Hero Bottom Bar -->
          <div class="p-5 fade-up delay-4">
            <div
              class="flex flex-col md:flex-row gap-4 md:items-center justify-between"
            >
              <div>
                <p class="text-white/60 text-xs mb-1">
                  Tawaran Tertinggi Saat Ini
                </p>
                <p class="text-white font-bold text-3xl">Rp 48.500.000</p>
                <p class="text-white/50 text-xs mt-1">
                  Karma Tanah Lot — I Wayan Sukerta
                </p>
              </div>
              <div class="flex gap-6">
                <div class="text-center">
                  <p class="text-white font-bold text-2xl">{{ heroH }}</p>
                  <p class="text-white/50 text-xs">Jam</p>
                </div>
                <div class="text-center">
                  <p class="text-white font-bold text-2xl">{{ heroM }}</p>
                  <p class="text-white/50 text-xs">Menit</p>
                </div>
                <div class="text-center">
                  <p class="text-white font-bold text-2xl">{{ heroS }}</p>
                  <p class="text-white/50 text-xs">Detik</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="flex -space-x-2">
                  <div
                    v-for="(avatar, i) in bidderAvatars"
                    :key="i"
                    class="w-8 h-8 rounded-full border-2 border-white/30 bg-gray-500 bg-cover bg-center"
                    :style="{ backgroundImage: `url('${avatar}')` }"
                  ></div>
                </div>
                <p class="text-white/60 text-xs">+24 penawar aktif</p>
              </div>
              <button
                class="px-6 py-2.5 bg-white text-black rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors whitespace-nowrap"
              >
                Ikuti Sekarang
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 px-4 py-10 md:p-10">
      <div
        v-for="stat in stats"
        :key="stat.label"
        :class="[stat.bg, 'px-6 py-8 rounded-lg card-lift', stat.text]"
      >
        <h2 class="font-bold text-3xl mb-1">{{ stat.value }}</h2>
        <p :class="[stat.labelColor, 'text-sm']">{{ stat.label }}</p>
      </div>
    </div>
  </section>
</template>
