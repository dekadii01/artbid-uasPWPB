<script setup>
const steps = [
  {
    number: '01',
    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    title: 'Daftar Akun',
    description: 'Buat akun gratis dengan email atau Google. Verifikasi identitas cukup sekali.',
    bg: 'bg-gray-100',
    text: 'text-black',
    iconBg: 'bg-black',
    iconColor: 'text-white',
    numberColor: 'text-gray-300'
  },
  {
    number: '02',
    icon: 'M4 6h16M4 10h16M4 14h8M4 18h8',
    title: 'Pilih Lelang',
    description: 'Jelajahi karya seni yang sedang dilelang. Filter berdasarkan kategori, harga, atau seniman.',
    bg: 'bg-gray-100',
    text: 'text-black',
    iconBg: 'bg-black',
    iconColor: 'text-white',
    numberColor: 'text-gray-300'
  },
  {
    number: '03',
    icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    title: 'Ajukan Penawaran',
    description: 'Masukkan nominal tawaran dan konfirmasi. Pantau posisimu secara realtime di papan lelang.',
    bg: 'bg-gray-100',
    text: 'text-black',
    iconBg: 'bg-black',
    iconColor: 'text-white',
    numberColor: 'text-gray-300'
  },
  {
    number: '04',
    icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
    title: 'Menangkan Karya',
    description: 'Jika tawaran tertinggi, kamu otomatis dinyatakan menang dan panduan pengiriman karya dikirim ke emailmu.',
    bg: 'bg-black',
    text: 'text-white',
    iconBg: 'bg-white/15',
    iconColor: 'text-white',
    numberColor: 'text-white/10'
  }
]
</script>

<template>
  <section id="how-it-works" class="px-10 py-20">
    <div class="text-center mb-14">
      <span class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400">Panduan</span>
      <h2 class="text-4xl font-bold mt-3">Cara Mengikuti Lelang</h2>
      <p class="text-gray-500 mt-4 text-sm max-w-md mx-auto">
        Mulai dari pendaftaran hingga memenangkan karya impianmu — prosesnya mudah dan aman.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div
        v-for="step in steps"
        :key="step.number"
        :class="[step.bg, step.text, 'rounded-2xl p-7 card-lift relative overflow-hidden']"
      >
        <span :class="[step.numberColor, 'font-bold text-6xl absolute -top-2 right-4 select-none leading-none']">
          {{ step.number }}
        </span>
        <div :class="[step.iconBg, 'w-11 h-11 rounded-xl flex items-center justify-center mb-5 relative z-10']">
          <svg :class="[step.iconColor, 'w-5 h-5']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="step.icon" />
          </svg>
        </div>
        <h3 class="font-semibold text-base mb-2">{{ step.title }}</h3>
        <p :class="[step.number === '04' ? 'text-white/50' : 'text-gray-500', 'text-sm leading-relaxed']">
          {{ step.description }}
        </p>
      </div>
    </div>
  </section>
</template>
