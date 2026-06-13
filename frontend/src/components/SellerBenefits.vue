<script setup>
const benefits = [
  {
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    title: 'Jangkau 500+ Kolektor Aktif',
    description: 'Karyamu langsung terlihat oleh komunitas kolektor berpengalaman yang siap menawar dengan serius.',
    bg: 'bg-white border border-gray-100 text-black',
    iconBg: 'bg-black',
    iconColor: 'text-white',
    descColor: 'text-gray-500'
  },
  {
    icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    title: 'Harga Terbaik Lewat Kompetisi',
    description: 'Sistem lelang memastikan karyamu terjual di harga pasar tertinggi, bukan harga tetap yang kamu pasang sendiri.',
    bg: 'bg-white border border-gray-100 text-black',
    iconBg: 'bg-black',
    iconColor: 'text-white',
    descColor: 'text-gray-500'
  },
  {
    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    title: 'Transaksi Aman & Terjamin',
    description: 'Dana ditahan di escrow hingga karya sampai ke tangan pemenang. Penjual dilindungi dari risiko penipuan.',
    bg: 'bg-white border border-gray-100 text-black',
    iconBg: 'bg-black',
    iconColor: 'text-white',
    descColor: 'text-gray-500'
  },
  {
    icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    title: 'Komisi Transparan, 0% Biaya Listing',
    description: 'Tidak ada biaya listing. Kami hanya mengambil komisi kecil saat karya berhasil terjual. Tidak ada kejutan.',
    bg: 'bg-black text-white',
    iconBg: 'bg-white/15',
    iconColor: 'text-white',
    descColor: 'text-white/50'
  }
]
</script>

<template>
  <section id="sellers" class="px-10 py-20 bg-gray-50 rounded-3xl mx-6">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
      <div>
        <span class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400">Untuk Seniman & Kolektor</span>
        <h2 class="text-4xl font-bold mt-3 leading-tight">Jual Karya Senimu<br>di Tempat yang Tepat</h2>
      </div>
      <p class="text-gray-500 max-w-sm text-sm leading-relaxed">
        ArtBid Bali menghubungkan seniman dengan ribuan kolektor serius yang siap membayar harga terbaik.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div
        v-for="benefit in benefits"
        :key="benefit.title"
        :class="[benefit.bg, 'rounded-2xl p-7 card-lift flex items-start gap-5']"
      >
        <div :class="[benefit.iconBg, 'w-12 h-12 rounded-xl flex items-center justify-center shrink-0']">
          <svg :class="[benefit.iconColor, 'w-6 h-6']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="benefit.icon" />
          </svg>
        </div>
        <div>
          <h3 class="font-semibold text-base mb-2">{{ benefit.title }}</h3>
          <p :class="[benefit.descColor, 'text-sm leading-relaxed']">
            {{ benefit.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
